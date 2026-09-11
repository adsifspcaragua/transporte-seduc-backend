<?php

namespace Tests\Feature\Inscricao;

use App\Models\Inscricao;
use App\Models\InscricaoInstituicoes;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ListagemInscricaoTest extends TestCase
{
    use RefreshDatabase;

    private function autenticar(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create(['ativo' => true]);
        $user->roles()->sync(Role::where('title', 'gestor')->pluck('id'));
        Sanctum::actingAs($user);
    }

    private function criarCompleta(): Inscricao
    {
        $inscricao = Inscricao::factory()->create();
        InscricaoInstituicoes::factory()->create([
            'inscricao_id' => $inscricao->id,
            'days_of_week' => [1, 2, 3],
        ]);
        $inscricao->inscricao_documentos()->create([
            'name' => 'rg',
            'type' => 'application/pdf',
            'file_path' => 'inscricoes/teste.pdf',
            'status' => 'Em analise',
        ]);

        return $inscricao;
    }

    public function test_lista_traz_dados_academicos_e_documentos_sem_expor_token_ou_caminho(): void
    {
        $this->autenticar();
        $inscricao = $this->criarCompleta();

        $this->getJson('/api/inscricoes')->assertOk()
            ->assertJsonPath('data.0.instituicaoAcademica.course', 'Engenharia de Software')
            ->assertJsonStructure(['data' => [['instituicaoAcademica' => ['instituicao' => ['name']], 'documentos' => [['download_url', 'preview_url']]]]])
            ->assertJsonPath('data.0.documentos.0.inscricao_id', $inscricao->id)
            ->assertJsonMissingPath('data.0.token')
            ->assertJsonMissingPath('data.0.documentos.0.file_path');
    }

    public function test_inscricao_incompleta_mantem_relacionamentos_vazios(): void
    {
        $this->autenticar();
        Inscricao::factory()->create();

        $this->getJson('/api/inscricoes')->assertOk()
            ->assertJsonPath('data.0.instituicaoAcademica', null)
            ->assertJsonPath('data.0.documentos', []);
    }

    public function test_quantidade_de_consultas_nao_cresce_por_inscricao(): void
    {
        $this->autenticar();
        $this->criarCompleta();
        // Aquece a resolucao de permissoes antes de medir somente as leituras.
        $this->getJson('/api/inscricoes')->assertOk();
        DB::enableQueryLog();
        DB::flushQueryLog();
        $this->getJson('/api/inscricoes')->assertOk()->assertJsonCount(1, 'data');
        $umaInscricao = count(DB::getQueryLog());
        DB::disableQueryLog();

        for ($i = 0; $i < 5; $i++) {
            $this->criarCompleta();
        }

        DB::enableQueryLog();
        DB::flushQueryLog();
        $this->getJson('/api/inscricoes')->assertOk()->assertJsonCount(6, 'data');
        $seisInscricoes = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertSame($umaInscricao, $seisInscricoes);
    }

    public function test_listagem_exige_autenticacao(): void
    {
        $this->getJson('/api/inscricoes')->assertUnauthorized();
    }
}
