<?php

namespace App\Http\Requests\Reecadastro\Periodo;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class InativarAusentesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Inativar corta o transporte do estudante, entao a lista e obrigatoria e
     * explicita: nao existe "inativar todos" implicito.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'estudantes' => 'required|array|min:1',
            'estudantes.*' => 'integer|exists:estudantes,id',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'estudantes.required' => 'Selecione ao menos um estudante.',
            'estudantes.*.exists' => 'Estudante não encontrado.',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'estudantes' => ['description' => 'IDs dos estudantes a inativar.', 'example' => [1, 2]],
        ];
    }
}
