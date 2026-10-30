<?php

namespace App\Http\Requests\Estudante;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class VerificarCodigoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cpf' => 'required|string|size:11',
            'codigo' => 'required|string|digits:6',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'cpf' => ['description' => 'CPF do estudante com 11 digitos, somente numeros.', 'example' => '12345678901'],
            'codigo' => ['description' => 'Codigo de 6 digitos recebido por e-mail.', 'example' => '482913'],
        ];
    }
}
