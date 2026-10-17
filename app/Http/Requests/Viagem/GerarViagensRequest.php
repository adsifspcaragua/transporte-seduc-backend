<?php

namespace App\Http\Requests\Viagem;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

class GerarViagensRequest extends FormRequest
{
    private const MAXIMO_DIAS = 62;

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
            'de' => 'required|date_format:Y-m-d',
            'ate' => 'required|date_format:Y-m-d|after_or_equal:de',
            'linha_id' => 'nullable|integer|exists:linhas,id',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                if (Carbon::parse($this->input('de'))->diffInDays(Carbon::parse($this->input('ate'))) > self::MAXIMO_DIAS) {
                    $validator->errors()->add('ate', 'Gere no máximo '.self::MAXIMO_DIAS.' dias por vez.');
                }
            },
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'de' => ['description' => 'Primeiro dia (AAAA-MM-DD).', 'example' => '2026-10-19'],
            'ate' => ['description' => 'Ultimo dia (AAAA-MM-DD). No maximo 62 dias depois de "de".', 'example' => '2026-10-31'],
            'linha_id' => ['description' => 'Restringe a uma linha.', 'example' => null],
        ];
    }
}
