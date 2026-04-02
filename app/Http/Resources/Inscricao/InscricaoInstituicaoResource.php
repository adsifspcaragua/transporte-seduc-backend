<?php

namespace App\Http\Resources\Inscricao;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class InscricaoInstituicaoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'course'              => $this->course,
            'semester'            => $this->semester,
            'expected_completion' => $this->expected_completion?->format('Y-m-d'),
            'instituicao_id'      => $this->instituicao_id,
            //'instituicao'         => new InstituicaoResource($this->whenLoaded('instituicao')), MODIFICAR
            'shift'               => $this->shift,
            'shift_label'         => match($this->shift) {
                1 => 'Matutino',
                2 => 'Noturno',
                default => 'Desconhecido',
            },
            'city_destination'    => $this->city_destination,
            'used_transport'      => $this->used_transport,
            'days_of_week'        => $this->days_of_week,
            'days_of_week_labels' => collect($this->days_of_week)->map(fn($day) => match($day) {
                0 => 'Domingo',
                1 => 'Segunda-feira',
                2 => 'Terça-feira',
                3 => 'Quarta-feira',
                4 => 'Quinta-feira',
                5 => 'Sexta-feira',
                6 => 'Sábado',
                default => 'Desconhecido',
            }),
            //'line_id'             => $this->line_id,
            //'line'                => new LineResource($this->whenLoaded('line')), MODIFICAR
            'has_scholarship'     => $this->has_scholarship,
            'scholarship_type'    => $this->scholarship_type,
            'created_at'          => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'          => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
