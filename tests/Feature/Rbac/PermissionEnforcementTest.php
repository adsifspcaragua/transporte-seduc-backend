<?php

namespace Tests\Feature\Rbac;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PermissionEnforcementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create(['ativo' => true]);
        $user->roles()->sync(Role::where('title', $role)->pluck('id'));

        return $user;
    }

    public function test_unauthenticated_user_is_rejected(): void
    {
        $this->deleteJson('/api/estudantes/999')->assertUnauthorized();
    }

    public function test_operador_cannot_delete_estudante(): void
    {
        Sanctum::actingAs($this->userWithRole('operador'));

        $this->deleteJson('/api/estudantes/999')
            ->assertForbidden()
            ->assertJsonPath('message', 'Acesso negado.');
    }

    public function test_gestor_passes_permission_and_reaches_controller(): void
    {
        Sanctum::actingAs($this->userWithRole('gestor'));

        // Tem a permissão estudantes.delete -> passa o middleware e chega no service (404, não 403).
        $this->deleteJson('/api/estudantes/999')->assertNotFound();
    }

    public function test_admin_bypasses_all_permissions(): void
    {
        Sanctum::actingAs($this->userWithRole('admin'));

        // admin não recebe 403 mesmo em recurso que não existe.
        $this->deleteJson('/api/estudantes/999')->assertNotFound();
        // Listagem de usuários acessível (há ao menos o próprio admin).
        $this->getJson('/api/users')->assertOk();
    }

    public function test_operador_cannot_access_user_management(): void
    {
        Sanctum::actingAs($this->userWithRole('operador'));

        $this->getJson('/api/users')->assertForbidden();
    }

    public function test_inactive_user_is_blocked_by_middleware(): void
    {
        $user = $this->userWithRole('gestor');
        $user->update(['ativo' => false]);

        Sanctum::actingAs($user);

        $this->deleteJson('/api/estudantes/999')
            ->assertForbidden()
            ->assertJsonPath('message', 'Usuário inativo.');
    }
}
