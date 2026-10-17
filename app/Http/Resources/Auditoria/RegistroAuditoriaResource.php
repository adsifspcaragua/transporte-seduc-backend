<?php

namespace App\Http\Resources\Auditoria;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegistroAuditoriaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => $this->whenLoaded('user', fn () => $this->user?->only(['id', 'name'])),
            'acao' => $this->acao,
            'auditavel_type' => $this->auditavel_type ? class_basename($this->auditavel_type) : null,
            'auditavel_id' => $this->auditavel_id,
            'dados' => $this->dados,
            'justificativa' => $this->justificativa,
            'created_at' => $this->created_at,
        ];
    }
}
