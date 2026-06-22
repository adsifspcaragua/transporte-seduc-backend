<?php

namespace Database\Factories;

use App\Models\Instituicao;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Instituicao>
 */
class InstituicaoFactory extends Factory
{
    protected $model = Instituicao::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'linhas_ids' => [],
        ];
    }
}
