<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EstudanteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "email" => $this->email,
            "cpf" => $this->cpf,
            "birth_date" => $this->birth_date,
            "phone" => $this->phone,
            "address" => $this->address,
            "start_time" => $this->start_time,
            "end_time" => $this->end_time,
            "days_of_week" => $this->days_of_week,
            "observation" => $this->observation,
            "status" => $this->status,
            "linha_id" => $this->linha_id,
            "user_id" => $this->user_id,
            "instituicao_id" => $this->instituicao_id,
            "inscricao_id" => $this->inscricao_id
        ];
    }
}
