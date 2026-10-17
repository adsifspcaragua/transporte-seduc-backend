<?php

namespace App\Services\Presenca;

use App\Enums\CodigoPresenca;
use App\Exceptions\PresencaNegada;
use App\Http\Resources\Presenca\PresencaResource;
use App\Http\Resources\Presenca\TentativaPresencaResource;
use App\Http\Resources\Viagem\ViagemResource;
use App\Models\DesafioDispositivo;
use App\Models\DispositivoEstudante;
use App\Models\Estudante;
use App\Models\Presenca;
use App\Models\SolicitacaoDispositivo;
use App\Models\TentativaPresenca;
use App\Models\Veiculo;
use App\Models\Viagem;
use App\Services\Dispositivo\DesafioService;
use App\Services\Presenca\Geocerca\Localizacao;
use App\Services\Presenca\Geocerca\PoliticaGeocerca;
use App\Services\Viagem\AutorizacaoEstudante;
use App\Services\Viagem\GeradorViagens;
use App\Services\Viagem\ResolvedorViagem;
use App\Support\HorarioLocal;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * A presenca registrada pelo proprio estudante, sem o motorista.
 *
 * 1. le o QR da van -> resolverQr: identifica a viagem (previa, para a tela);
 * 2. pede o desafio -> desafio: amarrado a estudante, dispositivo, van e viagem;
 * 3. confirma -> confirmar: assinatura + localizacao. Aqui TODAS as regras
 *    sao refeitas no servidor; nada do que veio nas etapas anteriores, nem a
 *    viagem nem o horario do aparelho, e tomado como verdade.
 *
 * Toda negativa vira uma TentativaPresenca com o codigo do motivo.
 */
class RegistroPresencaService
{
    public function __construct(
        private readonly ResolvedorViagem $resolvedor,
        private readonly AutorizacaoEstudante $autorizacao,
        private readonly GeradorViagens $gerador,
        private readonly DesafioService $desafios,
        private readonly PoliticaGeocerca $geocerca,
    ) {}

    public function resolverQr(Estudante $estudante, string $qrToken): JsonResponse
    {
        return $this->executar(TentativaPresenca::RESOLVER_QR, $estudante, function (RastroTentativa $rastro) use ($estudante, $qrToken) {
            $viagem = $this->identificar($estudante, $qrToken, $rastro);

            return response()->json([
                'data' => [
                    'viagem' => new ViagemResource($viagem->load(['linha:id,name', 'veiculo:id,identificacao,placa'])),
                    'localizacao' => $this->exigenciasDeLocalizacao(),
                ],
                'message' => 'Viagem identificada',
            ]);
        });
    }

    public function desafio(Estudante $estudante, string $qrToken): JsonResponse
    {
        return $this->executar(TentativaPresenca::DESAFIO, $estudante, function (RastroTentativa $rastro) use ($estudante, $qrToken) {
            $viagem = $this->identificar($estudante, $qrToken, $rastro);

            [$desafio, $texto] = $this->desafios->emitir($estudante, DesafioDispositivo::PRESENCA, [
                'dispositivo_id' => $rastro->dispositivo->id,
                'viagem_id' => $viagem->id,
                'veiculo_id' => $rastro->veiculo->id,
            ]);

            return response()->json([
                'data' => [
                    'desafio_id' => $desafio->id,
                    'desafio' => $texto,
                    'expira_em' => $desafio->expira_em->toISOString(),
                    'viagem' => new ViagemResource($viagem->load(['linha:id,name', 'veiculo:id,identificacao,placa'])),
                    'localizacao' => $this->exigenciasDeLocalizacao(),
                ],
                'message' => 'Assine o desafio e envie com a localização atual.',
            ], 201);
        });
    }

