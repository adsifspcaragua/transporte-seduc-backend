<?php

namespace App\Services\Presenca;

use App\Http\Resources\Presenca\PresencaResource;
use App\Http\Resources\Presenca\TentativaPresencaResource;
use App\Models\Estudante;
use App\Models\Linha;
use App\Models\Presenca;
use App\Models\TentativaPresenca;
use App\Models\User;
use App\Models\Viagem;
use App\Services\Auditoria\AuditoriaService;
use App\Support\HorarioLocal;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * O acompanhamento da gestao: presencas, tentativas negadas, revisoes e
 * excecoes. Nada aqui depende do motorista.
 */
class PresencaAdminService
{
    public function __construct(private readonly AuditoriaService $auditoria) {}

    /**
     * @param  array<string, mixed>  $filtros
     */
    public function index(array $filtros): AnonymousResourceCollection
    {
        $presencas = Presenca::with(['estudante:id,name,cpf', 'viagem.linha:id,name', 'veiculo:id,identificacao', 'ponto:id,nome', 'registradaPor:id,name'])
            ->when($filtros['estudante_id'] ?? null, fn ($q, $id) => $q->where('estudante_id', $id))
            ->when($filtros['viagem_id'] ?? null, fn ($q, $id) => $q->where('viagem_id', $id))
            ->when($filtros['metodo'] ?? null, fn ($q, $metodo) => $q->where('metodo', $metodo))
            ->when($filtros['linha_id'] ?? null, fn ($q, $id) => $q->whereHas('viagem', fn ($v) => $v->where('linha_id', $id)))
            ->when($filtros['de'] ?? null, fn ($q, $de) => $q->whereHas('viagem', fn ($v) => $v->whereDate('data', '>=', $de)))
            ->when($filtros['ate'] ?? null, fn ($q, $ate) => $q->whereHas('viagem', fn ($v) => $v->whereDate('data', '<=', $ate)))
            ->orderByDesc('registrada_em')
            ->paginate($filtros['per_page'] ?? 15);

        return PresencaResource::collection($presencas);
    }

