<?php

namespace App\Services\Dispositivo;

use App\Enums\CodigoPresenca;
use App\Exceptions\PresencaNegada;
use App\Http\Resources\Dispositivo\DispositivoResource;
use App\Http\Resources\Dispositivo\SolicitacaoDispositivoResource;
use App\Models\DesafioDispositivo;
use App\Models\DispositivoEstudante;
use App\Models\Estudante;
use App\Models\SolicitacaoDispositivo;
use App\Services\Auditoria\AuditoriaService;
use App\Services\Viagem\AutorizacaoEstudante;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * O lado do estudante na autorizacao do navegador.
 *
 * 1. pede a vinculacao (ou a troca, se ja tem um navegador autorizado);
 * 2. o atendimento confere a identidade e aprova (AnaliseDispositivoService);
 * 3. com a aprovacao valida, pede um desafio;
 * 4. o navegador gera o par ECDSA P-256, guarda a privada e devolve a publica
 *    com a assinatura do desafio. So ai a credencial passa a existir.
 *
 * Quem fizer o primeiro login nao vincula nada sozinho: sem o passo 2 nao ha
 * desafio, e sem desafio assinado nao ha credencial.
 */
class VinculacaoDispositivoService
{
    public function __construct(
        private readonly DesafioService $desafios,
        private readonly AssinaturaEcdsa $assinatura,
        private readonly AutorizacaoEstudante $autorizacao,
        private readonly AuditoriaService $auditoria,
    ) {}

    /**
     * A tela "Meu dispositivo". O servidor nao sabe se ESTE navegador guarda
     * a chave: se a situacao for "ativo" e o IndexedDB estiver vazio, o
     * navegador nao e o autorizado e o caminho e pedir a troca.
     */
    public function situacao(Estudante $estudante): JsonResponse
    {
        $this->expirarAprovacoesVencidas($estudante);

        $dispositivo = $estudante->dispositivoAtivo()->with('aprovadoPor:id,name')->first();
        $solicitacao = $this->emAberto($estudante);

        $situacao = match (true) {
            $dispositivo !== null => 'ativo',
            $solicitacao?->status === SolicitacaoDispositivo::APROVADA => 'aguardando_conclusao',
            $solicitacao !== null => 'aguardando_aprovacao',
            default => 'sem_dispositivo',
        };

        return response()->json([
            'data' => [
                'situacao' => $situacao,
                'pode_registrar_presenca' => $dispositivo !== null && $this->autorizacao->habilitado($estudante),
                'dispositivo' => $dispositivo ? new DispositivoResource($dispositivo) : null,
                'solicitacao' => $solicitacao ? new SolicitacaoDispositivoResource($solicitacao) : null,
            ],
        ]);
    }

    public function solicitar(Estudante $estudante, ?string $motivo): JsonResponse
    {
        try {
            if (! $this->autorizacao->habilitado($estudante)) {
                return $this->negar(CodigoPresenca::STUDENT_NOT_ELIGIBLE);
            }

            $this->expirarAprovacoesVencidas($estudante);

            if ($this->emAberto($estudante)) {
                return response()->json([
                    'message' => 'Você já tem uma solicitação de dispositivo em andamento.',
                ], 409);
            }

            $tipo = $estudante->dispositivoAtivo()->exists()
                ? SolicitacaoDispositivo::TROCA
                : SolicitacaoDispositivo::VINCULACAO;

            if ($tipo === SolicitacaoDispositivo::TROCA && blank($motivo)) {
                return response()->json([
                    'message' => 'Informe o motivo da troca de dispositivo.',
                    'errors' => ['motivo' => ['Informe o motivo da troca de dispositivo.']],
                ], 422);
            }

            $solicitacao = SolicitacaoDispositivo::create([
                'estudante_id' => $estudante->id,
                'tipo' => $tipo,
                'status' => SolicitacaoDispositivo::PENDENTE,
                'motivo' => $motivo,
            ]);

            return response()->json([
                'data' => new SolicitacaoDispositivoResource($solicitacao),
                'message' => 'Solicitação registrada. Procure o atendimento do transporte universitário com um documento de identificação.',
            ], 201);
        } catch (UniqueConstraintViolationException) {
            return response()->json([
                'message' => 'Você já tem uma solicitação de dispositivo em andamento.',
            ], 409);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Erro ao registrar solicitação de dispositivo'], 500);
        }
    }

