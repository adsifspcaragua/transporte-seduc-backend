<?php

namespace App\Http\Resources\Frequencia;

use App\Models\Frequencia;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChamadaResource extends JsonResource
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
            'data' => $this->data?->toDateString(),
            'status' => $this->status,
            'observacoes' => $this->observacoes,
            'fechada_em' => $this->fechada_em,
            'linha' => $this->whenLoaded('linha', fn () => [
                'id' => $this->linha->id,
                'name' => $this->linha->name,
                'motorista' => $this->linha->relationLoaded('motorista')
                    ? $this->linha->motorista?->only(['id', 'name'])
                    : null,
            ]),
            'registrada_por' => $this->whenLoaded('registradaPor', fn () => $this->registradaPor?->only(['id', 'name'])),
            'contadores' => $this->contadores(),
            'frequencias' => FrequenciaResource::collection($this->whenLoaded('frequencias')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Quantos estao em cada situacao. Na listagem vem do withCount; na folha
     * aberta, das proprias marcacoes carregadas.
     *
     * @return array<string, int>
     */
    private function contadores(): array
    {
        if ($this->relationLoaded('frequencias')) {
            $porSituacao = $this->frequencias->countBy('situacao');

            return [
                'total' => $this->frequencias->count(),
                'presentes' => (int) ($porSituacao[Frequencia::PRESENTE] ?? 0),
                'faltas' => (int) ($porSituacao[Frequencia::FALTA] ?? 0),
                'justificadas' => (int) ($porSituacao[Frequencia::JUSTIFICADA] ?? 0),
                'pendentes' => (int) ($porSituacao[Frequencia::PENDENTE] ?? 0),
            ];
        }

        return [
            'total' => (int) $this->total_estudantes,
            'presentes' => (int) $this->presentes,
            'faltas' => (int) $this->faltas,
            'justificadas' => (int) $this->justificadas,
            'pendentes' => (int) $this->pendentes,
        ];
    }
}
