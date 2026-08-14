<?php

namespace Database\Seeders;

use App\Models\PeriodoReecadastro;
use Illuminate\Database\Seeder;

class PeriodoReecadastroSeeder extends Seeder
{
    public function run(): void
    {
        $ano = (int) now()->format('Y');

        PeriodoReecadastro::updateOrCreate(
            ['ano' => $ano, 'semestre' => 1],
            [
                'data_inicio' => $ano.'-02-01',
                'data_fim' => $ano.'-02-28',
                'status' => 'Fechado',
                'observacoes' => 'Recadastro do primeiro semestre.',
            ],
        );

        PeriodoReecadastro::updateOrCreate(
            ['ano' => $ano, 'semestre' => 2],
            [
                'data_inicio' => $ano.'-07-01',
                'data_fim' => $ano.'-07-31',
                'status' => 'Aberto',
                'observacoes' => 'Recadastro do segundo semestre.',
            ],
        );
    }
}
