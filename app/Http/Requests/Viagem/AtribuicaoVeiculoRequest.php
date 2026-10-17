<?php

namespace App\Http\Requests\Viagem;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * POST cria a atribuicao; PUT/PATCH so mexe no fim e no motivo.
 */
class AtribuicaoVeiculoRequest extends FormRequest
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
        if (! $this->isMethod('POST')) {
            return [
                'fim' => 'sometimes|nullable|date_format:Y-m-d',
                'motivo' => 'sometimes|nullable|string|max:255',
            ];
        }

        return [
            'veiculo_id' => 'required|integer|exists:veiculos,id',
            'linha_id' => 'required|integer|exists:linhas,id',
            'inicio' => 'required|date_format:Y-m-d',
            'fim' => 'nullable|date_format:Y-m-d|after_or_equal:inicio',
            'motivo' => 'nullable|string|max:255',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'veiculo_id' => ['description' => 'ID da van.', 'example' => 1],
            'linha_id' => ['description' => 'ID da linha.', 'example' => 1],
            'inicio' => ['description' => 'Primeiro dia da atribuicao (AAAA-MM-DD).', 'example' => '2026-10-19'],
            'fim' => ['description' => 'Ultimo dia. Nulo: sem prazo. Igual ao inicio: troca de um dia.', 'example' => null],
            'motivo' => ['description' => 'Motivo, obrigatorio na pratica para trocas excepcionais.', 'example' => 'Van 02 em manutencao.'],
        ];
    }
}
