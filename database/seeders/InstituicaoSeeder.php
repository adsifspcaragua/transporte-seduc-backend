<?php

namespace Database\Seeders;

use App\Models\Instituicao;
use App\Models\Linha;
use Illuminate\Database\Seeder;

class InstituicaoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $linhas = Linha::pluck('id', 'name');

        $instituicoes = [
            [
                'name' => 'Universidade do Vale do Paraíba',
                'linhas' => ['Linha Centro', 'Linha Noturna'],
            ],
            [
                'name' => 'Faculdade Módulo',
                'linhas' => ['Linha Centro', 'Linha Sul'],
            ],
            [
                'name' => 'FATEC Caraguatatuba',
                'linhas' => ['Linha Norte', 'Linha Noturna'],
            ],
            [
                'name' => 'UNIVESP Polo Caraguatatuba',
                'linhas' => ['Linha Sul'],
            ],
            [
                'name' => 'Universidade Paulista',
                'linhas' => ['Linha Centro', 'Linha Norte'],
            ],
        ];

        foreach ($instituicoes as $instituicao) {
            Instituicao::updateOrCreate(
                ['name' => $instituicao['name']],
                [
                    'linhas_ids' => collect($instituicao['linhas'])
                        ->map(fn (string $linha) => $linhas->get($linha))
                        ->filter()
                        ->values()
                        ->all(),
                ],
            );
        }
    }
}
