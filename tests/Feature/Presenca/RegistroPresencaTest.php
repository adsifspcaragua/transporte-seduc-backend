<?php

namespace Tests\Feature\Presenca;

use App\Models\DispositivoEstudante;
use App\Models\Estudante;
use App\Models\HorarioViagem;
use App\Models\Linha;
use App\Models\LinhaPonto;
use App\Models\Presenca;
use App\Models\RegistroAuditoria;
use App\Models\SolicitacaoDispositivo;
use App\Models\TentativaPresenca;
use App\Models\User;
use App\Models\Veiculo;
use App\Models\Viagem;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\Sanctum;
use Tests\Support\CenarioPresenca;
use Tests\Support\NavegadorDeTeste;
use Tests\TestCase;

/**
 * TESTE FUNCIONAL da Fase 3/4: a presenca registrada pelo estudante.
 *
 * Cobre a tabela "Cenarios tipicos" do ROADMAP.md (secao 8): cada linha vira
 * um teste. Ponto de embarque em (-23,62; -45,41) com raio de 200 m; a ida
 * sai as 17h30 com janela 17h15-17h45.
 */
class RegistroPresencaTest extends TestCase
{
    use CenarioPresenca, RefreshDatabase;

    /** Segunda-feira. */
    private const DIA = '2026-10-19';

    /** ~50 m do ponto. */
    private const NO_PONTO = ['latitude' => -23.6204, 'longitude' => -45.41, 'precisao_metros' => 20];

    private Linha $linha;

    private Veiculo $van;

    private string $qr;

    private Estudante $estudante;

    private NavegadorDeTeste $navegador;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->travelTo($this->local(self::DIA.' 17:25:00'));

        $this->linha = Linha::factory()->create();
        $this->horario($this->linha, HorarioViagem::IDA, '17:30:00');
        $this->horario($this->linha, HorarioViagem::VOLTA, '22:30:00');
        [$this->van, $this->qr] = $this->vanDaLinha($this->linha);
        $this->ponto($this->linha);

        $this->estudante = $this->estudanteComConta($this->linha);
        $this->navegador = new NavegadorDeTeste;
        $this->autorizarNavegador($this->estudante, $this->navegador);

