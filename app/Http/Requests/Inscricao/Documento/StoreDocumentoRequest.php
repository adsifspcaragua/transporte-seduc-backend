<?php

namespace App\Http\Requests\Inscricao\Documento;

use App\Models\InscricaoDocumento;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', Rule::in(array_keys(InscricaoDocumento::TIPOS))],
            'type' => 'required|string|max:100',
            'file_path' => 'required|file|mimes:pdf,png,jpg,jpeg|max:5120',
            'status' => 'prohibited',
        ];
    }
}
