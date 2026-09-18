<?php

namespace App\Services\Frequencia;

use App\Models\Estudante;
use App\Models\Frequencia;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Frequencia consolidada a partir das chamadas.
 *
 * Como as regras contam:
 *  - Falta justificada nao e falta: aparece separada e fica fora do percentual.
 *    O percentual e presencas / (presencas + faltas).
 *  - Faltas consecutivas sao as faltas seguidas mais recentes do periodo. Uma
 *    falta justificada interrompe a sequencia (o estudante deu noticia); um
 *    pendente e ignorado (a folha nao foi terminada, nao se sabe o que houve).
 *
 * Nada aqui muda o status do estudante: o relatorio mostra, a decisao e da
 * responsavel.
 */
class RelatorioFrequenciaService
{
    /** Periodo padrao quando nenhum e informado. */
    private const DIAS_PADRAO = 30;

    public function __construct(
        private readonly EscopoFrequencia $escopo,
        private readonly BeneficioFrequenciaService $beneficio,
    ) {}

    /**
     * Um resumo por estudante, com quem mais falta primeiro.
     *
     * @param  array<string, mixed>  $filtros
     */
    public function geral(User $user, array $filtros): JsonResponse
    {
        [$de, $ate] = $this->periodo($filtros);

        // Uma consulta so, sem montar models: o relatorio pode cobrir meses de
        // chamadas de todas as linhas.
        $registros = $this->registros($user, $de, $ate)
            ->when($filtros['linha_id'] ?? null, fn (Builder $query, $linhaId) => $query->where('chamadas.linha_id', $linhaId))
            ->get()
            ->groupBy('estudante_id');

        $estudantes = Estudante::with('linha:id,name')
            ->whereIn('id', $registros->keys())
            ->get(['id', 'name', 'cpf', 'status', 'linha_id'])
            ->keyBy('id');

        $minimo = (int) ($filtros['faltas_consecutivas_min'] ?? 0);

        $linhas = $registros
            ->map(fn (Collection $doEstudante, int $estudanteId) => [
                'estudante' => $this->dadosDoEstudante($estudantes->get($estudanteId), $estudanteId),
                ...$this->resumo($doEstudante),
            ])
            ->filter(fn (array $linha) => $linha['faltas_consecutivas'] >= $minimo)
            ->sortBy([
                ['faltas', 'desc'],
                ['faltas_consecutivas', 'desc'],
                [fn (array $a, array $b) => strcmp($a['estudante']['name'] ?? '', $b['estudante']['name'] ?? '')],
            ])
            ->values();

        return response()->json([
            'data' => $linhas,
            'periodo' => ['de' => $de->toDateString(), 'ate' => $ate->toDateString()],
            'totais' => [
                'estudantes' => $linhas->count(),
                'presencas' => $linhas->sum('presencas'),
                'faltas' => $linhas->sum('faltas'),
                'justificadas' => $linhas->sum('justificadas'),
                'pendentes' => $linhas->sum('pendentes'),
            ],
        ]);
    }

    /**
     * A frequencia de um estudante, com a folha de cada dia.
     *
     * @param  array<string, mixed>  $filtros
     */
    public function estudante(User $user, string $estudanteId, array $filtros): JsonResponse
    {
        $estudante = Estudante::with('linha:id,name')->find($estudanteId);

        if (! $estudante) {
            return response()->json(['message' => 'Estudante não encontrado'], 404);
        }

        [$de, $ate] = $this->periodo($filtros);

        $registros = $this->registros($user, $de, $ate)
            ->where('frequencias.estudante_id', $estudante->id)
            ->get();

        return response()->json([
            'data' => [
                'estudante' => $this->dadosDoEstudante($estudante, $estudante->id),
                ...$this->resumo($registros),
                // Como esta frente a regra do beneficio no mes corrente. A regra
                // olha todas as linhas do estudante, entao so a secretaria ve;
                // o motorista enxergaria faltas de linhas que nao conduz.
                'beneficio' => $this->escopo->linhas($user) === null
                    ? $this->beneficio->situacao($estudante, today())
                    : null,
                'historico' => $registros
                    ->sortByDesc('data')
                    ->map(fn (object $registro) => [
                        'chamada_id' => $registro->chamada_id,
                        'data' => Carbon::parse($registro->data)->toDateString(),
                        'linha' => ['id' => $registro->linha_id, 'name' => $registro->linha_nome],
                        'situacao' => $registro->situacao,
                        'observacao' => $registro->observacao,
                    ])
                    ->values(),
            ],
            'periodo' => ['de' => $de->toDateString(), 'ate' => $ate->toDateString()],
        ]);
    }

