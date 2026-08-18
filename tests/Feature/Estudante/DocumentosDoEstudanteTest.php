<?php

namespace Tests\Feature\Estudante;

use App\Models\Estudante;
use App\Models\Inscricao;
use App\Models\InscricaoDocumento;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * TESTE FUNCIONAL dos documentos no cadastro do estudante.
 *
 * Os documentos ficam na inscricao que originou o estudante. Depois de aprovar,
 * a responsavel ainda precisa conseguir reconferir o que analisou, entao eles
 * acompanham o estudante.
 */
class DocumentosDoEstudanteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create(['ativo' => true]);
        $user->roles()->sync(Role::where('title', 'gestor')->pluck('id'));

        Sanctum::actingAs($user);
    }

    public function test_estudante_traz_os_documentos_da_inscricao(): void
    {
        $inscricao = Inscricao::factory()->create();
        InscricaoDocumento::create([
            'inscricao_id' => $inscricao->id,
            'name' => 'identidade',
            'type' => 'documento',
            'file_path' => "inscricoes/{$inscricao->id}/identidade.pdf",
            'status' => 'Aprovado',
        ]);

        $estudante = Estudante::factory()->create(['inscricao_id' => $inscricao->id]);

        $documentos = $this->getJson("/api/estudantes/{$estudante->id}")
            ->assertOk()
            ->json('data.documentos');

        $this->assertCount(1, $documentos);
        $this->assertSame('identidade', $documentos[0]['name']);
        $this->assertNotEmpty($documentos[0]['preview_url']);
        $this->assertNotEmpty($documentos[0]['download_url']);
    }

    public function test_inscricao_sem_documentos_devolve_lista_vazia(): void
    {
        // Todo estudante nasce de uma inscricao (a coluna e obrigatoria), entao o
        // caso real de lista vazia e a inscricao que ainda nao teve documentos.
        $inscricao = Inscricao::factory()->create();
        $estudante = Estudante::factory()->create(['inscricao_id' => $inscricao->id]);

        $this->getJson("/api/estudantes/{$estudante->id}")
            ->assertOk()
            ->assertJsonPath('data.documentos', []);
    }
}
