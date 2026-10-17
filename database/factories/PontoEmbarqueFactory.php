<?php

namespace Database\Factories;

use App\Models\PontoEmbarque;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Ponto no centro de Caraguatatuba, com raio de 200 m.
 *
 * @extends Factory<PontoEmbarque>
 */
class PontoEmbarqueFactory extends Factory
{
    protected $model = PontoEmbarque::class;

    public function definition(): array
    {
        return [
            'nome' => 'Ponto '.fake()->unique()->streetName(),
            'latitude' => -23.6200000,
            'longitude' => -45.4100000,
            'raio_metros' => 200,
            'ativo' => true,
        ];
    }
}
