<?php

namespace App\Http\Requests\Dispositivo;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AnalisarSolicitacaoDispositivoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aprovar tambem exige parecer: e o registro de como a identidade foi
     * conferida (documento apresentado, atendimento presencial etc.).
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'parecer' => 'required|string|min:5|max:1000',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'parecer.required' => 'Registre como a identidade do estudante foi conferida.',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'parecer' => ['description' => 'Como a identidade foi conferida, ou o motivo da rejeicao.', 'example' => 'RG conferido presencialmente no atendimento.'],
        ];
    }
}
