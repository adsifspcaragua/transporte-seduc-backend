<?php

namespace Tests\Feature\Presenca;

use App\Models\DispositivoEstudante;
use App\Models\Estudante;
use App\Models\HorarioViagem;
use App\Models\Linha;
use App\Models\Presenca;
use App\Models\TentativaPresenca;
use App\Models\Viagem;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\Sanctum;
use Tests\Support\CenarioPresenca;
use Tests\Support\NavegadorDeTeste;
use Tests\TestCase;

/**
 * TESTE FUNCIONAL do registro feito sem conexao.
 *
 * O celular grava e assina o comprovante sem internet e envia depois. A API
 * confere tudo o que da para conferir e coloca na fila da gestao, mas nunca
 * registra a presenca sozinha: horario e GPS do celular nao sao confiaveis.
 */
class PresencaOfflineTest extends TestCase
{
    use CenarioPresenca, RefreshDatabase;

    /** Segunda-feira; a ida sai as 17h30 com janela 17h15-17h45. */
    private const DIA = '2026-10-19';

    /** ~44 m do ponto. */
    private const NO_PONTO = '-23.6204000,-45.4100000,20';

    private Estudante $estudante;

    private DispositivoEstudante $dispositivo;

    private NavegadorDeTeste $navegador;

    private string $qr;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->travelTo($this->local(self::DIA.' 17:25:00'));

        $linha = Linha::factory()->create();
        $this->horario($linha, HorarioViagem::IDA, '17:30:00');
        $this->horario($linha, HorarioViagem::VOLTA, '22:30:00');
        [, $this->qr] = $this->vanDaLinha($linha);
        $this->ponto($linha);

        $this->estudante = $this->estudanteComConta($linha);
        $this->navegador = new NavegadorDeTeste;
        $this->dispositivo = $this->autorizarNavegador($this->estudante, $this->navegador);

