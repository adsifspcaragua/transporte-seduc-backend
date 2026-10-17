<?php

namespace App\Http\Resources\Viagem;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LinhaPontoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'linha_id' => $this->linha_id,
            'linha' => $this->whenLoaded('linha', fn () => $this->linha?->only(['id', 'name'])),
            'ponto_id' => $this->ponto_id,
            'ponto' => $this->whenLoaded('ponto', fn () => $this->ponto?->only(['id', 'nome', 'latitude', 'longitude', 'raio_metros', 'ativo'])),
            'sentido' => $this->sentido,
            'ordem' => $this->ordem,
            'horario_previsto' => $this->horario_previsto,
            'janela_antes_min' => $this->janela_antes_min,
            'janela_depois_min' => $this->janela_depois_min,
        ];
    }
}
