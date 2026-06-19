<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
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
            'email' => 'required|email',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'email' => ['description' => 'E-mail da conta para envio do link de redefinicao.', 'example' => 'maria@example.com'],
        ];
    }
}
