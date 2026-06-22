<?php

namespace Database\Factories;

use App\Models\InscricaoInstituicoes;
use App\Models\Instituicao;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Gera o vinculo inscricao/instituicao com todos os campos exigidos por
 * InscricaoStatusService::isComplete() preenchidos.
 *
 * @extends Factory<InscricaoInstituicoes>
 */
class InscricaoInstituicoesFactory extends Factory
{
    protected $model = InscricaoInstituicoes::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course' => 'Engenharia de Software',
            'semester' => '3',
            'expected_completion' => '2027-12-15',
            'instituicao_id' => Instituicao::factory(),
            'shift' => 1,
            'city_destination' => 'Vitoria da Conquista',
            'used_transport' => true,
            'days_of_week' => ['seg', 'ter', 'qua'],
            'has_scholarship' => false,
        ];
    }
}
