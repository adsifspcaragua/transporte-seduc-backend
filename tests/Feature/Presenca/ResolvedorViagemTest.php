<?php

namespace Tests\Feature\Presenca;

use App\Enums\CodigoPresenca;
use App\Exceptions\PresencaNegada;
use App\Models\AtribuicaoVeiculo;
use App\Models\AutorizacaoLinha;
use App\Models\DiaSemOperacao;
use App\Models\Estudante;
use App\Models\HorarioViagem;
use App\Models\Linha;
use App\Models\Veiculo;
use App\Models\Viagem;
use App\Services\Viagem\GeradorViagens;
use App\Services\Viagem\ResolvedorViagem;
use App\Support\HorarioLocal;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Support\CenarioPresenca;
use Tests\TestCase;

/**
 * TESTE FUNCIONAL da Fase 1: a viagem e deduzida da van lida, do estudante e
 * do relogio do servidor — nunca informada pelo frontend.
 *
 * Valores-limite da janela (17h15 e 17h45 para saida as 17h30 com 15/15),
 * ida x volta, feriado, troca de van, remanejamento e viagens sobrepostas.
 */
class ResolvedorViagemTest extends TestCase
{
    use CenarioPresenca, RefreshDatabase;

    /** Segunda-feira. */
    private const DIA = '2026-10-19';

    private Linha $linha;

    private Veiculo $van;

    private Estudante $estudante;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->travelTo($this->local(self::DIA.' 17:20:00'));

