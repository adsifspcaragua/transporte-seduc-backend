<?php

namespace App\Http\Requests\Frequencia;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CriarJustificativaRequest extends FormRequest
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
            'frequencia_id' => 'required|integer',
            'motivo' => 'required|string|min:3|max:1000',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'motivo.required' => 'Informe o motivo da falta.',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'frequencia_id' => ['description' => 'ID da marcacao de falta (frequencias[].id da chamada).', 'example' => 10],
            'motivo' => ['description' => 'Motivo da falta.', 'example' => 'Atestado medico entregue na secretaria.'],
        ];
    }
}
