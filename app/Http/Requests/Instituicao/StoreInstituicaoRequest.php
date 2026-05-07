<?php

namespace App\Http\Requests\Instituicao;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreInstituicaoRequest extends FormRequest
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
            'name' => 'required|string|min:3|max:255',
            'linhas_ids' => 'nullable|array',
            'linhas_ids.*' => 'nullable|integer',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'name' => ['description' => 'Nome da instituicao.', 'example' => 'Universidade Estadual'],
            'linhas_ids' => ['description' => 'IDs das linhas vinculadas a instituicao.', 'example' => [1, 2]],
            'linhas_ids.*' => ['description' => 'ID de uma linha vinculada.', 'example' => 1],
        ];
    }
}
