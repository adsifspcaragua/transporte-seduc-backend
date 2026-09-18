<?php

namespace App\Http\Resources\Linha;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LinhaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $ocupacao = (int) ($this->ocupacao ?? $this->estudantes()->where('status', 'Ativo')->count());

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'departure_time' => $this->departure_time,
            'return_time' => $this->return_time,
            'max_capacity' => $this->max_capacity,
            'motorista_id' => $this->motorista_id,
            'motorista' => $this->whenLoaded('motorista', fn () => $this->motorista?->only(['id', 'name'])),
            // Quantos lugares ja estao tomados. So estudante ativo ocupa vaga:
            // inativo nao anda de onibus.
            'ocupacao' => $ocupacao,
            'vagas_restantes' => max(
                0,
                $this->max_capacity - $ocupacao,
            ),
        ];
    }
}
