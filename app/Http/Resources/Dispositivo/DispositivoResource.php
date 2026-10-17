<?php

namespace App\Http\Resources\Dispositivo;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A credencial do estudante, sem a chave: nem a publica sai daqui.
 */
class DispositivoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'estudante_id' => $this->estudante_id,
            'estudante' => $this->whenLoaded('estudante', fn () => $this->estudante?->only(['id', 'name', 'cpf'])),
            'rotulo' => $this->rotulo,
            'status' => $this->status,
            'aprovado_por' => $this->whenLoaded('aprovadoPor', fn () => $this->aprovadoPor?->only(['id', 'name'])),
            'aprovado_em' => $this->aprovado_em?->toISOString(),
            'ultimo_uso_em' => $this->ultimo_uso_em?->toISOString(),
            'revogado_em' => $this->revogado_em?->toISOString(),
            'revogado_por' => $this->whenLoaded('revogadoPor', fn () => $this->revogadoPor?->only(['id', 'name'])),
            'motivo_revogacao' => $this->motivo_revogacao,
        ];
    }
}
