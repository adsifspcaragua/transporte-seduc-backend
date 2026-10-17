<?php

namespace App\Http\Requests\Presenca;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PedirRevisaoRequest extends FormRequest
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
            'motivo' => 'required|string|min:5|max:1000',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'motivo' => ['description' => 'O que aconteceu na tentativa.', 'example' => 'Estava no ponto, mas o GPS nao pegou dentro da van.'],
        ];
    }
}
