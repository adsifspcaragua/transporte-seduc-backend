<?php

namespace App\Services\Presenca;

use App\Enums\CodigoPresenca;
use App\Exceptions\PresencaNegada;
use App\Http\Resources\Presenca\TentativaPresencaResource;
use App\Models\DispositivoEstudante;
use App\Models\Estudante;
use App\Models\Presenca;
use App\Models\TentativaPresenca;
use App\Models\Veiculo;
use App\Models\Viagem;
use App\Services\Dispositivo\AssinaturaEcdsa;
use App\Services\Presenca\Geocerca\PoliticaGeocerca;
use App\Services\Viagem\AutorizacaoEstudante;
use App\Services\Viagem\ResolvedorViagem;
use App\Support\HorarioLocal;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Throwable;

/**
 * Recebe o comprovante que o estudante gravou sem conexao (ver
 * ComprovanteOffline) e o coloca na fila de revisao da gestao.
 *
 * Refaz as verificacoes da presenca online usando o horario declarado pelo
 * celular, e o que nao passar vira alerta (`codigo`) para quem vai decidir.
 * So a assinatura, o dispositivo, o prazo, a van e a viagem impedem o envio:
 * sem eles nao ha o que revisar. Nunca registra presenca por conta propria.
 */
class PresencaOfflineService
{
    public function __construct(
        private readonly AssinaturaEcdsa $assinatura,
        private readonly AutorizacaoEstudante $autorizacao,
        private readonly ResolvedorViagem $resolvedor,
        private readonly PoliticaGeocerca $geocerca,
    ) {}

    public function receber(Estudante $estudante, string $texto, string $assinatura): JsonResponse
    {
        $comprovante = ComprovanteOffline::ler($texto);

        if (! $comprovante) {
            $mensagem = 'Comprovante em formato inválido. Atualize o aplicativo e registre de novo.';

            return response()->json(['message' => $mensagem, 'errors' => ['comprovante' => [$mensagem]]], 422);
        }

        try {
            if (! $this->autorizacao->habilitado($estudante)) {
                return $this->negar(CodigoPresenca::STUDENT_NOT_ELIGIBLE);
            }

            $dispositivo = $estudante->dispositivoAtivo()->first();

            if (! $dispositivo || $dispositivo->id !== $comprovante->dispositivoId) {
                // Trocou ou perdeu a autorizacao depois de gravar o comprovante.
                $foiDele = DispositivoEstudante::where('estudante_id', $estudante->id)->whereKey($comprovante->dispositivoId)->exists();

                return $this->negar($foiDele ? CodigoPresenca::DEVICE_REVOKED : CodigoPresenca::DEVICE_PROOF_INVALID);
            }

            if (! $this->assinatura->verificar($dispositivo->chaveDer(), $texto, $assinatura)) {
                return $this->negar(CodigoPresenca::DEVICE_PROOF_INVALID);
            }

            // Reenvio do mesmo comprovante (a conexao caiu na resposta): devolve
            // o que ja foi registrado, sem duplicar.
            $existente = TentativaPresenca::where('estudante_id', $estudante->id)
                ->where('comprovante_id', $comprovante->id)
                ->first();

            if ($existente) {
                return $this->resposta($existente, 'Este registro já tinha sido recebido.', 200);
            }

            return $this->avaliar($estudante, $dispositivo, $comprovante);
        } catch (UniqueConstraintViolationException) {
            $existente = TentativaPresenca::where('estudante_id', $estudante->id)->where('comprovante_id', $comprovante->id)->firstOrFail();

            return $this->resposta($existente, 'Este registro já tinha sido recebido.', 200);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Erro ao receber o registro feito sem conexão. Tente novamente.'], 500);
        }
    }