    public function cancelar(Estudante $estudante, string $id): JsonResponse
    {
        $solicitacao = $this->daConta($estudante, $id);

        if (! $solicitacao) {
            return response()->json(['message' => 'Solicitação não encontrada'], 404);
        }

        if (! $solicitacao->estaEmAberto()) {
            return response()->json(['message' => 'Esta solicitação já foi encerrada.'], 409);
        }

        $solicitacao->update(['status' => SolicitacaoDispositivo::CANCELADA]);

        return response()->json([
            'data' => new SolicitacaoDispositivoResource($solicitacao),
            'message' => 'Solicitação cancelada',
        ]);
    }

    public function desafio(Estudante $estudante, string $id): JsonResponse
    {
        try {
            $solicitacao = $this->daConta($estudante, $id);

            if (! $solicitacao) {
                return response()->json(['message' => 'Solicitação não encontrada'], 404);
            }

            if ($recusa = $this->recusaConclusao($solicitacao)) {
                return $recusa;
            }

            [$desafio, $texto] = $this->desafios->emitir($estudante, DesafioDispositivo::VINCULACAO, [
                'solicitacao_id' => $solicitacao->id,
            ]);

            return response()->json([
                'data' => [
                    'desafio_id' => $desafio->id,
                    'desafio' => $texto,
                    'expira_em' => $desafio->expira_em->toISOString(),
                    'algoritmo' => 'ECDSA-P256-SHA256',
                    'formato_chave' => 'spki',
                    'formato_assinatura' => 'p1363',
                ],
                'message' => 'Assine o desafio com a nova chave deste navegador.',
            ], 201);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Erro ao emitir desafio'], 500);
        }
    }

