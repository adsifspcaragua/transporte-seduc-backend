<?php

namespace App\Http\Requests\Presenca;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ComprovanteOfflineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * O formato do comprovante e conferido no service, linha a linha, contra o
     * texto que foi assinado (ver ComprovanteOffline).
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'comprovante' => 'required|string|max:700',
            'assinatura' => 'required|string|max:200',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'comprovante' => ['description' => 'Texto do comprovante montado pelo navegador sem conexao, exatamente como foi assinado.', 'example' => "SIGTU\nv1\npresenca-offline\n..."],
            'assinatura' => ['description' => 'Assinatura P1363 (r||s) do texto com a chave do dispositivo, em base64url.', 'example' => 'q83v...'],
        ];
    }
}
