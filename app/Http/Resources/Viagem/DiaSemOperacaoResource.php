<?php

namespace App\Http\Resources\Viagem;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiaSemOperacaoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'data' => $this->data?->toDateString(),
            'linha_id' => $this->linha_id,
            'linha' => $this->whenLoaded('linha', fn () => $this->linha?->only(['id', 'name'])),
            'motivo' => $this->motivo,
            'criado_por' => $this->whenLoaded('criadoPor', fn () => $this->criadoPor?->only(['id', 'name'])),
        ];
    }
}
