<?php

namespace App\Http\Requests\Dispositivo;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SolicitarDispositivoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * O motivo so e obrigatorio na troca, e quem decide se e troca e o
     * servidor (ha credencial ativa?). Por isso a exigencia fica no service.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'motivo' => 'nullable|string|max:500',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'motivo' => ['description' => 'Motivo da solicitacao. Obrigatorio quando ja existe dispositivo ativo (troca).', 'example' => 'Troquei de celular.'],
        ];
    }
}
