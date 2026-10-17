<?php

namespace App\Http\Requests\Viagem;

use App\Models\HorarioViagem;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HorarioViagemRequest extends FormRequest
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
        $obrigatorio = $this->isMethod('POST') ? 'required' : 'sometimes';

        return [
            'linha_id' => [$obrigatorio, 'integer', 'exists:linhas,id'],
            'sentido' => [$obrigatorio, Rule::in(HorarioViagem::SENTIDOS)],
            'dias_semana' => [$obrigatorio, 'array', 'min:1'],
            'dias_semana.*' => 'integer|between:0,6|distinct',
            'horario_saida' => [$obrigatorio, 'date_format:H:i'],
            'janela_antes_min' => 'sometimes|nullable|integer|between:0,180',
            'janela_depois_min' => 'sometimes|nullable|integer|between:0,180',
            'vigencia_inicio' => 'sometimes|nullable|date_format:Y-m-d',
            'vigencia_fim' => 'sometimes|nullable|date_format:Y-m-d|after_or_equal:vigencia_inicio',
            'ativo' => 'sometimes|boolean',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'linha_id' => ['description' => 'ID da linha.', 'example' => 1],
            'sentido' => ['description' => 'Ida ou Volta.', 'example' => 'Ida'],
            'dias_semana' => ['description' => 'Dias da semana (0 = domingo ... 6 = sabado).', 'example' => [1, 2, 3, 4, 5]],
            'horario_saida' => ['description' => 'Horario de saida (HH:MM).', 'example' => '17:30'],
            'janela_antes_min' => ['description' => 'Minutos antes da saida em que a presenca ja e aceita. Nulo: padrao.', 'example' => 15],
            'janela_depois_min' => ['description' => 'Minutos depois da saida em que a presenca ainda e aceita. Nulo: padrao.', 'example' => 15],
            'vigencia_inicio' => ['description' => 'Primeiro dia em que o horario vale.', 'example' => '2026-08-01'],
            'vigencia_fim' => ['description' => 'Ultimo dia em que o horario vale.', 'example' => '2026-12-18'],
            'ativo' => ['description' => 'Horario inativo nao gera viagens.', 'example' => true],
        ];
    }
}