    /**
     * As marcacoes do periodo, na ordem das datas, restritas as linhas que o
     * usuario enxerga.
     */
    private function registros(User $user, Carbon $de, Carbon $ate): Builder
    {
        $permitidas = $this->escopo->linhas($user);

        return DB::table('frequencias')
            ->join('chamadas', 'chamadas.id', '=', 'frequencias.chamada_id')
            ->join('linhas', 'linhas.id', '=', 'chamadas.linha_id')
            ->whereBetween('chamadas.data', [$de->toDateString(), $ate->toDateString()])
            ->when($permitidas !== null, fn (Builder $query) => $query->whereIn('chamadas.linha_id', $permitidas))
            ->orderBy('chamadas.data')
            ->select([
                'frequencias.estudante_id',
                'frequencias.situacao',
                'frequencias.observacao',
                'frequencias.chamada_id',
                'chamadas.data',
                'chamadas.linha_id',
                'linhas.name as linha_nome',
            ]);
    }

    /**
     * @param  Collection<int, object>  $registros  em ordem crescente de data
     * @return array<string, int|float|null>
     */
    private function resumo(Collection $registros): array
    {
        $porSituacao = $registros->countBy('situacao');

        $presencas = (int) ($porSituacao[Frequencia::PRESENTE] ?? 0);
        $faltas = (int) ($porSituacao[Frequencia::FALTA] ?? 0);
        $consideradas = $presencas + $faltas;

        return [
            'chamadas' => $registros->count(),
            'presencas' => $presencas,
            'faltas' => $faltas,
            'justificadas' => (int) ($porSituacao[Frequencia::JUSTIFICADA] ?? 0),
            'pendentes' => (int) ($porSituacao[Frequencia::PENDENTE] ?? 0),
            'percentual_presenca' => $consideradas > 0 ? round($presencas / $consideradas * 100, 1) : null,
            'faltas_consecutivas' => $this->faltasConsecutivas($registros),
        ];
    }

    /**
     * @param  Collection<int, object>  $registros  em ordem crescente de data
     */
    private function faltasConsecutivas(Collection $registros): int
    {
        $sequencia = 0;

        foreach ($registros->reverse() as $registro) {
            if ($registro->situacao === Frequencia::PENDENTE) {
                continue;
            }

            if ($registro->situacao !== Frequencia::FALTA) {
                break;
            }

            $sequencia++;
        }

        return $sequencia;
    }

    /**
     * @return array<string, mixed>
     */
    private function dadosDoEstudante(?Estudante $estudante, int $id): array
    {
        return [
            'id' => $id,
            'name' => $estudante?->name,
            'cpf' => $estudante?->cpf,
            'status' => $estudante?->status,
            // A linha de hoje; o historico mostra em qual linha foi cada chamada.
            'linha' => $estudante?->linha?->only(['id', 'name']),
        ];
    }

    /**
     * @param  array<string, mixed>  $filtros
     * @return array{0: Carbon, 1: Carbon}
     */
    private function periodo(array $filtros): array
    {
        $ate = isset($filtros['ate']) ? Carbon::parse($filtros['ate']) : today();
        $de = isset($filtros['de']) ? Carbon::parse($filtros['de']) : $ate->copy()->subDays(self::DIAS_PADRAO - 1);

        return [$de->startOfDay(), $ate->startOfDay()];
    }
}
