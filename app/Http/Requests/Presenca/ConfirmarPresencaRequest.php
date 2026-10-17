<?php

namespace App\Http\Requests\Presenca;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ConfirmarPresencaRequest extends FormRequest
{
    public const ERROS_LOCALIZACAO = ['permissao_negada', 'indisponivel', 'tempo_esgotado'];

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Sem localizacao, o frontend informa o erro da Geolocation API em
     * `erro_localizacao`. A tentativa e registrada (e pode ir para revisao),
     * mas nunca aprovada sozinha.
     *
     * O horario do aparelho (`timestamp_ms`) e aceito so como dado auxiliar:
     * quem decide a janela e o relogio do servidor.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'qr_token' => ['required', 'string', 'regex:/^[A-Za-z0-9_-]{20,128}$/'],
            'desafio_id' => 'required|uuid',
            'desafio' => 'required|string|max:1000',
            'assinatura' => 'required|string|max:200',
            'erro_localizacao' => 'nullable|string|in:'.implode(',', self::ERROS_LOCALIZACAO),
            'localizacao' => 'required_without:erro_localizacao|nullable|array',
            'localizacao.latitude' => 'required_with:localizacao|numeric|between:-90,90',
            'localizacao.longitude' => 'required_with:localizacao|numeric|between:-180,180',
            'localizacao.precisao_metros' => 'required_with:localizacao|numeric|gt:0|max:100000',
            'localizacao.timestamp_ms' => 'nullable|integer|min:0',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'qr_token.regex' => 'QR Code não reconhecido. Leia o código afixado na van.',
            'localizacao.required_without' => 'Informe a localização atual ou o erro ao obtê-la.',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'qr_token' => ['description' => 'Token do QR da van, o mesmo usado para pedir o desafio.', 'example' => 'k3Jx9Qe_7sPz2LmN4vB8cD1fG6hJ0aYtRwUiOpEq5Ss'],
            'desafio_id' => ['description' => 'ID do desafio recebido.', 'example' => '9d1c2f0e-8f5a-4c1e-9b7a-1a2b3c4d5e6f'],
            'desafio' => ['description' => 'Texto do desafio exatamente como recebido.', 'example' => "SIGTU\nv1\npresenca\n..."],
            'assinatura' => ['description' => 'Assinatura P1363 (r||s) do texto feita com a chave do dispositivo, em base64url.', 'example' => 'q83v...'],
            'erro_localizacao' => ['description' => 'Quando nao ha localizacao: permissao_negada, indisponivel ou tempo_esgotado.', 'example' => null],
            'localizacao.latitude' => ['description' => 'Latitude informada pela Geolocation API.', 'example' => -23.62],
            'localizacao.longitude' => ['description' => 'Longitude informada pela Geolocation API.', 'example' => -45.41],
            'localizacao.precisao_metros' => ['description' => 'Precisao (accuracy) em metros.', 'example' => 18.5],
            'localizacao.timestamp_ms' => ['description' => 'Horario da leitura no aparelho. Apenas auxiliar.', 'example' => 1792170000000],
        ];
    }
}
