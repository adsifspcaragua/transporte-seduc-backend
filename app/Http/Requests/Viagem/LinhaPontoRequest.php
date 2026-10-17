<?php

namespace App\Http\Requests\Viagem;

use App\Models\HorarioViagem;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * POST vincula ponto a linha/sentido; PUT/PATCH ajusta ordem e horario.
 */
class LinhaPontoRequest extends FormRequest
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
        $vinculo = $this->isMethod('POST')
            ? [
                'linha_id' => 'required|integer|exists:linhas,id',
                'ponto_id' => 'required|integer|exists:pontos_embarque,id',
                'sentido' => ['required', Rule::in(HorarioViagem::SENTIDOS)],
            ]
            : [];

        return [
            ...$vinculo,
            'ordem' => 'sometimes|nullable|integer|min:1|max:999',
            'horario_previsto' => 'sometimes|nullable|date_format:H:i',
            'janela_antes_min' => 'sometimes|nullable|integer|between:0,180',
            'janela_depois_min' => 'sometimes|nullable|integer|between:0,180',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'linha_id' => ['description' => 'ID da linha.', 'example' => 1],
            'ponto_id' => ['description' => 'ID do ponto.', 'example' => 1],
            'sentido' => ['description' => 'Ida ou Volta.', 'example' => 'Ida'],
            'ordem' => ['description' => 'Posicao do ponto no trajeto.', 'example' => 1],
            'horario_previsto' => ['description' => 'Horario previsto de passagem (HH:MM). Nulo: vale a janela da viagem.', 'example' => '17:40'],
            'janela_antes_min' => ['description' => 'Minutos antes do horario previsto. Nulo: padrao.', 'example' => 10],
            'janela_depois_min' => ['description' => 'Minutos depois do horario previsto. Nulo: padrao.', 'example' => 10],
        ];
    }
}
