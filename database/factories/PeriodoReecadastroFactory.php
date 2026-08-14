<?php

namespace Database\Factories;

use App\Models\PeriodoReecadastro;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PeriodoReecadastro>
 */
class PeriodoReecadastroFactory extends Factory
{
    protected $model = PeriodoReecadastro::class;

    /** Cada periodo gerado ocupa um ano/semestre diferente (par unico na tabela). */
    private static int $sequencia = 0;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $indice = self::$sequencia++;

        return [
            'ano' => (int) now()->format('Y') + intdiv($indice, 2),
            'semestre' => $indice % 2 + 1,
            'data_inicio' => now()->subDays(5)->toDateString(),
            'data_fim' => now()->addDays(25)->toDateString(),
            'status' => 'Fechado',
        ];
    }

    /** Periodo liberado para os estudantes recadastrarem. */
    public function aberto(): static
    {
        return $this->state(['status' => 'Aberto']);
    }
}