        $this->linha = Linha::factory()->create();
        $this->horario($this->linha, HorarioViagem::IDA, '17:30:00');
        $this->horario($this->linha, HorarioViagem::VOLTA, '22:30:00');
        [$this->van] = $this->vanDaLinha($this->linha);
        $this->estudante = $this->estudanteComConta($this->linha);
    }

    private function resolver(?Veiculo $van = null, ?Estudante $estudante = null, ?string $hora = null): Viagem
    {
        return app(ResolvedorViagem::class)->resolver(
            $van ?? $this->van,
            $estudante ?? $this->estudante,
            $hora ? $this->local($hora) : now(),
        );
    }

    private function assertNegada(CodigoPresenca $esperado, callable $acao): PresencaNegada
    {
        try {
            $acao();
        } catch (PresencaNegada $negada) {
            $this->assertSame($esperado, $negada->codigo);

            return $negada;
        }

        $this->fail("Esperava {$esperado->value}, mas a viagem foi resolvida.");
    }

    public function test_resolve_a_ida_dentro_da_janela_e_gera_as_viagens_do_dia_uma_vez(): void
    {
        $viagem = $this->resolver();
        $this->resolver();

        $this->assertSame(HorarioViagem::IDA, $viagem->sentido);
        $this->assertSame($this->van->id, $viagem->veiculo_id);
        $this->assertSame('2026-10-19 17:15:00', HorarioLocal::exibir($viagem->janela_inicio));
        $this->assertSame('2026-10-19 17:45:00', HorarioLocal::exibir($viagem->janela_fim));
        // Ida e volta do dia: duas viagens, mesmo chamando o resolvedor de novo.
        $this->assertSame(2, Viagem::whereDate('data', self::DIA)->count());
    }

    public function test_limites_da_janela_sao_inclusivos(): void
    {
        $this->assertSame(HorarioViagem::IDA, $this->resolver(hora: self::DIA.' 17:15:00')->sentido);
        $this->assertSame(HorarioViagem::IDA, $this->resolver(hora: self::DIA.' 17:45:00')->sentido);

        $antes = $this->assertNegada(CodigoPresenca::OUTSIDE_TIME_WINDOW, fn () => $this->resolver(hora: self::DIA.' 17:14:59'));
        $depois = $this->assertNegada(CodigoPresenca::OUTSIDE_TIME_WINDOW, fn () => $this->resolver(hora: self::DIA.' 17:45:01'));

        // Fora do horario, a viagem mais proxima vai junto para a revisao.
        $this->assertSame(HorarioViagem::IDA, $antes->viagem->sentido);
        $this->assertSame(HorarioViagem::IDA, $depois->viagem->sentido);
    }

    /**
     * A aplicacao roda em UTC; o horario das linhas e o de Caraguatatuba
     * (UTC-3). 17h30 local sao 20h30 no relogio da aplicacao.
     */
    public function test_horario_da_linha_e_o_local_e_nao_o_da_aplicacao(): void
    {
        $this->assertSame('UTC', config('app.timezone'));

        $viagem = $this->resolver(hora: self::DIA.' 17:30:00');
        $this->assertSame('2026-10-19 20:15:00', $viagem->janela_inicio->setTimezone('UTC')->toDateTimeString());

        // 17h30 no relogio da aplicacao sao 14h30 em Caraguatatuba: fora.
        $this->assertNegada(CodigoPresenca::OUTSIDE_TIME_WINDOW, fn () => app(ResolvedorViagem::class)->resolver(
            $this->van,
            $this->estudante,
            Carbon::parse(self::DIA.' 17:30:00', 'UTC'),
        ));
    }

    public function test_ida_e_volta_sao_viagens_diferentes_com_o_mesmo_qr(): void
    {
        $ida = $this->resolver(hora: self::DIA.' 17:30:00');
        $volta = $this->resolver(hora: self::DIA.' 22:30:00');

        $this->assertSame(HorarioViagem::VOLTA, $volta->sentido);
        $this->assertNotSame($ida->id, $volta->id);
    }

    public function test_janela_que_passa_da_meia_noite_ainda_acha_a_viagem_de_ontem(): void
    {
        $this->horario($this->linha, HorarioViagem::VOLTA, '23:55:00');

        $viagem = $this->resolver(hora: '2026-10-20 00:05:00');

        $this->assertSame('2026-10-19', $viagem->data->toDateString());
        $this->assertSame('23:55:00', $viagem->horario_saida);
    }

    public function test_fim_de_semana_nao_tem_viagem(): void
    {
        $this->assertNegada(CodigoPresenca::NO_MATCHING_TRIP, fn () => $this->resolver(hora: '2026-10-18 17:30:00'));
    }

    public function test_dia_sem_operacao_nao_gera_viagem(): void
    {
        DiaSemOperacao::create(['data' => self::DIA, 'linha_id' => null, 'motivo' => 'Feriado municipal']);

        $this->assertNegada(CodigoPresenca::NO_MATCHING_TRIP, fn () => $this->resolver());
        $this->assertSame(0, Viagem::whereDate('data', self::DIA)->count());
    }

    public function test_estudante_de_outra_linha_nao_e_autorizado(): void
    {
        $outro = $this->estudanteComConta(Linha::factory()->create());

        $this->assertNegada(CodigoPresenca::LINE_NOT_AUTHORIZED, fn () => $this->resolver(estudante: $outro));
    }

    public function test_remanejamento_temporario_autoriza_so_no_periodo(): void
    {
        $outro = $this->estudanteComConta(Linha::factory()->create());

        AutorizacaoLinha::create([
            'estudante_id' => $outro->id,
            'linha_id' => $this->linha->id,
            'inicio' => self::DIA,
            'fim' => self::DIA,
            'motivo' => 'Estagio na semana',
        ]);

        $this->assertSame($this->linha->id, $this->resolver(estudante: $outro)->linha_id);
        $this->assertNegada(CodigoPresenca::LINE_NOT_AUTHORIZED, fn () => $this->resolver(estudante: $outro, hora: '2026-10-20 17:30:00'));
    }

    public function test_estudante_sem_beneficio_ativo_nao_usa_nenhuma_linha(): void
    {
        $this->estudante->update(['situacao' => 'Inativo']);

        $this->assertNegada(CodigoPresenca::LINE_NOT_AUTHORIZED, fn () => $this->resolver());
    }

    public function test_troca_de_van_de_um_dia_sem_reimprimir_qr(): void
    {
        $reserva = Veiculo::factory()->create();

        AtribuicaoVeiculo::create([
            'veiculo_id' => $reserva->id,
            'linha_id' => $this->linha->id,
            'inicio' => self::DIA,
            'fim' => self::DIA,
            'motivo' => 'Van titular em manutencao',
        ]);

        $this->assertSame($reserva->id, $this->resolver(van: $reserva)->veiculo_id);
        $this->assertNegada(CodigoPresenca::NO_MATCHING_TRIP, fn () => $this->resolver());

        // No dia seguinte a titular volta.
        $this->assertSame($this->van->id, $this->resolver(hora: '2026-10-20 17:30:00')->veiculo_id);
    }

    public function test_van_inativa_nao_identifica_viagem(): void
    {
        $this->van->update(['ativo' => false]);

        $this->assertNegada(CodigoPresenca::QR_INVALID, fn () => $this->resolver());
    }

    public function test_viagem_cancelada_nao_aceita_presenca(): void
    {
        $this->resolver();
        Viagem::whereDate('data', self::DIA)->update(['status' => Viagem::CANCELADA]);

        $this->assertNegada(CodigoPresenca::NO_MATCHING_TRIP, fn () => $this->resolver());
    }

    public function test_mesma_van_em_janelas_sobrepostas_e_apontada_como_conflito(): void
    {
        $outraLinha = Linha::factory()->create();
        $this->horario($outraLinha, HorarioViagem::IDA, '17:40:00');
        AtribuicaoVeiculo::create(['veiculo_id' => $this->van->id, 'linha_id' => $outraLinha->id, 'inicio' => '2026-01-01']);

        $gerador = app(GeradorViagens::class);
        $gerador->gerar(Carbon::parse(self::DIA), Carbon::parse(self::DIA));

        $conflitos = $gerador->conflitos(Carbon::parse(self::DIA), Carbon::parse(self::DIA));
        $this->assertCount(1, $conflitos);
        $this->assertSame($this->van->id, $conflitos[0]['veiculo_id']);

        // Autorizado so na sua linha, o estudante ainda tem uma viagem so.
        $this->assertSame($this->linha->id, $this->resolver(hora: self::DIA.' 17:35:00')->linha_id);

        // Autorizado nas duas, o QR nao decide sozinho.
        AutorizacaoLinha::create(['estudante_id' => $this->estudante->id, 'linha_id' => $outraLinha->id, 'inicio' => self::DIA, 'motivo' => 'Teste']);
        $this->assertNegada(CodigoPresenca::MANUAL_REVIEW_REQUIRED, fn () => $this->resolver(hora: self::DIA.' 17:35:00'));
    }

    public function test_nova_atribuicao_atualiza_viagens_futuras_mas_nao_as_trocadas_a_mao(): void
    {
        $gerador = app(GeradorViagens::class);
        $gerador->gerar(Carbon::parse(self::DIA), Carbon::parse('2026-10-20'));

        $trocadaAMao = Viagem::whereDate('data', '2026-10-20')->where('sentido', HorarioViagem::IDA)->first();
        $especial = Veiculo::factory()->create();
        $trocadaAMao->update(['veiculo_id' => $especial->id, 'veiculo_substituido' => true]);

        $nova = Veiculo::factory()->create();
        AtribuicaoVeiculo::create(['veiculo_id' => $nova->id, 'linha_id' => $this->linha->id, 'inicio' => self::DIA]);
        $gerador->sincronizarVeiculos($this->linha->id);

        $this->assertSame($especial->id, $trocadaAMao->refresh()->veiculo_id);
        $this->assertSame(3, Viagem::where('veiculo_id', $nova->id)->count());
    }
}
