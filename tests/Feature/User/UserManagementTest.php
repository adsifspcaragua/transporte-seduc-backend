<?php

namespace Tests\Feature\User;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserManagementTest extends TestCase
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

    public function test_admin_can_create_a_gestor_user_with_role(): void
    {
        Sanctum::actingAs($this->userWithRole('admin'));

        $response = $this->postJson('/api/users', [
            'name' => 'Novo Gestor',
            'email' => 'gestor@example.com',
            'password' => 'password123',
            'cpf' => '98765432100',
            'matricula' => 5001,
            'data_nascimento' => '1990-05-10',
            'role' => 'gestor',
        ]);

        $response->assertCreated()->assertJsonPath('roles.0', 'gestor');

        $created = User::where('email', 'gestor@example.com')->first();
        $this->assertNotNull($created);
        $this->assertTrue($created->hasRole('gestor'));
        $this->assertTrue((bool) $created->ativo);
    }

    public function test_operador_cannot_create_users(): void
    {
        Sanctum::actingAs($this->userWithRole('operador'));

        $this->postJson('/api/users', [
            'name' => 'Hacker',
            'email' => 'hacker@example.com',
            'password' => 'password123',
            'role' => 'admin',
        ])->assertForbidden();
    }

    public function test_admin_can_inactivate_a_user(): void
    {
        Sanctum::actingAs($this->userWithRole('admin'));
        $target = $this->userWithRole('operador');

        $this->patchJson("/api/users/{$target->id}/inativar")
            ->assertOk()
            ->assertJsonPath('data.ativo', false);

        $this->assertFalse((bool) $target->fresh()->ativo);
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        $user = User::factory()->create([
            'email' => 'inativo@example.com',
            'password' => bcrypt('password'),
            'ativo' => false,
        ]);

        $this->postJson('/api/auth/token', [
            'login' => $user->email,
            'password' => 'password',
        ])
            ->assertForbidden()
            ->assertJsonPath('message', 'Usuário inativo.');
    }
}