        Sanctum::actingAs($this->estudante->user);
    }

    private function desafio(?string $qr = null): TestResponse
    {
        return $this->postJson('/api/presencas/desafios', ['qr_token' => $qr ?? $this->qr]);
    }

    /**
     * @param  array<string, mixed>|null  $localizacao
     * @param  array<string, mixed>  $extra
     */
    private function confirmar(array $desafio, ?array $localizacao = self::NO_PONTO, ?NavegadorDeTeste $quemAssina = null, array $extra = []): TestResponse
    {
        return $this->postJson('/api/presencas', [
            'qr_token' => $this->qr,
            'desafio_id' => $desafio['desafio_id'],
            'desafio' => $desafio['desafio'],
            'assinatura' => ($quemAssina ?? $this->navegador)->assinar($desafio['desafio']),
            'localizacao' => $localizacao,
            ...$extra,
        ]);
    }

    /**
     * @param  array<string, mixed>|null  $localizacao
     */
    private function registrar(?array $localizacao = self::NO_PONTO): TestResponse
    {
        return $this->confirmar($this->desafio()->assertCreated()->json('data'), $localizacao);
    }

    public function test_conta_correta_credencial_ativa_qr_horario_e_local_validos_registra(): void
    {
        $this->postJson('/api/presencas/resolver-qr', ['qr_token' => $this->qr])
            ->assertOk()
            ->assertJsonPath('data.viagem.linha.id', $this->linha->id)
            ->assertJsonPath('data.viagem.sentido', HorarioViagem::IDA)
            ->assertJsonPath('data.localizacao.precisao_maxima_metros', 100);

        $resposta = $this->registrar()
            ->assertCreated()
            ->assertJsonPath('data.metodo', Presenca::DISPOSITIVO)
            ->assertJsonPath('data.resultado_geo', 'dentro')
            ->assertJsonPath('data.politica_geo', 'pontos.v1')
            ->assertJsonPath('data.viagem.sentido', HorarioViagem::IDA)
            ->assertJsonMissingPath('data.latitude');

        $this->assertEqualsWithDelta(44, $resposta->json('data.distancia_metros'), 2);
        $this->assertSame(1, Presenca::count());
        $this->assertSame(TentativaPresenca::REGISTRADA, TentativaPresenca::latest('id')->first()->resultado);
        $this->assertNotNull(DispositivoEstudante::first()->ultimo_uso_em);

        // Coordenadas exatas nao sao guardadas (minimo necessario, D10).
        $this->assertFalse(Schema::hasColumn('presencas', 'latitude'));
        $this->assertFalse(Schema::hasColumn('tentativas_presenca', 'latitude'));
    }

    public function test_senha_sem_a_credencial_correta_nao_registra(): void
    {
        $amigo = $this->estudanteComConta($this->linha);
        Sanctum::actingAs($amigo->user);

        $this->desafio()->assertForbidden()->assertJsonPath('codigo', 'DEVICE_NOT_ENROLLED');

        SolicitacaoDispositivo::create(['estudante_id' => $amigo->id, 'tipo' => SolicitacaoDispositivo::VINCULACAO, 'status' => SolicitacaoDispositivo::PENDENTE]);
        $this->desafio()->assertForbidden()->assertJsonPath('codigo', 'DEVICE_PENDING_APPROVAL');

        // Mesmo com a conta certa, a assinatura de outro navegador nao passa.
        Sanctum::actingAs($this->estudante->user);
        $this->confirmar($this->desafio()->json('data'), quemAssina: new NavegadorDeTeste)
            ->assertForbidden()
            ->assertJsonPath('codigo', 'DEVICE_PROOF_INVALID')
            ->assertJsonPath('pode_pedir_revisao', false);

        $this->assertSame(0, Presenca::count());
    }

    public function test_qr_de_linha_nao_autorizada_e_bloqueado(): void
    {
        $outraLinha = Linha::factory()->create();
        $this->horario($outraLinha, HorarioViagem::IDA, '17:30:00');
        [, $qrOutraVan] = $this->vanDaLinha($outraLinha);

        $this->desafio($qrOutraVan)->assertForbidden()->assertJsonPath('codigo', 'LINE_NOT_AUTHORIZED');
    }

    public function test_qr_correto_fora_do_periodo_bloqueia_e_permite_revisao(): void
    {
        $this->travelTo($this->local(self::DIA.' 17:50:00'));

        $resposta = $this->desafio()
            ->assertStatus(422)
            ->assertJsonPath('codigo', 'OUTSIDE_TIME_WINDOW')
            ->assertJsonPath('pode_pedir_revisao', true);

        $tentativa = TentativaPresenca::find($resposta->json('tentativa_id'));
        $this->assertSame(HorarioViagem::IDA, $tentativa->viagem->sentido);
        $this->assertSame(TentativaPresenca::DESAFIO, $tentativa->etapa);
    }

    public function test_qr_compartilhado_com_quem_esta_longe_e_bloqueado(): void
    {
        // ~2,2 km do ponto.
        $longe = ['latitude' => -23.64, 'longitude' => -45.41, 'precisao_metros' => 15];

        $resposta = $this->registrar($longe)
            ->assertStatus(422)
            ->assertJsonPath('codigo', 'OUTSIDE_GEOFENCE')
            ->assertJsonPath('pode_pedir_revisao', true);

        $tentativa = TentativaPresenca::find($resposta->json('tentativa_id'));
        $this->assertEqualsWithDelta(2224, $tentativa->distancia_metros, 5);
        $this->assertSame(15, $tentativa->precisao_metros);
        $this->assertSame(0, Presenca::count());
    }

    /**
     * Limite do raio: 200 m aceita, 201 m nao. A precisao declarada nao
     * amplia o raio.
     */
    public function test_borda_do_raio(): void
    {
        $metroEmLatitude = 1 / 111_194.9;

        $this->registrar(['latitude' => -23.62 - 199.5 * $metroEmLatitude, 'longitude' => -45.41, 'precisao_metros' => 90])
            ->assertCreated();

        Presenca::query()->delete();

        $this->registrar(['latitude' => -23.62 - 201 * $metroEmLatitude, 'longitude' => -45.41, 'precisao_metros' => 90])
            ->assertStatus(422)
            ->assertJsonPath('codigo', 'OUTSIDE_GEOFENCE');
    }

    public function test_leitura_imprecisa_pede_nova_leitura(): void
    {
        $this->registrar([...self::NO_PONTO, 'precisao_metros' => 101])
            ->assertStatus(422)
            ->assertJsonPath('codigo', 'LOCATION_INACCURATE');

        $this->registrar([...self::NO_PONTO, 'precisao_metros' => 100])->assertCreated();
    }

    public function test_reenvio_da_mesma_assinatura_e_bloqueado(): void
    {
        $desafio = $this->desafio()->json('data');

        $this->confirmar($desafio)->assertCreated();
        $this->confirmar($desafio)->assertStatus(409)->assertJsonPath('codigo', 'CHALLENGE_ALREADY_USED');

        $this->assertSame(1, Presenca::count());
    }

    public function test_segunda_presenca_na_mesma_viagem_e_bloqueada(): void
    {
        $primeiro = $this->desafio()->json('data');
        $segundo = $this->desafio()->json('data');

        $this->confirmar($primeiro)->assertCreated();
        $this->confirmar($segundo)->assertStatus(409)->assertJsonPath('codigo', 'ATTENDANCE_ALREADY_RECORDED');
        $this->desafio()->assertStatus(409)->assertJsonPath('codigo', 'ATTENDANCE_ALREADY_RECORDED');
    }

    /**
     * A checagem previa nao basta com duas requisicoes simultaneas; quem
     * segura e o indice unico (estudante, viagem).
     */
    public function test_indice_unico_barra_presenca_dupla_concorrente(): void
    {
        $this->registrar()->assertCreated();
        $presenca = Presenca::first();

        $this->expectException(UniqueConstraintViolationException::class);

        Presenca::create([...$presenca->only(['estudante_id', 'viagem_id', 'metodo']), 'registrada_em' => now()]);
    }

    public function test_ida_e_volta_registram_separado(): void
    {
        $this->registrar()->assertCreated();

        $this->travelTo($this->local(self::DIA.' 22:25:00'));
        LinhaPonto::create(['linha_id' => $this->linha->id, 'ponto_id' => LinhaPonto::first()->ponto_id, 'sentido' => HorarioViagem::VOLTA]);

        $this->registrar()->assertCreated()->assertJsonPath('data.viagem.sentido', HorarioViagem::VOLTA);
        $this->assertSame(2, Presenca::count());
    }

    public function test_gps_negado_nao_aprova_e_vai_para_revisao(): void
    {
        $desafio = $this->desafio()->json('data');

        $resposta = $this->confirmar($desafio, null, extra: ['erro_localizacao' => 'permissao_negada'])
            ->assertStatus(422)
            ->assertJsonPath('codigo', 'LOCATION_PERMISSION_DENIED')
            ->assertJsonPath('pode_pedir_revisao', true);
        $tentativa = $resposta->json('tentativa_id');

        $this->postJson("/api/me/presencas/tentativas/{$tentativa}/revisao", ['motivo' => 'O celular não liberou o GPS.'])
            ->assertCreated()
            ->assertJsonPath('data.revisao.status', TentativaPresenca::REVISAO_PENDENTE);
        $this->postJson("/api/me/presencas/tentativas/{$tentativa}/revisao", ['motivo' => 'De novo.'])->assertStatus(422);

        $gestor = $this->usuario('gestor');
        Sanctum::actingAs($gestor);
        $this->putJson("/api/presencas/tentativas/{$tentativa}/revisao", [
            'decisao' => TentativaPresenca::REVISAO_APROVADA,
            'parecer' => 'Embarque confirmado pela secretaria.',
        ])->assertOk();

        $presenca = Presenca::sole();
        $this->assertSame(Presenca::MANUAL, $presenca->metodo);
        $this->assertSame($gestor->id, $presenca->registrada_por);
        $this->assertSame($presenca->id, TentativaPresenca::find($tentativa)->presenca_id);
        $this->assertTrue(RegistroAuditoria::where('acao', 'presenca.revisao.aprovada')->exists());
    }

    public function test_gps_indisponivel_sem_dados_tambem_nao_aprova(): void
    {
        $this->confirmar($this->desafio()->json('data'), null, extra: ['erro_localizacao' => 'tempo_esgotado'])
            ->assertStatus(422)
            ->assertJsonPath('codigo', 'LOCATION_UNAVAILABLE');

        $this->confirmar($this->desafio()->json('data'), null)
            ->assertStatus(422)
            ->assertJsonValidationErrors('localizacao');
    }

    public function test_linha_sem_geocerca_cadastrada_nao_aprova_sozinha(): void
    {
        LinhaPonto::query()->delete();

        $this->registrar()
            ->assertStatus(422)
            ->assertJsonPath('codigo', 'MANUAL_REVIEW_REQUIRED')
            ->assertJsonPath('pode_pedir_revisao', true);
    }

    public function test_ponto_com_horario_previsto_respeita_a_janela_do_ponto(): void
    {
        LinhaPonto::query()->update(['horario_previsto' => '17:40:00', 'janela_antes_min' => 5, 'janela_depois_min' => 5]);

        $this->registrar()->assertStatus(422)->assertJsonPath('codigo', 'OUTSIDE_TIME_WINDOW');

        $this->travelTo($this->local(self::DIA.' 17:36:00'));
        $this->registrar()->assertCreated();
    }

    public function test_revogacao_bloqueia_imediatamente(): void
    {
        $desafio = $this->desafio()->json('data');

        DispositivoEstudante::first()->update(['status' => DispositivoEstudante::REVOGADO, 'revogado_em' => now()]);

        $this->confirmar($desafio)->assertForbidden()->assertJsonPath('codigo', 'DEVICE_REVOKED');
        $this->assertSame(0, Presenca::count());
    }

    public function test_desafio_vencido_pede_nova_leitura(): void
    {
        $desafio = $this->desafio()->json('data');

        $this->travel(121)->seconds();

        $this->confirmar($desafio)->assertStatus(422)->assertJsonPath('codigo', 'CHALLENGE_EXPIRED');
    }

    public function test_qr_invalido_ou_rotacionado(): void
    {
        $this->desafio(str_repeat('x', 43))->assertNotFound()->assertJsonPath('codigo', 'QR_INVALID');
        $this->desafio('curto')->assertStatus(422);

        $novo = $this->van->gerarTokenQr();

        $this->desafio()->assertNotFound()->assertJsonPath('codigo', 'QR_INVALID');
        $this->desafio($novo)->assertCreated();
    }

    public function test_troca_de_van_operacional_pela_gestao(): void
    {
        $this->desafio()->assertCreated();
        $viagem = Viagem::where('sentido', HorarioViagem::IDA)->first();
        $reserva = Veiculo::factory()->create();
        $qrReserva = $reserva->gerarTokenQr();

        Sanctum::actingAs($this->usuario('gestor'));
        $this->patchJson("/api/viagens/{$viagem->id}", ['veiculo_id' => $reserva->id])->assertStatus(422);
        $this->patchJson("/api/viagens/{$viagem->id}", ['veiculo_id' => $reserva->id, 'motivo' => 'Van titular quebrou.'])
            ->assertOk()
            ->assertJsonPath('data.veiculo_substituido', true);

        // A titular so tem agora a volta das 22h30: fora do horario.
        Sanctum::actingAs($this->estudante->user);
        $this->desafio()->assertStatus(422)->assertJsonPath('codigo', 'OUTSIDE_TIME_WINDOW');
        $this->desafio($qrReserva)->assertCreated()->assertJsonPath('data.viagem.id', $viagem->id);
    }

    public function test_estudante_inativo_nao_registra(): void
    {
        $this->estudante->update(['situacao' => 'Inativo']);

        $this->desafio()->assertForbidden()->assertJsonPath('codigo', 'STUDENT_NOT_ELIGIBLE');
    }

    public function test_desafio_amarrado_a_van_lida(): void
    {
        $desafio = $this->desafio()->json('data');
        $this->assertStringContainsString("presenca\n{$desafio['desafio_id']}\nestudante:{$this->estudante->id}\nviagem:", $desafio['desafio']);

        // Mesmo desafio com o QR de outra van: recusado.
        [, $outroQr] = $this->vanDaLinha(Linha::factory()->create());
        $this->postJson('/api/presencas', [
            'qr_token' => $outroQr,
            'desafio_id' => $desafio['desafio_id'],
            'desafio' => $desafio['desafio'],
            'assinatura' => $this->navegador->assinar($desafio['desafio']),
            'localizacao' => self::NO_PONTO,
        ])->assertNotFound()->assertJsonPath('codigo', 'QR_INVALID');
    }

    public function test_historico_proximas_viagens_e_tentativas_do_estudante(): void
    {
        $this->registrar([...self::NO_PONTO, 'precisao_metros' => 500])->assertStatus(422);
        $this->registrar()->assertCreated();

        $this->getJson('/api/me/presencas')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.viagem.linha.id', $this->linha->id);

        $viagens = $this->getJson('/api/me/viagens')->assertOk()->json('data');
        $this->assertTrue($viagens[0]['presenca_registrada']);
        $this->assertSame(HorarioViagem::VOLTA, $viagens[1]['sentido']);
        $this->assertFalse($viagens[1]['presenca_registrada']);

        $this->getJson('/api/me/presencas/tentativas')
            ->assertOk()
            ->assertJsonPath('data.0.resultado', TentativaPresenca::NEGADA);
    }

    public function test_revisao_nao_vale_para_falha_de_dispositivo(): void
    {
        $tentativa = $this->confirmar($this->desafio()->json('data'), quemAssina: new NavegadorDeTeste)->json('tentativa_id');

        $this->postJson("/api/me/presencas/tentativas/{$tentativa}/revisao", ['motivo' => 'Quero revisão.'])->assertStatus(422);

        // Tentativa de outro estudante nao e alcancavel.
        Sanctum::actingAs($this->estudanteComConta($this->linha)->user);
        $this->postJson("/api/me/presencas/tentativas/{$tentativa}/revisao", ['motivo' => 'Quero revisão.'])->assertNotFound();
    }

    public function test_presenca_manual_exige_justificativa_e_permissao(): void
    {
        $this->desafio()->assertCreated();
        $viagem = Viagem::where('sentido', HorarioViagem::IDA)->first();
        $dados = ['estudante_id' => $this->estudante->id, 'viagem_id' => $viagem->id];

        Sanctum::actingAs($this->usuario('operador'));
        $this->postJson('/api/presencas/manual', [...$dados, 'justificativa' => 'Celular sem bateria no embarque.'])->assertForbidden();

        $gestor = $this->usuario('gestor');
        Sanctum::actingAs($gestor);
        $this->postJson('/api/presencas/manual', $dados)->assertStatus(422)->assertJsonValidationErrors('justificativa');
        $this->postJson('/api/presencas/manual', [...$dados, 'justificativa' => 'Celular sem bateria no embarque.'])
            ->assertCreated()
            ->assertJsonPath('data.metodo', Presenca::MANUAL);
        $this->postJson('/api/presencas/manual', [...$dados, 'justificativa' => 'Celular sem bateria no embarque.'])->assertStatus(409);

        $this->assertTrue(RegistroAuditoria::where('acao', 'presenca.manual')->where('user_id', $gestor->id)->exists());
    }

    public function test_painel_de_tentativas_e_indicadores(): void
    {
        $this->registrar()->assertCreated();
        $this->travelTo($this->local(self::DIA.' 22:20:00'));
        $this->registrar(['latitude' => -23.64, 'longitude' => -45.41, 'precisao_metros' => 30]);

        Sanctum::actingAs($this->usuario('operador'));

        $this->getJson('/api/presencas/tentativas?resultado=Negada')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.estudante.id', $this->estudante->id);

        $indicadores = $this->getJson('/api/presencas/indicadores?de='.self::DIA.'&ate='.self::DIA)->assertOk()->json('data');

        $this->assertSame([['linha_id' => $this->linha->id, 'linha' => $this->linha->name, 'total' => 1, 'manuais' => 0]], $indicadores['presencas_por_linha']);
        $this->assertSame([['data' => self::DIA, 'total' => 1]], $indicadores['presencas_por_dia']);
        $this->assertSame([['codigo' => 'MANUAL_REVIEW_REQUIRED', 'total' => 1]], $indicadores['negadas_por_motivo']);
        $this->assertSame(2, $indicadores['gps']['leituras']);
    }

    public function test_motorista_nao_participa_do_fluxo(): void
    {
        $motorista = $this->usuario('motorista');
        $this->linha->update(['motorista_id' => $motorista->id]);

        Sanctum::actingAs($motorista);
        $this->postJson('/api/presencas/desafios', ['qr_token' => $this->qr])->assertForbidden();
        $this->getJson('/api/presencas')->assertForbidden();
    }

    public function test_limite_de_tentativas(): void
    {
        for ($i = 0; $i < 30; $i++) {
            $this->postJson('/api/presencas/resolver-qr', ['qr_token' => $this->qr])->assertOk();
        }

        $this->postJson('/api/presencas/resolver-qr', ['qr_token' => $this->qr])
            ->assertStatus(429)
            ->assertJsonPath('codigo', 'RATE_LIMITED');
    }

    public function test_conta_inativa_e_barrada_antes_de_tudo(): void
    {
        User::whereKey($this->estudante->user_id)->update(['ativo' => false]);
        Sanctum::actingAs($this->estudante->user->refresh());

        $this->desafio()->assertForbidden()->assertJsonPath('message', 'Usuário inativo.');
    }
}
