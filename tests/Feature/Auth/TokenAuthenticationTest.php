<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TokenAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_log_in_and_access_me_with_a_bearer_token(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $loginResponse = $this->postJson('/api/auth/token', [
            'login' => $user->email,
            'password' => 'password',
            'device_name' => 'insomnia',
        ]);

        $loginResponse
            ->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonStructure([
                'access_token',
                'expires_at',
                'token_type',
                'user',
            ]);

        $token = $loginResponse->json('access_token');

        $this->assertNotEmpty($token);

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('id', $user->id);
    }

    public function test_bearer_token_can_be_revoked(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $loginResponse = $this->postJson('/api/auth/token', [
            'login' => $user->email,
            'password' => 'password',
        ]);

        $token = $loginResponse->json('access_token');

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/auth/token/revoke')
            ->assertOk()
            ->assertJsonPath('message', 'Token revogado');

        $this->app['auth']->forgetGuards();

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/me')
            ->assertUnauthorized();
    }

    public function test_bearer_token_must_use_the_token_revoke_route_to_log_out(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $loginResponse = $this->postJson('/api/auth/token', [
            'login' => $user->email,
            'password' => 'password',
        ]);

        $token = $loginResponse->json('access_token');

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/logout')
            ->assertStatus(400)
            ->assertJsonPath('message', 'Use /api/auth/token/revoke para revogar token Bearer.');
    }

    public function test_bearer_token_login_rejects_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->postJson('/api/auth/token', [
            'login' => $user->email,
            'password' => 'wrong-password',
        ])
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Credenciais inválidas');
    }
}
