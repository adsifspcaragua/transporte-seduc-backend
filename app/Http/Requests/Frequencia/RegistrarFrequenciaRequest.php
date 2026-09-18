<?php

namespace App\Http\Requests\Frequencia;

use App\Models\Frequencia;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegistrarFrequenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * A falta justificada exige o motivo: sem ele, e so uma falta com outro nome.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'frequencias' => 'required|array|min:1',
            'frequencias.*.estudante_id' => 'required|integer',
            'frequencias.*.situacao' => ['required', 'string', Rule::in(Frequencia::SITUACOES)],
            'frequencias.*.observacao' => [
                'nullable',
                'string',
                'max:255',
                'required_if:frequencias.*.situacao,'.Frequencia::JUSTIFICADA,
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'frequencias.required' => 'Informe ao menos uma marcação.',
            'frequencias.*.situacao.in' => 'A situação deve ser Presente, Falta ou Justificada.',
            'frequencias.*.observacao.required_if' => 'Informe o motivo da falta justificada.',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'frequencias' => ['description' => 'Marcacoes a registrar. Pode ser a folha inteira ou so parte dela.'],
            'frequencias.*.estudante_id' => ['description' => 'ID do estudante na chamada.', 'example' => 1],
            'frequencias.*.situacao' => ['description' => 'Presente, Falta ou Justificada.', 'example' => 'Falta'],
            'frequencias.*.observacao' => ['description' => 'Motivo, obrigatorio quando a situacao e Justificada.', 'example' => 'Atestado medico'],
        ];
    }
}
