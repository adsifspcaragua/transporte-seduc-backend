<?php

namespace Tests\Feature\Reecadastro;

use App\Models\DocumentacaoReecadastro;
use App\Models\Estudante;
use App\Models\PeriodoReecadastro;
use App\Models\Role;
use App\Models\SolicitacaoReecadastro;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * TESTE FUNCIONAL (caixa-preta) da homologacao do recadastro.
 *
 * Cobre as tres decisoes da responsavel (Aprovado, Pendencia, Rejeitado), o
 * efeito de cada uma no estudante e nos documentos, as regras de estado e o
 * controle de acesso por permissao.
 */
class AnaliseReecadastroTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->seed(RolePermissionSeeder::class);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create(['ativo' => true]);
        $user->roles()->sync(Role::where('title', $role)->pluck('id'));

        return $user;
    }

    /** Solicitacao ja enviada pelo estudante, com os tres documentos. */
    private function solicitacaoEnviada(): SolicitacaoReecadastro
    {
        $solicitacao = SolicitacaoReecadastro::factory()->emAnalise()->create();

        foreach (DocumentacaoReecadastro::slugs() as $tipo) {
            DocumentacaoReecadastro::create([
                'estudante_id' => $solicitacao->estudante_id,
                'solicitacao_id' => $solicitacao->id,
                'type' => $tipo,
                'file_path' => "reecadastro/{$solicitacao->id}/{$tipo}.pdf",
                'nome_original' => "{$tipo}.pdf",
                'status' => 'Enviado',
            ]);
        }

        return $solicitacao;
    }

    public function test_aprovacao_mantem_o_estudante_ativo_e_aceita_os_documentos(): void
    {
        Sanctum::actingAs($this->userWithRole('gestor'));
        $solicitacao = $this->solicitacaoEnviada();

        $this->putJson("/api/reecadastro/solicitacoes/{$solicitacao->id}/analise", [
            'decisao' => 'Aprovado',
        ])->assertOk()->assertJsonPath('data.status', 'Aprovado');

        $solicitacao->refresh();

        $this->assertSame('Aprovado', $solicitacao->status);
        $this->assertNotNull($solicitacao->analisado_em);
        $this->assertSame('Ativo', Estudante::find($solicitacao->estudante_id)->status);
        $this->assertTrue($solicitacao->documentos->every(fn ($doc) => $doc->status === 'Aprovado'));
    }

    public function test_rejeicao_exige_motivo_e_inativa_o_estudante(): void
    {
        Sanctum::actingAs($this->userWithRole('gestor'));
        $solicitacao = $this->solicitacaoEnviada();

        $this->putJson("/api/reecadastro/solicitacoes/{$solicitacao->id}/analise", [
            'decisao' => 'Rejeitado',
        ])->assertStatus(422)->assertJsonValidationErrors('motivo');

        $this->putJson("/api/reecadastro/solicitacoes/{$solicitacao->id}/analise", [
            'decisao' => 'Rejeitado',
            'motivo' => 'Documentos de outro estudante.',
        ])->assertOk();

        $solicitacao->refresh();

        $this->assertSame('Rejeitado', $solicitacao->status);
        $this->assertSame('Documentos de outro estudante.', $solicitacao->observacoes);
        $this->assertSame('Inativo', Estudante::find($solicitacao->estudante_id)->status);
    }

    public function test_pendencia_exige_a_lista_de_documentos_devolvidos(): void
    {
        Sanctum::actingAs($this->userWithRole('gestor'));
        $solicitacao = $this->solicitacaoEnviada();

        $this->putJson("/api/reecadastro/solicitacoes/{$solicitacao->id}/analise", [
            'decisao' => 'Pendencia',
            'motivo' => 'Comprovante ilegível.',
        ])->assertStatus(422)->assertJsonValidationErrors('documentos');
    }

    public function test_pendencia_devolve_apenas_os_documentos_informados(): void
    {
        Sanctum::actingAs($this->userWithRole('gestor'));
        $solicitacao = $this->solicitacaoEnviada();

        $this->putJson("/api/reecadastro/solicitacoes/{$solicitacao->id}/analise", [
            'decisao' => 'Pendencia',
            'motivo' => 'Comprovante ilegível.',
            'documentos' => ['comprovante_residencia'],
        ])->assertOk();

        $solicitacao->refresh()->load('documentos');

        $this->assertSame('Pendencia', $solicitacao->status);
        $this->assertSame('Rejeitado', $solicitacao->documentos->firstWhere('type', 'comprovante_residencia')->status);
        $this->assertSame('Aprovado', $solicitacao->documentos->firstWhere('type', 'cronograma_aulas')->status);
        $this->assertSame('Ativo', Estudante::find($solicitacao->estudante_id)->status);
    }

    public function test_estudante_reenvia_apenas_o_documento_devolvido(): void
    {
        $gestor = $this->userWithRole('gestor');
        Sanctum::actingAs($gestor);
        $solicitacao = $this->solicitacaoEnviada();

        $this->putJson("/api/reecadastro/solicitacoes/{$solicitacao->id}/analise", [
            'decisao' => 'Pendencia',
            'motivo' => 'Comprovante ilegível.',
            'documentos' => ['comprovante_residencia'],
        ])->assertOk();

        // O estudante volta pelo CPF, sem login, e recebe um novo token.
        app('auth')->forgetGuards();
        $estudante = Estudante::find($solicitacao->estudante_id);
        $token = $this->postJson('/api/reecadastro/consulta', ['cpf' => $estudante->cpf])
            ->assertOk()
            ->assertJsonPath('data.status', 'Pendencia')
            ->assertJsonPath('data.pode_enviar', true)
            ->json('data.token');

        $this->postJson("/api/reecadastro/solicitacoes/{$solicitacao->id}/documentos", [
            'token' => $token,
            'type' => 'cronograma_aulas',
            'arquivo' => UploadedFile::fake()->create('cronograma.pdf', 100, 'application/pdf'),
        ])->assertForbidden();

        $this->postJson("/api/reecadastro/solicitacoes/{$solicitacao->id}/documentos", [
            'token' => $token,
            'type' => 'comprovante_residencia',
            'arquivo' => UploadedFile::fake()->create('novo-comprovante.pdf', 100, 'application/pdf'),
        ])->assertOk();

        $this->assertSame(
            'Enviado',
            DocumentacaoReecadastro::where('solicitacao_id', $solicitacao->id)
                ->where('type', 'comprovante_residencia')
                ->value('status'),
        );
    }

    public function test_solicitacao_ainda_nao_enviada_nao_pode_ser_analisada(): void
    {
        Sanctum::actingAs($this->userWithRole('gestor'));
        $solicitacao = SolicitacaoReecadastro::factory()->create();

        $this->putJson("/api/reecadastro/solicitacoes/{$solicitacao->id}/analise", [
            'decisao' => 'Aprovado',
        ])->assertStatus(409);
    }

    public function test_operador_nao_pode_analisar_recadastro(): void
    {
        Sanctum::actingAs($this->userWithRole('operador'));
        $solicitacao = $this->solicitacaoEnviada();

        $this->putJson("/api/reecadastro/solicitacoes/{$solicitacao->id}/analise", [
            'decisao' => 'Aprovado',
        ])->assertForbidden();
    }

    public function test_periodo_so_fica_aberto_um_por_vez(): void
    {
        Sanctum::actingAs($this->userWithRole('gestor'));
        $anterior = PeriodoReecadastro::factory()->aberto()->create();
        $novo = PeriodoReecadastro::factory()->create();

        $this->patchJson("/api/reecadastro/periodos/{$novo->id}/abrir")
            ->assertOk()
            ->assertJsonPath('data.status', 'Aberto');

        $this->assertSame('Fechado', $anterior->refresh()->status);
        $this->assertSame('Aberto', $novo->refresh()->status);
    }

    public function test_gestor_baixa_o_documento_enviado_pelo_estudante(): void
    {
        Sanctum::actingAs($this->userWithRole('gestor'));
        $solicitacao = $this->solicitacaoEnviada();
        $documento = $solicitacao->documentos()->firstOrFail();
        Storage::put($documento->file_path, 'conteudo do arquivo');

        $this->get("/api/reecadastro/documentos/{$documento->id}/download")
            ->assertOk()
            ->assertDownload($documento->nome_original);
    }
}
