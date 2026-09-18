<?php

namespace Tests\Feature\Frequencia;

use App\Models\Chamada;
use App\Models\Estudante;
use App\Models\Frequencia;
use App\Models\Linha;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * TESTE FUNCIONAL da chamada diaria.
 *
 * O motorista abre a folha da linha, marca presenca, falta ou falta justificada
 * e fecha. So alcanca as linhas que conduz; a secretaria alcanca todas.
 */
class ChamadaTest extends TestCase
{
    use RefreshDatabase;

    /** Quarta-feira: dia 3 na grade do estudante (0 = domingo). */
    private const HOJE = '2026-09-09';

    private const QUARTA = 3;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse(self::HOJE.' 08:00:00'));
        $this->seed(RolePermissionSeeder::class);
    }

    private function usuario(string $perfil): User
    {
        $user = User::factory()->create(['ativo' => true]);
        $user->roles()->sync(Role::where('title', $perfil)->pluck('id'));

        return $user;
    }

    private function linhaDe(?User $motorista = null): Linha
    {
        return Linha::factory()->create(['motorista_id' => $motorista?->id]);
    }

    /**
     * @param  list<int>  $dias
     */
    private function estudanteNa(Linha $linha, array $dias = [1, 2, 3, 4, 5], string $status = 'Ativo', ?string $nome = null): Estudante
    {
        return Estudante::factory()->create(array_filter([
            'linha_id' => $linha->id,
            'days_of_week' => $dias,
            'status' => $status,
            'name' => $nome,
        ]));
    }

    private function abrir(Linha $linha, string $data = self::HOJE)
    {
        return $this->postJson('/api/frequencias/chamadas', [
            'linha_id' => $linha->id,
            'data' => $data,
        ]);
    }

    public function test_abre_a_folha_so_com_quem_e_esperado_no_dia(): void
    {
        $motorista = $this->usuario('motorista');
        $linha = $this->linhaDe($motorista);

        $esperadoA = $this->estudanteNa($linha, nome: 'Ana');
        $esperadoB = $this->estudanteNa($linha, [self::QUARTA], nome: 'Bruno');
        $this->estudanteNa($linha, [1, 5]); // nao vai as quartas
        $this->estudanteNa($linha, status: 'Inativo'); // perdeu o beneficio
        $this->estudanteNa($this->linhaDe()); // outra linha

        Sanctum::actingAs($motorista);

        $resposta = $this->abrir($linha)->assertCreated();

        $this->assertSame([$esperadoA->id, $esperadoB->id], array_column($resposta->json('data.frequencias'), 'estudante_id'));
        $this->assertSame(['total' => 2, 'presentes' => 0, 'faltas' => 0, 'justificadas' => 0, 'pendentes' => 2], $resposta->json('data.contadores'));
        $this->assertSame(Chamada::ABERTA, $resposta->json('data.status'));
        $this->assertSame($motorista->id, Chamada::first()->registrada_por);
    }

    public function test_dia_da_semana_guardado_como_texto_tambem_conta(): void
    {
        $motorista = $this->usuario('motorista');
        $linha = $this->linhaDe($motorista);
        $this->estudanteNa($linha, [(string) self::QUARTA]);

        Sanctum::actingAs($motorista);

        $this->abrir($linha)->assertCreated()->assertJsonPath('data.contadores.total', 1);
    }

    public function test_nao_abre_folha_em_dia_que_ninguem_usa_o_transporte(): void
    {
        $motorista = $this->usuario('motorista');
        $linha = $this->linhaDe($motorista);
        $this->estudanteNa($linha, [1, 5]);

        Sanctum::actingAs($motorista);

        $this->abrir($linha)->assertStatus(422);

        $this->assertSame(0, Chamada::count());
    }

    public function test_nao_abre_folha_de_data_futura(): void
    {
        $motorista = $this->usuario('motorista');
        $linha = $this->linhaDe($motorista);
        $this->estudanteNa($linha);

        Sanctum::actingAs($motorista);

        $this->abrir($linha, '2026-09-10')
            ->assertStatus(422)
            ->assertJsonValidationErrors('data');
    }

    public function test_abrir_de_novo_retoma_a_mesma_folha(): void
    {
        $motorista = $this->usuario('motorista');
        $linha = $this->linhaDe($motorista);
        $this->estudanteNa($linha);

        Sanctum::actingAs($motorista);

        $primeira = $this->abrir($linha)->assertCreated()->json('data.id');
        $segunda = $this->abrir($linha)->assertOk()->json('data.id');

        $this->assertSame($primeira, $segunda);
        $this->assertSame(1, Chamada::count());
        $this->assertSame(1, Frequencia::count());
    }

    public function test_retomar_acerta_a_folha_de_hoje_com_a_linha_atual(): void
    {
        $motorista = $this->usuario('motorista');
        $linha = $this->linhaDe($motorista);
        $outraLinha = $this->linhaDe();

        $fica = $this->estudanteNa($linha);
        $saiSemMarcacao = $this->estudanteNa($linha);
        $saiJaMarcado = $this->estudanteNa($linha);

        Sanctum::actingAs($motorista);

        $chamadaId = $this->abrir($linha)->json('data.id');
        $this->putJson("/api/frequencias/chamadas/{$chamadaId}", [
            'frequencias' => [['estudante_id' => $saiJaMarcado->id, 'situacao' => Frequencia::PRESENTE]],
        ])->assertOk();

        // Depois de aberta a folha, a linha muda.
        $saiSemMarcacao->update(['linha_id' => $outraLinha->id]);
        $saiJaMarcado->update(['linha_id' => $outraLinha->id]);
        $chegou = $this->estudanteNa($linha);

        $naFolha = array_column($this->abrir($linha)->assertOk()->json('data.frequencias'), 'estudante_id');

        sort($naFolha);
        $esperado = [$fica->id, $saiJaMarcado->id, $chegou->id];
        sort($esperado);

        // Entra quem chegou, sai o pendente que saiu, fica a marcacao ja feita.
        $this->assertSame($esperado, $naFolha);
    }

    public function test_motorista_marca_presenca_falta_e_justificada(): void
    {
        $motorista = $this->usuario('motorista');
        $linha = $this->linhaDe($motorista);
        [$a, $b, $c] = [$this->estudanteNa($linha), $this->estudanteNa($linha), $this->estudanteNa($linha)];

        Sanctum::actingAs($motorista);

        $chamadaId = $this->abrir($linha)->json('data.id');

        $this->putJson("/api/frequencias/chamadas/{$chamadaId}", [
            'frequencias' => [
                ['estudante_id' => $a->id, 'situacao' => 'Presente'],
                ['estudante_id' => $b->id, 'situacao' => 'Falta'],
                ['estudante_id' => $c->id, 'situacao' => 'Justificada', 'observacao' => 'Atestado médico'],
            ],
        ])
            ->assertOk()
            ->assertJsonPath('data.contadores.presentes', 1)
            ->assertJsonPath('data.contadores.faltas', 1)
            ->assertJsonPath('data.contadores.justificadas', 1)
            ->assertJsonPath('data.contadores.pendentes', 0);

        $justificada = Frequencia::where('estudante_id', $c->id)->first();
        $this->assertSame('Atestado médico', $justificada->observacao);
        $this->assertSame($motorista->id, $justificada->marcada_por);
        $this->assertNotNull($justificada->marcada_em);
    }

    public function test_remarcar_substitui_a_marcacao_anterior(): void
    {
        $motorista = $this->usuario('motorista');
        $linha = $this->linhaDe($motorista);
        $estudante = $this->estudanteNa($linha);

        Sanctum::actingAs($motorista);

        $chamadaId = $this->abrir($linha)->json('data.id');
        $url = "/api/frequencias/chamadas/{$chamadaId}";

        $this->putJson($url, ['frequencias' => [['estudante_id' => $estudante->id, 'situacao' => 'Justificada', 'observacao' => 'Consulta']]]);
        $this->putJson($url, ['frequencias' => [['estudante_id' => $estudante->id, 'situacao' => 'Presente']]])->assertOk();

        $frequencia = Frequencia::where('estudante_id', $estudante->id)->first();
        $this->assertSame(Frequencia::PRESENTE, $frequencia->situacao);
        $this->assertNull($frequencia->observacao);
    }

    public function test_falta_justificada_exige_o_motivo(): void
    {
        $motorista = $this->usuario('motorista');
        $linha = $this->linhaDe($motorista);
        $estudante = $this->estudanteNa($linha);

        Sanctum::actingAs($motorista);

        $chamadaId = $this->abrir($linha)->json('data.id');

        $this->putJson("/api/frequencias/chamadas/{$chamadaId}", [
            'frequencias' => [['estudante_id' => $estudante->id, 'situacao' => 'Justificada']],
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('frequencias.0.observacao');
    }

    public function test_situacao_pendente_nao_pode_ser_marcada(): void
    {
        $motorista = $this->usuario('motorista');
        $linha = $this->linhaDe($motorista);
        $estudante = $this->estudanteNa($linha);

        Sanctum::actingAs($motorista);

        $chamadaId = $this->abrir($linha)->json('data.id');

        $this->putJson("/api/frequencias/chamadas/{$chamadaId}", [
            'frequencias' => [['estudante_id' => $estudante->id, 'situacao' => 'Pendente']],
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('frequencias.0.situacao');
    }

    public function test_estudante_fora_da_folha_e_ignorado(): void
    {
        $motorista = $this->usuario('motorista');
        $linha = $this->linhaDe($motorista);
        $naFolha = $this->estudanteNa($linha);
        $deOutraLinha = $this->estudanteNa($this->linhaDe());

        Sanctum::actingAs($motorista);

        $chamadaId = $this->abrir($linha)->json('data.id');

        $this->putJson("/api/frequencias/chamadas/{$chamadaId}", [
            'frequencias' => [
                ['estudante_id' => $naFolha->id, 'situacao' => 'Presente'],
                ['estudante_id' => $deOutraLinha->id, 'situacao' => 'Falta'],
            ],
        ])
            ->assertOk()
            ->assertJsonPath('ignorados', [$deOutraLinha->id]);

        $this->assertFalse(Frequencia::where('estudante_id', $deOutraLinha->id)->exists());
    }

    public function test_so_estudantes_fora_da_folha_e_recusado(): void
    {
        $motorista = $this->usuario('motorista');
        $linha = $this->linhaDe($motorista);
        $this->estudanteNa($linha);
        $deOutraLinha = $this->estudanteNa($this->linhaDe());

        Sanctum::actingAs($motorista);

        $chamadaId = $this->abrir($linha)->json('data.id');

        $this->putJson("/api/frequencias/chamadas/{$chamadaId}", [
            'frequencias' => [['estudante_id' => $deOutraLinha->id, 'situacao' => 'Falta']],
        ])->assertStatus(422);
    }

    public function test_nao_fecha_com_estudante_sem_marcacao(): void
    {
        $motorista = $this->usuario('motorista');
        $linha = $this->linhaDe($motorista);
        $a = $this->estudanteNa($linha);
        $b = $this->estudanteNa($linha);

        Sanctum::actingAs($motorista);

        $chamadaId = $this->abrir($linha)->json('data.id');
        $url = "/api/frequencias/chamadas/{$chamadaId}";

        $this->putJson($url, ['frequencias' => [['estudante_id' => $a->id, 'situacao' => 'Presente']]]);

        $this->patchJson("{$url}/fechar")
            ->assertStatus(422)
            ->assertJsonPath('pendentes', 1);

        $this->putJson($url, ['frequencias' => [['estudante_id' => $b->id, 'situacao' => 'Falta']]]);

        $this->patchJson("{$url}/fechar")
            ->assertOk()
            ->assertJsonPath('data.status', Chamada::FECHADA);

        $this->assertNotNull(Chamada::find($chamadaId)->fechada_em);
    }

    public function test_folha_fechada_so_aceita_marcacao_depois_de_reaberta(): void
    {
        $motorista = $this->usuario('motorista');
        $linha = $this->linhaDe($motorista);
        $estudante = $this->estudanteNa($linha);

        Sanctum::actingAs($motorista);

        $chamadaId = $this->abrir($linha)->json('data.id');
        $url = "/api/frequencias/chamadas/{$chamadaId}";
        $marcarFalta = ['frequencias' => [['estudante_id' => $estudante->id, 'situacao' => 'Falta']]];

        $this->putJson($url, ['frequencias' => [['estudante_id' => $estudante->id, 'situacao' => 'Presente']]]);
        $this->patchJson("{$url}/fechar")->assertOk();

        $this->putJson($url, $marcarFalta)->assertStatus(409);

        $this->patchJson("{$url}/reabrir")->assertOk()->assertJsonPath('data.status', Chamada::ABERTA);
        $this->putJson($url, $marcarFalta)->assertOk();

        $this->assertSame(Frequencia::FALTA, Frequencia::where('estudante_id', $estudante->id)->value('situacao'));
    }

    public function test_motorista_nao_alcanca_linha_que_nao_conduz(): void
    {
        $motorista = $this->usuario('motorista');
        $alheia = $this->linhaDe($this->usuario('motorista'));
        $estudante = $this->estudanteNa($alheia);
        $chamada = Chamada::factory()->create(['linha_id' => $alheia->id]);
        Frequencia::factory()->create(['chamada_id' => $chamada->id, 'estudante_id' => $estudante->id]);

        Sanctum::actingAs($motorista);

        $this->abrir($alheia)->assertForbidden();
        $this->getJson("/api/frequencias/chamadas/{$chamada->id}")->assertForbidden();
        $this->putJson("/api/frequencias/chamadas/{$chamada->id}", [
            'frequencias' => [['estudante_id' => $estudante->id, 'situacao' => 'Falta']],
        ])->assertForbidden();
        $this->patchJson("/api/frequencias/chamadas/{$chamada->id}/fechar")->assertForbidden();

        $this->assertSame(Frequencia::PENDENTE, Frequencia::first()->situacao);
    }

    public function test_motorista_so_lista_as_proprias_linhas_e_chamadas(): void
    {
        $motorista = $this->usuario('motorista');
        $minha = $this->linhaDe($motorista);
        $alheia = $this->linhaDe($this->usuario('motorista'));
        $minhaChamada = Chamada::factory()->create(['linha_id' => $minha->id]);
        Chamada::factory()->create(['linha_id' => $alheia->id]);

        Sanctum::actingAs($motorista);

        $this->getJson('/api/frequencias/linhas')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $minha->id)
            ->assertJsonPath('data.0.chamada_hoje.id', $minhaChamada->id);

        $this->getJson('/api/frequencias/chamadas')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $minhaChamada->id);
    }

    public function test_motorista_sem_linha_nao_ve_nada(): void
    {
        Chamada::factory()->create();

        Sanctum::actingAs($this->usuario('motorista'));

        $this->getJson('/api/frequencias/linhas')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/frequencias/chamadas')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_listagem_filtra_por_data_e_traz_contadores(): void
    {
        $linha = $this->linhaDe();
        $hoje = Chamada::factory()->create(['linha_id' => $linha->id]);
        Chamada::factory()->create(['linha_id' => $linha->id, 'data' => '2026-09-08']);
        Frequencia::factory()->create(['chamada_id' => $hoje->id, 'situacao' => Frequencia::FALTA]);
        Frequencia::factory()->create(['chamada_id' => $hoje->id, 'situacao' => Frequencia::PRESENTE]);

        Sanctum::actingAs($this->usuario('gestor'));

        $this->getJson('/api/frequencias/chamadas?data='.self::HOJE)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.contadores', [
                'total' => 2, 'presentes' => 1, 'faltas' => 1, 'justificadas' => 0, 'pendentes' => 0,
            ]);
    }

    public function test_secretaria_lanca_em_qualquer_linha(): void
    {
        $linha = $this->linhaDe($this->usuario('motorista'));
        $this->estudanteNa($linha);

        Sanctum::actingAs($this->usuario('gestor'));

        $this->abrir($linha)->assertCreated();
    }

    public function test_operador_ve_todas_mas_nao_lanca(): void
    {
        $linhaA = $this->linhaDe($this->usuario('motorista'));
        $linhaB = $this->linhaDe($this->usuario('motorista'));
        $this->estudanteNa($linhaA);
        Chamada::factory()->create(['linha_id' => $linhaA->id]);
        Chamada::factory()->create(['linha_id' => $linhaB->id]);

        Sanctum::actingAs($this->usuario('operador'));

        $this->getJson('/api/frequencias/chamadas')->assertOk()->assertJsonCount(2, 'data');
        $this->abrir($linhaA)->assertForbidden();
    }

    public function test_motorista_nao_ve_cadastro_de_estudantes(): void
    {
        Sanctum::actingAs($this->usuario('motorista'));

        $this->getJson('/api/estudantes')->assertForbidden();
        $this->getJson('/api/inscricoes')->assertForbidden();
    }

    public function test_so_a_secretaria_exclui_chamada(): void
    {
        $motorista = $this->usuario('motorista');
        $chamada = Chamada::factory()->create(['linha_id' => $this->linhaDe($motorista)->id]);
        Frequencia::factory()->create(['chamada_id' => $chamada->id]);

        Sanctum::actingAs($motorista);
        $this->deleteJson("/api/frequencias/chamadas/{$chamada->id}")->assertForbidden();

        Sanctum::actingAs($this->usuario('gestor'));
        $this->deleteJson("/api/frequencias/chamadas/{$chamada->id}")->assertOk();

        $this->assertSame(0, Chamada::count());
        $this->assertSame(0, Frequencia::count());
    }

    public function test_linha_com_chamada_nao_pode_ser_excluida(): void
    {
        $linha = $this->linhaDe();
        Chamada::factory()->create(['linha_id' => $linha->id]);

        Sanctum::actingAs($this->usuario('gestor'));

        $this->deleteJson("/api/linha/{$linha->id}")->assertStatus(409);

        $this->assertNotNull(Linha::find($linha->id));
    }

    public function test_so_usuario_motorista_pode_conduzir_a_linha(): void
    {
        $linha = $this->linhaDe();
        $operador = $this->usuario('operador');
        $motorista = $this->usuario('motorista');

        Sanctum::actingAs($this->usuario('gestor'));

        $this->putJson("/api/linha/{$linha->id}", ['max_capacity' => 40, 'motorista_id' => $operador->id])
            ->assertStatus(422)
            ->assertJsonValidationErrors('motorista_id');

        $this->putJson("/api/linha/{$linha->id}", ['max_capacity' => 40, 'motorista_id' => $motorista->id])
            ->assertOk()
            ->assertJsonPath('data.motorista.id', $motorista->id);
    }
}
