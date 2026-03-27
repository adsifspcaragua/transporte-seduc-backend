<?php

namespace App\Http\Resources\Documento;

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
            "estudante_id" => $this->estudante_id,
            "tipo" =>  $this->tipo,
            "arquivo_path" => $this->arquivo_path
        ];
    }
}