    private function avaliar(Estudante $estudante, DispositivoEstudante $dispositivo, ComprovanteOffline $comprovante): JsonResponse
    {
        $agora = now();
        $registro = [
            'estudante_id' => $estudante->id,
            'dispositivo_id' => $dispositivo->id,
            'comprovante_id' => $comprovante->id,
            'capturado_em' => $comprovante->capturadoEm,
            'etapa' => TentativaPresenca::OFFLINE,
            'recebida_em' => $agora,
            'precisao_metros' => $comprovante->localizacao ? (int) round($comprovante->localizacao->precisaoMetros) : null,
        ];

        // O relogio do celular nao e confiavel, mas tem limites: nao pode estar
        // no futuro nem tao no passado que a viagem ja nao possa ser conferida.
        if ($comprovante->capturadoEm->gt($agora->copy()->addMinutes((int) config('presenca.offline.tolerancia_relogio_minutos')))) {
            return $this->recusar($registro, CodigoPresenca::OFFLINE_CLOCK_INVALID);
        }

        if ($comprovante->capturadoEm->lt($agora->copy()->subHours((int) config('presenca.offline.prazo_horas')))) {
            return $this->recusar($registro, CodigoPresenca::OFFLINE_PROOF_EXPIRED);
        }

        $veiculo = Veiculo::peloToken($comprovante->qrToken);

        if (! $veiculo || ! $veiculo->ativo) {
            return $this->recusar($registro, CodigoPresenca::QR_INVALID);
        }

        $registro['veiculo_id'] = $veiculo->id;
        $alerta = null;

        try {
            $viagem = $this->resolvedor->resolver($veiculo, $estudante, $comprovante->capturadoEm);
        } catch (PresencaNegada $negada) {
            // Fora do horario ainda aponta a viagem mais proxima: a gestao decide
            // se foi atraso. Sem viagem nenhuma, nao ha o que revisar.
            if (! $negada->viagem) {
                return $this->recusar($registro, $negada->codigo);
            }

            $viagem = $negada->viagem;
            $alerta = $negada->codigo;
        }

        $registro['viagem_id'] = $viagem->id;

        if (Presenca::where('estudante_id', $estudante->id)->where('viagem_id', $viagem->id)->exists()) {
            return $this->negar(CodigoPresenca::ATTENDANCE_ALREADY_RECORDED);
        }

        $emRevisao = TentativaPresenca::where('estudante_id', $estudante->id)
            ->where('viagem_id', $viagem->id)
            ->where('revisao_status', TentativaPresenca::REVISAO_PENDENTE)
            ->exists();

        if ($emRevisao) {
            return response()->json(['message' => 'Já há um registro desta viagem aguardando a confirmação da gestão.'], 409);
        }

        [$alertaLocal, $distancia] = $this->conferirLocal($viagem, $comprovante);
        $registro['distancia_metros'] = $distancia;

        $tentativa = TentativaPresenca::create([
            ...$registro,
            'resultado' => TentativaPresenca::EM_REVISAO,
            'codigo' => $alerta ?? $alertaLocal,
            'revisao_status' => TentativaPresenca::REVISAO_PENDENTE,
            'revisao_motivo' => 'Registro feito sem conexão em '.HorarioLocal::exibir($comprovante->capturadoEm)
                .' (horário do celular) e enviado em '.HorarioLocal::exibir($agora).'.',
            'revisao_solicitada_em' => $agora,
        ]);

        return $this->resposta($tentativa, 'Registro recebido. A gestão vai conferir e confirmar a sua presença.', 202);
    }

    /**
     * A mesma conferencia da presenca online, mas so como alerta.
     *
     * @return array{0: CodigoPresenca|null, 1: int|null}
     */
    private function conferirLocal(Viagem $viagem, ComprovanteOffline $comprovante): array
    {
        if (! $comprovante->localizacao) {
            return [$comprovante->erroLocalizacao === 'permissao_negada'
                ? CodigoPresenca::LOCATION_PERMISSION_DENIED
                : CodigoPresenca::LOCATION_UNAVAILABLE, null];
        }

        $geo = $this->geocerca->avaliar($viagem, $comprovante->localizacao, $comprovante->capturadoEm);

        if ($comprovante->localizacao->precisaoMetros > (int) config('presenca.geocerca.precisao_maxima_metros')) {
            return [CodigoPresenca::LOCATION_INACCURATE, $geo->distanciaMetros];
        }

        return [$geo->aceita() ? null : $geo->codigo, $geo->distanciaMetros];
    }

    /**
     * @param  array<string, mixed>  $registro
     */
    private function recusar(array $registro, CodigoPresenca $codigo): JsonResponse
    {
        TentativaPresenca::create([...$registro, 'resultado' => TentativaPresenca::NEGADA, 'codigo' => $codigo]);

        return $this->negar($codigo);
    }

    private function negar(CodigoPresenca $codigo): JsonResponse
    {
        return response()->json([
            'message' => $codigo->mensagem(),
            'codigo' => $codigo->value,
        ], $codigo->status());
    }

    private function resposta(TentativaPresenca $tentativa, string $mensagem, int $status): JsonResponse
    {
        return response()->json([
            'data' => new TentativaPresencaResource($tentativa->load(['viagem.linha:id,name', 'veiculo:id,identificacao'])),
            'message' => $mensagem,
        ], $status);
    }
}
