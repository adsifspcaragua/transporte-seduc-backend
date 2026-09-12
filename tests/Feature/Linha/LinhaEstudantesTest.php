<?php

namespace Tests\Feature\Linha;

use App\Models\Estudante;
use App\Models\InscricaoInstituicoes;
use App\Models\Linha;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LinhaEstudantesTest extends TestCase
{
    use RefreshDatabase;

    private function autenticar(string $perfil = 'gestor'): User
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create(['ativo' => true]);
        $user->roles()->sync(Role::where('title', $perfil)->pluck('id'));
        Sanctum::actingAs($user);

        return $user;
    }

    private function estudanteCompleto(Linha $linha): Estudante
    {
        $estudante = Estudante::factory()->create(['linha_id' => $linha->id]);
        InscricaoInstituicoes::factory()->create([
            'inscricao_id' => $estudante->inscricao_id,
            'days_of_week' => [1, 2],
        ]);

        return $estudante;
    }

    public function test_retorna_apenas_ativos_da_linha_com_paginacao(): void
    {
        $this->autenticar();
        $linha = Linha::factory()->create();
        $outra = Linha::factory()->create();
        Estudante::factory()->count(11)->create(['linha_id' => $linha->id]);
        Estudante::factory()->inativo()->create(['linha_id' => $linha->id, 'name' => 'AAA Inativo']);
        Estudante::factory()->create(['linha_id' => $linha->id, 'name' => 'AAA Espera', 'status' => 'Em espera']);
        Estudante::factory()->create(['linha_id' => $linha->id, 'name' => 'AAA Ativo', 'status' => 'ATIVO']);
        $fora = Estudante::factory()->create(['linha_id' => $outra->id]);

        $primeira = $this->getJson("/api/linha/{$linha->id}/estudantes")->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.total', 12)
            ->assertJsonPath('data.0.status', 'ATIVO');
        $segunda = $this->getJson("/api/linha/{$linha->id}/estudantes?page=2")->assertOk()
            ->assertJsonCount(2, 'data')->assertJsonPath('meta.current_page', 2);

        $ids = array_merge(array_column($primeira->json('data'), 'id'), array_column($segunda->json('data'), 'id'));
        $this->assertCount(12, array_unique($ids));
        $this->assertNotContains($fora->id, $ids);
        $this->assertEqualsCanonicalizing(
            Estudante::where('linha_id', $linha->id)->whereRaw('LOWER(status) = ?', ['ativo'])->pluck('id')->all(),
            $ids,
        );
        $this->getJson("/api/linha/{$linha->id}/estudantes?per_page=15")->assertOk()->assertJsonCount(12, 'data');
    }

    public function test_retorna_curso_instituicao_e_contato_sem_documentos_ou_dados_extras(): void
    {
        $this->autenticar('operador');
        $linha = Linha::factory()->create();
        $estudante = $this->estudanteCompleto($linha);

        $this->getJson("/api/linha/{$linha->id}/estudantes")->assertOk()
            ->assertJsonPath('data.0.name', $estudante->name)
            ->assertJsonPath('data.0.email', $estudante->email)
            ->assertJsonPath('data.0.phone', $estudante->phone)
            ->assertJsonPath('data.0.course', 'Engenharia de Software')
            ->assertJsonPath('data.0.semester', '3')
            ->assertJsonPath('data.0.instituicao_name', $estudante->instituicao->name)
            ->assertJsonMissingPath('data.0.documentos')
            ->assertJsonMissingPath('data.0.cpf')
            ->assertJsonMissingPath('data.0.token');
    }

    public function test_consulta_reflete_realocacao_do_estudante(): void
    {
        $this->autenticar();
        $linha = Linha::factory()->create();
        $estudante = $this->estudanteCompleto($linha);
        $outra = Linha::factory()->create();
        $estudante->update(['linha_id' => $outra->id]);
        $this->getJson("/api/linha/{$linha->id}/estudantes")->assertOk()->assertJsonCount(0, 'data');
        $this->getJson("/api/linha/{$outra->id}/estudantes")->assertOk()->assertJsonPath('data.0.id', $estudante->id);
    }

    public function test_estudante_sem_dados_academicos_e_lista_vazia_sao_validos(): void
    {
        $this->autenticar();
        $linha = Linha::factory()->create();
        $this->getJson("/api/linha/{$linha->id}/estudantes")->assertOk()
            ->assertJsonCount(0, 'data')->assertJsonPath('meta.total', 0);
        $estudante = Estudante::factory()->create(['linha_id' => $linha->id]);
        $this->getJson("/api/linha/{$linha->id}/estudantes")->assertOk()
            ->assertJsonPath('data.0.course', null)
            ->assertJsonPath('data.0.instituicao_name', $estudante->instituicao->name);
    }

    public function test_valida_linha_e_parametros_de_paginacao(): void
    {
        $this->autenticar();
        $linha = Linha::factory()->create();
        $this->getJson('/api/linha/999999/estudantes')->assertNotFound();
        $this->getJson("/api/linha/{$linha->id}/estudantes?per_page=1000")->assertUnprocessable();
        $this->getJson("/api/linha/{$linha->id}/estudantes?page=0")->assertUnprocessable();
    }

    public function test_exige_autenticacao_e_permissao_para_ler_estudantes(): void
    {
        $linha = Linha::factory()->create();
        $this->getJson("/api/linha/{$linha->id}/estudantes")->assertUnauthorized();
        $this->seed(RolePermissionSeeder::class);
        $role = Role::create(['title' => 'somente-linhas']);
        $role->permissions()->sync(Permission::where('title', 'linhas.view')->pluck('id'));
        $user = User::factory()->create(['ativo' => true]);
        $user->roles()->sync([$role->id]);
        Sanctum::actingAs($user);
        $this->getJson("/api/linha/{$linha->id}")->assertOk();
        $this->getJson("/api/linha/{$linha->id}/estudantes")->assertForbidden();
    }

    public function test_consultas_nao_crescem_por_estudante_e_nao_carregam_documentos(): void
    {
        $this->autenticar();
        $linha = Linha::factory()->create();
        $this->estudanteCompleto($linha);
        $this->getJson("/api/linha/{$linha->id}/estudantes")->assertOk();
        DB::enableQueryLog();
        DB::flushQueryLog();
        $this->getJson("/api/linha/{$linha->id}/estudantes")->assertOk();
        $umEstudante = count(DB::getQueryLog());
        DB::disableQueryLog();

        for ($i = 0; $i < 5; $i++) {
            $this->estudanteCompleto($linha);
        }

        DB::enableQueryLog();
        DB::flushQueryLog();
        $this->getJson("/api/linha/{$linha->id}/estudantes")->assertOk()->assertJsonCount(6, 'data');
        $queries = DB::getQueryLog();
        DB::disableQueryLog();
        $this->assertCount($umEstudante, $queries);
        foreach ($queries as $query) {
            $this->assertStringNotContainsString('inscricao_documentos', $query['query']);
        }
    }
}
