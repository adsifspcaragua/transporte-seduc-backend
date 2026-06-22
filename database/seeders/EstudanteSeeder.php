<?php

namespace Database\Seeders;

use App\Models\Estudante;
use App\Models\Inscricao;
use App\Models\Instituicao;
use App\Models\Linha;
use App\Models\User;
use Illuminate\Database\Seeder;

class EstudanteSeeder extends Seeder
{
    public function run(): void
    {
        $instituicoes = Instituicao::pluck('id', 'name');
        $linhas = Linha::pluck('id', 'name');
        $adminId = User::where('email', 'admin@example.com')->value('id');
        $operadorId = User::where('email', 'user@example.com')->value('id');

        $estudantes = [
            [
                'cpf' => '11111111111',
                'status' => 'Lista de espera',
                'institution' => 'Universidade do Vale do Paraíba',
                'line' => 'Linha Centro',
                'days_of_week' => [1, 2, 3, 4, 5],
                'user_id' => $adminId,
            ],
            [
                'cpf' => '22222222222',
                'status' => 'Aprovado',
                'institution' => 'Faculdade Módulo',
                'line' => 'Linha Noturna',
                'days_of_week' => [1, 3, 5],
                'user_id' => $operadorId,
            ],
            [
                'cpf' => '33333333333',
                'status' => 'Rejeitado',
                'institution' => 'Universidade Paulista',
                'line' => null,
                'days_of_week' => [2, 4],
                'user_id' => $operadorId,
            ],
            [
                'cpf' => '44444444444',
                'status' => 'Ativo',
                'institution' => 'FATEC Caraguatatuba',
                'line' => 'Linha Norte',
                'days_of_week' => [1, 2, 3, 4],
                'user_id' => $adminId,
            ],
            [
                'cpf' => '55555555555',
                'status' => 'Inativo',
                'institution' => 'UNIVESP Polo Caraguatatuba',
                'line' => 'Linha Sul',
                'days_of_week' => [6],
                'user_id' => $adminId,
            ],
        ];

        foreach ($estudantes as $data) {
            $inscricao = Inscricao::where('cpf', $data['cpf'])->first();

            if (! $inscricao) {
                continue;
            }

            Estudante::updateOrCreate(
                ['cpf' => $inscricao->cpf],
                [
                    'name' => $inscricao->name,
                    'email' => $inscricao->email,
                    'birth_date' => $inscricao->birth_date,
                    'phone' => $inscricao->phone,
                    'address' => $inscricao->address,
                    'days_of_week' => $data['days_of_week'],
                    'observation' => $inscricao->observation,
                    'status' => $data['status'],
                    'instituicao_id' => $instituicoes->get($data['institution']),
                    'linha_id' => $data['line'] ? $linhas->get($data['line']) : null,
                    'user_id' => $data['user_id'],
                    'inscricao_id' => $inscricao->id,
                ],
            );
        }
    }
}
