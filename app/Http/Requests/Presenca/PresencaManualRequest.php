<?php

namespace App\Http\Requests\Presenca;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PresencaManualRequest extends FormRequest
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
            'estudante_id' => 'required|integer|exists:estudantes,id',
            'viagem_id' => 'required|integer|exists:viagens,id',
            'justificativa' => 'required|string|min:10|max:1000',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'estudante_id' => ['description' => 'ID do estudante.', 'example' => 1],
            'viagem_id' => ['description' => 'ID da viagem.', 'example' => 10],
            'justificativa' => ['description' => 'Motivo da excecao administrativa.', 'example' => 'Celular sem bateria; embarque confirmado pela secretaria.'],
        ];
    }
}
