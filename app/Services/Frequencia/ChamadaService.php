<?php

namespace App\Services\Frequencia;

use App\Http\Resources\Frequencia\ChamadaResource;
use App\Models\Chamada;
use App\Models\Estudante;
use App\Models\Frequencia;
use App\Models\Linha;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * A chamada do dia, feita pelo motorista como uma folha de presenca.
 *
 * O motorista abre a folha da sua linha, marca cada estudante como presente,
 * falta ou falta justificada e fecha. A lista vem pronta: sao os estudantes
 * ativos da linha que tem aquele dia da semana na grade de uso do transporte.
 */
class ChamadaService
{
    private const DIAS_DA_SEMANA = [
        0 => 'domingo',
        1 => 'segunda-feira',
        2 => 'terça-feira',
        3 => 'quarta-feira',
        4 => 'quinta-feira',
        5 => 'sexta-feira',
        6 => 'sábado',
    ];

    public function __construct(
        private readonly EscopoFrequencia $escopo,
        private readonly JustificativaService $justificativas,
        private readonly BeneficioFrequenciaService $beneficio,
    ) {}

    /**
     * Linhas em que o usuario pode fazer chamada, com a situacao da de hoje.
     *
     * E a tela de entrada do motorista: "qual linha e a minha e ja fiz a chamada?".
     */
    public function linhas(User $user): JsonResponse
    {
        $permitidas = $this->escopo->linhas($user);

        $linhas = Linha::with('motorista:id,name')
            ->when($permitidas !== null, fn ($query) => $query->whereIn('id', $permitidas))
            ->orderBy('name')
            ->get();

        $hoje = Chamada::whereIn('linha_id', $linhas->pluck('id'))
            ->whereDate('data', today())
            ->get()
            ->keyBy('linha_id');

        return response()->json([
            'data' => $linhas->map(fn (Linha $linha) => [
                'id' => $linha->id,
                'name' => $linha->name,
                'departure_time' => $linha->departure_time,
                'return_time' => $linha->return_time,
                'motorista' => $linha->motorista?->only(['id', 'name']),
                'chamada_hoje' => $hoje->has($linha->id)
                    ? ['id' => $hoje[$linha->id]->id, 'status' => $hoje[$linha->id]->status]
                    : null,
            ])->values(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    public function index(User $user, array $filtros): AnonymousResourceCollection
    {
        $permitidas = $this->escopo->linhas($user);

        $chamadas = Chamada::with('linha:id,name')
            ->withCount([
                'frequencias as total_estudantes',
                'frequencias as presentes' => fn ($query) => $query->where('situacao', Frequencia::PRESENTE),
                'frequencias as faltas' => fn ($query) => $query->where('situacao', Frequencia::FALTA),
                'frequencias as justificadas' => fn ($query) => $query->where('situacao', Frequencia::JUSTIFICADA),
                'frequencias as pendentes' => fn ($query) => $query->where('situacao', Frequencia::PENDENTE),
            ])
            ->when($permitidas !== null, fn ($query) => $query->whereIn('linha_id', $permitidas))
            ->when($filtros['linha_id'] ?? null, fn ($query, $linhaId) => $query->where('linha_id', $linhaId))
            ->when($filtros['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filtros['data'] ?? null, fn ($query, $data) => $query->whereDate('data', $data))
            ->when($filtros['de'] ?? null, fn ($query, $de) => $query->whereDate('data', '>=', $de))
            ->when($filtros['ate'] ?? null, fn ($query, $ate) => $query->whereDate('data', '<=', $ate))
            ->orderByDesc('data')
            ->orderBy('linha_id')
            ->paginate($filtros['per_page'] ?? 15);

        return ChamadaResource::collection($chamadas);
    }

    /**
     * Abre a folha do dia, ou devolve a que ja existe.
     *
     * Abrir de novo nao e erro: e o motorista voltando para terminar a chamada.
     * Por isso a mesma rota serve para "abrir" e "continuar".
     *
     * @param  array<string, mixed>  $data
     */
    public function abrir(User $user, array $data): JsonResponse
    {
        try {
            $linha = Linha::find($data['linha_id']);

            if (! $linha) {
                return response()->json(['message' => 'Linha não encontrada'], 404);
            }

            if (! $this->escopo->permiteLinha($user, $linha->id)) {
                return $this->semAcesso();
            }

            $dia = Carbon::parse($data['data'])->startOfDay();

            $existente = Chamada::where('linha_id', $linha->id)->whereDate('data', $dia)->first();

            if ($existente) {
                if ($existente->estaAberta() && $dia->isToday()) {
                    $this->sincronizarEsperados($existente);
                }

                return response()->json([
                    'data' => new ChamadaResource($this->carregar($existente)),
                    'message' => $existente->estaAberta()
                        ? 'Chamada retomada'
                        : 'A chamada deste dia já foi fechada',
                ], 200);
            }

            $esperados = $this->esperados($linha, $dia);

            if ($esperados->isEmpty()) {
                $diaDaSemana = self::DIAS_DA_SEMANA[$dia->dayOfWeek];

                return response()->json([
                    'message' => "Nenhum estudante ativo da linha {$linha->name} usa o transporte neste dia ({$diaDaSemana}).",
                ], 422);
            }

            $chamada = DB::transaction(function () use ($linha, $dia, $user, $data, $esperados) {
                $chamada = Chamada::create([
                    'linha_id' => $linha->id,
                    'data' => $dia->toDateString(),
                    'status' => Chamada::ABERTA,
                    'registrada_por' => $user->id,
                    'observacoes' => $data['observacoes'] ?? null,
                ]);

                $this->incluirPendentes($chamada, $esperados);

                return $chamada;
            });

            return response()->json([
                'data' => new ChamadaResource($this->carregar($chamada)),
                'message' => 'Chamada aberta com sucesso',
            ], 201);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao abrir chamada',
            ], 500);
        }
    }

    public function show(User $user, string $id): JsonResponse
    {
        try {
            $chamada = Chamada::find($id);

            if (! $chamada) {
                return response()->json(['message' => 'Chamada não encontrada'], 404);
            }

            if (! $this->escopo->permiteLinha($user, $chamada->linha_id)) {
                return $this->semAcesso();
            }

            return response()->json([
                'data' => new ChamadaResource($this->carregar($chamada)),
                'message' => 'Chamada encontrada com sucesso',
            ], 200);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao encontrar chamada',
            ], 500);
        }
    }

    /**
     * Marca presenca, falta ou falta justificada.
     *
     * Aceita a folha inteira ou so parte dela: o motorista pode ir marcando
     * conforme os estudantes embarcam. So altera quem esta na folha; um id que
     * nao pertence a ela e devolvido em `ignorados`, em vez de criar marcacao
     * para estudante de outra linha.
     *
     * Marcar "Justificada" envia o motivo para a analise da responsavel. Uma
     * marcacao cuja justificativa ja foi analisada nao muda mais: a decisao da
     * responsavel prevalece, e o estudante volta em `bloqueados`.
     *
     * @param  list<array{estudante_id: int, situacao: string, observacao?: string|null}>  $marcacoes
     */
    public function registrar(User $user, string $id, array $marcacoes): JsonResponse
    {
        try {
            $chamada = Chamada::find($id);

            if (! $chamada) {
                return response()->json(['message' => 'Chamada não encontrada'], 404);
            }

            if (! $this->escopo->permiteLinha($user, $chamada->linha_id)) {
                return $this->semAcesso();
            }

            if (! $chamada->estaAberta()) {
                return response()->json([
                    'message' => 'Esta chamada está fechada. Reabra-a para alterar as marcações.',
                ], 409);
            }

            // Se o mesmo estudante vier duas vezes, vale a ultima marcacao.
            $porEstudante = collect($marcacoes)->keyBy(fn ($marcacao) => (int) $marcacao['estudante_id']);

            $naFolha = $chamada->frequencias()
                ->with('justificativa')
                ->whereIn('estudante_id', $porEstudante->keys())
                ->get();

            $ignorados = $porEstudante->keys()->diff($naFolha->pluck('estudante_id'))->values();

            [$bloqueadas, $frequencias] = $naFolha->partition(
                fn (Frequencia $frequencia) => (bool) $frequencia->justificativa?->foiAnalisada(),
            );
            $bloqueados = $bloqueadas->pluck('estudante_id')->values();

            if ($frequencias->isEmpty()) {
                return response()->json([
                    'message' => $naFolha->isEmpty()
                        ? 'Nenhum dos estudantes informados está nesta chamada.'
                        : 'As marcações informadas já tiveram a justificativa analisada e não podem ser alteradas.',
                    'ignorados' => $ignorados,
                    'bloqueados' => $bloqueados,
                ], 422);
            }

            DB::transaction(function () use ($frequencias, $porEstudante, $user) {
                $agora = now();

                foreach ($frequencias as $frequencia) {
                    $marcacao = $porEstudante[$frequencia->estudante_id];

                    // Cada marcacao substitui a anterior por inteiro: trocar uma
                    // falta justificada por presenca apaga a justificativa.
                    $frequencia->update([
                        'situacao' => $marcacao['situacao'],
                        'observacao' => $marcacao['observacao'] ?? null,
                        'marcada_por' => $user->id,
                        'marcada_em' => $agora,
                    ]);

                    if ($marcacao['situacao'] === Frequencia::JUSTIFICADA) {
                        $this->justificativas->justificar($frequencia, $marcacao['observacao'], $user);
                    } else {
                        $this->justificativas->retirarPendente($frequencia);
                    }
                }
            });

            return response()->json([
                'data' => new ChamadaResource($this->carregar($chamada)),
                'message' => "{$frequencias->count()} marcação(ões) registrada(s)",
                'ignorados' => $ignorados,
                'bloqueados' => $bloqueados,
            ], 200);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao registrar frequência',
            ], 500);
        }
    }

