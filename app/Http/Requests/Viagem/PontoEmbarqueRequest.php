<?php

namespace App\Http\Requests\Viagem;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PontoEmbarqueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * O raio e obrigatorio e nao tem valor padrao: depende de teste de campo
     * (D03). O teto vem da configuracao.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $obrigatorio = $this->isMethod('POST') ? 'required' : 'sometimes';
        $raioMaximo = (int) config('presenca.geocerca.raio_maximo_metros');

        return [
            'nome' => [$obrigatorio, 'string', 'min:3', 'max:255'],
            'latitude' => [$obrigatorio, 'numeric', 'between:-90,90'],
            'longitude' => [$obrigatorio, 'numeric', 'between:-180,180'],
            'raio_metros' => [$obrigatorio, 'integer', 'min:10', "max:{$raioMaximo}"],
            'ativo' => 'sometimes|boolean',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'nome' => ['description' => 'Nome do ponto.', 'example' => 'Praça Cândido Mota'],
            'latitude' => ['description' => 'Latitude (graus decimais).', 'example' => -23.6203],
            'longitude' => ['description' => 'Longitude (graus decimais).', 'example' => -45.4126],
            'raio_metros' => ['description' => 'Raio da geocerca em metros. Calibrar em campo.', 'example' => 200],
            'ativo' => ['description' => 'Ponto inativo nao aceita presenca.', 'example' => true],
        ];
    }
}
