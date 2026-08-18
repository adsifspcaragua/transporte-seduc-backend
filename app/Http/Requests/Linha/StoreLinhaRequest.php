<?php

namespace App\Http\Requests\Linha;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreLinhaRequest extends FormRequest
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
            'name' => 'required|string|max:255|min:3',
            'description' => 'sometimes|string|max:255|min:3',
            'departure_time' => 'sometimes|date_format:H:i',
            'return_time' => 'sometimes|date_format:H:i|after:departure_time',
            'max_capacity' => 'required|integer|min:1',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'name' => ['description' => 'Nome da linha.', 'example' => 'Linha Centro'],
            'description' => ['description' => 'Descricao da rota da linha.', 'example' => 'Rota principal ate o centro'],
            'departure_time' => ['description' => 'Horario de saida no formato HH:MM.', 'example' => '07:00'],
            'return_time' => ['description' => 'Horario de retorno no formato HH:MM.', 'example' => '18:00'],
            'max_capacity' => ['description' => 'Capacidade maxima de estudantes.', 'example' => 40],
        ];
    }
}
