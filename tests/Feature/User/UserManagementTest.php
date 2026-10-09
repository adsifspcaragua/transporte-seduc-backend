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

    public function test_rejects_a_role_that_is_not_available_for_system_users(): void
    {
        Sanctum::actingAs($this->userWithRole('admin'));

        $this->postJson('/api/users', [
            'name' => 'Estudante sem papel',
            'email' => 'estudante@example.com',
            'password' => 'password123',
            'role' => 'estudante',
        ])->assertUnprocessable()->assertJsonValidationErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'estudante@example.com']);
    }

    public function test_update_without_role_preserves_all_current_roles(): void
    {
        Sanctum::actingAs($this->userWithRole('admin'));
        $target = $this->userWithRole('operador');
        $target->roles()->attach(Role::where('title', 'motorista')->value('id'));

        $this->putJson("/api/users/{$target->id}", [
            'name' => 'Nome atualizado',
            'email' => $target->email,
        ])->assertOk();

        $this->assertEqualsCanonicalizing(
            ['operador', 'motorista'],
            $target->fresh()->roles()->pluck('title')->all(),
        );
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

    public function test_user_cannot_inactivate_or_delete_itself(): void
    {
        $admin = $this->userWithRole('admin');
        Sanctum::actingAs($admin);

        $this->patchJson("/api/users/{$admin->id}/inativar")
            ->assertUnprocessable();
        $this->deleteJson("/api/users/{$admin->id}")
            ->assertUnprocessable();

        $this->assertTrue((bool) $admin->fresh()->ativo);
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_user_cannot_inactivate_itself_through_update(): void
    {
        $admin = $this->userWithRole('admin');
        Sanctum::actingAs($admin);

        $this->putJson("/api/users/{$admin->id}", [
            'name' => $admin->name,
            'email' => $admin->email,
            'ativo' => false,
        ])->assertUnprocessable();

        $this->assertTrue((bool) $admin->fresh()->ativo);
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
