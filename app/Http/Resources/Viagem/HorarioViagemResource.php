<?php

namespace App\Http\Resources\Viagem;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HorarioViagemResource extends JsonResource
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
            'sentido' => $this->sentido,
            'dias_semana' => array_map('intval', $this->dias_semana ?? []),
            'horario_saida' => $this->horario_saida,
            'janela_antes_min' => $this->janela_antes_min,
            'janela_depois_min' => $this->janela_depois_min,
            // A janela que vale de fato, ja com o padrao quando o horario nao define.
            'janela_efetiva' => [
                'antes_min' => $this->janelaAntes(),
                'depois_min' => $this->janelaDepois(),
            ],
            'vigencia_inicio' => $this->vigencia_inicio?->toDateString(),
            'vigencia_fim' => $this->vigencia_fim?->toDateString(),
            'ativo' => $this->ativo,
        ];
    }
}
