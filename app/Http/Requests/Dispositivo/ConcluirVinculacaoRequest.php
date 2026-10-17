<?php

namespace App\Http\Requests\Dispositivo;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ConcluirVinculacaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'chave_publica' => 'required|string|max:500',
            'desafio_id' => 'required|uuid',
            'desafio' => 'required|string|max:1000',
            'assinatura' => 'required|string|max:200',
            'rotulo' => 'nullable|string|max:100',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'chave_publica' => ['description' => 'Chave publica ECDSA P-256 exportada com crypto.subtle.exportKey("spki"), em base64.', 'example' => 'MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAE...'],
            'desafio_id' => ['description' => 'ID do desafio recebido em /desafio.', 'example' => '9d1c2f0e-8f5a-4c1e-9b7a-1a2b3c4d5e6f'],
            'desafio' => ['description' => 'Texto do desafio exatamente como recebido.', 'example' => "SIGTU\nv1\nvinculacao\n..."],
            'assinatura' => ['description' => 'Assinatura ECDSA/SHA-256 do texto, no formato da Web Crypto (r||s, 64 bytes), em base64url.', 'example' => 'q83v...'],
            'rotulo' => ['description' => 'Nome informativo do navegador. Nao e usado como prova de nada.', 'example' => 'Chrome no celular'],
        ];
    }
}
