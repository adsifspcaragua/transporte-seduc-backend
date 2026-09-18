<?php

namespace App\Http\Requests\Frequencia;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AbrirChamadaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * A data pode ser passada (o motorista lancando a folha de papel depois),
     * mas nunca futura: nao ha como marcar presenca de quem ainda nao embarcou.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'linha_id' => 'required|integer|exists:linhas,id',
            'data' => 'required|date_format:Y-m-d|before_or_equal:today',
            'observacoes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'linha_id.exists' => 'Linha não encontrada.',
            'data.before_or_equal' => 'Não é possível fazer chamada de uma data futura.',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'linha_id' => ['description' => 'ID da linha.', 'example' => 1],
            'data' => ['description' => 'Dia da chamada no formato AAAA-MM-DD. Nao pode ser futuro.', 'example' => '2026-09-11'],
            'observacoes' => ['description' => 'Observacao geral do dia (atraso, troca de veiculo etc.).', 'example' => 'Onibus reserva por manutencao.'],
        ];
    }
}
