<?php

namespace Database\Factories;

use App\Models\HorarioViagem;
use App\Models\Linha;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Ida de segunda a sexta as 17h30, com a janela padrao da configuracao.
 *
 * @extends Factory<HorarioViagem>
 */
class HorarioViagemFactory extends Factory
{
    protected $model = HorarioViagem::class;

    public function definition(): array
    {
        return [
            'linha_id' => Linha::factory(),
            'sentido' => HorarioViagem::IDA,
            'dias_semana' => [1, 2, 3, 4, 5],
            'horario_saida' => '17:30:00',
            'ativo' => true,
        ];
    }

    public function volta(string $saida = '22:30:00'): static
    {
        return $this->state(['sentido' => HorarioViagem::VOLTA, 'horario_saida' => $saida]);
    }
}