    /**
     * @param  array{qr_token: string, desafio_id: string, desafio: string, assinatura: string, localizacao?: array{latitude: float, longitude: float, precisao_metros: float}|null, erro_localizacao?: string|null}  $data
     */
    public function confirmar(Estudante $estudante, array $data): JsonResponse
    {
        $agora = now();

        return $this->executar(TentativaPresenca::CONFIRMACAO, $estudante, function (RastroTentativa $rastro) use ($estudante, $data, $agora) {
            $this->verificarEstudante($estudante);
            $rastro->dispositivo = $this->dispositivoAtivo($estudante);
            $rastro->veiculo = $this->veiculo($data['qr_token']);

            $desafio = $this->desafios->consumir(
                $estudante,
                DesafioDispositivo::PRESENCA,
                $data['desafio_id'],
                $data['desafio'],
                $data['assinatura'],
                $rastro->dispositivo->chaveDer(),
            );

            if ($desafio->dispositivo_id !== $rastro->dispositivo->id) {
                throw new PresencaNegada(CodigoPresenca::DEVICE_PROOF_INVALID);
            }

            if ((int) $desafio->veiculo_id !== $rastro->veiculo->id) {
                throw new PresencaNegada(CodigoPresenca::QR_INVALID);
            }

            // A viagem e resolvida de novo, agora, pelo relogio do servidor.
            $viagem = $rastro->viagem = $this->resolvedor->resolver($rastro->veiculo, $estudante, $agora);

            if ($viagem->id !== (int) $desafio->viagem_id) {
                // A janela virou entre o desafio e a confirmacao.
                throw new PresencaNegada(CodigoPresenca::CHALLENGE_EXPIRED, $viagem);
            }

            $this->barrarDuplicada($estudante, $viagem);

            if (filled($data['erro_localizacao'] ?? null)) {
                throw new PresencaNegada($data['erro_localizacao'] === 'permissao_negada'
                    ? CodigoPresenca::LOCATION_PERMISSION_DENIED
                    : CodigoPresenca::LOCATION_UNAVAILABLE, $viagem);
            }

            $localizacao = new Localizacao(
                (float) $data['localizacao']['latitude'],
                (float) $data['localizacao']['longitude'],
                (float) $data['localizacao']['precisao_metros'],
            );
            $rastro->precisaoMetros = (int) round($localizacao->precisaoMetros);

            // Leitura ruim pede outra leitura; nao vira tolerancia maior.
            if ($localizacao->precisaoMetros > (int) config('presenca.geocerca.precisao_maxima_metros')) {
                throw new PresencaNegada(CodigoPresenca::LOCATION_INACCURATE, $viagem);
            }

            $geo = $this->geocerca->avaliar($viagem, $localizacao, $agora);
            $rastro->distanciaMetros = $geo->distanciaMetros;

            if (! $geo->aceita()) {
                throw new PresencaNegada($geo->codigo, $viagem);
            }

            try {
                $presenca = DB::transaction(function () use ($estudante, $viagem, $rastro, $geo, $agora) {
                    // Revogacao vale na hora: a credencial e conferida de novo
                    // com a linha travada, na mesma transacao do registro.
                    $dispositivo = DispositivoEstudante::whereKey($rastro->dispositivo->id)->lockForUpdate()->first();

                    if (! $dispositivo?->estaAtivo()) {
                        throw new PresencaNegada(CodigoPresenca::DEVICE_REVOKED, $viagem);
                    }

                    $presenca = Presenca::create([
                        'estudante_id' => $estudante->id,
                        'viagem_id' => $viagem->id,
                        'veiculo_id' => $rastro->veiculo->id,
                        'dispositivo_id' => $dispositivo->id,
                        'ponto_id' => $geo->pontoId,
                        'metodo' => Presenca::DISPOSITIVO,
                        'registrada_em' => $agora,
                        'politica_geo' => $this->geocerca->nome(),
                        'resultado_geo' => $geo->resultado,
                        'distancia_metros' => $geo->distanciaMetros,
                        'precisao_metros' => $rastro->precisaoMetros,
                    ]);

                    $dispositivo->update(['ultimo_uso_em' => $agora]);

                    return $presenca;
                });
            } catch (UniqueConstraintViolationException) {
                // Duas confirmacoes simultaneas: o indice unico deixou passar uma.
                throw new PresencaNegada(CodigoPresenca::ATTENDANCE_ALREADY_RECORDED, $viagem);
            }

            $this->registrarTentativa($estudante, TentativaPresenca::CONFIRMACAO, $rastro, null, $presenca);

            return response()->json([
                'data' => new PresencaResource($presenca->load(['viagem.linha:id,name', 'veiculo:id,identificacao', 'ponto:id,nome'])),
                'message' => 'Presença registrada',
            ], 201);
        });
    }

