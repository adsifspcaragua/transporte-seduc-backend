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
            'name',
            'cpf',
            'rg',
            'date_of_birth',
            'phone',
            'email',
            'cep',
            'address',
            'neighborhood',
            'city',
            'number',
            'status',
            'inscricao_instituicao_id',
            'accepted_terms',
            'accepted_terms_2'
        ];
    }
}
