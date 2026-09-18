<?php

namespace App\Services\Frequencia;

use App\Http\Resources\Frequencia\JustificativaResource;
use App\Models\Chamada;
use App\Models\Frequencia;
use App\Models\Justificativa;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Justificativas de falta e a analise da responsavel.
 *
 * A justificativa nasce de duas formas:
 *  - o motorista (ou a secretaria) marca o estudante como "Justificada" na
 *    chamada, informando o motivo;
 *  - uma falta ja registrada recebe justificativa depois, inclusive com a folha
 *    fechada (o estudante levou o atestado a secretaria dias depois).
 *
 * A decisao:
 *  - Aprovada: a falta e retirada. A marcacao fica "Justificada" e nao conta.
 *  - Rejeitada: a justificativa e invalida. A marcacao volta a ser "Falta",
 *    passa a contar e a regra do beneficio e reaplicada na hora.
 */
class JustificativaService
{
    public function __construct(
        private readonly EscopoFrequencia $escopo,
        private readonly BeneficioFrequenciaService $beneficio,
    ) {}

    /**
     * @param  array<string, mixed>  $filtros
     */
    public function index(User $user, array $filtros): AnonymousResourceCollection
    {
        $permitidas = $this->escopo->linhas($user);

        $noEscopo = fn ($query) => $query->whereHas('frequencia.chamada', fn ($chamada) => $chamada
            ->when($permitidas !== null, fn ($q) => $q->whereIn('linha_id', $permitidas)));

        $justificativas = Justificativa::with($this->relacoes())
            ->tap($noEscopo)
            ->when($filtros['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filtros['estudante_id'] ?? null, fn ($query, $id) => $query->whereHas('frequencia', fn ($q) => $q->where('estudante_id', $id)))
            ->when($filtros['linha_id'] ?? null, fn ($query, $id) => $query->whereHas('frequencia.chamada', fn ($q) => $q->where('linha_id', $id)))
            ->when($filtros['de'] ?? null, fn ($query, $de) => $query->whereHas('frequencia.chamada', fn ($q) => $q->whereDate('data', '>=', $de)))
            ->when($filtros['ate'] ?? null, fn ($query, $ate) => $query->whereHas('frequencia.chamada', fn ($q) => $q->whereDate('data', '<=', $ate)))
            // A fila de trabalho primeiro: o que espera decisao, do mais antigo
            // para o mais novo.
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [Justificativa::EM_ANALISE])
            ->orderBy('created_at')
            ->paginate($filtros['per_page'] ?? 15);

        $emAnalise = Justificativa::where('status', Justificativa::EM_ANALISE)->tap($noEscopo)->count();

        return JustificativaResource::collection($justificativas)->additional([
            'em_analise' => $emAnalise,
        ]);
    }

    public function show(User $user, string $id): JsonResponse
    {
        $justificativa = Justificativa::with($this->relacoes())->find($id);

        if (! $justificativa) {
            return response()->json(['message' => 'Justificativa não encontrada'], 404);
        }

        if (! $this->escopo->permiteLinha($user, $justificativa->frequencia->chamada->linha_id)) {
            return $this->semAcesso();
        }

        return response()->json([
            'data' => new JustificativaResource($justificativa),
            'message' => 'Justificativa encontrada com sucesso',
        ]);
    }

    /**
     * Justifica uma falta ja registrada, mesmo com a folha fechada.
     *
     * @param  array{frequencia_id: int, motivo: string}  $data
     */
    public function criar(User $user, array $data): JsonResponse
    {
        try {
            $frequencia = Frequencia::with(['chamada', 'justificativa'])->find($data['frequencia_id']);

            if (! $frequencia) {
                return response()->json(['message' => 'Marcação não encontrada'], 404);
            }

            if (! $this->escopo->permiteLinha($user, $frequencia->chamada->linha_id)) {
                return $this->semAcesso();
            }

            if ($frequencia->justificativa?->foiAnalisada()) {
                return response()->json([
                    'message' => 'Esta falta já teve uma justificativa analisada pela responsável.',
                ], 409);
            }

            if ($frequencia->situacao !== Frequencia::FALTA) {
                return response()->json([
                    'message' => 'Só é possível justificar uma marcação de falta.',
                ], 422);
            }

            DB::transaction(fn () => $this->justificar($frequencia, $data['motivo'], $user));

            return response()->json([
                'data' => new JustificativaResource($frequencia->justificativa()->with($this->relacoes())->first()),
                'message' => 'Justificativa enviada para análise',
            ], 201);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao enviar justificativa',
            ], 500);
        }
    }

    /**
     * @param  array{decisao: string, parecer?: string|null}  $data
     */
    public function analisar(User $user, string $id, array $data): JsonResponse
    {
        try {
            $justificativa = Justificativa::with('frequencia.chamada')->find($id);

            if (! $justificativa) {
                return response()->json(['message' => 'Justificativa não encontrada'], 404);
            }

            $frequencia = $justificativa->frequencia;

            if (! $this->escopo->permiteLinha($user, $frequencia->chamada->linha_id)) {
                return $this->semAcesso();
            }

            if ($justificativa->foiAnalisada()) {
                return response()->json([
                    'message' => 'Esta justificativa já foi analisada.',
                ], 409);
            }

            $rejeitada = $data['decisao'] === Justificativa::REJEITADA;

            DB::transaction(function () use ($justificativa, $frequencia, $data, $user, $rejeitada) {
                $justificativa->update([
                    'status' => $data['decisao'],
                    'parecer' => $data['parecer'] ?? null,
                    'analisada_por' => $user->id,
                    'analisada_em' => now(),
                ]);

                if ($rejeitada) {
                    $frequencia->update(['situacao' => Frequencia::FALTA]);
                }
            });

            // A falta que voltou a valer pode completar o limite. Folha aberta
            // ainda nao conta: sera avaliada quando for fechada.
            if ($rejeitada && $frequencia->chamada->status === Chamada::FECHADA) {
                $this->beneficio->avaliar([$frequencia->estudante_id], $frequencia->chamada->data);
            }

            $justificativa->load($this->relacoes());

            return response()->json([
                'data' => new JustificativaResource($justificativa),
                'message' => $rejeitada
                    ? 'Justificativa rejeitada: a falta passa a contar'
                    : 'Justificativa aprovada: a falta foi retirada',
                ...$this->alertaEstudanteInativo($justificativa, $rejeitada),
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Erro ao analisar justificativa',
            ], 500);
        }
    }

    /**
     * Cria a justificativa da marcacao, ou troca o motivo da que ainda espera
     * analise, e deixa a marcacao como "Justificada".
     *
     * Chamado tambem pela chamada, quando o motorista marca "Justificada".
     */
    public function justificar(Frequencia $frequencia, string $motivo, User $user): void
    {
        Justificativa::updateOrCreate(
            ['frequencia_id' => $frequencia->id],
            [
                'motivo' => $motivo,
                'status' => Justificativa::EM_ANALISE,
                'enviada_por' => $user->id,
            ],
        );

        $frequencia->update([
            'situacao' => Frequencia::JUSTIFICADA,
            'observacao' => $motivo,
        ]);
    }

    /**
     * O motorista trocou "Justificada" por outra marcacao antes da analise: a
     * justificativa deixa de existir.
     */
    public function retirarPendente(Frequencia $frequencia): void
    {
        $frequencia->justificativa()->where('status', Justificativa::EM_ANALISE)->delete();
    }

    /**
     * Aprovar retira a falta, mas nao devolve o beneficio sozinho: se o
     * estudante ja foi inativado, reativar e decisao da responsavel, que ve a
     * situacao completa (outras faltas, vaga na linha).
     *
     * @return array<string, string>
     */
    private function alertaEstudanteInativo(Justificativa $justificativa, bool $rejeitada): array
    {
        $estudante = $justificativa->frequencia->estudante;

        if ($rejeitada || ! $estudante || $estudante->status === 'Ativo') {
            return [];
        }

        return [
            'alerta' => 'O estudante está inativo. Se ele perdeu o benefício por estas faltas, reative-o no cadastro do estudante.',
        ];
    }

    /**
     * @return list<string>
     */
    private function relacoes(): array
    {
        return [
            'frequencia.chamada.linha:id,name',
            'frequencia.estudante:id,name,cpf,email,status',
            'enviadaPor:id,name',
            'analisadaPor:id,name',
        ];
    }

    private function semAcesso(): JsonResponse
    {
        return response()->json([
            'message' => 'Você não tem acesso às justificativas desta linha.',
        ], 403);
    }
}
