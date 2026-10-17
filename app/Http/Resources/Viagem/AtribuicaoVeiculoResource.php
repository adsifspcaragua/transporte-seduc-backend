<?php

namespace App\Http\Resources\Viagem;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AtribuicaoVeiculoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'veiculo_id' => $this->veiculo_id,
            'veiculo' => $this->whenLoaded('veiculo', fn () => $this->veiculo?->only(['id', 'identificacao', 'placa'])),
            'linha_id' => $this->linha_id,
            'linha' => $this->whenLoaded('linha', fn () => $this->linha?->only(['id', 'name'])),
            'inicio' => $this->inicio?->toDateString(),
            'fim' => $this->fim?->toDateString(),
            'motivo' => $this->motivo,
            'criada_por' => $this->whenLoaded('criadaPor', fn () => $this->criadaPor?->only(['id', 'name'])),
        ];
    }
}
