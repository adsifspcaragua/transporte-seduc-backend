<?php

namespace Tests\Feature\Auth;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_retorna_os_papeis_e_a_uniao_das_permissoes_do_usuario(): void
    {
        $user = User::factory()->create();
        $user->roles()->sync(Role::whereIn('title', ['operador', 'motorista'])->pluck('id'));

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('id', $user->id)
            ->assertJsonPath('name', $user->name)
            ->assertJsonPath('email', $user->email);

        $this->assertEqualsCanonicalizing(
            ['operador', 'motorista'],
            $response->json('roles')
        );
        $this->assertEqualsCanonicalizing(
            [
                'estudantes.view', 'estudantes.write',
                'inscricoes.view', 'linhas.view', 'documentos.view',
                'periodos.view', 'solicitacoes.view',
                'frequencias.view', 'frequencias.write', 'frequencias.todas',
                'justificativas.view',
            ],
            $response->json('permissions')
        );
    }

    public function test_admin_recebe_todas_as_permissoes_cadastradas(): void
    {
        $adminRole = Role::where('title', 'admin')->firstOrFail();
        $adminRole->permissions()->detach(
            Permission::where('title', 'users.delete')->value('id')
        );
        $user = User::factory()->create();
        $user->roles()->sync([$adminRole->id]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/me')->assertOk();

        $this->assertSame(['admin'], $response->json('roles'));
        $this->assertEqualsCanonicalizing(
            Permission::query()->pluck('title')->all(),
            $response->json('permissions')
        );
        $this->assertContains('users.delete', $response->json('permissions'));
    }
}
