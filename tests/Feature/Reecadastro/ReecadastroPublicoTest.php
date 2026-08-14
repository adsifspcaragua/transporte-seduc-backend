<?php

namespace Tests\Feature\Reecadastro;

use App\Models\DocumentacaoReecadastro;
use App\Models\Estudante;
use App\Models\PeriodoReecadastro;
use App\Models\SolicitacaoReecadastro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * TESTE FUNCIONAL (caixa-preta) do fluxo publico do recadastro.
 *
 * O estudante nao faz login: informa o CPF e, se ja estiver no sistema e o
 * periodo estiver aberto, envia os tres documentos exigidos.
 *
 * Classes de equivalencia cobertas na consulta:
 *   - periodo fechado            -> 409
 *   - CPF fora do sistema        -> 404
 *   - estudante inativo          -> 403
 *   - estudante ativo            -> 200 com token e documentos pendentes
 *
 * E, no envio: token invalido, documento fora do vocabulario, arquivo grande,
 * envio depois de finalizado e a regra de prazo adicional.
 */
class ReecadastroPublicoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    private function periodoAberto(): PeriodoReecadastro
    {
        return PeriodoReecadastro::factory()->aberto()->create();
    }

    /** Consulta o CPF e devolve o token da sessao publica. */
    private function consultar(Estudante $estudante): array
    {
        $resposta = $this->postJson('/api/reecadastro/consulta', ['cpf' => $estudante->cpf])
            ->assertOk()
            ->json('data');

        return [$resposta['solicitacao_id'], $resposta['token']];
    }

    private function arquivo(string $nome = 'documento.pdf'): UploadedFile
    {
        return UploadedFile::fake()->create($nome, 120, 'application/pdf');
    }

    public function test_consulta_e_recusada_quando_nao_ha_periodo_aberto(): void
    {
        PeriodoReecadastro::factory()->create();
        $estudante = Estudante::factory()->create();

        $this->postJson('/api/reecadastro/consulta', ['cpf' => $estudante->cpf])
            ->assertStatus(409)
            ->assertJson(['message' => 'O período de recadastro está fechado no momento.']);
    }

    public function test_consulta_de_cpf_fora_do_sistema_retorna_404(): void
    {
        $this->periodoAberto();

        $this->postJson('/api/reecadastro/consulta', ['cpf' => '99999999999'])
            ->assertNotFound();
    }

    public function test_estudante_inativo_nao_pode_recadastrar(): void
    {
        $this->periodoAberto();
        $estudante = Estudante::factory()->inativo()->create();

        $this->postJson('/api/reecadastro/consulta', ['cpf' => $estudante->cpf])
            ->assertForbidden();
    }

    public function test_cpf_com_tamanho_invalido_e_rejeitado(): void
    {
        $this->periodoAberto();

        $this->postJson('/api/reecadastro/consulta', ['cpf' => '1234567890'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('cpf');
    }

    public function test_consulta_abre_a_solicitacao_do_periodo_com_os_tres_documentos_pendentes(): void
    {
        $periodo = $this->periodoAberto();
        $estudante = Estudante::factory()->create();

        $resposta = $this->postJson('/api/reecadastro/consulta', ['cpf' => $estudante->cpf])
            ->assertOk()
            ->assertJsonPath('data.status', 'Pendente')
            ->assertJsonPath('data.pode_enviar', true)
            ->assertJsonCount(3, 'data.documentos')
            ->json('data');

        $this->assertNotEmpty($resposta['token']);
        $this->assertSame(
            ['declaracao_matricula', 'cronograma_aulas', 'comprovante_residencia'],
            array_column($resposta['documentos'], 'type'),
        );
        $this->assertTrue(collect($resposta['documentos'])->every(fn ($doc) => $doc['pendente'] === true));

        $this->assertDatabaseHas('solicitacoes_reecadastro', [
            'estudante_id' => $estudante->id,
            'periodo_id' => $periodo->id,
            'status' => 'Pendente',
        ]);
    }

    public function test_consultar_duas_vezes_nao_duplica_a_solicitacao(): void
    {
        $this->periodoAberto();
        $estudante = Estudante::factory()->create();

        $this->consultar($estudante);
        $this->consultar($estudante);

        $this->assertSame(1, SolicitacaoReecadastro::where('estudante_id', $estudante->id)->count());
    }

    public function test_envio_com_token_invalido_e_recusado(): void
    {
        $this->periodoAberto();
        $estudante = Estudante::factory()->create();
        [$solicitacaoId] = $this->consultar($estudante);

        $this->postJson("/api/reecadastro/solicitacoes/{$solicitacaoId}/documentos", [
            'token' => 'token-errado',
            'type' => 'comprovante_residencia',
            'arquivo' => $this->arquivo(),
        ])->assertUnauthorized();
    }

    public function test_documento_fora_do_vocabulario_e_rejeitado(): void
    {
        $this->periodoAberto();
        $estudante = Estudante::factory()->create();
        [$solicitacaoId, $token] = $this->consultar($estudante);

        $this->postJson("/api/reecadastro/solicitacoes/{$solicitacaoId}/documentos", [
            'token' => $token,
            'type' => 'historico_escolar',
            'arquivo' => $this->arquivo(),
        ])->assertStatus(422)->assertJsonValidationErrors('type');
    }

    public function test_arquivo_acima_do_limite_e_rejeitado(): void
    {
        $this->periodoAberto();
        $estudante = Estudante::factory()->create();
        [$solicitacaoId, $token] = $this->consultar($estudante);

        $this->postJson("/api/reecadastro/solicitacoes/{$solicitacaoId}/documentos", [
            'token' => $token,
            'type' => 'comprovante_residencia',
            'arquivo' => UploadedFile::fake()->create('grande.pdf', 5121, 'application/pdf'),
        ])->assertStatus(422)->assertJsonValidationErrors('arquivo');
    }

    public function test_documento_enviado_e_guardado_em_disco_privado(): void
    {
        $this->periodoAberto();
        $estudante = Estudante::factory()->create();
        [$solicitacaoId, $token] = $this->consultar($estudante);

        $this->postJson("/api/reecadastro/solicitacoes/{$solicitacaoId}/documentos", [
            'token' => $token,
            'type' => 'comprovante_residencia',
            'arquivo' => $this->arquivo('conta-de-luz.pdf'),
        ])->assertOk();

        $documento = DocumentacaoReecadastro::where('solicitacao_id', $solicitacaoId)->firstOrFail();

        $this->assertSame('comprovante_residencia', $documento->type);
        $this->assertSame('Enviado', $documento->status);
        $this->assertSame('conta-de-luz.pdf', $documento->nome_original);
        Storage::assertExists($documento->file_path);
    }

    public function test_reenvio_do_mesmo_tipo_substitui_o_documento_anterior(): void
    {
        $this->periodoAberto();
        $estudante = Estudante::factory()->create();
        [$solicitacaoId, $token] = $this->consultar($estudante);

        foreach (['primeira.pdf', 'segunda.pdf'] as $nome) {
            $this->postJson("/api/reecadastro/solicitacoes/{$solicitacaoId}/documentos", [
                'token' => $token,
                'type' => 'comprovante_residencia',
                'arquivo' => $this->arquivo($nome),
            ])->assertOk();
        }

        $documentos = DocumentacaoReecadastro::where('solicitacao_id', $solicitacaoId)->get();

        $this->assertCount(1, $documentos);
        $this->assertSame('segunda.pdf', $documentos->first()->nome_original);
    }

    public function test_finalizar_sem_todos_os_documentos_e_recusado(): void
    {
        $this->periodoAberto();
        $estudante = Estudante::factory()->create();
        [$solicitacaoId, $token] = $this->consultar($estudante);

        $this->postJson("/api/reecadastro/solicitacoes/{$solicitacaoId}/finalizar", [
            'token' => $token,
            'possui_matricula' => true,
            'possui_cronograma' => true,
            'aceite_veracidade' => true,
            'aceite_ciencia' => true,
        ])->assertStatus(422);

        $this->assertSame('Pendente', SolicitacaoReecadastro::find($solicitacaoId)->status);
    }

    public function test_finalizar_exige_os_aceites_da_declaracao(): void
    {
        $this->periodoAberto();
        $estudante = Estudante::factory()->create();
        [$solicitacaoId, $token] = $this->consultar($estudante);

        $this->postJson("/api/reecadastro/solicitacoes/{$solicitacaoId}/finalizar", [
            'token' => $token,
            'possui_matricula' => true,
            'possui_cronograma' => true,
            'aceite_veracidade' => false,
            'aceite_ciencia' => false,
        ])->assertStatus(422)->assertJsonValidationErrors(['aceite_veracidade', 'aceite_ciencia']);
    }

    public function test_quem_nao_tem_matricula_precisa_pedir_prazo_adicional(): void
    {
        $this->periodoAberto();
        $estudante = Estudante::factory()->create();
        [$solicitacaoId, $token] = $this->consultar($estudante);

        $this->postJson("/api/reecadastro/solicitacoes/{$solicitacaoId}/finalizar", [
            'token' => $token,
            'possui_matricula' => false,
            'possui_cronograma' => true,
            'aceite_veracidade' => true,
            'aceite_ciencia' => true,
        ])->assertStatus(422)->assertJsonValidationErrors('prazo_matricula');
    }

    public function test_recadastro_completo_vai_para_analise(): void
    {
        $this->periodoAberto();
        $estudante = Estudante::factory()->create();
        [$solicitacaoId, $token] = $this->consultar($estudante);

        foreach (['declaracao_matricula', 'cronograma_aulas', 'comprovante_residencia'] as $tipo) {
            $this->postJson("/api/reecadastro/solicitacoes/{$solicitacaoId}/documentos", [
                'token' => $token,
                'type' => $tipo,
                'arquivo' => $this->arquivo("{$tipo}.pdf"),
            ])->assertOk();
        }

        $this->postJson("/api/reecadastro/solicitacoes/{$solicitacaoId}/finalizar", [
            'token' => $token,
            'possui_matricula' => true,
            'possui_cronograma' => true,
            'aceite_veracidade' => true,
            'aceite_ciencia' => true,
        ])->assertOk()->assertJsonPath('data.status', 'Em analise');

        $solicitacao = SolicitacaoReecadastro::find($solicitacaoId);

        $this->assertSame('Em analise', $solicitacao->status);
        $this->assertNotNull($solicitacao->enviada_em);
        $this->assertNull($solicitacao->access_token);
    }

    public function test_prazo_adicional_dispensa_o_documento_que_o_estudante_ainda_nao_tem(): void
    {
        $this->periodoAberto();
        $estudante = Estudante::factory()->create();
        [$solicitacaoId, $token] = $this->consultar($estudante);

        foreach (['cronograma_aulas', 'comprovante_residencia'] as $tipo) {
            $this->postJson("/api/reecadastro/solicitacoes/{$solicitacaoId}/documentos", [
                'token' => $token,
                'type' => $tipo,
                'arquivo' => $this->arquivo("{$tipo}.pdf"),
            ])->assertOk();
        }

        $this->postJson("/api/reecadastro/solicitacoes/{$solicitacaoId}/finalizar", [
            'token' => $token,
            'possui_matricula' => false,
            'prazo_matricula' => true,
            'possui_cronograma' => true,
            'aceite_veracidade' => true,
            'aceite_ciencia' => true,
        ])->assertOk();

        $solicitacao = SolicitacaoReecadastro::find($solicitacaoId);

        $this->assertSame('Em analise', $solicitacao->status);
        $this->assertTrue($solicitacao->prazo_matricula);
        $this->assertFalse($solicitacao->prazo_cronograma);
    }

    public function test_solicitacao_em_analise_nao_aceita_novos_envios(): void
    {
        $solicitacao = SolicitacaoReecadastro::factory()->emAnalise()->comToken()->create();

        $this->postJson("/api/reecadastro/solicitacoes/{$solicitacao->id}/documentos", [
            'token' => 'token-de-teste',
            'type' => 'comprovante_residencia',
            'arquivo' => $this->arquivo(),
        ])->assertStatus(409);
    }

    public function test_consulta_de_solicitacao_ja_enviada_informa_que_esta_em_analise(): void
    {
        $periodo = $this->periodoAberto();
        $estudante = Estudante::factory()->create();
        SolicitacaoReecadastro::factory()->emAnalise()->create([
            'estudante_id' => $estudante->id,
            'periodo_id' => $periodo->id,
        ]);

        $this->postJson('/api/reecadastro/consulta', ['cpf' => $estudante->cpf])
            ->assertOk()
            ->assertJsonPath('data.status', 'Em analise')
            ->assertJsonPath('data.pode_enviar', false)
            ->assertJson(['message' => 'Seus dados já foram enviados e estão em análise.']);
    }
}
