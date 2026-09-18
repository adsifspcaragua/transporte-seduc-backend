<?php

namespace App\Http\Resources\Frequencia;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FrequenciaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'estudante' => $this->whenLoaded('estudante', fn () => $this->estudante?->only(['id', 'name', 'cpf'])),
            'estudante_id' => $this->estudante_id,
            'situacao' => $this->situacao,
            'observacao' => $this->observacao,
            'marcada_em' => $this->marcada_em,
            'justificativa' => $this->whenLoaded('justificativa', fn () => $this->justificativa?->only(['id', 'status', 'parecer'])),
        ];
    }
}
