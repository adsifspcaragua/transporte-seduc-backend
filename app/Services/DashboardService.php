<?php

namespace App\Services;

use App\Models\Estudante;
use App\Models\Inscricao;
use App\Models\Linha;
use App\Models\PeriodoReecadastro;
use App\Models\SolicitacaoReecadastro;
use Illuminate\Http\JsonResponse;

/**
 * Numeros da tela inicial, em uma unica consulta por assunto.
 *
 * A tela responde "o que precisa da minha atencao hoje": o que esta na fila de
 * analise, quem ficou sem linha e como esta a ocupacao. Por isso os contadores
 * separam o que exige acao do que e apenas panorama.
 */
class DashboardService
{
    /**
     * Recadastro em andamento: com a responsavel ou com o estudante.
     */
    private const RECADASTRO_EM_DIA = ['Aprovado', 'Em analise'];

    public function index(): JsonResponse
    {
        $periodo = PeriodoReecadastro::aberto();

        return response()->json([
            'data' => [
                'estudantes' => $this->estudantes(),
                'inscricoes' => $this->inscricoes(),
                'recadastro' => $this->recadastro($periodo),
                'linhas' => $this->linhas(),
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function estudantes(): array
    {
        $porStatus = Estudante::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'ativos' => (int) ($porStatus['Ativo'] ?? 0),
            'inativos' => (int) ($porStatus['Inativo'] ?? 0),
            'em_espera' => (int) ($porStatus['Em espera'] ?? 0),
            'total' => (int) $porStatus->sum(),
            // Ativo sem linha nao tem como ser transportado: e uma pendencia,
            // nao um numero de panorama.
            'sem_linha' => Estudante::where('status', 'Ativo')->whereNull('linha_id')->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function inscricoes(): array
    {
        $porStatus = Inscricao::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'em_analise' => (int) ($porStatus['Em analise'] ?? 0),
            'incompletas' => (int) ($porStatus['Incompleto'] ?? 0),
            'aprovadas' => (int) ($porStatus['Aprovado'] ?? 0),
            'rejeitadas' => (int) ($porStatus['Rejeitado'] ?? 0),
            'total' => (int) $porStatus->sum(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function recadastro(?PeriodoReecadastro $periodo): array
    {
        if (! $periodo) {
            return [
                'periodo' => null,
                'em_analise' => 0,
                'pendencias' => 0,
                'ausentes' => 0,
            ];
        }

        $emDia = SolicitacaoReecadastro::where('periodo_id', $periodo->id)
            ->whereIn('status', self::RECADASTRO_EM_DIA)
            ->pluck('estudante_id');

        return [
            'periodo' => [
                'id' => $periodo->id,
                'referencia' => $periodo->referencia,
                'status' => $periodo->status,
                'data_fim' => $periodo->data_fim?->toDateString(),
            ],
            'em_analise' => SolicitacaoReecadastro::where('periodo_id', $periodo->id)
                ->where('status', 'Em analise')
                ->count(),
            'pendencias' => SolicitacaoReecadastro::where('periodo_id', $periodo->id)
                ->where('status', 'Pendencia')
                ->count(),
            'ausentes' => Estudante::where('status', 'Ativo')
                ->whereNotIn('id', $emDia)
                ->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function linhas(): array
    {
        // withCount resolve a ocupacao de todas as linhas em uma consulta, em vez
        // de uma por linha.
        $linhas = Linha::withCount([
            'estudantes as ocupacao' => fn ($query) => $query->where('status', 'Ativo'),
        ])->orderBy('name')->get();

        return [
            'total' => $linhas->count(),
            'capacidade_total' => (int) $linhas->sum('max_capacity'),
            'ocupacao_total' => (int) $linhas->sum('ocupacao'),
            'lista' => $linhas->map(fn (Linha $linha) => [
                'id' => $linha->id,
                'name' => $linha->name,
                'ocupacao' => (int) $linha->ocupacao,
                'max_capacity' => (int) $linha->max_capacity,
                'vagas_restantes' => max(0, $linha->max_capacity - $linha->ocupacao),
            ])->values(),
        ];
    }
}