    /**
     * Entrega a folha. So fecha com todos marcados: fechar com pendente deixaria
     * um buraco no historico sem ninguem saber se o estudante foi ou nao.
     *
     * E ao fechar que as faltas passam a valer: quem faltou e reavaliado pela
     * regra do beneficio (aviso por e-mail ou perda do beneficio).
     */
    public function fechar(User $user, string $id): JsonResponse
    {
        try {
            $chamada = Chamada::find($id);

            if (! $chamada) {
                return response()->json(['message' => 'Chamada não encontrada'], 404);
            }

            if (! $this->escopo->permiteLinha($user, $chamada->linha_id)) {
                return $this->semAcesso();
            }

            if ($chamada->estaAberta()) {
                $pendentes = $chamada->frequencias()->where('situacao', Frequencia::PENDENTE)->count();

                if ($pendentes > 0) {
                    return response()->json([
                        'message' => "Ainda há {$pendentes} estudante(s) sem marcação.",
                        'pendentes' => $pendentes,
                    ], 422);
                }

                $chamada->update([
                    'status' => Chamada::FECHADA,
                    'fechada_em' => now(),
                ]);

                // So quem faltou nesta folha pode ter mudado de situacao.
                $this->beneficio->avaliar(
                    $chamada->frequencias()->where('situacao', Frequencia::FALTA)->pluck('estudante_id'),
                    $chamada->data,
                );
            }

            return response()->json([
                'data' => new ChamadaResource($this->carregar($chamada)),
                'message' => 'Chamada fechada com sucesso',
            ], 200);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao fechar chamada',
            ], 500);
        }
    }

    /**
     * Devolve a folha para correcao. A lista de estudantes nao e refeita: a
     * folha continua sendo a daquele dia, com quem era esperado naquele dia.
     */
    public function reabrir(User $user, string $id): JsonResponse
    {
        try {
            $chamada = Chamada::find($id);

            if (! $chamada) {
                return response()->json(['message' => 'Chamada não encontrada'], 404);
            }

            if (! $this->escopo->permiteLinha($user, $chamada->linha_id)) {
                return $this->semAcesso();
            }

            $chamada->update([
                'status' => Chamada::ABERTA,
                'fechada_em' => null,
            ]);

            return response()->json([
                'data' => new ChamadaResource($this->carregar($chamada)),
                'message' => 'Chamada reaberta com sucesso',
            ], 200);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao reabrir chamada',
            ], 500);
        }
    }

    public function destroy(User $user, string $id): JsonResponse
    {
        try {
            $chamada = Chamada::find($id);

            if (! $chamada) {
                return response()->json(['message' => 'Chamada não encontrada'], 404);
            }

            if (! $this->escopo->permiteLinha($user, $chamada->linha_id)) {
                return $this->semAcesso();
            }

            $chamada->delete();

            return response()->json([
                'message' => 'Chamada excluída com sucesso',
            ], 200);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao excluir chamada',
            ], 500);
        }
    }

    /**
     * Quem deveria embarcar na linha naquele dia.
     *
     * `days_of_week` segue o Carbon (0 = domingo). A regra de validacao
     * `integer` aceita "1" como texto, entao o JSON pode ter guardado o dia
     * como numero ou como string; os dois formatos contam.
     *
     * @return Collection<int, int>
     */
    private function esperados(Linha $linha, Carbon $dia): Collection
    {
        $diaDaSemana = $dia->dayOfWeek;

        return Estudante::where('linha_id', $linha->id)
            ->where('status', 'Ativo')
            ->where(fn ($query) => $query
                ->whereJsonContains('days_of_week', $diaDaSemana)
                ->orWhereJsonContains('days_of_week', (string) $diaDaSemana))
            ->pluck('id');
    }

    /**
     * @param  Collection<int, int>  $estudanteIds
     */
    private function incluirPendentes(Chamada $chamada, Collection $estudanteIds): void
    {
        $agora = now();

        Frequencia::insert($estudanteIds->map(fn ($estudanteId) => [
            'chamada_id' => $chamada->id,
            'estudante_id' => $estudanteId,
            'situacao' => Frequencia::PENDENTE,
            'created_at' => $agora,
            'updated_at' => $agora,
        ])->all());
    }

    /**
     * Acerta a folha de hoje com a linha como ela esta agora.
     *
     * Entra quem foi alocado na linha depois que a folha foi aberta. Sai quem
     * deixou de ser esperado (trocou de linha, foi inativado), mas so se ainda
     * nao tiver sido marcado: marcacao feita e historia, nao se apaga. Sem a
     * saida, o pendente que nao esta mais na linha impediria fechar a folha.
     */
    private function sincronizarEsperados(Chamada $chamada): void
    {
        $esperados = $this->esperados($chamada->linha, $chamada->data);
        $naFolha = $chamada->frequencias()->pluck('estudante_id');

        DB::transaction(function () use ($chamada, $esperados, $naFolha) {
            $novos = $esperados->diff($naFolha)->values();

            if ($novos->isNotEmpty()) {
                $this->incluirPendentes($chamada, $novos);
            }

            $chamada->frequencias()
                ->where('situacao', Frequencia::PENDENTE)
                ->whereNotIn('estudante_id', $esperados)
                ->delete();
        });
    }

    /**
     * A folha pronta para exibir: estudantes em ordem alfabetica, como na lista
     * de papel.
     */
    private function carregar(Chamada $chamada): Chamada
    {
        $chamada->load([
            'linha:id,name,motorista_id',
            'linha.motorista:id,name',
            'registradaPor:id,name',
            'frequencias.estudante:id,name,cpf',
            'frequencias.justificativa:id,frequencia_id,status,parecer',
        ]);

        return $chamada->setRelation(
            'frequencias',
            $chamada->frequencias->sortBy(fn (Frequencia $frequencia) => $frequencia->estudante?->name)->values(),
        );
    }

    private function semAcesso(): JsonResponse
    {
        return response()->json([
            'message' => 'Você não tem acesso à chamada desta linha.',
        ], 403);
    }
}
