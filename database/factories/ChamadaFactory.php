<?php

namespace Database\Factories;

use App\Models\Chamada;
use App\Models\Linha;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Gera uma chamada aberta de hoje, sem estudantes na folha.
 *
 * @extends Factory<Chamada>
 */
class ChamadaFactory extends Factory
{
    protected $model = Chamada::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'linha_id' => Linha::factory(),
            'data' => today()->toDateString(),
            'status' => Chamada::ABERTA,
        ];
    }

    /** Folha ja entregue. */
    public function fechada(): static
    {
        return $this->state([
            'status' => Chamada::FECHADA,
            'fechada_em' => now(),
        ]);
    }
}
