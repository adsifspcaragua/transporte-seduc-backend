<?php

namespace App\Http\Requests\Reecadastro\Solicitacao;

use App\Models\DocumentacaoReecadastro;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnaliseSolicitacaoRequest extends FormRequest
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
            'decisao' => 'required|string|in:Aprovado,Rejeitado,Pendencia',
            'motivo' => 'required_unless:decisao,Aprovado|nullable|string|min:3|max:255',
            'documentos' => 'required_if:decisao,Pendencia|array|min:1',
            'documentos.*' => [Rule::in(DocumentacaoReecadastro::slugs())],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'decisao.in' => 'A decisão deve ser Aprovado, Rejeitado ou Pendencia.',
            'motivo.required_unless' => 'Informe o motivo da devolução ou da rejeição.',
            'documentos.required_if' => 'Informe quais documentos devem ser reenviados.',
            'documentos.*.in' => 'Documento inválido para o recadastro.',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'decisao' => ['description' => 'Decisao da homologacao: Aprovado, Rejeitado ou Pendencia.', 'example' => 'Pendencia'],
            'motivo' => ['description' => 'Devolutiva exibida ao estudante. Obrigatoria fora da aprovacao.', 'example' => 'Comprovante de residencia ilegivel.'],
            'documentos' => ['description' => 'Documentos devolvidos para reenvio. Obrigatorio quando a decisao e Pendencia.', 'example' => ['comprovante_residencia']],
        ];
    }
}
