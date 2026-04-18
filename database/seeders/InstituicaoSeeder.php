<?php

namespace Database\Seeders;

use App\Models\Instituicao;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InstituicaoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        Instituicao::create([
            'name' => 'Escola Municipal A',
            'linhas_ids' => [1, 2, 3],
        ]);

        Instituicao::create([
            'name' => 'Escola Estadual B',
            'linhas_ids' => [4, 5],
        ]);
    
    }
}
