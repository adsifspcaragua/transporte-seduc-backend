<?php

namespace App\Http\Requests\Reecadastro\Publico;

use App\Models\DocumentacaoReecadastro;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EnviarDocumentoRequest extends FormRequest
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
            'token' => 'required|string',
            'type' => ['required', 'string', Rule::in(DocumentacaoReecadastro::slugs())],
            'arquivo' => 'required|file|mimes:pdf,png,jpg,jpeg|max:5120',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.in' => 'Documento inválido para o recadastro.',
            'arquivo.mimes' => 'Envie o documento em PDF ou imagem (png/jpg).',
            'arquivo.max' => 'O arquivo deve ter no máximo 5 MB.',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'token' => ['description' => 'Token devolvido pela consulta do CPF.', 'example' => 'No-example'],
            'type' => ['description' => 'Documento enviado: '.implode(', ', DocumentacaoReecadastro::slugs()).'.', 'example' => 'declaracao_matricula'],
            'arquivo' => ['description' => 'Arquivo do documento (PDF ou imagem, ate 5 MB).', 'type' => 'file'],
        ];
    }
}
