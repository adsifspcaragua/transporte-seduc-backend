<?php

namespace App\Http\Requests\Inscricao;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AnaliseInscricaoRequest extends FormRequest
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
            'decisao' => 'required|string|in:Aprovado,Rejeitado',
            'motivo' => 'required_if:decisao,Rejeitado|nullable|string|min:3|max:255',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'decisao.in' => 'A decisão deve ser Aprovado ou Rejeitado.',
            'motivo.required_if' => 'Informe o motivo da rejeição.',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'decisao' => ['description' => 'Decisao da responsavel sobre a inscricao.', 'example' => 'Aprovado'],
            'motivo' => ['description' => 'Motivo da rejeicao. Obrigatorio quando a decisao e Rejeitado.', 'example' => 'Comprovante de residencia ilegivel.'],
        ];
    }
}
