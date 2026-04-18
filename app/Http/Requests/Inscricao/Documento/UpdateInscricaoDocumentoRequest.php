<?php

namespace App\Http\Requests\Inscricao\Documento;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInscricaoDocumentoRequest extends FormRequest
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
            'name' => "required|string",
            'type' => "required|string",
            'file_path' => "required|string|max:100",
            'status' => "sometimes|max:255",
            'inscricao_id' => "required|exists:inscricoes,id"
        ];
    }
}
