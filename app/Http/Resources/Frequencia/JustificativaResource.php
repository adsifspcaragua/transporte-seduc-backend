<?php

namespace App\Http\Resources\Frequencia;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JustificativaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $frequencia = $this->frequencia;
        $chamada = $frequencia?->chamada;

        return [
            'id' => $this->id,
            'status' => $this->status,
            'motivo' => $this->motivo,
            'parecer' => $this->parecer,
            'estudante' => $frequencia?->estudante?->only(['id', 'name', 'cpf', 'email', 'status']),
            'falta' => [
                'frequencia_id' => $frequencia?->id,
                'situacao' => $frequencia?->situacao,
                'chamada_id' => $chamada?->id,
                'data' => $chamada?->data?->toDateString(),
                'linha' => $chamada?->linha?->only(['id', 'name']),
            ],
            'enviada_por' => $this->whenLoaded('enviadaPor', fn () => $this->enviadaPor?->only(['id', 'name'])),
            'analisada_por' => $this->whenLoaded('analisadaPor', fn () => $this->analisadaPor?->only(['id', 'name'])),
            'analisada_em' => $this->analisada_em,
            'created_at' => $this->created_at,
        ];
    }
}
