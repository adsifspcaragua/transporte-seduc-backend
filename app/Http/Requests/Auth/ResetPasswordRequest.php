<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'token' => ['description' => 'Token recebido no e-mail de redefinicao.', 'example' => 'a1b2c3...'],
            'email' => ['description' => 'E-mail da conta.', 'example' => 'maria@example.com'],
            'password' => ['description' => 'Nova senha (min. 8 caracteres).', 'example' => 'novaSenha123'],
            'password_confirmation' => ['description' => 'Confirmacao da nova senha.', 'example' => 'novaSenha123'],
        ];
    }
}
