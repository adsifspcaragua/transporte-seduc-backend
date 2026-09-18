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
 * TESTE FUNCIONAL do relatorio de frequencia.
 *
 * Regras sob teste:
 *  - percentual = presencas / (presencas + faltas); justificada fica de fora;
 *  - faltas consecutivas contam do fim do periodo para tras; justificada
 *    interrompe, pendente e ignorado.
 */
class RelatorioFrequenciaTest extends TestCase
{
    use RefreshDatabase;

    private const INICIO = '2026-09-01';

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse('2026-09-09 08:00:00'));
        $this->seed(RolePermissionSeeder::class);
    }

    private function usuario(string $perfil): User
    {
        $user = User::factory()->create(['ativo' => true]);
        $user->roles()->sync(Role::where('title', $perfil)->pluck('id'));

        return $user;
    }

    /**
     * Uma marcacao por dia a partir de INICIO, na ordem dada.
     *
     * @param  list<string>  $situacoes
     */
    private function historico(Estudante $estudante, Linha $linha, array $situacoes): void
    {
        foreach ($situacoes as $dia => $situacao) {
            $chamada = Chamada::firstOrCreate(
                ['linha_id' => $linha->id, 'data' => Carbon::parse(self::INICIO)->addDays($dia)->toDateString()],
                ['status' => Chamada::FECHADA],
            );

            Frequencia::factory()->create([
                'chamada_id' => $chamada->id,
                'estudante_id' => $estudante->id,
                'situacao' => $situacao,
            ]);
        }
    }

    private function relatorio(string $query = '')
    {
        return $this->getJson('/api/frequencias/relatorio?de='.self::INICIO.'&ate=2026-09-09'.$query)->assertOk();
    }

    /**
     * @return array<string, mixed>
     */
    private function linhaDo(Estudante $estudante, $resposta): array
    {
        return collect($resposta->json('data'))->firstWhere('estudante.id', $estudante->id);
    }

    public function test_resume_cada_estudante_com_quem_mais_falta_primeiro(): void
    {
        $linha = Linha::factory()->create();
        $faltoso = Estudante::factory()->create(['linha_id' => $linha->id]);
        $assiduo = Estudante::factory()->create(['linha_id' => $linha->id]);

        $this->historico($faltoso, $linha, ['Presente', 'Presente', 'Falta', 'Falta', 'Falta']);
        $this->historico($assiduo, $linha, ['Presente', 'Justificada', 'Presente', 'Falta', 'Presente']);

        Sanctum::actingAs($this->usuario('gestor'));

        $resposta = $this->relatorio();

        $this->assertSame($faltoso->id, $resposta->json('data.0.estudante.id'));
        $this->assertSame([
            'chamadas' => 5, 'presencas' => 2, 'faltas' => 3, 'justificadas' => 0, 'pendentes' => 0,
            'percentual_presenca' => 40, 'faltas_consecutivas' => 3,
        ], collect($this->linhaDo($faltoso, $resposta))->except('estudante')->all());

        $this->assertSame([
            'chamadas' => 5, 'presencas' => 3, 'faltas' => 1, 'justificadas' => 1, 'pendentes' => 0,
            'percentual_presenca' => 75, 'faltas_consecutivas' => 0,
        ], collect($this->linhaDo($assiduo, $resposta))->except('estudante')->all());

        $resposta->assertJsonPath('totais.faltas', 4)->assertJsonPath('totais.estudantes', 2);
    }

    public function test_justificada_interrompe_a_sequencia_e_fica_fora_do_percentual(): void
    {
        $linha = Linha::factory()->create();
        $estudante = Estudante::factory()->create(['linha_id' => $linha->id]);
        $this->historico($estudante, $linha, ['Falta', 'Justificada', 'Falta']);

        Sanctum::actingAs($this->usuario('gestor'));

        $linhaDoRelatorio = $this->linhaDo($estudante, $this->relatorio());

        $this->assertSame(1, $linhaDoRelatorio['faltas_consecutivas']);
        $this->assertEquals(0, $linhaDoRelatorio['percentual_presenca']);
        $this->assertSame(1, $linhaDoRelatorio['justificadas']);
    }

    public function test_pendente_nao_interrompe_a_sequencia(): void
    {
        $linha = Linha::factory()->create();
        $estudante = Estudante::factory()->create(['linha_id' => $linha->id]);
        $this->historico($estudante, $linha, ['Presente', 'Falta', 'Falta', 'Pendente']);

        Sanctum::actingAs($this->usuario('gestor'));

        $linhaDoRelatorio = $this->linhaDo($estudante, $this->relatorio());

        $this->assertSame(2, $linhaDoRelatorio['faltas_consecutivas']);
        $this->assertSame(1, $linhaDoRelatorio['pendentes']);
    }

    public function test_sem_marcacao_nao_ha_percentual(): void
    {
        $linha = Linha::factory()->create();
        $estudante = Estudante::factory()->create(['linha_id' => $linha->id]);
        $this->historico($estudante, $linha, ['Pendente', 'Justificada']);

        Sanctum::actingAs($this->usuario('gestor'));

        $this->assertNull($this->linhaDo($estudante, $this->relatorio())['percentual_presenca']);
    }

    public function test_filtra_quem_esta_com_faltas_seguidas(): void
    {
        $linha = Linha::factory()->create();
        $sumido = Estudante::factory()->create(['linha_id' => $linha->id]);
        $regular = Estudante::factory()->create(['linha_id' => $linha->id]);
        $this->historico($sumido, $linha, ['Presente', 'Falta', 'Falta', 'Falta']);
        $this->historico($regular, $linha, ['Falta', 'Falta', 'Falta', 'Presente']);

        Sanctum::actingAs($this->usuario('gestor'));

        $this->relatorio('&faltas_consecutivas_min=3')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.estudante.id', $sumido->id);
    }

    public function test_so_conta_o_periodo_pedido(): void
    {
        $linha = Linha::factory()->create();
        $estudante = Estudante::factory()->create(['linha_id' => $linha->id]);
        // 01 a 04 de setembro.
        $this->historico($estudante, $linha, ['Falta', 'Falta', 'Presente', 'Presente']);

        Sanctum::actingAs($this->usuario('gestor'));

        $this->getJson('/api/frequencias/relatorio?de=2026-09-03&ate=2026-09-09')
            ->assertOk()
            ->assertJsonPath('data.0.faltas', 0)
            ->assertJsonPath('data.0.presencas', 2);
    }

    public function test_filtra_por_linha(): void
    {
        $linhaA = Linha::factory()->create();
        $linhaB = Linha::factory()->create();
        $estudante = Estudante::factory()->create(['linha_id' => $linhaB->id]);
        // Trocou de linha: parte do historico em cada uma.
        $this->historico($estudante, $linhaA, ['Falta', 'Falta']);
        Chamada::factory()->create(['linha_id' => $linhaB->id, 'data' => '2026-09-05']);
        Frequencia::factory()->create([
            'chamada_id' => Chamada::where('linha_id', $linhaB->id)->value('id'),
            'estudante_id' => $estudante->id,
            'situacao' => 'Presente',
        ]);

        Sanctum::actingAs($this->usuario('gestor'));

        $this->relatorio("&linha_id={$linhaB->id}")
            ->assertJsonPath('data.0.chamadas', 1)
            ->assertJsonPath('data.0.faltas', 0);
    }

    public function test_historico_do_estudante_dia_a_dia(): void
    {
        $linha = Linha::factory()->create();
        $estudante = Estudante::factory()->create(['linha_id' => $linha->id]);
        $this->historico($estudante, $linha, ['Presente', 'Justificada', 'Falta']);
        Frequencia::where('situacao', 'Justificada')->update(['observacao' => 'Atestado']);

        Sanctum::actingAs($this->usuario('gestor'));

        $resposta = $this->getJson("/api/frequencias/estudantes/{$estudante->id}/relatorio?de=".self::INICIO)
            ->assertOk()
            ->assertJsonPath('data.estudante.id', $estudante->id)
            ->assertJsonPath('data.faltas', 1)
            ->assertJsonCount(3, 'data.historico');

        // Mais recente primeiro.
        $this->assertSame(
            ['2026-09-03', '2026-09-02', '2026-09-01'],
            array_column($resposta->json('data.historico'), 'data'),
        );
        $this->assertSame('Atestado', $resposta->json('data.historico.1.observacao'));
    }

    public function test_estudante_inexistente(): void
    {
        Sanctum::actingAs($this->usuario('gestor'));

        $this->getJson('/api/frequencias/estudantes/999999/relatorio')->assertNotFound();
    }

    public function test_motorista_so_ve_frequencia_das_linhas_que_conduz(): void
    {
        $motorista = $this->usuario('motorista');
        $minha = Linha::factory()->create(['motorista_id' => $motorista->id]);
        $alheia = Linha::factory()->create();
        $meu = Estudante::factory()->create(['linha_id' => $minha->id]);
        $alheio = Estudante::factory()->create(['linha_id' => $alheia->id]);
        $this->historico($meu, $minha, ['Falta']);
        $this->historico($alheio, $alheia, ['Falta']);

        Sanctum::actingAs($motorista);

        $this->relatorio()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.estudante.id', $meu->id);

        // Mesmo pedindo o estudante pelo id, nao aparece nada de linha alheia.
        $this->getJson("/api/frequencias/estudantes/{$alheio->id}/relatorio?de=".self::INICIO)
            ->assertOk()
            ->assertJsonPath('data.chamadas', 0);
    }

    public function test_periodo_padrao_sao_os_ultimos_trinta_dias(): void
    {
        Sanctum::actingAs($this->usuario('gestor'));

        $this->getJson('/api/frequencias/relatorio')
            ->assertOk()
            ->assertJsonPath('periodo', ['de' => '2026-08-11', 'ate' => '2026-09-09']);
    }

    public function test_intervalo_maior_que_um_ano_e_recusado(): void
    {
        Sanctum::actingAs($this->usuario('gestor'));

        $this->getJson('/api/frequencias/relatorio?de=2025-01-01&ate=2026-09-09')
            ->assertStatus(422)
            ->assertJsonValidationErrors('de');
    }
}
