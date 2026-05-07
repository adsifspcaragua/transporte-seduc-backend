<?php

namespace App\Http\Requests\Reecadastro\Solicitacao;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSolicitacaoRequest extends FormRequest
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
            "estudante_id" => "required|exists:estudantes,id",
            "status" => "required|string",
            "observacoes" => "required|string",
        ];
    }
}
