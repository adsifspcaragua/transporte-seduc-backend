<?php

namespace App\Http\Requests\Inscricao\Documento;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentoRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'file_path' => 'required|file|mimes:pdf,doc,docx,png,jpg|max:2048',
            'status' => 'prohibited',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'name' => ['description' => 'Nome do documento exigido na inscricao.', 'example' => 'foto'],
            'type' => ['description' => 'Tipo ou categoria do documento.', 'example' => 'imagem'],
            'file_path' => ['description' => 'Arquivo do documento.', 'type' => 'file'],
            'status' => ['description' => 'Campo controlado pelo sistema. Nao envie este campo.', 'example' => 'No-example'],
        ];
    }
}
