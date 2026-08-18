<?php

namespace App\Http\Resources\Inscricao\Documento;

use App\Models\InscricaoDocumento;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'label' => InscricaoDocumento::TIPOS[$this->name] ?? $this->name,
            'type' => $this->type,
            'nome_original' => $this->nome_original,
            'status' => $this->status,
            'inscricao_id' => $this->inscricao_id,
            'download_url' => url("/api/inscricoes/{$this->inscricao_id}/documentos/{$this->id}/download"),
            'preview_url' => url("/api/inscricoes/{$this->inscricao_id}/documentos/{$this->id}/download?inline=1"),
        ];
    }
}
