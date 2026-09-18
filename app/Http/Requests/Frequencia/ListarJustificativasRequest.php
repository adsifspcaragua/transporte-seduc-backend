<?php

namespace App\Http\Requests\Frequencia;

use App\Models\Justificativa;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListarJustificativasRequest extends FormRequest
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
            'status' => ['nullable', Rule::in([Justificativa::EM_ANALISE, Justificativa::APROVADA, Justificativa::REJEITADA])],
            'estudante_id' => 'nullable|integer',
            'linha_id' => 'nullable|integer',
            'de' => 'nullable|date_format:Y-m-d',
            'ate' => 'nullable|date_format:Y-m-d|after_or_equal:de',
            'per_page' => 'nullable|integer|in:10,15,20,30',
        ];
    }

    public function queryParameters(): array
    {
        return [
            'status' => ['description' => 'Em analise, Aprovada ou Rejeitada.', 'example' => 'Em analise'],
            'estudante_id' => ['description' => 'Filtra por estudante.', 'example' => 1],
            'linha_id' => ['description' => 'Filtra pela linha da chamada.', 'example' => 1],
            'de' => ['description' => 'Faltas a partir desta data (AAAA-MM-DD).', 'example' => '2026-09-01'],
            'ate' => ['description' => 'Faltas ate esta data (AAAA-MM-DD).', 'example' => '2026-09-30'],
            'per_page' => ['description' => 'Itens por pagina: 10, 15, 20 ou 30.', 'example' => 15],
        ];
    }
}
