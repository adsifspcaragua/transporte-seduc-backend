<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InscricaoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [ 
            'name' => $this->name,
            'cpf' => $this->cpf,
            'rg' => $this->rg,
            'date_of_birth' => $this->date_of_birth,
            'phone' => $this->phone,
            'email' => $this->email,
            'cep' => $this->cep,
            'address' => $this->address,
            'neighborhood' => $this->neighborhood,
            'complement' => $this->complement,
            'city' => $this->city,
            'number' => $this->number,
            'status' => $this->status,
            'inscricao_instituicao_id' => $this->inscricao_instituicao_id,
            'accepted_terms' => $this->accepted_terms,
            'accepted_terms_2' => $this->accepted_terms_2
        ];
    }
}
