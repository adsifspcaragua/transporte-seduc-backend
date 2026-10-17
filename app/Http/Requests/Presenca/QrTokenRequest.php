<?php

namespace App\Http\Requests\Presenca;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class QrTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * So o token (parametro `t` da URL do QR). O frontend aceita apenas URLs
     * do dominio do SIGTU e extrai o token antes de enviar.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'qr_token' => ['required', 'string', 'regex:/^[A-Za-z0-9_-]{20,128}$/'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'qr_token.regex' => 'QR Code não reconhecido. Leia o código afixado na van.',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'qr_token' => ['description' => 'Token lido do QR Code da van (parametro t da URL).', 'example' => 'k3Jx9Qe_7sPz2LmN4vB8cD1fG6hJ0aYtRwUiOpEq5Ss'],
        ];
    }
}
