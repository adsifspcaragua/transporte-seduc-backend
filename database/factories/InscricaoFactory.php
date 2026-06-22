<?php

namespace Database\Factories;

use App\Models\Inscricao;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Gera uma inscricao com todos os campos exigidos por
 * InscricaoStatusService::isComplete() preenchidos.
 *
 * @extends Factory<Inscricao>
 */
class InscricaoFactory extends Factory
{
    protected $model = Inscricao::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'cpf' => fake()->unique()->numerify('###########'),
            'rg' => fake()->numerify('########'),
            'birth_date' => '2005-08-15',
            'phone' => fake()->unique()->numerify('###########'),
            'email' => fake()->unique()->safeEmail(),
            'cep' => fake()->numerify('########'),
            'address' => 'Rua Principal',
            'neighborhood' => 'Centro',
            'city' => 'Vitoria da Conquista',
            'complement' => 'Casa',
            'number' => '100',
            'father_name' => fake()->name('male'),
            'mother_name' => fake()->name('female'),
            'status' => 'Incompleto',
            'accepted_terms' => true,
            'accepted_terms_2' => true,
        ];
    }
}
