<?php

namespace App\Http\Resources\Presenca;

use App\Http\Resources\Viagem\ViagemResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TentativaPresencaResource extends JsonResource
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
            'dispositivo_id' => $this->dispositivo_id,
            'presenca_id' => $this->presenca_id,
            'etapa' => $this->etapa,
            'resultado' => $this->resultado,
            'codigo' => $this->codigo?->value,
            'mensagem' => $this->codigo?->mensagem(),
            'distancia_metros' => $this->distancia_metros,
            'precisao_metros' => $this->precisao_metros,
            'recebida_em' => $this->recebida_em,
            'pode_pedir_revisao' => $this->admiteRevisao(),
            'revisao' => $this->revisao_status === null ? null : [
                'status' => $this->revisao_status,
                'motivo' => $this->revisao_motivo,
                'solicitada_em' => $this->revisao_solicitada_em,
                'analisada_por' => $this->relationLoaded('revisaoAnalisadaPor')
                    ? $this->revisaoAnalisadaPor?->only(['id', 'name'])
                    : $this->revisao_analisada_por,
                'analisada_em' => $this->revisao_analisada_em,
                'parecer' => $this->revisao_parecer,
            ],
        ];
    }
}
