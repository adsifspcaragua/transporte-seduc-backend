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
            "name" => $this->name,
            "email" => $this->email,
            "cpf" => $this->cpf,
            "birth_date" => $this->birth_date,
            "rg" => $this->rg,
            "phone" => $this->phone,
            "address" => $this->address,
            "father_name" => $this->father_name,
            "mother_name" => $this->mother_name,
            "course" => $this->course,
            "semester" => $this->semester,
            "year_completion" => $this->year_completion,
            "instituicao_id" => $this->instituicao_id,
            "start_time" => $this->start_time,
            "end_time" => $this->end_time,
            "days_of_week" => $this->days_of_week,
            "has_scholarship" => $this->has_scholarship,
            "scholarship_type" => $this->scholarship_type,
            "observation" => $this->observation,
            "status" => $this->status,
            "line_id" => $this->line_id,
            "port" => $this->port,
            "user_id" => $this->user_id
        ];
    }
}
