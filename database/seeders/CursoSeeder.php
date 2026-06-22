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
        $cursos = [
            'Administração',
            'Arquitetura e Urbanismo',
            'Ciência da Computação',
            'Direito',
            'Educação Física',
            'Enfermagem',
            'Engenharia Civil',
            'Engenharia de Software',
            'Fisioterapia',
            'Medicina',
            'Pedagogia',
            'Psicologia',
            'Sistemas de Informação',
        ];

        foreach ($cursos as $curso) {
            Curso::updateOrCreate(['name' => $curso]);
        }
    }
}
