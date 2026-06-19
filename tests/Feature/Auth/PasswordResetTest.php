<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_sends_reset_notification(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'reset@example.com']);

        $this->postJson('/api/password/email', ['email' => $user->email])
            ->assertOk();

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_forgot_password_is_generic_for_unknown_email(): void
    {
        Notification::fake();

        $this->postJson('/api/password/email', ['email' => 'desconhecido@example.com'])
            ->assertOk();

        Notification::assertNothingSent();
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'reset@example.com',
            'password' => bcrypt('senha-antiga'),
        ]);

        $token = Password::createToken($user);

        $this->postJson('/api/password/reset', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'nova-senha123',
            'password_confirmation' => 'nova-senha123',
        ])
            ->assertOk()
            ->assertJsonPath('message', 'Senha redefinida com sucesso.');

        $this->assertTrue(Hash::check('nova-senha123', $user->fresh()->password));
    }

    public function test_reset_fails_with_invalid_token(): void
    {
        $user = User::factory()->create(['email' => 'reset@example.com']);

        $this->postJson('/api/password/reset', [
            'token' => 'token-invalido',
            'email' => $user->email,
            'password' => 'nova-senha123',
            'password_confirmation' => 'nova-senha123',
        ])->assertStatus(422);
    }
}
