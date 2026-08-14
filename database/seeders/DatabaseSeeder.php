<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            UserSeeder::class,
            LinhaSeeder::class,
            InstituicaoSeeder::class,
            CursoSeeder::class,
            InscricaoSeeder::class,
            EstudanteSeeder::class,
            PeriodoReecadastroSeeder::class,
        ]);
    }
}
