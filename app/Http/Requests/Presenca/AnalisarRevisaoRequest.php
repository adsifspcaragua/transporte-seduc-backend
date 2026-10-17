<?php

namespace App\Http\Requests\Presenca;

use App\Models\TentativaPresenca;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnalisarRevisaoRequest extends FormRequest
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
            'decisao' => ['required', Rule::in([TentativaPresenca::REVISAO_APROVADA, TentativaPresenca::REVISAO_REJEITADA])],
            'parecer' => 'required|string|min:5|max:1000',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'decisao' => ['description' => 'Aprovada (registra a presenca) ou Rejeitada.', 'example' => 'Aprovada'],
            'parecer' => ['description' => 'Justificativa da decisao.', 'example' => 'Atraso da van confirmado pela operacao.'],
        ];
    }
}
