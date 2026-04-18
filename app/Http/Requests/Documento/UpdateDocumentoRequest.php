<?php

namespace App\Http\Requests\Documento;

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
            "inscricao_id" => "required|exists:inscricoes,id",
            "type" => "required|string|max:100",
            'file_path' => 'required|file|mimes:pdf,doc,docx,png,jpg|max:2048',
            "status" => "required|string|max:255",
        ];
    }
}
