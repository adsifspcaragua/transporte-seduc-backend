<?php

namespace App\Http\Requests\Inscricao;

use App\Models\Inscricao;
use App\Models\InscricaoDocumento;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'decisao' => 'required|string|in:Aprovado,Rejeitado,Devolvido',
            'motivo' => 'required_if:decisao,Rejeitado|required_if:decisao,Devolvido|nullable|string|min:3|max:255',
            // Documentos que a responsável quer de volta. Só valem na devolução:
            // os listados voltam a pendentes e precisam ser reenviados.
            'documentos' => 'sometimes|array',
            'documentos.*' => ['string', Rule::in(array_keys(InscricaoDocumento::TIPOS))],
            // Campos do cadastro a corrigir. O motivo em texto diz o que houve;
            // isto diz onde, para o estudante não ter de adivinhar.
            'campos' => 'sometimes|array',
            'campos.*' => ['string', Rule::in(Inscricao::camposCorrigiveis())],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'decisao.in' => 'A decisão deve ser Aprovado, Rejeitado ou Devolvido.',
            'motivo.required_if' => 'Informe o motivo.',
            'documentos.*.in' => 'Documento desconhecido na lista de reenvio.',
            'campos.*.in' => 'Campo inválido para correção.',
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
