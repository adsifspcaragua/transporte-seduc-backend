<?php

namespace App\Http\Requests\Dispositivo;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Acoes que so exigem uma justificativa: revogar dispositivo, rotacionar QR.
 */
class MotivoRequest extends FormRequest
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
            'motivo' => 'required|string|min:5|max:255',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'motivo' => ['description' => 'Justificativa registrada na auditoria.', 'example' => 'Celular roubado, informado pelo estudante.'],
        ];
    }
}
