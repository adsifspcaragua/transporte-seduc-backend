<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInstituicaoRequest extends FormRequest
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
        $id = $this->route('instituicao');
        return [
            'name' => "sometimes|string|min:3|max:255|unique:instituicoes,id,{$id}",
            'linhas_ids' => 'sometimes|array',
            'linhas_ids.*' => 'sometimes|integer',
        ];
    }
}
