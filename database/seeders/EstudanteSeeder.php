<?php

namespace Database\Seeders;

use App\Models\Estudante;
use App\Models\Inscricao;
use App\Models\Instituicao;
use Database\Seeders\Support\Cenarios;
use Illuminate\Database\Seeder;

class EstudanteSeeder extends Seeder
{
    /** [status, situação] de cada tipo de estudante do roteiro. */
    private const SITUACOES = [
        'Ativo' => ['Aprovado', 'Ativo'],
        'Inativo' => ['Aprovado', 'Inativo'],
        'Recusado' => ['Recusado', 'Inativo'],
    ];

    public function run(): void
    {
        $linhasPorInstituicao = Instituicao::pluck('linhas_ids', 'id');

        foreach (Cenarios::pessoas() as $pessoa) {
            // Só vira estudante quem foi aprovado: a lista de espera fica apenas
            // nas solicitações.
            if ($pessoa['estudante'] === null) {
                continue;
            }

            $inscricao = Inscricao::with('inscricao_instituicao')->where('cpf', $pessoa['cpf'])->first();

            if (! $inscricao) {
                continue;
            }

            [$status, $situacao] = self::SITUACOES[$pessoa['estudante']];
            $academico = $inscricao->inscricao_instituicao;
            $linhas = array_values($linhasPorInstituicao->get($academico->instituicao_id) ?? []);

            Estudante::updateOrCreate(
                ['cpf' => $inscricao->cpf],
                [
                    'name' => $inscricao->name,
                    'email' => $inscricao->email,
                    'birth_date' => $inscricao->birth_date,
                    'phone' => $inscricao->phone,
                    'address' => "{$inscricao->address}, {$inscricao->number}",
                    'days_of_week' => $academico->days_of_week,
                    'observation' => $inscricao->observation,
                    'status' => $status,
                    'situacao' => $situacao,
                    'instituicao_id' => $academico->instituicao_id,
                    'linha_id' => $this->linha($pessoa, $situacao, $linhas),
                    'inscricao_id' => $inscricao->id,
                ],
            );
        }
    }

    /**
     * Ativos ficam numa linha que atende a instituição, menos alguns que ficam
     * sem linha para exercitar a pendência "ativo sem linha" do painel.
     *
     * @param  array<string, mixed>  $pessoa
     * @param  list<int>  $linhas
     */
    private function linha(array $pessoa, string $situacao, array $linhas): ?int
    {
        if ($situacao !== 'Ativo' || $linhas === [] || $pessoa['indice'] % 9 === 6) {
            return null;
        }

        return $linhas[$pessoa['indice'] % count($linhas)];
    }
}
