<?php

namespace Tests\Feature\Presenca;

use App\Models\DesafioDispositivo;
use App\Models\DispositivoEstudante;
use App\Models\Estudante;
use App\Models\Linha;
use App\Models\RegistroAuditoria;
use App\Models\SolicitacaoDispositivo;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Support\CenarioPresenca;
use Tests\Support\NavegadorDeTeste;
use Tests\TestCase;

/**
 * TESTE FUNCIONAL da Fase 2: vinculo do navegador por par de chaves.
 *
 * Saber a senha nao basta: sem a aprovacao do atendimento nao ha desafio, e
 * sem o desafio assinado pela chave nao ha credencial. Uma credencial ativa
 * por estudante, uma chave por conta, desafio de uso unico.
 */
class VinculacaoDispositivoTest extends TestCase
{
    use CenarioPresenca, RefreshDatabase;

    private Estudante $estudante;

    private User $gestor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->travelTo($this->local('2026-10-19 10:00:00'));

        $this->estudante = $this->estudanteComConta(Linha::factory()->create());
        $this->gestor = $this->usuario('gestor');
    }

    private function comoEstudante(?Estudante $estudante = null): void
    {
        Sanctum::actingAs(($estudante ?? $this->estudante)->user);
    }

    private function solicitar(?string $motivo = null): int
    {
        $this->comoEstudante();

        return $this->postJson('/api/me/dispositivo/solicitacoes', array_filter(['motivo' => $motivo]))
            ->assertCreated()
            ->json('data.id');
    }

    private function aprovar(int $solicitacao): void
    {
        Sanctum::actingAs($this->gestor);

        $this->patchJson("/api/dispositivos/solicitacoes/{$solicitacao}/aprovar", [
            'parecer' => 'RG conferido presencialmente.',
        ])->assertOk();
    }

    /**
     * @return array{desafio_id: string, desafio: string}
     */
    private function desafio(int $solicitacao): array
    {
        $this->comoEstudante();

        return $this->postJson("/api/me/dispositivo/solicitacoes/{$solicitacao}/desafio")
            ->assertCreated()
            ->json('data');
    }

    private function concluir(int $solicitacao, NavegadorDeTeste $navegador, array $desafio, ?NavegadorDeTeste $quemAssina = null)
    {
        $this->comoEstudante();

        return $this->postJson("/api/me/dispositivo/solicitacoes/{$solicitacao}/concluir", [
            'chave_publica' => $navegador->chavePublica(),
            'desafio_id' => $desafio['desafio_id'],
            'desafio' => $desafio['desafio'],
            'assinatura' => ($quemAssina ?? $navegador)->assinar($desafio['desafio']),
            'rotulo' => 'Chrome no celular',
        ]);
    }

    public function test_vinculacao_completa_com_aprovacao_do_atendimento(): void
    {
        $this->comoEstudante();
        $this->getJson('/api/me/dispositivo')
            ->assertOk()
            ->assertJsonPath('data.situacao', 'sem_dispositivo')
            ->assertJsonPath('data.pode_registrar_presenca', false);

        $solicitacao = $this->solicitar();
        $this->assertSame(SolicitacaoDispositivo::VINCULACAO, SolicitacaoDispositivo::find($solicitacao)->tipo);

        $this->getJson('/api/me/dispositivo')->assertJsonPath('data.situacao', 'aguardando_aprovacao');

        $this->aprovar($solicitacao);

        $this->comoEstudante();
        $this->getJson('/api/me/dispositivo')->assertJsonPath('data.situacao', 'aguardando_conclusao');

        $desafio = $this->desafio($solicitacao);
        $this->assertStringStartsWith("SIGTU\nv1\nvinculacao\n", $desafio['desafio']);
        $this->assertStringContainsString("estudante:{$this->estudante->id}\nsolicitacao:{$solicitacao}", $desafio['desafio']);

        $navegador = new NavegadorDeTeste;
        $this->concluir($solicitacao, $navegador, $desafio)
            ->assertCreated()
            ->assertJsonPath('data.status', DispositivoEstudante::ATIVO)
            ->assertJsonMissingPath('data.chave_publica');

        $this->getJson('/api/me/dispositivo')
            ->assertJsonPath('data.situacao', 'ativo')
            ->assertJsonPath('data.pode_registrar_presenca', true)
            ->assertJsonPath('data.dispositivo.aprovado_por.id', $this->gestor->id);

        $this->assertSame(SolicitacaoDispositivo::CONCLUIDA, SolicitacaoDispositivo::find($solicitacao)->status);
        $this->assertTrue(RegistroAuditoria::where('acao', 'dispositivo.solicitacao.aprovar')->where('justificativa', 'RG conferido presencialmente.')->exists());
        $this->assertTrue(RegistroAuditoria::where('acao', 'dispositivo.vincular')->exists());
    }

    public function test_so_a_senha_nao_vincula_sem_aprovacao(): void
    {
        $solicitacao = $this->solicitar();

        $this->postJson("/api/me/dispositivo/solicitacoes/{$solicitacao}/desafio")
            ->assertForbidden()
            ->assertJsonPath('codigo', 'DEVICE_PENDING_APPROVAL');

        $this->postJson("/api/me/dispositivo/solicitacoes/{$solicitacao}/concluir", [
            'chave_publica' => (new NavegadorDeTeste)->chavePublica(),
            'desafio_id' => '9d1c2f0e-8f5a-4c1e-9b7a-1a2b3c4d5e6f',
            'desafio' => 'qualquer',
            'assinatura' => 'AAAA',
        ])->assertForbidden()->assertJsonPath('codigo', 'DEVICE_PENDING_APPROVAL');

        $this->assertSame(0, DispositivoEstudante::count());
    }

    public function test_assinatura_de_outra_chave_e_recusada_e_queima_o_desafio(): void
    {
        $solicitacao = $this->solicitar();
        $this->aprovar($solicitacao);
        $desafio = $this->desafio($solicitacao);
        $navegador = new NavegadorDeTeste;

        // Chave publica de um navegador, assinatura de outro: nao prova posse.
        $this->concluir($solicitacao, $navegador, $desafio, new NavegadorDeTeste)
            ->assertForbidden()
            ->assertJsonPath('codigo', 'DEVICE_PROOF_INVALID');

        // O desafio vale uma tentativa, certa ou errada.
        $this->concluir($solicitacao, $navegador, $desafio)
            ->assertStatus(409)
            ->assertJsonPath('codigo', 'CHALLENGE_ALREADY_USED');

        $this->assertSame(0, DispositivoEstudante::count());
    }

    public function test_texto_do_desafio_alterado_e_recusado(): void
    {
        $solicitacao = $this->solicitar();
        $this->aprovar($solicitacao);
        $desafio = $this->desafio($solicitacao);
        $desafio['desafio'] = str_replace("solicitacao:{$solicitacao}", 'solicitacao:999', $desafio['desafio']);

        $this->concluir($solicitacao, new NavegadorDeTeste, $desafio)
            ->assertForbidden()
            ->assertJsonPath('codigo', 'DEVICE_PROOF_INVALID');
    }

    public function test_desafio_vencido_e_recusado(): void
    {
        $solicitacao = $this->solicitar();
        $this->aprovar($solicitacao);
        $desafio = $this->desafio($solicitacao);

        $this->travel(121)->seconds();

        $this->concluir($solicitacao, new NavegadorDeTeste, $desafio)
            ->assertStatus(422)
            ->assertJsonPath('codigo', 'CHALLENGE_EXPIRED');
    }

    public function test_aprovacao_vale_por_pouco_tempo(): void
    {
        $solicitacao = $this->solicitar();
        $this->aprovar($solicitacao);

        $this->travel(31)->minutes();

        $this->comoEstudante();
        $this->postJson("/api/me/dispositivo/solicitacoes/{$solicitacao}/desafio")->assertStatus(409);
        $this->assertSame(SolicitacaoDispositivo::EXPIRADA, SolicitacaoDispositivo::find($solicitacao)->status);

        // Expirada nao trava o estudante: ele pede de novo.
        $this->solicitar();
    }

    public function test_chave_fora_do_padrao_e_recusada(): void
    {
        $solicitacao = $this->solicitar();
        $this->aprovar($solicitacao);
        $desafio = $this->desafio($solicitacao);
        $p384 = new NavegadorDeTeste(['private_key_type' => OPENSSL_KEYTYPE_EC, 'curve_name' => 'secp384r1']);

        $this->concluir($solicitacao, $p384, $desafio)
            ->assertStatus(422)
            ->assertJsonValidationErrors('chave_publica');

        // A chave invalida e recusada antes de gastar o desafio.
        $this->assertNull(DesafioDispositivo::find($desafio['desafio_id'])->usado_em);
    }

    public function test_mesma_chave_nao_serve_a_duas_contas(): void
    {
        $navegador = new NavegadorDeTeste;
        $outro = $this->estudanteComConta(Linha::factory()->create());
        $this->autorizarNavegador($outro, $navegador);

        $solicitacao = $this->solicitar();
        $this->aprovar($solicitacao);

        $this->concluir($solicitacao, $navegador, $this->desafio($solicitacao))->assertStatus(409);

        $this->assertSame(0, DispositivoEstudante::where('estudante_id', $this->estudante->id)->count());
    }

    public function test_troca_revoga_a_credencial_antiga_no_mesmo_passo(): void
    {
        $antigo = $this->autorizarNavegador($this->estudante, new NavegadorDeTeste);

        $this->comoEstudante();
        $this->postJson('/api/me/dispositivo/solicitacoes')
            ->assertStatus(422)
            ->assertJsonValidationErrors('motivo');

        $solicitacao = $this->solicitar('Troquei de celular.');
        $this->assertSame(SolicitacaoDispositivo::TROCA, SolicitacaoDispositivo::find($solicitacao)->tipo);

        // Enquanto a troca nao termina, o navegador antigo continua valendo.
        $this->aprovar($solicitacao);
        $this->assertTrue($antigo->refresh()->estaAtivo());

        $this->concluir($solicitacao, new NavegadorDeTeste, $this->desafio($solicitacao))->assertCreated();

        $antigo->refresh();
        $this->assertSame(DispositivoEstudante::REVOGADO, $antigo->status);
        $this->assertSame($this->gestor->id, $antigo->revogado_por);
        $this->assertSame("Substituído pela solicitação #{$solicitacao}", $antigo->motivo_revogacao);
        $this->assertSame(1, DispositivoEstudante::where('estudante_id', $this->estudante->id)->where('status', DispositivoEstudante::ATIVO)->count());
    }

    public function test_banco_impede_duas_credenciais_ativas(): void
    {
        $this->autorizarNavegador($this->estudante, new NavegadorDeTeste);

        $this->expectException(UniqueConstraintViolationException::class);

        $this->autorizarNavegador($this->estudante, new NavegadorDeTeste);
    }

    public function test_uma_solicitacao_em_aberto_por_vez(): void
    {
        $solicitacao = $this->solicitar();

        $this->postJson('/api/me/dispositivo/solicitacoes')->assertStatus(409);

        $this->deleteJson("/api/me/dispositivo/solicitacoes/{$solicitacao}")->assertOk();
        $this->solicitar();
    }

    public function test_revogacao_pelo_atendimento(): void
    {
        $dispositivo = $this->autorizarNavegador($this->estudante, new NavegadorDeTeste);

        Sanctum::actingAs($this->gestor);
        $this->patchJson("/api/dispositivos/{$dispositivo->id}/revogar", [])->assertStatus(422)->assertJsonValidationErrors('motivo');
        $this->patchJson("/api/dispositivos/{$dispositivo->id}/revogar", ['motivo' => 'Celular roubado.'])
            ->assertOk()
            ->assertJsonPath('data.status', DispositivoEstudante::REVOGADO);
        $this->patchJson("/api/dispositivos/{$dispositivo->id}/revogar", ['motivo' => 'De novo.'])->assertStatus(409);

        $this->comoEstudante();
        $this->getJson('/api/me/dispositivo')->assertJsonPath('data.situacao', 'sem_dispositivo');
        $this->assertTrue(RegistroAuditoria::where('acao', 'dispositivo.revogar')->where('justificativa', 'Celular roubado.')->exists());
    }

    public function test_rejeicao_exige_parecer_e_encerra_a_solicitacao(): void
    {
        $solicitacao = $this->solicitar();

        Sanctum::actingAs($this->gestor);
        $this->patchJson("/api/dispositivos/solicitacoes/{$solicitacao}/rejeitar", [])->assertStatus(422);
        $this->patchJson("/api/dispositivos/solicitacoes/{$solicitacao}/rejeitar", ['parecer' => 'Documento não confere.'])
            ->assertOk()
            ->assertJsonPath('data.status', SolicitacaoDispositivo::REJEITADA);
        $this->patchJson("/api/dispositivos/solicitacoes/{$solicitacao}/aprovar", ['parecer' => 'Tentando aprovar.'])->assertStatus(409);
    }

    public function test_fila_mostra_pendentes_primeiro(): void
    {
        $outro = $this->estudanteComConta(Linha::factory()->create());
        $rejeitada = $this->solicitar();
        Sanctum::actingAs($this->gestor);
        $this->patchJson("/api/dispositivos/solicitacoes/{$rejeitada}/rejeitar", ['parecer' => 'Não compareceu.']);

        $this->travel(1)->minutes();
        $this->comoEstudante($outro);
        $pendente = $this->postJson('/api/me/dispositivo/solicitacoes')->json('data.id');

        Sanctum::actingAs($this->gestor);
        $this->assertSame([$pendente, $rejeitada], array_column($this->getJson('/api/dispositivos/solicitacoes')->json('data'), 'id'));
    }

    public function test_permissoes(): void
    {
        $solicitacao = $this->solicitar();

        // Operador acompanha, mas nao libera credencial.
        Sanctum::actingAs($this->usuario('operador'));
        $this->getJson('/api/dispositivos/solicitacoes')->assertOk();
        $this->patchJson("/api/dispositivos/solicitacoes/{$solicitacao}/aprovar", ['parecer' => 'Aprovando.'])->assertForbidden();

        // Estudante nao chega na fila do atendimento.
        $this->comoEstudante();
        $this->getJson('/api/dispositivos/solicitacoes')->assertForbidden();

        // Motorista nao tem area de estudante.
        Sanctum::actingAs($this->usuario('motorista'));
        $this->getJson('/api/me/dispositivo')->assertForbidden();

        // Conta de estudante sem cadastro vinculado.
        Sanctum::actingAs($this->usuario('estudante'));
        $this->getJson('/api/me/dispositivo')
            ->assertForbidden()
            ->assertJsonPath('message', 'Esta conta não está vinculada a um cadastro de estudante.');
    }

    public function test_estudante_sem_beneficio_ativo_nao_solicita(): void
    {
        $this->estudante->update(['situacao' => 'Inativo']);
        $this->comoEstudante();

        $this->postJson('/api/me/dispositivo/solicitacoes')
            ->assertForbidden()
            ->assertJsonPath('codigo', 'STUDENT_NOT_ELIGIBLE');
    }
}
