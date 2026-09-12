<?php

namespace App\Http\Resources\Linha;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LinhaEstudanteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $inscricao = $this->inscricao;
        $academico = $inscricao?->inscricao_instituicao;
        $instituicao = $this->instituicao;

        return [
            'id' => $this->id,
            'name' => $this->name ?? $inscricao?->name,
            'email' => $this->email ?? $inscricao?->email,
            'phone' => $this->phone ?? $inscricao?->phone,
            'status' => $this->status,
            'course' => $academico?->course,
            'semester' => $academico?->semester,
            'instituicao_name' => $instituicao?->name,
        ];
    }
}
