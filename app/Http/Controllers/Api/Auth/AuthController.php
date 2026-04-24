<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\TransientToken;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $result = $this->resolveAuthenticatedUser($request);

        if ($result instanceof JsonResponse) {
            return $result;
        }

        $user = $result;

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return response()->json([
            'user' => $user,
        ], 200);
    }

    public function tokenLogin(Request $request): JsonResponse
    {
        $result = $this->resolveAuthenticatedUser($request);

        if ($result instanceof JsonResponse) {
            return $result;
        }

        $user = $result;
        $tokenName = trim((string) $request->input('device_name', 'insomnia'));

        if ($tokenName === '') {
            $tokenName = 'insomnia';
        }

        $expiresAt = $this->apiTokenExpiresAt();
        $token = $user->createToken($tokenName, ['*'], $expiresAt);

        return response()->json([
            'token_type' => 'Bearer',
            'access_token' => $token->plainTextToken,
            'expires_at' => $expiresAt?->toISOString(),
            'user' => $user,
        ], 200);
    }

    public function logout(Request $request): JsonResponse
    {
        $currentToken = $request->user()?->currentAccessToken();

        if ($currentToken && ! $currentToken instanceof TransientToken) {
            return response()->json([
                'message' => 'Use /api/auth/token/revoke para revogar token Bearer.',
            ], 400);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logout realizado',
        ], 200);
    }

    public function tokenLogout(Request $request): JsonResponse
    {
        $currentToken = $request->user()?->currentAccessToken();

        if (! $currentToken || $currentToken instanceof TransientToken) {
            return response()->json([
                'message' => 'Nenhum token Bearer encontrado nesta requisição.',
            ], 400);
        }

        $currentToken->delete();

        return response()->json([
            'message' => 'Token revogado',
        ], 200);
    }

    private function resolveAuthenticatedUser(Request $request): User|JsonResponse
    {
        $validator = Validator::make(
            $request->all(),
            [
                'login' => ['required', 'string', 'max:255'],
                'password' => ['required', 'string', 'max:255'],
                'device_name' => ['nullable', 'string', 'max:255'],
            ],
            [
                'login.required' => 'Informe o e-mail ou CPF.',
                'password.required' => 'Informe a senha.',
            ]
        );

        $validator->after(function ($validator) use ($request) {
            $login = trim((string) $request->input('login', ''));
            $cpf = preg_replace('/\D/', '', $login);

            $isEmail = filter_var($login, FILTER_VALIDATE_EMAIL) !== false;
            $isCpf = strlen($cpf) === 11;

            if (! $isEmail && ! $isCpf) {
                $validator->errors()->add('login', 'Informe um e-mail válido ou um CPF válido.');

                return;
            }

            if ($isCpf && ! $this->isValidCpf($cpf)) {
                $validator->errors()->add('login', 'Informe um CPF válido.');
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $login = trim((string) $request->input('login'));
        $password = (string) $request->input('password');
        $user = $this->findUserByLogin($login);

        if (! $user || ! Hash::check($password, $user->password)) {
            return response()->json([
                'message' => 'Credenciais inválidas',
            ], 401);
        }

        return $user;
    }

    private function findUserByLogin(string $login): ?User
    {
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            return User::where('email', $login)->first();
        }

        $cpf = preg_replace('/\D/', '', $login);

        return User::whereRaw(
            "REPLACE(REPLACE(cpf, '.', ''), '-', '') = ?",
            [$cpf]
        )->first();
    }

    private function apiTokenExpiresAt(): ?\DateTimeInterface
    {
        $expirationMinutes = config('auth.api_token_expiration_minutes');

        if (! is_numeric($expirationMinutes) || (int) $expirationMinutes <= 0) {
            return null;
        }

        return now()->addMinutes((int) $expirationMinutes);
    }

    private function isValidCpf(string $cpf): bool
    {
        $cpf = preg_replace('/\D/', '', $cpf);

        if (strlen($cpf) !== 11) {
            return false;
        }

        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $sum = 0;

            for ($i = 0; $i < $t; $i++) {
                $sum += ((int) $cpf[$i]) * (($t + 1) - $i);
            }

            $digit = ((10 * $sum) % 11) % 10;

            if ((int) $cpf[$t] !== $digit) {
                return false;
            }
        }

        return true;
    }
}
