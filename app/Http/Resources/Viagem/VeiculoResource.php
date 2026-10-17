<?php

namespace App\Http\Resources\Viagem;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A van. O token do QR nunca sai daqui: so existe na resposta de quem o gera.
 */
class VeiculoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'identificacao' => $this->identificacao,
            'placa' => $this->placa,
            'ativo' => $this->ativo,
            'qr_gerado_em' => $this->qr_gerado_em,
            'atribuicoes' => AtribuicaoVeiculoResource::collection($this->whenLoaded('atribuicoes')),
        ];
    }
}
