<?php

namespace App\Http\Requests\Viagem;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DiaSemOperacaoRequest extends FormRequest
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
            'data' => 'required|date_format:Y-m-d',
            'linha_id' => 'nullable|integer|exists:linhas,id',
            'motivo' => 'required|string|min:3|max:255',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'data' => ['description' => 'Dia sem operacao (AAAA-MM-DD).', 'example' => '2026-11-02'],
            'linha_id' => ['description' => 'Linha afetada. Nulo: todas.', 'example' => null],
            'motivo' => ['description' => 'Feriado, recesso, paralisacao etc.', 'example' => 'Finados'],
        ];
    }
}