    /**
     * Excecao administrativa (D08): celular perdido, GPS indisponivel, falha
     * do QR. Exige justificativa e fica na auditoria com o responsavel.
     *
     * @param  array{estudante_id: int, viagem_id: int, justificativa: string}  $data
     */
    public function registrarManual(User $user, array $data): JsonResponse
    {
        try {
            $estudante = Estudante::find($data['estudante_id']);
            $viagem = Viagem::find($data['viagem_id']);

            if (! $estudante || ! $viagem) {
                return response()->json(['message' => 'Estudante ou viagem não encontrados'], 404);
            }

            $presenca = $this->criarManual($user, $estudante, $viagem, $data['justificativa']);

            if ($presenca instanceof JsonResponse) {
                return $presenca;
            }

            return response()->json([
                'data' => new PresencaResource($presenca->load(['estudante:id,name,cpf', 'viagem.linha:id,name', 'registradaPor:id,name'])),
                'message' => 'Presença registrada manualmente',
            ], 201);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Erro ao registrar presença manual'], 500);
        }
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    public function tentativas(array $filtros): AnonymousResourceCollection
    {
        $tentativas = TentativaPresenca::with(['estudante:id,name,cpf', 'viagem.linha:id,name', 'veiculo:id,identificacao', 'revisaoAnalisadaPor:id,name'])
            ->when($filtros['resultado'] ?? null, fn ($q, $resultado) => $q->where('resultado', $resultado))
            ->when($filtros['codigo'] ?? null, fn ($q, $codigo) => $q->where('codigo', $codigo))
            ->when($filtros['revisao_status'] ?? null, fn ($q, $status) => $q->where('revisao_status', $status))
            ->when($filtros['estudante_id'] ?? null, fn ($q, $id) => $q->where('estudante_id', $id))
            ->when($filtros['linha_id'] ?? null, fn ($q, $id) => $q->whereHas('viagem', fn ($v) => $v->where('linha_id', $id)))
            // Dias da operacao, convertidos para o fuso em que recebida_em e gravada.
            ->when($filtros['de'] ?? null, fn ($q, $de) => $q->where('recebida_em', '>=', HorarioLocal::inicioDoDia($de)))
            ->when($filtros['ate'] ?? null, fn ($q, $ate) => $q->where('recebida_em', '<', HorarioLocal::inicioDoDia(Carbon::parse($ate)->addDay())))
            ->orderByRaw('CASE WHEN revisao_status = ? THEN 0 ELSE 1 END', [TentativaPresenca::REVISAO_PENDENTE])
            ->orderByDesc('recebida_em')
            ->paginate($filtros['per_page'] ?? 15);

        return TentativaPresencaResource::collection($tentativas);
    }

    /**
     * Decide o pedido de revisao. Aprovar registra a presenca como manual,
     * com o parecer como justificativa.
     */
    public function analisarRevisao(User $user, string $id, string $decisao, string $parecer): JsonResponse
    {
        try {
            $tentativa = TentativaPresenca::with(['estudante', 'viagem'])->find($id);

            if (! $tentativa) {
                return response()->json(['message' => 'Tentativa não encontrada'], 404);
            }

            if ($tentativa->revisao_status !== TentativaPresenca::REVISAO_PENDENTE) {
                return response()->json(['message' => 'Esta tentativa não tem pedido de revisão pendente.'], 409);
            }

            $resultado = DB::transaction(function () use ($user, $tentativa, $decisao, $parecer) {
                $presenca = null;

                if ($decisao === TentativaPresenca::REVISAO_APROVADA) {
                    $presenca = $this->criarManual(
                        $user,
                        $tentativa->estudante,
                        $tentativa->viagem,
                        "Revisão da tentativa #{$tentativa->id}: {$parecer}",
                        $tentativa,
                    );

                    if ($presenca instanceof JsonResponse) {
                        return $presenca;
                    }
                }

                $tentativa->update([
                    'revisao_status' => $decisao,
                    'revisao_parecer' => $parecer,
                    'revisao_analisada_por' => $user->id,
                    'revisao_analisada_em' => now(),
                    'presenca_id' => $presenca?->id ?? $tentativa->presenca_id,
                ]);

                $this->auditoria->registrar($user, 'presenca.revisao.'.mb_strtolower($decisao), $tentativa, [
                    'estudante_id' => $tentativa->estudante_id,
                    'viagem_id' => $tentativa->viagem_id,
                    'presenca_id' => $presenca?->id,
                ], $parecer);

                return null;
            });

            if ($resultado instanceof JsonResponse) {
                return $resultado;
            }

            return response()->json([
                'data' => new TentativaPresencaResource($tentativa->refresh()->load(['estudante:id,name,cpf', 'viagem.linha:id,name', 'revisaoAnalisadaPor:id,name'])),
                'message' => $decisao === TentativaPresenca::REVISAO_APROVADA
                    ? 'Revisão aprovada e presença registrada'
                    : 'Revisão rejeitada',
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Erro ao analisar revisão'], 500);
        }
    }

    /**
     * Numeros para calibrar raio, precisao e horario no piloto: presencas por
     * linha e dia, negativas por motivo e qualidade das leituras de GPS.
     *
     * @param  array<string, mixed>  $filtros
     */
    public function indicadores(array $filtros): JsonResponse
    {
        $de = Carbon::parse($filtros['de'] ?? HorarioLocal::hoje()->subDays(30)->toDateString());
        $ate = Carbon::parse($filtros['ate'] ?? HorarioLocal::hoje()->toDateString());
        $linhaId = $filtros['linha_id'] ?? null;

        $presencas = Presenca::query()
            ->join('viagens', 'viagens.id', '=', 'presencas.viagem_id')
            ->whereBetween('viagens.data', [$de->toDateString(), $ate->toDateString()])
            ->when($linhaId, fn ($q) => $q->where('viagens.linha_id', $linhaId));

        $porLinha = (clone $presencas)
            ->selectRaw('viagens.linha_id, COUNT(*) as total, SUM(CASE WHEN presencas.metodo = ? THEN 1 ELSE 0 END) as manuais', [Presenca::MANUAL])
            ->groupBy('viagens.linha_id')
            ->get();
        $nomes = Linha::whereIn('id', $porLinha->pluck('linha_id'))->pluck('name', 'id');

        $porDia = (clone $presencas)
            ->selectRaw('viagens.data as dia, COUNT(*) as total')
            ->groupBy('viagens.data')
            ->orderBy('viagens.data')
            ->get();

        $tentativas = TentativaPresenca::query()
            ->where('recebida_em', '>=', HorarioLocal::inicioDoDia($de))
            ->where('recebida_em', '<', HorarioLocal::inicioDoDia($ate->copy()->addDay()))
            ->when($linhaId, fn ($q) => $q->whereHas('viagem', fn ($v) => $v->where('linha_id', $linhaId)));

        $negadas = (clone $tentativas)
            ->where('resultado', TentativaPresenca::NEGADA)
            ->selectRaw('codigo, COUNT(*) as total')
            ->groupBy('codigo')
            ->orderByDesc('total')
            ->get();

        $leituras = (clone $tentativas)->whereNotNull('precisao_metros');

        return response()->json([
            'data' => [
                'periodo' => ['de' => $de->toDateString(), 'ate' => $ate->toDateString()],
                'presencas_por_linha' => $porLinha->map(fn ($linha) => [
                    'linha_id' => (int) $linha->linha_id,
                    'linha' => $nomes[$linha->linha_id] ?? null,
                    'total' => (int) $linha->total,
                    'manuais' => (int) $linha->manuais,
                ])->values(),
                'presencas_por_dia' => $porDia->map(fn ($dia) => [
                    'data' => Carbon::parse($dia->dia)->toDateString(),
                    'total' => (int) $dia->total,
                ])->values(),
                'negadas_por_motivo' => $negadas->map(fn ($negada) => [
                    'codigo' => $negada->codigo?->value,
                    'total' => (int) $negada->total,
                ])->values(),
                'gps' => [
                    'leituras' => (clone $leituras)->count(),
                    'precisao_media_metros' => ($media = (clone $leituras)->avg('precisao_metros')) !== null ? (int) round($media) : null,
                    'distancia_media_aceita_metros' => ($distancia = (clone $presencas)->whereNotNull('presencas.distancia_metros')->avg('presencas.distancia_metros')) !== null
                        ? (int) round($distancia)
                        : null,
                ],
                'revisoes' => (clone $tentativas)
                    ->whereNotNull('revisao_status')
                    ->selectRaw('revisao_status, COUNT(*) as total')
                    ->groupBy('revisao_status')
                    ->pluck('total', 'revisao_status')
                    ->map(fn ($total) => (int) $total),
            ],
        ]);
    }

    private function criarManual(User $user, Estudante $estudante, Viagem $viagem, string $justificativa, ?TentativaPresenca $tentativa = null): Presenca|JsonResponse
    {
        if (! $viagem->estaProgramada()) {
            return response()->json(['message' => 'A viagem está cancelada. Reative-a antes de registrar presença.'], 422);
        }

        try {
            $presenca = Presenca::create([
                'estudante_id' => $estudante->id,
                'viagem_id' => $viagem->id,
                'veiculo_id' => $tentativa?->veiculo_id ?? $viagem->veiculo_id,
                'dispositivo_id' => $tentativa?->dispositivo_id,
                'metodo' => Presenca::MANUAL,
                'registrada_em' => now(),
                'registrada_por' => $user->id,
                'justificativa' => $justificativa,
            ]);
        } catch (UniqueConstraintViolationException) {
            return response()->json(['message' => 'O estudante já tem presença nesta viagem.'], 409);
        }

        if (! $tentativa) {
            $this->auditoria->registrar($user, 'presenca.manual', $presenca, [
                'estudante_id' => $estudante->id,
                'viagem_id' => $viagem->id,
            ], $justificativa);
        }

        return $presenca;
    }
}
