<?php

namespace Database\Seeders;

use App\Models\Linha;
use Illuminate\Database\Seeder;

class LinhaSeeder extends Seeder
{
    public function run(): void
    {
        $linhas = [
            [
                'name' => 'Linha Centro',
                'description' => 'Centro, Martim de Sá e região universitária.',
                'departure_time' => '06:10:00',
                'return_time' => '22:40:00',
                'max_capacity' => 44,
            ],
            [
                'name' => 'Linha Sul',
                'description' => 'Porto Novo, Travessão e instituições do eixo sul.',
                'departure_time' => '06:00:00',
                'return_time' => '22:30:00',
                'max_capacity' => 40,
            ],
            [
                'name' => 'Linha Norte',
                'description' => 'Massaguaçu, Olaria e instituições do eixo norte.',
                'departure_time' => '05:50:00',
                'return_time' => '22:50:00',
                'max_capacity' => 38,
            ],
            [
                'name' => 'Linha Noturna',
                'description' => 'Rota noturna para cursos presenciais no período da noite.',
                'departure_time' => '17:20:00',
                'return_time' => '23:20:00',
                'max_capacity' => 36,
            ],
        ];

        foreach ($linhas as $linha) {
            Linha::updateOrCreate(
                ['name' => $linha['name']],
                $linha,
            );
        }
    }
}
