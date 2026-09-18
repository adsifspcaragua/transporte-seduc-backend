<?php

namespace Database\Factories;

use App\Models\Chamada;
use App\Models\Estudante;
use App\Models\Frequencia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Gera a linha de um estudante na folha, ainda sem marcacao.
 *
 * @extends Factory<Frequencia>
 */
class FrequenciaFactory extends Factory
{
    protected $model = Frequencia::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'chamada_id' => Chamada::factory(),
            'estudante_id' => Estudante::factory(),
            'situacao' => Frequencia::PENDENTE,
        ];
    }
}
