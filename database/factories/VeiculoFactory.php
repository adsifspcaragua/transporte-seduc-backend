<?php

namespace Database\Factories;

use App\Models\Veiculo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Van sem QR. Para ter o token em maos, chame gerarTokenQr() no modelo criado.
 *
 * @extends Factory<Veiculo>
 */
class VeiculoFactory extends Factory
{
    protected $model = Veiculo::class;

    public function definition(): array
    {
        return [
            'identificacao' => 'Van '.fake()->unique()->numerify('###'),
            'placa' => strtoupper(fake()->unique()->bothify('???#?##')),
            'ativo' => true,
        ];
    }
}
