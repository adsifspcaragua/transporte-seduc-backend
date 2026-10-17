<?php

namespace App\Services\Dispositivo;

use App\Http\Resources\Dispositivo\DispositivoResource;
use App\Http\Resources\Dispositivo\SolicitacaoDispositivoResource;
use App\Models\DispositivoEstudante;
use App\Models\SolicitacaoDispositivo;
use App\Models\User;
use App\Services\Auditoria\AuditoriaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Throwable;

/**
 * O lado do atendimento: conferir a identidade, aprovar ou rejeitar o pedido
 * e revogar credenciais. Toda decisao fica com responsavel e justificativa.
 */
class AnaliseDispositivoService
{
    public function __construct(private readonly AuditoriaService $auditoria) {}

    /**
     * Fila de solicitacoes, com as pendentes primeiro e as mais antigas antes.
     *
     * @param  array<string, mixed>  $filtros
     */
    public function solicitacoes(array $filtros): AnonymousResourceCollection
    {
        $solicitacoes = SolicitacaoDispositivo::with(['estudante:id,name,cpf,linha_id', 'analisadaPor:id,name'])
            ->when($filtros['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filtros['tipo'] ?? null, fn ($q, $tipo) => $q->where('tipo', $tipo))
            ->when($filtros['estudante_id'] ?? null, fn ($q, $id) => $q->where('estudante_id', $id))
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [SolicitacaoDispositivo::PENDENTE])
            ->orderBy('created_at')
            ->paginate($filtros['per_page'] ?? 15);

        return SolicitacaoDispositivoResource::collection($solicitacoes);
    }

    public function solicitacao(string $id): JsonResponse
    {
        $solicitacao = SolicitacaoDispositivo::with(['estudante:id,name,cpf,linha_id', 'analisadaPor:id,name'])->find($id);

        if (! $solicitacao) {
            return response()->json(['message' => 'Solicitação não encontrada'], 404);
        }

        return response()->json(['data' => new SolicitacaoDispositivoResource($solicitacao)]);
    }

    /**
     * A aprovacao vale por pouco tempo: o estudante conclui o vinculo ali, no
     * atendimento, e nao fica uma autorizacao aberta esperando qualquer um.
     */
    public function aprovar(User $user, string $id, string $parecer): JsonResponse
    {
        return $this->decidir($user, $id, $parecer, true);
    }

    public function rejeitar(User $user, string $id, string $parecer): JsonResponse
    {
        return $this->decidir($user, $id, $parecer, false);
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    public function dispositivos(array $filtros): AnonymousResourceCollection
    {
        $dispositivos = DispositivoEstudante::with(['estudante:id,name,cpf', 'aprovadoPor:id,name', 'revogadoPor:id,name'])
            ->when($filtros['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filtros['estudante_id'] ?? null, fn ($q, $id) => $q->where('estudante_id', $id))
            ->orderByDesc('created_at')
            ->paginate($filtros['per_page'] ?? 15);

        return DispositivoResource::collection($dispositivos);
    }

    /**
     * Perda, roubo ou suspeita de uso indevido. Vale na hora: a confirmacao
     * de presenca confere a credencial de novo, dentro da transacao.
     */
    public function revogar(User $user, string $id, string $motivo): JsonResponse
    {
        try {
            $dispositivo = DispositivoEstudante::find($id);

            if (! $dispositivo) {
                return response()->json(['message' => 'Dispositivo não encontrado'], 404);
            }

            if (! $dispositivo->estaAtivo()) {
                return response()->json(['message' => 'Este dispositivo já foi revogado.'], 409);
            }

            $dispositivo->update([
                'status' => DispositivoEstudante::REVOGADO,
                'revogado_em' => now(),
                'revogado_por' => $user->id,
                'motivo_revogacao' => $motivo,
            ]);

            $this->auditoria->registrar($user, 'dispositivo.revogar', $dispositivo, [
                'estudante_id' => $dispositivo->estudante_id,
            ], $motivo);

            return response()->json([
                'data' => new DispositivoResource($dispositivo->load(['estudante:id,name,cpf', 'revogadoPor:id,name'])),
                'message' => 'Dispositivo revogado',
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Erro ao revogar dispositivo'], 500);
        }
    }

    private function decidir(User $user, string $id, string $parecer, bool $aprovar): JsonResponse
    {
        try {
            $solicitacao = SolicitacaoDispositivo::find($id);

            if (! $solicitacao) {
                return response()->json(['message' => 'Solicitação não encontrada'], 404);
            }

            $permitidos = $aprovar ? [SolicitacaoDispositivo::PENDENTE] : SolicitacaoDispositivo::EM_ABERTO;

            if (! in_array($solicitacao->status, $permitidos, true)) {
                return response()->json([
                    'message' => "Esta solicitação está {$solicitacao->status} e não pode ser ".($aprovar ? 'aprovada.' : 'rejeitada.'),
                ], 409);
            }

            $solicitacao->update([
                'status' => $aprovar ? SolicitacaoDispositivo::APROVADA : SolicitacaoDispositivo::REJEITADA,
                'analisada_por' => $user->id,
                'analisada_em' => now(),
                'parecer' => $parecer,
                'aprovacao_expira_em' => $aprovar
                    ? now()->addMinutes((int) config('presenca.dispositivo.aprovacao_validade_minutos'))
                    : null,
            ]);

            $this->auditoria->registrar(
                $user,
                $aprovar ? 'dispositivo.solicitacao.aprovar' : 'dispositivo.solicitacao.rejeitar',
                $solicitacao,
                ['estudante_id' => $solicitacao->estudante_id, 'tipo' => $solicitacao->tipo],
                $parecer,
            );

            return response()->json([
                'data' => new SolicitacaoDispositivoResource($solicitacao->load(['estudante:id,name,cpf,linha_id', 'analisadaPor:id,name'])),
                'message' => $aprovar
                    ? 'Solicitação aprovada. O estudante deve concluir a vinculação no navegador até '.$solicitacao->aprovacao_expira_em->format('H:i').'.'
                    : 'Solicitação rejeitada',
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Erro ao analisar solicitação'], 500);
        }
    }
}
