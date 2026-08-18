<?php

namespace App\Http\Resources\Reecadastro\Documento;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentoResource extends JsonResource
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
            'solicitacao_id' => $this->solicitacao_id,
            'type' => $this->type,
            'label' => $this->label,
            'nome_original' => $this->nome_original,
            'status' => $this->status,
            'observacoes' => $this->observacoes,
            // Sem estes links a responsável não tem como abrir o arquivo: os
            // documentos ficam em disco privado e só saem por esta rota.
            'download_url' => url("/api/reecadastro/documentos/{$this->id}/download"),
            'preview_url' => url("/api/reecadastro/documentos/{$this->id}/download?inline=1"),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
