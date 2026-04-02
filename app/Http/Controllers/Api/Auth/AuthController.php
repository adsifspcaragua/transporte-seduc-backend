<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'login' => ['required', 'string', 'max:255'],
                'password' => ['required', 'string', 'max:255'],
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

            if (!$isEmail && !$isCpf) {
                $validator->errors()->add('login', 'Informe um e-mail válido ou um CPF válido.');
                return;
            }

            if ($isCpf && !$this->isValidCpf($cpf)) {
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

        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $login)->first();
        } else {
            $cpf = preg_replace('/\D/', '', $login);

            $user = User::whereRaw(
                "REPLACE(REPLACE(cpf, '.', ''), '-', '') = ?",
                [$cpf]
            )->first();
        }

        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json([
                'message' => 'Credenciais inválidas',
            ], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logout realizado',
        ], 200);
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
