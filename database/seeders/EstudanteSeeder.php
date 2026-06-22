<?php

namespace Database\Seeders;

use App\Models\Estudante;
use App\Models\Inscricao;
use Illuminate\Database\Seeder;

class EstudanteSeeder extends Seeder
{
    public function run(): void
    {
        $inscricao1 = Inscricao::where('cpf', '11111111111')->first();
        $inscricao2 = Inscricao::where('cpf', '22222222222')->first();

        Estudante::create([
            'name' => $inscricao1->name,
            'email' => $inscricao1->email,
            'cpf' => $inscricao1->cpf,
            'birth_date' => $inscricao1->birth_date,
            'phone' => $inscricao1->phone,
            'address' => $inscricao1->address,
            'days_of_week' => [1, 2],
            'observation' => $inscricao1->observation,
            'status' => 'Em espera',
            'instituicao_id' => 1,
            'linha_id' => 1,
            'user_id' => 1,
            'inscricao_id' => $inscricao1->id,
        ]);

        Estudante::create([
            'name' => $inscricao2->name,
            'email' => $inscricao2->email,
            'cpf' => $inscricao2->cpf,
            'birth_date' => $inscricao2->birth_date,
            'phone' => $inscricao2->phone,
            'address' => $inscricao2->address,
            'days_of_week' => [3, 4],
            'observation' => $inscricao2->observation,
            'status' => 'Em espera',
            'instituicao_id' => 2,
            'linha_id' => null,
            'user_id' => 2,
            'inscricao_id' => $inscricao2->id,
        ]);
    }
}
