<?php

namespace Tests\Feature\Frequencia;

use App\Models\Chamada;
use App\Models\Estudante;
use App\Models\Frequencia;
use App\Models\Justificativa;
use App\Models\Linha;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * TESTE FUNCIONAL das justificativas de falta.
 *
 * A justificativa nasce na chamada ("Justificada" com motivo) ou depois, para
 * uma falta ja registrada. A responsavel aprova (a falta e retirada) ou rejeita
 * (a falta volta a contar).
 */
class JustificativaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse('2026-09-09 08:00:00'));
        $this->seed(RolePermissionSeeder::class);
        Mail::fake();
    }

    private function usuario(string $perfil): User
    {
        $user = User::factory()->create(['ativo' => true]);
        $user->roles()->sync(Role::where('title', $perfil)->pluck('id'));

        return $user;
    }

    private function marcacao(string $situacao, ?Linha $linha = null, string $status = Chamada::FECHADA, string $data = '2026-09-08'): Frequencia
    {
        $linha ??= Linha::factory()->create();

        return Frequencia::factory()->create([
            'chamada_id' => Chamada::factory()->create(['linha_id' => $linha->id, 'status' => $status, 'data' => $data])->id,
            'estudante_id' => Estudante::factory()->create(['linha_id' => $linha->id])->id,
            'situacao' => $situacao,
        ]);
    }

    private function justificativa(string $status = Justificativa::EM_ANALISE, ?Frequencia $frequencia = null): Justificativa
    {
        $frequencia ??= $this->marcacao(Frequencia::JUSTIFICADA);

        return Justificativa::create([
            'frequencia_id' => $frequencia->id,
            'motivo' => 'Atestado médico',
            'status' => $status,
        ]);
    }

    private function marcar(Frequencia $frequencia, string $situacao, ?string $observacao = null)
    {
        return $this->putJson("/api/frequencias/chamadas/{$frequencia->chamada_id}", [
            'frequencias' => [array_filter([
                'estudante_id' => $frequencia->estudante_id,
                'situacao' => $situacao,
                'observacao' => $observacao,
            ])],
        ]);
    }

    public function test_marcar_justificada_na_chamada_envia_para_analise(): void
    {
        $motorista = $this->usuario('motorista');
        $frequencia = $this->marcacao(Frequencia::PENDENTE, Linha::factory()->create(['motorista_id' => $motorista->id]), Chamada::ABERTA);

        Sanctum::actingAs($motorista);

        $this->marcar($frequencia, 'Justificada', 'Consulta médica')
            ->assertOk()
            ->assertJsonPath('data.frequencias.0.justificativa.status', Justificativa::EM_ANALISE);

        $justificativa = Justificativa::first();
        $this->assertSame('Consulta médica', $justificativa->motivo);
        $this->assertSame($motorista->id, $justificativa->enviada_por);
        $this->assertSame($frequencia->id, $justificativa->frequencia_id);
    }

    public function test_trocar_justificada_antes_da_analise_retira_a_justificativa(): void
    {
        $gestor = $this->usuario('gestor');
        $frequencia = $this->marcacao(Frequencia::PENDENTE, status: Chamada::ABERTA);

        Sanctum::actingAs($gestor);

        $this->marcar($frequencia, 'Justificada', 'Consulta médica')->assertOk();
        $this->marcar($frequencia, 'Presente')->assertOk();

        $this->assertSame(0, Justificativa::count());
        $this->assertSame(Frequencia::PRESENTE, $frequencia->refresh()->situacao);
    }

    public function test_marcacao_com_justificativa_analisada_nao_muda_mais(): void
    {
        $frequencia = $this->marcacao(Frequencia::JUSTIFICADA, status: Chamada::ABERTA);
        $this->justificativa(Justificativa::APROVADA, $frequencia);

        Sanctum::actingAs($this->usuario('gestor'));

        $this->marcar($frequencia, 'Falta')
            ->assertStatus(422)
            ->assertJsonPath('bloqueados', [$frequencia->estudante_id]);

        $this->assertSame(Frequencia::JUSTIFICADA, $frequencia->refresh()->situacao);
    }

    public function test_justifica_falta_de_chamada_ja_fechada(): void
    {
        $frequencia = $this->marcacao(Frequencia::FALTA);

        Sanctum::actingAs($this->usuario('gestor'));

        $this->postJson('/api/frequencias/justificativas', [
            'frequencia_id' => $frequencia->id,
            'motivo' => 'Atestado entregue na secretaria',
        ])
            ->assertCreated()
            ->assertJsonPath('data.status', Justificativa::EM_ANALISE)
            ->assertJsonPath('data.falta.data', '2026-09-08');

        $this->assertSame(Frequencia::JUSTIFICADA, $frequencia->refresh()->situacao);
    }

    public function test_so_falta_pode_ser_justificada(): void
    {
        $frequencia = $this->marcacao(Frequencia::PRESENTE);

        Sanctum::actingAs($this->usuario('gestor'));

        $this->postJson('/api/frequencias/justificativas', ['frequencia_id' => $frequencia->id, 'motivo' => 'Qualquer'])
            ->assertStatus(422);
    }

    public function test_falta_com_justificativa_rejeitada_nao_e_justificada_de_novo(): void
    {
        $frequencia = $this->marcacao(Frequencia::FALTA);
        $this->justificativa(Justificativa::REJEITADA, $frequencia);

        Sanctum::actingAs($this->usuario('gestor'));

        $this->postJson('/api/frequencias/justificativas', ['frequencia_id' => $frequencia->id, 'motivo' => 'Outro motivo'])
            ->assertStatus(409);
    }

    public function test_fila_traz_as_pendentes_primeiro_e_o_total(): void
    {
        $aprovada = $this->justificativa(Justificativa::APROVADA);
        $this->travel(1)->minutes();
        $primeira = $this->justificativa();
        $this->travel(1)->minutes();
        $segunda = $this->justificativa();

        Sanctum::actingAs($this->usuario('gestor'));

        $resposta = $this->getJson('/api/frequencias/justificativas')
            ->assertOk()
            ->assertJsonPath('em_analise', 2);

        $this->assertSame([$primeira->id, $segunda->id, $aprovada->id], array_column($resposta->json('data'), 'id'));

        $this->getJson('/api/frequencias/justificativas?status=Aprovada')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $aprovada->id);
    }

    public function test_aprovar_retira_a_falta(): void
    {
        $gestor = $this->usuario('gestor');
        $justificativa = $this->justificativa();

        Sanctum::actingAs($gestor);

        $this->putJson("/api/frequencias/justificativas/{$justificativa->id}/analise", ['decisao' => 'Aprovada'])
            ->assertOk()
            ->assertJsonPath('data.status', Justificativa::APROVADA)
            ->assertJsonPath('data.falta.situacao', Frequencia::JUSTIFICADA)
            ->assertJsonPath('data.analisada_por.id', $gestor->id);

        $this->assertNotNull($justificativa->refresh()->analisada_em);
    }

    public function test_rejeitar_exige_parecer_e_a_falta_volta_a_contar(): void
    {
        $justificativa = $this->justificativa();

        Sanctum::actingAs($this->usuario('gestor'));

        $url = "/api/frequencias/justificativas/{$justificativa->id}/analise";

        $this->putJson($url, ['decisao' => 'Rejeitada'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('parecer');

        $this->putJson($url, ['decisao' => 'Rejeitada', 'parecer' => 'Atestado sem data.'])
            ->assertOk()
            ->assertJsonPath('data.status', Justificativa::REJEITADA)
            ->assertJsonPath('data.parecer', 'Atestado sem data.')
            ->assertJsonPath('data.falta.situacao', Frequencia::FALTA);
    }

    public function test_justificativa_nao_e_analisada_duas_vezes(): void
    {
        $justificativa = $this->justificativa(Justificativa::APROVADA);

        Sanctum::actingAs($this->usuario('gestor'));

        $this->putJson("/api/frequencias/justificativas/{$justificativa->id}/analise", [
            'decisao' => 'Rejeitada',
            'parecer' => 'Mudei de ideia.',
        ])->assertStatus(409);

        $this->assertSame(Justificativa::APROVADA, $justificativa->refresh()->status);
    }

    public function test_aprovar_de_estudante_inativo_avisa_que_a_reativacao_e_manual(): void
    {
        $justificativa = $this->justificativa();
        $justificativa->frequencia->estudante->update(['status' => 'Inativo']);

        Sanctum::actingAs($this->usuario('gestor'));

        $this->putJson("/api/frequencias/justificativas/{$justificativa->id}/analise", ['decisao' => 'Aprovada'])
            ->assertOk()
            ->assertJsonStructure(['alerta']);

        $this->assertSame('Inativo', $justificativa->frequencia->estudante->refresh()->status);
    }

    public function test_motorista_justifica_falta_da_propria_linha_mas_nao_ve_a_fila(): void
    {
        $motorista = $this->usuario('motorista');
        $propria = $this->marcacao(Frequencia::FALTA, Linha::factory()->create(['motorista_id' => $motorista->id]));
        $alheia = $this->marcacao(Frequencia::FALTA);

        Sanctum::actingAs($motorista);

        $this->postJson('/api/frequencias/justificativas', ['frequencia_id' => $propria->id, 'motivo' => 'Avisou que estava doente'])
            ->assertCreated();
        $this->postJson('/api/frequencias/justificativas', ['frequencia_id' => $alheia->id, 'motivo' => 'Qualquer'])
            ->assertForbidden();

        $this->getJson('/api/frequencias/justificativas')->assertForbidden();
        $this->putJson('/api/frequencias/justificativas/'.Justificativa::first()->id.'/analise', ['decisao' => 'Aprovada'])
            ->assertForbidden();
    }

    public function test_operador_ve_a_fila_mas_nao_decide(): void
    {
        $justificativa = $this->justificativa();

        Sanctum::actingAs($this->usuario('operador'));

        $this->getJson('/api/frequencias/justificativas')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson("/api/frequencias/justificativas/{$justificativa->id}")->assertOk();
        $this->putJson("/api/frequencias/justificativas/{$justificativa->id}/analise", ['decisao' => 'Aprovada'])
            ->assertForbidden();
    }
}
