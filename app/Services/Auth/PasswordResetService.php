<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetService
{
    /**
     * Envia o link de redefinição de senha.
     *
     * Resposta sempre genérica (200) para não revelar se o e-mail existe (anti-enumeração).
     */
    public function sendResetLink(string $email): JsonResponse
    {
        Password::sendResetLink(['email' => $email]);

        return response()->json([
            'message' => 'Se o e-mail estiver cadastrado, enviaremos um link de redefinição.',
        ], 200);
    }

    /**
     * Redefine a senha a partir do token recebido por e-mail.
     *
     * @param  array<string, mixed>  $data
     */
    public function reset(array $data): JsonResponse
    {
        $status = Password::reset(
            $data,
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PasswordReset) {
            return response()->json([
                'message' => 'Senha redefinida com sucesso.',
            ], 200);
        }

        return response()->json([
            'message' => 'Não foi possível redefinir a senha. Token inválido ou expirado.',
        ], 422);
    }
}
