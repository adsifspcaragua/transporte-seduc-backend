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
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * TESTE FUNCIONAL da devolucao do recadastro apontando campos.
 *
 * O motivo em texto diz o que houve; os campos dizem ONDE esta o erro. Sem eles
 * o estudante lia "dados incorretos" e tinha de adivinhar o que corrigir.
 */
class CamposPendentesTest extends TestCase
{
    use RefreshDatabase;

    private Estudante $estudante;

    private SolicitacaoReecadastro $solicitacao;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $periodo = PeriodoReecadastro::factory()->create(['status' => 'Aberto']);
        $this->estudante = Estudante::factory()->create(['status' => 'Ativo']);
        $this->solicitacao = SolicitacaoReecadastro::create([
            'estudante_id' => $this->estudante->id,
            'periodo_id' => $periodo->id,
            'status' => 'Em analise',
        ]);

        foreach (DocumentacaoReecadastro::slugs() as $slug) {
            DocumentacaoReecadastro::create([
                'estudante_id' => $this->estudante->id,
                'solicitacao_id' => $this->solicitacao->id,
                'type' => $slug,
                'file_path' => "reecadastro/{$this->solicitacao->id}/{$slug}.pdf",
                'nome_original' => "{$slug}.pdf",
                'status' => 'Enviado',
            ]);
        }
    }

    private function autenticarResponsavel(): void
    {
        $user = User::factory()->create(['ativo' => true]);
        $user->roles()->sync(Role::where('title', 'gestor')->pluck('id'));

        Sanctum::actingAs($user);
    }

    public function test_devolucao_guarda_os_campos_apontados(): void
    {
        $this->autenticarResponsavel();

        $this->putJson("/api/reecadastro/solicitacoes/{$this->solicitacao->id}/analise", [
            'decisao' => 'Pendencia',
            'motivo' => 'O nome esta escrito errado.',
            'documentos' => [DocumentacaoReecadastro::slugs()[0]],
            'campos' => ['name', 'cep'],
        ])->assertOk();

        $this->assertSame(['name', 'cep'], $this->solicitacao->refresh()->campos_pendentes);
    }

    public function test_estudante_ve_os_campos_com_o_rotulo(): void
    {
        $this->autenticarResponsavel();

        $this->putJson("/api/reecadastro/solicitacoes/{$this->solicitacao->id}/analise", [
            'decisao' => 'Pendencia',
            'motivo' => 'Confira o nome e o CEP.',
            'documentos' => [DocumentacaoReecadastro::slugs()[0]],
            'campos' => ['name', 'cep'],
        ])->assertOk();

        // O acesso pelo CPF e publico: e assim que o estudante volta, sem login.
        $campos = $this->postJson('/api/area-estudante/acesso', ['cpf' => $this->estudante->cpf])
            ->assertOk()
            ->json('data.campos_pendentes');

        $this->assertSame(
            [
                ['campo' => 'name', 'label' => 'Nome completo'],
                ['campo' => 'cep', 'label' => 'CEP'],
            ],
            $campos,
        );
    }

    public function test_campo_desconhecido_e_recusado(): void
    {
        $this->autenticarResponsavel();

        $this->putJson("/api/reecadastro/solicitacoes/{$this->solicitacao->id}/analise", [
            'decisao' => 'Pendencia',
            'motivo' => 'Tentativa invalida.',
            'documentos' => [DocumentacaoReecadastro::slugs()[0]],
            'campos' => ['senha_do_admin'],
        ])->assertStatus(422)->assertJsonValidationErrors('campos.0');
    }

    public function test_aprovacao_limpa_os_campos_pendentes(): void
    {
        $this->autenticarResponsavel();

        $this->solicitacao->update(['campos_pendentes' => ['name']]);

        $this->putJson("/api/reecadastro/solicitacoes/{$this->solicitacao->id}/analise", [
            'decisao' => 'Aprovado',
        ])->assertOk();

        $this->assertNull($this->solicitacao->refresh()->campos_pendentes);
    }

    public function test_documentos_trazem_o_link_de_visualizacao(): void
    {
        $this->autenticarResponsavel();

        $documentos = $this->getJson('/api/reecadastro/solicitacoes')
            ->assertOk()
            ->json('data.0.documentos');

        $this->assertNotEmpty($documentos);
        $this->assertStringContainsString('inline=1', $documentos[0]['preview_url']);
        $this->assertNotEmpty($documentos[0]['download_url']);
    }
}
