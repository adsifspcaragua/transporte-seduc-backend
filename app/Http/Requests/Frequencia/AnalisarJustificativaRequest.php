<?php

namespace App\Http\Requests\Frequencia;

use App\Models\Justificativa;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnalisarJustificativaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Rejeitar faz a falta voltar a contar e pode tirar o estudante do
     * beneficio, entao o parecer e obrigatorio: ele precisa saber o porque.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'decisao' => ['required', Rule::in([Justificativa::APROVADA, Justificativa::REJEITADA])],
            'parecer' => 'nullable|string|max:1000|required_if:decisao,'.Justificativa::REJEITADA,
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'decisao.in' => 'A decisão deve ser Aprovada ou Rejeitada.',
            'parecer.required_if' => 'Informe o motivo da rejeição.',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'decisao' => ['description' => 'Aprovada (retira a falta) ou Rejeitada (a falta volta a contar).', 'example' => 'Rejeitada'],
            'parecer' => ['description' => 'Parecer da responsavel. Obrigatorio na rejeicao.', 'example' => 'Atestado sem data legivel.'],
        ];
    }
}
