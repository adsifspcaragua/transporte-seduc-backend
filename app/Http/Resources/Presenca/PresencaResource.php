<?php

namespace App\Http\Resources\Presenca;

use App\Http\Resources\Viagem\ViagemResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * O comprovante da presenca. Sem coordenadas: elas nem sao guardadas.
 */
class PresencaResource extends JsonResource
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
            'viagem' => new ViagemResource($this->whenLoaded('viagem')),
            'veiculo' => $this->whenLoaded('veiculo', fn () => $this->veiculo?->only(['id', 'identificacao'])),
            'ponto' => $this->whenLoaded('ponto', fn () => $this->ponto?->only(['id', 'nome'])),
            'metodo' => $this->metodo,
            'registrada_em' => $this->registrada_em,
            'politica_geo' => $this->politica_geo,
            'resultado_geo' => $this->resultado_geo,
            'distancia_metros' => $this->distancia_metros,
            'precisao_metros' => $this->precisao_metros,
            'registrada_por' => $this->whenLoaded('registradaPor', fn () => $this->registradaPor?->only(['id', 'name'])),
            'justificativa' => $this->justificativa,
        ];
    }
}
