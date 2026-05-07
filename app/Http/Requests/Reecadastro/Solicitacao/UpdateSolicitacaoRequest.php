<?php

namespace App\Http\Requests\Reecadastro\Solicitacao;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSolicitacaoRequest extends FormRequest
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
            "estudante_id" => "sometimes|exists:estudantes,id",
            "status" => "sometimes|string",
            "observacoes" => "sometimes|string",
        ];
    }
}