    /**
     * Viagens previstas para os proximos dias nas linhas do estudante, com a
     * marcacao de quais ja tem presenca.
     */
    public function minhasViagens(Estudante $estudante): JsonResponse
    {
        $hoje = HorarioLocal::hoje();
        $ate = $hoje->copy()->addDays(6);

        $this->gerador->gerar($hoje, $ate);

        $viagens = Viagem::with(['linha:id,name', 'veiculo:id,identificacao'])
            ->whereIn('linha_id', $this->autorizacao->linhas($estudante, $hoje))
            ->where('status', Viagem::PROGRAMADA)
            ->whereDate('data', '>=', $hoje)
            ->whereDate('data', '<=', $ate)
            ->where('janela_fim', '>=', now())
            ->orderBy('janela_inicio')
            ->get();

        $comPresenca = Presenca::where('estudante_id', $estudante->id)
            ->whereIn('viagem_id', $viagens->pluck('id'))
            ->pluck('viagem_id')
            ->flip();

        return response()->json([
            'data' => $viagens->map(fn (Viagem $viagem) => [
                ...(new ViagemResource($viagem))->resolve(),
                'presenca_registrada' => $comPresenca->has($viagem->id),
            ])->values(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    public function minhasPresencas(Estudante $estudante, array $filtros): AnonymousResourceCollection
    {
        $presencas = Presenca::with(['viagem.linha:id,name', 'veiculo:id,identificacao', 'ponto:id,nome'])
            ->where('estudante_id', $estudante->id)
            ->orderByDesc('registrada_em')
            ->paginate($filtros['per_page'] ?? 15);

        return PresencaResource::collection($presencas);
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    public function minhasTentativas(Estudante $estudante, array $filtros): AnonymousResourceCollection
    {
        $tentativas = TentativaPresenca::with(['viagem.linha:id,name', 'veiculo:id,identificacao'])
            ->where('estudante_id', $estudante->id)
            ->where('resultado', TentativaPresenca::NEGADA)
            ->orderByDesc('recebida_em')
            ->paginate($filtros['per_page'] ?? 15);

        return TentativaPresencaResource::collection($tentativas);
    }

    /**
     * Pedido de revisao de uma negativa (GPS, horario, geocerca). Nao aprova
     * nada: entra na fila da gestao, que decide com justificativa.
     */
    public function pedirRevisao(Estudante $estudante, string $tentativaId, string $motivo): JsonResponse
    {
        $tentativa = TentativaPresenca::where('estudante_id', $estudante->id)->find($tentativaId);

        if (! $tentativa) {
            return response()->json(['message' => 'Tentativa não encontrada'], 404);
        }

        if (! $tentativa->admiteRevisao()) {
            return response()->json(['message' => 'Esta tentativa não admite pedido de revisão.'], 422);
        }

        if (Presenca::where('estudante_id', $estudante->id)->where('viagem_id', $tentativa->viagem_id)->exists()) {
            return response()->json([
                'message' => CodigoPresenca::ATTENDANCE_ALREADY_RECORDED->mensagem(),
                'codigo' => CodigoPresenca::ATTENDANCE_ALREADY_RECORDED->value,
            ], 409);
        }

        $jaPedida = TentativaPresenca::where('estudante_id', $estudante->id)
            ->where('viagem_id', $tentativa->viagem_id)
            ->whereIn('revisao_status', [TentativaPresenca::REVISAO_PENDENTE, TentativaPresenca::REVISAO_APROVADA])
            ->exists();

        if ($jaPedida) {
            return response()->json(['message' => 'Já existe um pedido de revisão para esta viagem.'], 409);
        }

        $tentativa->update([
            'revisao_status' => TentativaPresenca::REVISAO_PENDENTE,
            'revisao_motivo' => $motivo,
            'revisao_solicitada_em' => now(),
        ]);

        return response()->json([
            'data' => new TentativaPresencaResource($tentativa->load(['viagem.linha:id,name', 'veiculo:id,identificacao'])),
            'message' => 'Pedido de revisão enviado',
        ], 201);
    }

    /**
     * Etapas comuns a leitura do QR e ao desafio. A confirmacao repete estas
     * mesmas verificacoes por conta propria.
     */
    private function identificar(Estudante $estudante, string $qrToken, RastroTentativa $rastro): Viagem
    {
        $this->verificarEstudante($estudante);
        $rastro->dispositivo = $this->dispositivoAtivo($estudante);
        $rastro->veiculo = $this->veiculo($qrToken);

        $viagem = $rastro->viagem = $this->resolvedor->resolver($rastro->veiculo, $estudante, now());

        $this->barrarDuplicada($estudante, $viagem);

        return $viagem;
    }

    private function verificarEstudante(Estudante $estudante): void
    {
        if (! $this->autorizacao->habilitado($estudante)) {
            throw new PresencaNegada(CodigoPresenca::STUDENT_NOT_ELIGIBLE);
        }
    }

    /**
     * Sem credencial ativa, a senha sozinha nao registra presenca. O codigo
     * diz ao estudante em que pe esta a autorizacao.
     */
    private function dispositivoAtivo(Estudante $estudante): DispositivoEstudante
    {
        $ativo = $estudante->dispositivoAtivo()->first();

        if ($ativo) {
            return $ativo;
        }

        $emAberto = SolicitacaoDispositivo::where('estudante_id', $estudante->id)
            ->whereIn('status', SolicitacaoDispositivo::EM_ABERTO)
            ->exists();

        if ($emAberto) {
            throw new PresencaNegada(CodigoPresenca::DEVICE_PENDING_APPROVAL);
        }

        $revogado = DispositivoEstudante::where('estudante_id', $estudante->id)
            ->where('status', DispositivoEstudante::REVOGADO)
            ->exists();

        throw new PresencaNegada($revogado ? CodigoPresenca::DEVICE_REVOKED : CodigoPresenca::DEVICE_NOT_ENROLLED);
    }

    private function veiculo(string $qrToken): Veiculo
    {
        $veiculo = Veiculo::peloToken($qrToken);

        if (! $veiculo || ! $veiculo->ativo) {
            throw new PresencaNegada(CodigoPresenca::QR_INVALID);
        }

        return $veiculo;
    }

    private function barrarDuplicada(Estudante $estudante, Viagem $viagem): void
    {
        if (Presenca::where('estudante_id', $estudante->id)->where('viagem_id', $viagem->id)->exists()) {
            throw new PresencaNegada(CodigoPresenca::ATTENDANCE_ALREADY_RECORDED, $viagem);
        }
    }

    /**
     * @return array{obrigatoria: bool, precisao_maxima_metros: int}
     */
    private function exigenciasDeLocalizacao(): array
    {
        return [
            'obrigatoria' => true,
            'precisao_maxima_metros' => (int) config('presenca.geocerca.precisao_maxima_metros'),
        ];
    }

    /**
     * @param  callable(RastroTentativa): JsonResponse  $passos
     */
    private function executar(string $etapa, Estudante $estudante, callable $passos): JsonResponse
    {
        $rastro = new RastroTentativa;

        try {
            return $passos($rastro);
        } catch (PresencaNegada $negada) {
            $tentativa = $this->registrarTentativa($estudante, $etapa, $rastro, $negada);

            return response()->json([
                'message' => $negada->codigo->mensagem(),
                'codigo' => $negada->codigo->value,
                'tentativa_id' => $tentativa->id,
                'pode_pedir_revisao' => $tentativa->admiteRevisao(),
            ], $negada->codigo->status());
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Erro ao processar a presença. Tente novamente.'], 500);
        }
    }

    private function registrarTentativa(
        Estudante $estudante,
        string $etapa,
        RastroTentativa $rastro,
        ?PresencaNegada $negada,
        ?Presenca $presenca = null,
    ): TentativaPresenca {
        return TentativaPresenca::create([
            'estudante_id' => $estudante->id,
            'viagem_id' => ($negada?->viagem ?? $rastro->viagem)?->id,
            'veiculo_id' => $rastro->veiculo?->id,
            'dispositivo_id' => $rastro->dispositivo?->id,
            'presenca_id' => $presenca?->id,
            'etapa' => $etapa,
            'resultado' => $negada ? TentativaPresenca::NEGADA : TentativaPresenca::REGISTRADA,
            'codigo' => $negada?->codigo,
            'distancia_metros' => $rastro->distanciaMetros,
            'precisao_metros' => $rastro->precisaoMetros,
            'recebida_em' => now(),
        ]);
    }
}
