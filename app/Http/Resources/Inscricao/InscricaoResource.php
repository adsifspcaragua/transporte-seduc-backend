<?php

namespace App\Http\Resources\Inscricao;

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
            'id' => $this->id,
            'name' => $this->name,
            'cpf' => $this->cpf,
            'rg' => $this->rg,
            'birth_date' => $this->birth_date,
            "father_name" => $this->father_name,
            "mother_name" => $this->mother_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'cep' => $this->cep,
            'address' => $this->address,
            'neighborhood' => $this->neighborhood,
            'city' => $this->city,
            'number' => $this->number,
            'status' => $this->status,
            'accepted_terms' => $this->accepted_terms,
            'accepted_terms_2' => $this->accepted_terms_2,
            "observation" => $this->observation,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at


        
        ];
    }
}
