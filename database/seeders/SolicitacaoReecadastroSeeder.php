<?php

namespace Database\Seeders;

use App\Models\DocumentacaoReecadastro;
use App\Models\Estudante;
use App\Models\PeriodoReecadastro;
use App\Models\SolicitacaoReecadastro;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Database\Seeders\Support\Cenarios;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Recadastro do período aberto em todos os estados, para testar a análise,
 * as pendências e a lista de ausentes do painel.
 */
class SolicitacaoReecadastroSeeder extends Seeder
{
    private ?string $pdfDeTeste = null;

    public function run(): void
    {
        $periodo = PeriodoReecadastro::aberto();

        if (! $periodo) {
            return;
        }

        $analistaId = User::where('email', 'admin@example.com')->value('id');
        $emAnalise = 0;

        foreach (Cenarios::pessoas() as $pessoa) {
            if ($pessoa['recadastro'] === null) {
                continue;
            }

            $estudante = Estudante::where('cpf', $pessoa['cpf'])->first();

            if (! $estudante) {
                continue;
            }

            $status = $pessoa['recadastro'];
            $enviada = $status !== 'Pendente';
            $analisada = in_array($status, ['Pendencia', 'Aprovado', 'Rejeitado'], true);
            // Um dos enviados declarou que ainda não tem o cronograma e pediu prazo.
            $comPrazo = $status === 'Em analise' && $emAnalise++ === 0;

            $solicitacao = SolicitacaoReecadastro::updateOrCreate(
                ['estudante_id' => $estudante->id, 'periodo_id' => $periodo->id],
                [
                    'status' => $status,
                    'observacoes' => match ($status) {
                        'Pendencia' => 'Comprovante de residência desatualizado. Envie um com menos de 90 dias.',
                        'Rejeitado' => 'Não comprovou matrícula no semestre atual.',
                        default => null,
                    },
                    'campos_pendentes' => $status === 'Pendencia' ? ['address', 'number'] : null,
                    'prazo_matricula' => false,
                    'prazo_cronograma' => $comPrazo,
                    'aceite_veracidade' => $enviada,
                    'aceite_ciencia' => $enviada,
                    'enviada_em' => $enviada ? now()->subDays($pessoa['indice'] % 10 + 2) : null,
                    'analisado_por' => $analisada ? $analistaId : null,
                    'analisado_em' => $analisada ? now()->subDay() : null,
                ],
            );

            foreach ($this->tiposEnviados($status, $comPrazo) as $tipo) {
                $caminho = "reecadastro/{$periodo->ano}-{$periodo->semestre}/{$estudante->id}/{$tipo}.pdf";
                Storage::put($caminho, $this->pdfDeTeste());

                $devolvido = $status === 'Pendencia' && $tipo === 'comprovante_residencia';

                DocumentacaoReecadastro::updateOrCreate(
                    ['solicitacao_id' => $solicitacao->id, 'type' => $tipo],
                    [
                        'estudante_id' => $estudante->id,
                        'file_path' => $caminho,
                        'nome_original' => "{$tipo}.pdf",
                        'status' => match (true) {
                            $devolvido, $status === 'Rejeitado' => 'Rejeitado',
                            $analisada => 'Aprovado',
                            default => 'Enviado',
                        },
                        'observacoes' => $devolvido ? 'Comprovante com mais de 90 dias.' : null,
                    ],
                );
            }
        }
    }

    /**
     * @return list<string>
     */
    private function tiposEnviados(string $status, bool $comPrazo): array
    {
        $tipos = array_keys(DocumentacaoReecadastro::TIPOS);

        // Quem ainda não finalizou enviou só o primeiro documento.
        if ($status === 'Pendente') {
            return array_slice($tipos, 0, 1);
        }

        return $comPrazo ? array_values(array_diff($tipos, ['cronograma_aulas'])) : $tipos;
    }

    private function pdfDeTeste(): string
    {
        return $this->pdfDeTeste ??= Pdf::loadHTML(
            '<h1 style="font-family: Helvetica">Documento de recadastro de teste</h1>'
            .'<p style="font-family: Helvetica">Arquivo gerado pela seeder.</p>'
        )->output();
    }
}
