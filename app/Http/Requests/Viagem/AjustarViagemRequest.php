<?php

namespace App\Http\Requests\Viagem;

use App\Models\Viagem;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AjustarViagemRequest extends FormRequest
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
            'motivo' => 'required|string|min:5|max:255',
            'veiculo_id' => ['sometimes', 'nullable', 'integer', Rule::exists('veiculos', 'id')->where('ativo', true)],
            'janela_inicio' => 'sometimes|date_format:Y-m-d H:i',
            'janela_fim' => 'sometimes|date_format:Y-m-d H:i',
            'status' => ['sometimes', Rule::in(Viagem::STATUS)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'veiculo_id.exists' => 'Escolha uma van ativa.',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'motivo' => ['description' => 'Motivo do ajuste (atraso, manutencao, troca de van).', 'example' => 'Van 02 em manutencao; Van 04 cobre a viagem.'],
            'veiculo_id' => ['description' => 'Van desta viagem. Deixa de seguir a atribuicao da linha.', 'example' => 4],
            'janela_inicio' => ['description' => 'Novo inicio da janela de presenca (AAAA-MM-DD HH:MM).', 'example' => '2026-10-19 17:30'],
            'janela_fim' => ['description' => 'Novo fim da janela de presenca (AAAA-MM-DD HH:MM).', 'example' => '2026-10-19 18:10'],
            'status' => ['description' => 'Programada ou Cancelada.', 'example' => 'Programada'],
        ];
    }
}
