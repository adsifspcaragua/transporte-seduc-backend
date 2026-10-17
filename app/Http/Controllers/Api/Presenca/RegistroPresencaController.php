<?php

namespace App\Http\Controllers\Api\Presenca;

use App\Http\Controllers\Controller;
use App\Http\Requests\Presenca\ConfirmarPresencaRequest;
use App\Http\Requests\Presenca\PedirRevisaoRequest;
use App\Http\Requests\Presenca\QrTokenRequest;
use App\Services\Presenca\RegistroPresencaService;
use Illuminate\Http\Request;

/**
 * @group Presenca - Estudante
 *
 * O estudante registra a propria presenca lendo o QR fixo da van, sem o
 * motorista. A confirmacao exige a assinatura do dispositivo autorizado, a
 * viagem certa no horario certo (relogio do servidor) e a localizacao dentro
 * da geocerca. As recusas trazem `codigo` (ver CodigoPresenca) e, quando
 * cabe, `pode_pedir_revisao`.
 *
 * @authenticated
 */
class RegistroPresencaController extends Controller
{
    public function __construct(private readonly RegistroPresencaService $service) {}

    /**
     * Identificar viagem pelo QR.
     *
     * Previa para a tela: qual viagem a van esta fazendo agora e se o
     * estudante pode registrar presenca nela. Nao registra nada.
     */
    public function resolverQr(QrTokenRequest $request)
    {
        return $this->service->resolverQr($request->attributes->get('estudante'), $request->validated()['qr_token']);
    }

    /**
     * Emitir desafio de presenca.
     *
     * Desafio de uso unico e curta duracao, amarrado ao estudante, ao
     * dispositivo, a van e a viagem.
     */
    public function desafio(QrTokenRequest $request)
    {
        return $this->service->desafio($request->attributes->get('estudante'), $request->validated()['qr_token']);
    }

    /**
     * Confirmar presenca.
     *
     * Envia a assinatura do desafio e a localizacao atual (ou o erro ao obte-la).
     * Todas as regras sao verificadas de novo aqui.
     */
    public function confirmar(ConfirmarPresencaRequest $request)
    {
        return $this->service->confirmar($request->attributes->get('estudante'), $request->validated());
    }

    /**
     * Proximas viagens.
     *
     * Viagens dos proximos 7 dias nas linhas autorizadas, com a marcacao de
     * quais ja tem presenca.
     */
    public function viagens(Request $request)
    {
        return $this->service->minhasViagens($request->attributes->get('estudante'));
    }

    /**
     * Historico de presencas.
     */
    public function presencas(Request $request)
    {
        $filtros = $request->validate(['per_page' => 'sometimes|integer|in:10,15,20,30']);

        return $this->service->minhasPresencas($request->attributes->get('estudante'), $filtros);
    }

    /**
     * Tentativas negadas.
     *
     * Com `pode_pedir_revisao` nas que admitem revisao.
     */
    public function tentativas(Request $request)
    {
        $filtros = $request->validate(['per_page' => 'sometimes|integer|in:10,15,20,30']);

        return $this->service->minhasTentativas($request->attributes->get('estudante'), $filtros);
    }

    /**
     * Pedir revisao de tentativa negada.
     *
     * So para negativas por GPS, horario ou geocerca, com viagem identificada.
     *
     * @urlParam tentativa integer required ID da tentativa. Example: 1
     */
    public function pedirRevisao(PedirRevisaoRequest $request, string $tentativa)
    {
        return $this->service->pedirRevisao($request->attributes->get('estudante'), $tentativa, $request->validated()['motivo']);
    }
}
