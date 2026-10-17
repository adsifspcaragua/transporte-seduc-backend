<?php

namespace App\Http\Controllers\Api\Dispositivo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dispositivo\ConcluirVinculacaoRequest;
use App\Http\Requests\Dispositivo\SolicitarDispositivoRequest;
use App\Services\Dispositivo\VinculacaoDispositivoService;
use Illuminate\Http\Request;

/**
 * @group Presenca - Meu dispositivo
 *
 * Autorizacao do navegador do estudante para registrar presenca. O navegador
 * gera um par de chaves ECDSA P-256 (Web Crypto), guarda a privada como nao
 * exportavel no IndexedDB e envia so a publica. A vinculacao exige aprovacao
 * do atendimento, que confere a identidade: a senha sozinha nao basta.
 *
 * Fluxo: solicitar -> (atendimento aprova) -> desafio -> concluir.
 *
 * @authenticated
 */
class MeuDispositivoController extends Controller
{
    public function __construct(private readonly VinculacaoDispositivoService $service) {}

    /**
     * Situacao do dispositivo.
     *
     * sem_dispositivo, aguardando_aprovacao, aguardando_conclusao ou ativo. Se
     * vier "ativo" e este navegador nao tiver a chave no IndexedDB, ele nao e o
     * autorizado: o caminho e pedir troca.
     */
    public function show(Request $request)
    {
        return $this->service->situacao($request->attributes->get('estudante'));
    }

    /**
     * Solicitar vinculacao ou troca.
     *
     * Sem dispositivo ativo, e a primeira vinculacao; com dispositivo ativo, e
     * troca (motivo obrigatorio). Depois, o estudante procura o atendimento.
     */
    public function solicitar(SolicitarDispositivoRequest $request)
    {
        return $this->service->solicitar($request->attributes->get('estudante'), $request->validated()['motivo'] ?? null);
    }

    /**
     * Cancelar solicitacao.
     *
     * @urlParam solicitacao integer required ID da solicitacao. Example: 1
     */
    public function cancelar(Request $request, string $solicitacao)
    {
        return $this->service->cancelar($request->attributes->get('estudante'), $solicitacao);
    }

    /**
     * Emitir desafio de vinculacao.
     *
     * So com a solicitacao aprovada e dentro do prazo da aprovacao. O texto
     * devolvido em `desafio` deve ser assinado exatamente como veio.
     *
     * @urlParam solicitacao integer required ID da solicitacao. Example: 1
     */
    public function desafio(Request $request, string $solicitacao)
    {
        return $this->service->desafio($request->attributes->get('estudante'), $solicitacao);
    }

    /**
     * Concluir vinculacao.
     *
     * Registra a chave publica do navegador depois de conferir a assinatura do
     * desafio. Na troca, a credencial anterior e revogada no mesmo passo.
     *
     * @urlParam solicitacao integer required ID da solicitacao. Example: 1
     */
    public function concluir(ConcluirVinculacaoRequest $request, string $solicitacao)
    {
        return $this->service->concluir($request->attributes->get('estudante'), $solicitacao, $request->validated());
    }
}
