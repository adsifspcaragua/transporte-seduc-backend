<?php

namespace App\Http\Requests\Reecadastro\Publico;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ConsultaCpfRequest extends FormRequest
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
            'cpf' => 'required|string|size:11',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'cpf' => ['description' => 'CPF do estudante com 11 digitos, somente numeros.', 'example' => '12345678901'],
        ];
    }
}
