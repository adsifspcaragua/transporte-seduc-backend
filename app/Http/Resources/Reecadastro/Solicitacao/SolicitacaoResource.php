<?php

namespace App\Http\Resources\Reecadastro\Solicitacao;

use App\Http\Resources\Reecadastro\Documento\DocumentoResource;
use App\Http\Resources\Reecadastro\Periodo\PeriodoResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SolicitacaoResource extends JsonResource
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
            'estudante_id' => $this->estudante_id,
            'periodo_id' => $this->periodo_id,
            'status' => $this->status,
            'observacoes' => $this->observacoes,
            'campos_pendentes' => $this->campos_pendentes ?? [],
            'prazo_matricula' => $this->prazo_matricula,
            'prazo_cronograma' => $this->prazo_cronograma,
            'aceite_veracidade' => $this->aceite_veracidade,
            'aceite_ciencia' => $this->aceite_ciencia,
            'enviada_em' => $this->enviada_em,
            'analisado_por' => $this->analisado_por,
            'analisado_em' => $this->analisado_em,
            'estudante' => $this->whenLoaded('estudante', fn () => [
                'id' => $this->estudante->id,
                'name' => $this->estudante->name,
                'cpf' => $this->estudante->cpf,
                'email' => $this->estudante->email,
                'phone' => $this->estudante->phone,
                'status' => $this->estudante->status,
            ]),
            'periodo' => new PeriodoResource($this->whenLoaded('periodo')),
            'documentos' => DocumentoResource::collection($this->whenLoaded('documentos')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
