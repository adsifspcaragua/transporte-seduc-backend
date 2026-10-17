<?php

namespace App\Http\Resources\Viagem;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PontoEmbarqueResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'raio_metros' => $this->raio_metros,
            'ativo' => $this->ativo,
            'linhas' => LinhaPontoResource::collection($this->whenLoaded('linhas')),
        ];
    }
}
