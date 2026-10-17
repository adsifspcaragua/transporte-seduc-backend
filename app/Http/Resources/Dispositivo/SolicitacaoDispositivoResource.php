<?php

namespace App\Http\Resources\Dispositivo;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SolicitacaoDispositivoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'estudante_id' => $this->estudante_id,
            'estudante' => $this->whenLoaded('estudante', fn () => $this->estudante?->only(['id', 'name', 'cpf', 'linha_id'])),
            'tipo' => $this->tipo,
            'status' => $this->status,
            'motivo' => $this->motivo,
            'parecer' => $this->parecer,
            'analisada_por' => $this->whenLoaded('analisadaPor', fn () => $this->analisadaPor?->only(['id', 'name'])),
            'analisada_em' => $this->analisada_em?->toISOString(),
            'aprovacao_expira_em' => $this->aprovacao_expira_em?->toISOString(),
            'concluida_em' => $this->concluida_em?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
