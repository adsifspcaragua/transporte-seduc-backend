<?php

namespace App\Http\Requests\Reecadastro\Periodo;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePeriodoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ano' => 'sometimes|integer|min:2000|max:2100',
            'semestre' => [
                'sometimes',
                'integer',
                Rule::in([1, 2]),
                Rule::unique('periodos_reecadastro', 'semestre')
                    ->where('ano', $this->input('ano'))
                    ->ignore($this->route('periodo')),
            ],
            'data_inicio' => 'sometimes|date|date_format:Y-m-d',
            'data_fim' => 'sometimes|date|date_format:Y-m-d|after_or_equal:data_inicio',
            'observacoes' => 'nullable|string|max:255',
            'status' => 'prohibited',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'semestre.unique' => 'Já existe um período de recadastro para este ano e semestre.',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'ano' => ['description' => 'Ano do periodo de recadastro.', 'example' => 2026],
            'semestre' => ['description' => 'Semestre do periodo: 1 ou 2.', 'example' => 1],
            'data_inicio' => ['description' => 'Data de inicio no formato AAAA-MM-DD.', 'example' => '2026-02-01'],
            'data_fim' => ['description' => 'Data de encerramento no formato AAAA-MM-DD.', 'example' => '2026-02-28'],
            'observacoes' => ['description' => 'Observacoes internas sobre o periodo.', 'example' => 'Prazo prorrogado em uma semana.'],
            'status' => ['description' => 'Campo controlado pelo sistema. Use as rotas de abrir/fechar.', 'example' => 'No-example'],
        ];
    }
}
