<?php

namespace Tests\Feature\Presenca;

use App\Models\HorarioViagem;
use App\Models\Linha;
use App\Models\RegistroAuditoria;
use App\Models\User;
use App\Models\Veiculo;
use App\Models\Viagem;
use App\Support\HorarioLocal;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Support\CenarioPresenca;
use Tests\TestCase;

/**
 * TESTE FUNCIONAL da Fase 5: a gestao configura vans, QR, atribuicoes,
 * horarios, viagens e geocercas sem depender do motorista, e cada mudanca
 * sensivel fica na auditoria.
 */
class GestaoTransporteTest extends TestCase
{
    use CenarioPresenca, RefreshDatabase;

    /** Segunda-feira. */
    private const DIA = '2026-10-19';

    private User $gestor;

    private Linha $linha;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->travelTo($this->local(self::DIA.' 09:00:00'));

        $this->gestor = $this->usuario('gestor');
        $this->linha = Linha::factory()->create();

        Sanctum::actingAs($this->gestor);
    }

    public function test_cadastro_da_van_entrega_o_qr_uma_unica_vez(): void
    {
        $resposta = $this->postJson('/api/veiculos', ['identificacao' => 'Van 01', 'placa' => 'ABC1D23'])
            ->assertCreated()
            ->assertJsonMissingPath('data.qr_token_hash');

        $token = $resposta->json('data.qr.token');
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9_-]{43}$/', $token);
        $this->assertStringEndsWith('/presenca/scan?t='.$token, $resposta->json('data.qr.url'));

        $van = Veiculo::sole();
        $this->assertSame(hash('sha256', $token), $van->qr_token_hash);

        $this->getJson("/api/veiculos/{$van->id}")
            ->assertOk()
            ->assertJsonMissingPath('data.qr')
            ->assertJsonMissingPath('data.qr_token_hash');

        $this->postJson('/api/veiculos', ['identificacao' => 'Van 01'])->assertStatus(422)->assertJsonValidationErrors('identificacao');
    }

    public function test_rotacionar_qr_invalida_o_anterior(): void
    {
        $van = Veiculo::factory()->create();
        $antigo = $van->gerarTokenQr();

        $this->postJson("/api/veiculos/{$van->id}/qr", [])->assertStatus(422)->assertJsonValidationErrors('motivo');
        $novo = $this->postJson("/api/veiculos/{$van->id}/qr", ['motivo' => 'Adesivo danificado pela chuva.'])
            ->assertOk()
            ->json('data.qr.token');

        $this->assertNull(Veiculo::peloToken($antigo));
        $this->assertSame($van->id, Veiculo::peloToken($novo)->id);
        $this->assertTrue(RegistroAuditoria::where('acao', 'veiculo.qr.rotacionar')->where('justificativa', 'Adesivo danificado pela chuva.')->exists());
    }

    public function test_atribuicao_de_um_dia_troca_a_van_das_viagens_que_nao_passaram(): void
    {
        $this->horario($this->linha);
        [$titular] = $this->vanDaLinha($this->linha);
        $reserva = Veiculo::factory()->create();

        $this->postJson('/api/viagens/gerar', ['de' => self::DIA, 'ate' => '2026-10-20'])->assertOk()->assertJsonPath('data.criadas', 2);

        $this->postJson('/api/veiculos/atribuicoes', [
            'veiculo_id' => $reserva->id,
            'linha_id' => $this->linha->id,
            'inicio' => self::DIA,
            'fim' => self::DIA,
            'motivo' => 'Titular em manutenção',
        ])->assertCreated()->assertJsonPath('conflitos', []);

        $this->assertSame($reserva->id, Viagem::whereDate('data', self::DIA)->value('veiculo_id'));
        $this->assertSame($titular->id, Viagem::whereDate('data', '2026-10-20')->value('veiculo_id'));

        $this->getJson('/api/veiculos/atribuicoes?data='.self::DIA)->assertOk()->assertJsonCount(2, 'data');
        $this->getJson('/api/veiculos')->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_alterar_horario_refaz_as_viagens_futuras(): void
    {
        $horario = $this->postJson('/api/horarios-viagem', [
            'linha_id' => $this->linha->id,
            'sentido' => HorarioViagem::IDA,
            'dias_semana' => [1, 2, 3, 4, 5],
            'horario_saida' => '17:30',
        ])->assertCreated()->assertJsonPath('data.janela_efetiva', ['antes_min' => 15, 'depois_min' => 15])->json('data.id');

        $this->assertSame('17:30:00', Viagem::whereDate('data', self::DIA)->value('horario_saida'));

        $this->patchJson("/api/horarios-viagem/{$horario}", ['horario_saida' => '18:00', 'janela_antes_min' => 20])->assertOk();

        $viagem = Viagem::whereDate('data', self::DIA)->sole();
        $this->assertSame('18:00:00', $viagem->horario_saida);
        $this->assertSame('2026-10-19 17:40:00', HorarioLocal::exibir($viagem->janela_inicio));

        $this->postJson('/api/horarios-viagem', ['linha_id' => $this->linha->id, 'sentido' => 'Lado', 'dias_semana' => [9], 'horario_saida' => '25:00'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['sentido', 'dias_semana.0', 'horario_saida']);
    }

    public function test_ajuste_de_viagem_exige_motivo_e_nao_deixa_a_van_em_duas_viagens(): void
    {
        $this->horario($this->linha);
        [$van] = $this->vanDaLinha($this->linha);
        $outraLinha = Linha::factory()->create();
        $this->horario($outraLinha, HorarioViagem::IDA, '19:00:00');
        $this->vanDaLinha($outraLinha);

        $this->postJson('/api/viagens/gerar', ['de' => self::DIA, 'ate' => self::DIA])->assertOk();
        $daLinha = Viagem::where('linha_id', $this->linha->id)->sole();
        $daOutra = Viagem::where('linha_id', $outraLinha->id)->sole();

        // Atraso: a janela anda, com motivo.
        $this->patchJson("/api/viagens/{$daLinha->id}", ['janela_fim' => self::DIA.' 18:10'])->assertStatus(422)->assertJsonValidationErrors('motivo');
        $this->patchJson("/api/viagens/{$daLinha->id}", ['janela_fim' => self::DIA.' 18:10', 'motivo' => 'Van atrasada no trânsito.'])->assertOk();

        // A van da linha nao pode cobrir uma viagem que sobrepoe a dela.
        $this->patchJson("/api/viagens/{$daOutra->id}", [
            'veiculo_id' => $van->id,
            'janela_inicio' => self::DIA.' 18:00',
            'motivo' => 'Tentativa de cobrir as duas.',
        ])->assertStatus(409)->assertJsonPath('conflito_viagem_id', $daLinha->id);

        $this->patchJson("/api/viagens/{$daLinha->id}", ['janela_inicio' => self::DIA.' 18:30', 'motivo' => 'Janela invertida.'])->assertStatus(422);

        $registro = RegistroAuditoria::where('acao', 'viagem.ajustar')->sole();
        $this->assertSame(self::DIA.' 17:45:00', $registro->dados['antes']['janela_fim']);
        $this->assertSame(self::DIA.' 18:10:00', $registro->dados['depois']['janela_fim']);
    }

    public function test_dia_sem_operacao_cancela_as_viagens_do_dia(): void
    {
        $this->horario($this->linha);
        $this->postJson('/api/viagens/gerar', ['de' => self::DIA, 'ate' => '2026-10-20'])->assertOk();

        $this->postJson('/api/dias-sem-operacao', ['data' => '2026-10-20', 'motivo' => 'Paralisação'])
            ->assertCreated()
            ->assertJsonPath('viagens_canceladas', 1);

        $this->assertSame(Viagem::CANCELADA, Viagem::whereDate('data', '2026-10-20')->value('status'));
        $this->assertSame(Viagem::PROGRAMADA, Viagem::whereDate('data', self::DIA)->value('status'));

        $this->getJson('/api/viagens?status=Cancelada')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_geracao_limitada_a_62_dias(): void
    {
        $this->postJson('/api/viagens/gerar', ['de' => self::DIA, 'ate' => '2026-12-20'])->assertOk();
        $this->postJson('/api/viagens/gerar', ['de' => self::DIA, 'ate' => '2026-12-21'])->assertStatus(422)->assertJsonValidationErrors('ate');
    }

    public function test_ponto_exige_raio_dentro_do_teto(): void
    {
        $base = ['nome' => 'Praça Central', 'latitude' => -23.62, 'longitude' => -45.41];

        $this->postJson('/api/pontos-embarque', $base)->assertStatus(422)->assertJsonValidationErrors('raio_metros');
        $this->postJson('/api/pontos-embarque', [...$base, 'raio_metros' => 1001])->assertStatus(422)->assertJsonValidationErrors('raio_metros');
        $ponto = $this->postJson('/api/pontos-embarque', [...$base, 'raio_metros' => 1000])->assertCreated()->json('data.id');

        $vinculo = ['linha_id' => $this->linha->id, 'ponto_id' => $ponto, 'sentido' => HorarioViagem::IDA, 'horario_previsto' => '17:40'];
        $this->postJson('/api/pontos-embarque/linhas', $vinculo)->assertCreated();
        $this->postJson('/api/pontos-embarque/linhas', $vinculo)->assertStatus(409);

        $this->getJson('/api/pontos-embarque/linhas?linha_id='.$this->linha->id)->assertOk()->assertJsonPath('data.0.ponto.raio_metros', 1000);

        $this->patchJson("/api/pontos-embarque/{$ponto}", ['raio_metros' => 300])->assertOk();
        $this->assertTrue(RegistroAuditoria::where('acao', 'ponto.atualizar')->exists());
    }

    public function test_remanejamento_temporario_de_linha(): void
    {
        $estudante = $this->estudanteComConta(Linha::factory()->create());

        $this->postJson('/api/autorizacoes-linha', [
            'estudante_id' => $estudante->id,
            'linha_id' => $this->linha->id,
            'inicio' => self::DIA,
            'fim' => '2026-10-18',
            'motivo' => 'Estágio',
        ])->assertStatus(422)->assertJsonValidationErrors('fim');

        $this->postJson('/api/autorizacoes-linha', [
            'estudante_id' => $estudante->id,
            'linha_id' => $this->linha->id,
            'inicio' => self::DIA,
            'fim' => '2026-10-23',
            'motivo' => 'Estágio na semana',
        ])->assertCreated();

        $this->getJson('/api/autorizacoes-linha?data=2026-10-23')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/autorizacoes-linha?data=2026-10-24')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_van_com_historico_nao_e_excluida(): void
    {
        $this->horario($this->linha);
        [$van] = $this->vanDaLinha($this->linha);
        $this->postJson('/api/viagens/gerar', ['de' => self::DIA, 'ate' => self::DIA])->assertOk();

        $this->deleteJson("/api/veiculos/{$van->id}")->assertStatus(409);

        $nova = Veiculo::factory()->create();
        $this->deleteJson("/api/veiculos/{$nova->id}")->assertOk();
    }

    public function test_auditoria_so_para_quem_tem_permissao(): void
    {
        $this->postJson('/api/veiculos', ['identificacao' => 'Van 01'])->assertCreated();

        $this->getJson('/api/auditoria?acao=veiculo.')
            ->assertOk()
            ->assertJsonPath('data.0.acao', 'veiculo.criar')
            ->assertJsonPath('data.0.user.id', $this->gestor->id);

        Sanctum::actingAs($this->usuario('operador'));
        $this->getJson('/api/auditoria')->assertForbidden();
    }

    public function test_matriz_de_permissoes(): void
    {
        $van = Veiculo::factory()->create();

        // Operador acompanha, mas nao altera a operacao.
        Sanctum::actingAs($this->usuario('operador'));
        $this->getJson('/api/veiculos')->assertOk();
        $this->getJson('/api/viagens')->assertOk();
        $this->getJson('/api/pontos-embarque')->assertOk();
        $this->postJson('/api/veiculos', ['identificacao' => 'Van 09'])->assertForbidden();
        $this->postJson("/api/veiculos/{$van->id}/qr", ['motivo' => 'Tentativa do operador.'])->assertForbidden();
        $this->postJson('/api/viagens/gerar', ['de' => self::DIA, 'ate' => self::DIA])->assertForbidden();

        foreach (['motorista', 'estudante'] as $perfil) {
            Sanctum::actingAs($this->usuario($perfil));
            $this->getJson('/api/veiculos')->assertForbidden();
            $this->getJson('/api/viagens')->assertForbidden();
            $this->getJson('/api/dispositivos')->assertForbidden();
            $this->getJson('/api/presencas/tentativas')->assertForbidden();
        }

        // Admin passa por tudo.
        Sanctum::actingAs($this->usuario('admin'));
        $this->postJson('/api/veiculos', ['identificacao' => 'Van 10'])->assertCreated();
    }
}
