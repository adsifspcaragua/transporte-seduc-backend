<?php

namespace App\Http\Resources\Estudante;

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
        $inscricao = $this->inscricao;
        $dadosInstitucionais = $inscricao?->inscricao_instituicao;

        return [
            'id' => $this->id,
            'name' => $this->name ?? $inscricao?->name,
            'email' => $this->email ?? $inscricao?->email,
            'cpf' => $this->cpf ?? $inscricao?->cpf,
            'birth_date' => $this->birth_date ?? $inscricao?->birth_date,
            'phone' => $this->phone ?? $inscricao?->phone,
            'address' => $this->address ?? $inscricao?->address,
            'rg' => $inscricao?->rg,
            'mother_name' => $inscricao?->mother_name,
            'father_name' => $inscricao?->father_name,
            'cep' => $inscricao?->cep,
            'city' => $inscricao?->city,
            'neighborhood' => $inscricao?->neighborhood,
            'number' => $inscricao?->number,
            'complement' => $inscricao?->complement,
            'days_of_week' => $dadosInstitucionais?->days_of_week ?? $this->days_of_week,
            'observation' => $this->observation,
            'status' => $this->status,
            'linha_id' => $this->linha_id,
            'user_id' => $this->user_id,
            'instituicao_id' => $this->instituicao_id ?? $dadosInstitucionais?->instituicao_id,
            'inscricao_id' => $this->inscricao_id,
            'course' => $dadosInstitucionais?->course,
            'semester' => $dadosInstitucionais?->semester,
            'expected_completion' => $dadosInstitucionais?->expected_completion,
            'shift' => $dadosInstitucionais?->shift,
            'city_destination' => $dadosInstitucionais?->city_destination,
            'used_transport' => $dadosInstitucionais?->used_transport,
            'has_scholarship' => $dadosInstitucionais?->has_scholarship,
            'scholarship_type' => $dadosInstitucionais?->scholarship_type,
        ];
    }
}