    /**
     * @param  array{chave_publica: string, desafio_id: string, desafio: string, assinatura: string, rotulo?: string|null}  $data
     */
    public function concluir(Estudante $estudante, string $id, array $data): JsonResponse
    {
        try {
            $solicitacao = $this->daConta($estudante, $id);

            if (! $solicitacao) {
                return response()->json(['message' => 'Solicitação não encontrada'], 404);
            }

            if ($recusa = $this->recusaConclusao($solicitacao)) {
                return $recusa;
            }

            $chave = $this->assinatura->chavePublica($data['chave_publica']);

            if ($chave === null) {
                return response()->json([
                    'message' => 'Chave pública inválida. Use uma chave ECDSA P-256 exportada em SPKI.',
                    'errors' => ['chave_publica' => ['Chave pública inválida. Use uma chave ECDSA P-256 exportada em SPKI.']],
                ], 422);
            }

            // Prova de posse: so quem tem a privada desta chave assina o
            // desafio emitido para esta solicitacao.
            $desafio = $this->desafios->consumir(
                $estudante,
                DesafioDispositivo::VINCULACAO,
                $data['desafio_id'],
                $data['desafio'],
                $data['assinatura'],
                $chave,
            );

            if ((int) $desafio->solicitacao_id !== $solicitacao->id) {
                return $this->negar(CodigoPresenca::DEVICE_PROOF_INVALID);
            }

            $dispositivo = DB::transaction(function () use ($estudante, $solicitacao, $chave, $data) {
                $solicitacao = SolicitacaoDispositivo::whereKey($solicitacao->id)->lockForUpdate()->first();

                if (! $solicitacao->podeConcluir()) {
                    return null;
                }

                $atual = DispositivoEstudante::where('estudante_id', $estudante->id)
                    ->where('status', DispositivoEstudante::ATIVO)
                    ->lockForUpdate()
                    ->first();

                // A troca aprovada revoga a credencial antiga no mesmo passo em
                // que ativa a nova: nunca ficam duas ativas.
                $atual?->update([
                    'status' => DispositivoEstudante::REVOGADO,
                    'revogado_em' => now(),
                    'revogado_por' => $solicitacao->analisada_por,
                    'motivo_revogacao' => "Substituído pela solicitação #{$solicitacao->id}",
                ]);

                $dispositivo = DispositivoEstudante::create([
                    'estudante_id' => $estudante->id,
                    'solicitacao_id' => $solicitacao->id,
                    'chave_publica' => base64_encode($chave),
                    'chave_publica_sha256' => $this->assinatura->hashDaChave($chave),
                    'rotulo' => $data['rotulo'] ?? null,
                    'status' => DispositivoEstudante::ATIVO,
                    'aprovado_por' => $solicitacao->analisada_por,
                    'aprovado_em' => $solicitacao->analisada_em,
                ]);

                $solicitacao->update([
                    'status' => SolicitacaoDispositivo::CONCLUIDA,
                    'concluida_em' => now(),
                ]);

                return [$dispositivo, $atual];
            });

            if ($dispositivo === null) {
                return response()->json(['message' => 'Esta solicitação não pode mais ser concluída.'], 409);
            }

            [$novo, $revogado] = $dispositivo;

            $this->auditoria->registrar($estudante->user, 'dispositivo.vincular', $novo, array_filter([
                'solicitacao_id' => $solicitacao->id,
                'tipo' => $solicitacao->tipo,
                'dispositivo_revogado' => $revogado?->id,
            ]));

            return response()->json([
                'data' => new DispositivoResource($novo),
                'message' => 'Dispositivo autorizado. Agora você pode registrar presença por este navegador.',
            ], 201);
        } catch (PresencaNegada $negada) {
            return $this->negar($negada->codigo);
        } catch (UniqueConstraintViolationException) {
            // Mesma chave publica ja usada em outra credencial (desta ou de
            // outra conta). Uma chave nunca serve a duas.
            return response()->json([
                'message' => 'Esta credencial não pode ser vinculada. Gere uma nova chave neste navegador e tente novamente.',
            ], 409);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Erro ao concluir vinculação do dispositivo'], 500);
        }
    }

    private function recusaConclusao(SolicitacaoDispositivo $solicitacao): ?JsonResponse
    {
        if ($solicitacao->status === SolicitacaoDispositivo::PENDENTE) {
            return $this->negar(CodigoPresenca::DEVICE_PENDING_APPROVAL);
        }

        if ($solicitacao->aprovacaoVenceu()) {
            $solicitacao->update(['status' => SolicitacaoDispositivo::EXPIRADA]);

            return response()->json([
                'message' => 'O prazo da aprovação terminou. Faça uma nova solicitação e procure o atendimento.',
            ], 409);
        }

        if (! $solicitacao->podeConcluir()) {
            return response()->json(['message' => 'Esta solicitação não está aprovada.'], 409);
        }

        return null;
    }

    private function daConta(Estudante $estudante, string $id): ?SolicitacaoDispositivo
    {
        return SolicitacaoDispositivo::where('estudante_id', $estudante->id)->find($id);
    }

    private function emAberto(Estudante $estudante): ?SolicitacaoDispositivo
    {
        return SolicitacaoDispositivo::where('estudante_id', $estudante->id)
            ->whereIn('status', SolicitacaoDispositivo::EM_ABERTO)
            ->latest('id')
            ->first();
    }

    private function expirarAprovacoesVencidas(Estudante $estudante): void
    {
        SolicitacaoDispositivo::where('estudante_id', $estudante->id)
            ->where('status', SolicitacaoDispositivo::APROVADA)
            ->where('aprovacao_expira_em', '<=', now())
            ->get()
            ->each(fn (SolicitacaoDispositivo $solicitacao) => $solicitacao->update(['status' => SolicitacaoDispositivo::EXPIRADA]));
    }

    private function negar(CodigoPresenca $codigo): JsonResponse
    {
        return response()->json([
            'message' => $codigo->mensagem(),
            'codigo' => $codigo->value,
        ], $codigo->status());
    }
}