        Sanctum::actingAs($this->estudante->user);
    }

    /**
     * Monta o comprovante como o navegador monta, sem conexao.
     *
     * @param  array<string, mixed>  $mudancas
     */
    private function comprovante(array $mudancas = []): string
    {
        $c = [
            'proposito' => 'presenca-offline',
            'id' => (string) Str::uuid(),
            'dispositivo' => $this->dispositivo->id,
            'qr' => $this->qr,
            'capturado' => now(),
            'local' => self::NO_PONTO,
            ...$mudancas,
        ];

        return implode("\n", [
            'SIGTU',
            'v1',
            $c['proposito'],
            $c['id'],
            'dispositivo:'.$c['dispositivo'],
            'qr:'.$c['qr'],
            'capturado_em:'.$c['capturado']->getTimestampMs(),
            'local:'.$c['local'],
        ]);
    }

    private function enviar(string $texto, ?NavegadorDeTeste $quemAssina = null, ?string $assinadoComo = null): TestResponse
    {
        return $this->postJson('/api/presencas/offline', [
            'comprovante' => $texto,
            'assinatura' => ($quemAssina ?? $this->navegador)->assinar($assinadoComo ?? $texto),
        ]);
    }

    public function test_gravado_no_ponto_e_enviado_depois_vai_para_a_gestao_sem_alertas(): void
    {
        $texto = $this->comprovante();

        // A internet so volta depois que a janela da viagem fechou.
        $this->travelTo($this->local(self::DIA.' 18:30:00'));

        $resposta = $this->enviar($texto)
            ->assertStatus(202)
            ->assertJsonPath('data.resultado', TentativaPresenca::EM_REVISAO)
            ->assertJsonPath('data.etapa', TentativaPresenca::OFFLINE)
            ->assertJsonPath('data.codigo', null)
            ->assertJsonPath('data.revisao.status', TentativaPresenca::REVISAO_PENDENTE)
            ->assertJsonPath('data.viagem.sentido', HorarioViagem::IDA)
            ->assertJsonPath('data.pode_pedir_revisao', false);

        $this->assertSame(0, Presenca::count());
        $this->assertEqualsWithDelta(44, $resposta->json('data.distancia_metros'), 2);
        $this->assertStringContainsString('2026-10-19 17:25:00 (horário do celular)', TentativaPresenca::sole()->revisao_motivo);

        $this->getJson('/api/me/presencas/tentativas')
            ->assertOk()
            ->assertJsonPath('data.0.resultado', TentativaPresenca::EM_REVISAO);
    }

    public function test_aprovado_pela_gestao_vira_presenca_offline_no_horario_da_leitura(): void
    {
        $tentativa = $this->enviar($this->comprovante())->assertStatus(202)->json('data.id');

        $this->travelTo($this->local(self::DIA.' 20:00:00'));
        Sanctum::actingAs($this->usuario('gestor'));

        $this->getJson('/api/presencas/tentativas?resultado=Em revisao')->assertOk()->assertJsonCount(1, 'data');
        $this->putJson("/api/presencas/tentativas/{$tentativa}/revisao", [
            'decisao' => TentativaPresenca::REVISAO_APROVADA,
            'parecer' => 'Sem sinal na serra; ponto e horário conferem.',
        ])->assertOk();

        $presenca = Presenca::sole();
        $this->assertSame(Presenca::OFFLINE, $presenca->metodo);
        $this->assertSame(self::DIA.' 17:25:00', $presenca->registrada_em->setTimezone(config('presenca.fuso'))->toDateTimeString());
        $this->assertSame($presenca->id, TentativaPresenca::find($tentativa)->presenca_id);
    }

    public function test_reenviar_o_mesmo_comprovante_nao_duplica(): void
    {
        $texto = $this->comprovante();

        $primeiro = $this->enviar($texto)->assertStatus(202)->json('data.id');
        $this->enviar($texto)->assertOk()->assertJsonPath('data.id', $primeiro);

        $this->assertSame(1, TentativaPresenca::count());
    }

    public function test_assinatura_de_outro_navegador_ou_texto_alterado_sao_recusados(): void
    {
        $texto = $this->comprovante();

        $this->enviar($texto, new NavegadorDeTeste)->assertForbidden()->assertJsonPath('codigo', 'DEVICE_PROOF_INVALID');

        // Assinou estando longe e trocou a localizacao depois.
        $longe = $this->comprovante(['local' => '-23.6400000,-45.4100000,20']);
        $this->enviar(str_replace('-23.6400000', '-23.6204000', $longe), assinadoComo: $longe)
            ->assertForbidden()
            ->assertJsonPath('codigo', 'DEVICE_PROOF_INVALID');

        $this->assertSame(0, TentativaPresenca::count());
    }

    public function test_dispositivo_revogado_depois_da_leitura_nao_envia(): void
    {
        $texto = $this->comprovante();
        $this->dispositivo->update(['status' => DispositivoEstudante::REVOGADO, 'revogado_em' => now()]);

        $this->enviar($texto)->assertForbidden()->assertJsonPath('codigo', 'DEVICE_REVOKED');
    }

    public function test_o_que_nao_confere_vai_como_alerta_para_a_gestao(): void
    {
        $this->enviar($this->comprovante(['local' => '-23.6400000,-45.4100000,15']))
            ->assertStatus(202)
            ->assertJsonPath('data.codigo', 'OUTSIDE_GEOFENCE');

        TentativaPresenca::query()->delete();
        $this->enviar($this->comprovante(['local' => 'erro:permissao_negada']))
            ->assertStatus(202)
            ->assertJsonPath('data.codigo', 'LOCATION_PERMISSION_DENIED');

        TentativaPresenca::query()->delete();
        $this->enviar($this->comprovante(['local' => '-23.6204000,-45.4100000,500']))
            ->assertStatus(202)
            ->assertJsonPath('data.codigo', 'LOCATION_INACCURATE');

        // Lido depois da janela: ainda aponta a viagem, para avaliar atraso.
        TentativaPresenca::query()->delete();
        $this->enviar($this->comprovante(['capturado' => $this->local(self::DIA.' 17:55:00')]))->assertStatus(422);
        $this->travelTo($this->local(self::DIA.' 18:10:00'));
        $this->enviar($this->comprovante(['capturado' => $this->local(self::DIA.' 17:55:00')]))
            ->assertStatus(202)
            ->assertJsonPath('data.codigo', 'OUTSIDE_TIME_WINDOW')
            ->assertJsonPath('data.viagem.sentido', HorarioViagem::IDA);
    }

    public function test_prazo_de_envio_e_relogio_adiantado(): void
    {
        $texto = $this->comprovante();

        $this->travelTo($this->local('2026-10-20 17:26:00'));
        $this->enviar($texto)->assertStatus(422)->assertJsonPath('codigo', 'OFFLINE_PROOF_EXPIRED');

        $this->enviar($this->comprovante(['capturado' => now()->addMinutes(11)]))
            ->assertStatus(422)
            ->assertJsonPath('codigo', 'OFFLINE_CLOCK_INVALID');

        $this->assertSame(0, TentativaPresenca::where('resultado', TentativaPresenca::EM_REVISAO)->count());
        $this->assertSame(2, TentativaPresenca::where('resultado', TentativaPresenca::NEGADA)->count());
    }

    public function test_sem_van_ou_linha_valida_nao_ha_o_que_revisar(): void
    {
        $this->enviar($this->comprovante(['qr' => str_repeat('x', 43)]))->assertNotFound()->assertJsonPath('codigo', 'QR_INVALID');

        $outraLinha = Linha::factory()->create();
        $this->horario($outraLinha, HorarioViagem::IDA, '17:30:00');
        [, $qrOutraLinha] = $this->vanDaLinha($outraLinha);

        $this->enviar($this->comprovante(['qr' => $qrOutraLinha]))->assertForbidden()->assertJsonPath('codigo', 'LINE_NOT_AUTHORIZED');

        $this->assertSame(0, TentativaPresenca::where('resultado', TentativaPresenca::EM_REVISAO)->count());
    }

    public function test_viagem_com_presenca_ou_ja_em_revisao_nao_recebe_outro(): void
    {
        $this->enviar($this->comprovante())->assertStatus(202);
        $this->enviar($this->comprovante())->assertStatus(409);

        TentativaPresenca::query()->delete();
        Presenca::create([
            'estudante_id' => $this->estudante->id,
            'viagem_id' => Viagem::where('sentido', HorarioViagem::IDA)->value('id'),
            'metodo' => Presenca::DISPOSITIVO,
            'registrada_em' => now(),
        ]);

        $this->enviar($this->comprovante())->assertStatus(409)->assertJsonPath('codigo', 'ATTENDANCE_ALREADY_RECORDED');
    }

    public function test_formatos_invalidos_sao_recusados(): void
    {
        $valido = $this->comprovante();
        $invalidos = [
            'outra quantidade de linhas' => $valido."\nextra",
            'texto do desafio online' => $this->comprovante(['proposito' => 'presenca']),
            'id que nao e uuid' => $this->comprovante(['id' => 'abc']),
            'latitude fora do globo' => $this->comprovante(['local' => '-95.0000000,-45.4100000,20']),
            'precisao zero' => $this->comprovante(['local' => '-23.6204000,-45.4100000,0']),
            'quebra de linha do Windows' => str_replace("\n", "\r\n", $valido),
        ];

        foreach ($invalidos as $caso => $texto) {
            $this->enviar($texto)->assertStatus(422)->assertJsonValidationErrors('comprovante', 'errors', $caso);
        }
    }

    public function test_so_o_estudante_usa_a_rota(): void
    {
        Sanctum::actingAs($this->usuario('motorista'));

        $this->enviar($this->comprovante())->assertForbidden();
    }
}
