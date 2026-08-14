<?php

namespace Database\Factories;

use App\Models\Estudante;
use App\Models\Inscricao;
use App\Models\Instituicao;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Gera um estudante ativo, ja vinculado a uma inscricao aprovada.
 *
 * @extends Factory<Estudante>
 */
class EstudanteFactory extends Factory
{
    protected $model = Estudante::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'cpf' => fake()->unique()->numerify('###########'),
            'birth_date' => '2004-05-10',
            'phone' => fake()->numerify('###########'),
            'address' => 'Rua das Palmeiras, 100',
            'days_of_week' => [1, 2, 3, 4, 5],
            'status' => 'Ativo',
            'instituicao_id' => Instituicao::factory(),
            'inscricao_id' => Inscricao::factory()->state(['status' => 'Aprovado']),
        ];
    }

    /** Estudante que perdeu o beneficio. */
    public function inativo(): static
    {
        return $this->state(['status' => 'Inativo']);
    }
}
