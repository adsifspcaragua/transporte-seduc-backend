<?php

namespace App\Http\Requests\Reecadastro\Documento;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentoRequest extends FormRequest
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
            'estudante_id' => 'sometimes|exists:estudantes,id',
            'type' => 'sometimes|string',
            'file_path' => 'sometimes|string',
        ];
    }
}
