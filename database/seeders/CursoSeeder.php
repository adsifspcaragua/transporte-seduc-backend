<?php

namespace Database\Seeders;

use App\Models\Curso;
use Illuminate\Database\Seeder;

class CursoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Curso::create([
            'name' => 'Engenharia Civil',
        ]);

        Curso::create([
            'name' => 'Administração',
        ]);

        Curso::create([
            'name' => 'Direito',
        ]);

        Curso::create([
            'name' => 'Pedagogia',
        ]);
    }
}
