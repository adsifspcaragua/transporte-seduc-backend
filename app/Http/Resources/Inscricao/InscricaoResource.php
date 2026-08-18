<?php

namespace App\Http\Resources\Inscricao;

use App\Models\Inscricao;
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
            'father_name' => $this->father_name,
            'mother_name' => $this->mother_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'cep' => $this->cep,
            'address' => $this->address,
            'neighborhood' => $this->neighborhood,
            'city' => $this->city,
            'number' => $this->number,
            'status' => $this->status,
            // Credencial da inscricao: devolvida apenas ao proprio estudante,
            // que acessa a lista de espera sem login.
            'token' => $this->when($request->user() === null, $this->access_token),
            'accepted_terms' => $this->accepted_terms,
            'accepted_terms_2' => $this->accepted_terms_2,
            'observation' => $this->observation,
            // Campos que a responsável pediu para corrigir, com o rótulo pronto:
            // o motivo em texto diz o que houve, isto diz onde.
            'campos_pendentes' => collect($this->campos_pendentes ?? [])
                ->map(fn (string $campo) => [
                    'campo' => $campo,
                    'label' => Inscricao::CAMPOS_CORRIGIVEIS[$campo] ?? $campo,
                ])
                ->values()
                ->all(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

        ];
    }
}
