<?php

namespace App\Http\Requests\Viagem;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AutorizacaoLinhaRequest extends FormRequest
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
            'linha_id' => 'required|integer|exists:linhas,id',
            'inicio' => 'required|date_format:Y-m-d',
            'fim' => 'nullable|date_format:Y-m-d|after_or_equal:inicio',
            'motivo' => 'required|string|min:5|max:255',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'estudante_id' => ['description' => 'ID do estudante.', 'example' => 1],
            'linha_id' => ['description' => 'Linha adicional autorizada.', 'example' => 2],
            'inicio' => ['description' => 'Primeiro dia (AAAA-MM-DD).', 'example' => '2026-10-19'],
            'fim' => ['description' => 'Ultimo dia. Nulo: sem prazo.', 'example' => '2026-10-23'],
            'motivo' => ['description' => 'Motivo do remanejamento.', 'example' => 'Estagio no periodo noturno nesta semana.'],
        ];
    }
}
