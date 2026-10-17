<?php

namespace App\Http\Resources\Viagem;

use App\Support\HorarioLocal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ViagemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'horario_id' => $this->horario_id,
            'linha_id' => $this->linha_id,
            'linha' => $this->whenLoaded('linha', fn () => $this->linha?->only(['id', 'name'])),
            'sentido' => $this->sentido,
            'data' => $this->data?->toDateString(),
            'horario_saida' => $this->horario_saida,
            // Com o deslocamento local (-03:00): e um horario da operacao.
            'janela_inicio' => HorarioLocal::iso($this->janela_inicio),
            'janela_fim' => HorarioLocal::iso($this->janela_fim),
            'veiculo_id' => $this->veiculo_id,
            'veiculo' => $this->whenLoaded('veiculo', fn () => $this->veiculo?->only(['id', 'identificacao', 'placa'])),
            'veiculo_substituido' => $this->veiculo_substituido,
            'status' => $this->status,
            'motivo_alteracao' => $this->motivo_alteracao,
            'alterada_por' => $this->whenLoaded('alteradaPor', fn () => $this->alteradaPor?->only(['id', 'name'])),
            'presencas' => $this->whenCounted('presencas'),
        ];
    }
}
