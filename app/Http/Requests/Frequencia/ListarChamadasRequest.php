<?php

namespace App\Http\Requests\Frequencia;

use App\Models\Chamada;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListarChamadasRequest extends FormRequest
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
            'linha_id' => 'nullable|integer',
            'status' => ['nullable', Rule::in([Chamada::ABERTA, Chamada::FECHADA])],
            'data' => 'nullable|date_format:Y-m-d',
            'de' => 'nullable|date_format:Y-m-d',
            'ate' => 'nullable|date_format:Y-m-d|after_or_equal:de',
            'per_page' => 'nullable|integer|in:10,15,20,30',
        ];
    }

    public function queryParameters(): array
    {
        return [
            'linha_id' => ['description' => 'Filtra por linha.', 'example' => 1],
            'status' => ['description' => 'Aberta ou Fechada.', 'example' => 'Aberta'],
            'data' => ['description' => 'Um dia especifico (AAAA-MM-DD).', 'example' => '2026-09-11'],
            'de' => ['description' => 'Inicio do intervalo (AAAA-MM-DD).', 'example' => '2026-09-01'],
            'ate' => ['description' => 'Fim do intervalo (AAAA-MM-DD).', 'example' => '2026-09-30'],
            'per_page' => ['description' => 'Itens por pagina: 10, 15, 20 ou 30.', 'example' => 15],
        ];
    }
}
