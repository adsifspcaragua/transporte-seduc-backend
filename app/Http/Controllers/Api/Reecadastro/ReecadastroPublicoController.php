<?php

namespace App\Http\Controllers\Api\Reecadastro;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reecadastro\Publico\ConsultaCpfRequest;
use App\Http\Requests\Reecadastro\Publico\EnviarDocumentoRequest;
use App\Http\Requests\Reecadastro\Publico\FinalizarReecadastroRequest;
use App\Services\Reecadastro\ReecadastroPublicoService;

/**
 * @group Recadastro (estudante)
 *
 * Rotas publicas usadas pelo estudante para recadastrar-se pelo CPF, sem login.
 * A consulta devolve um token de curta duracao exigido nas rotas seguintes.
 */
class ReecadastroPublicoController extends Controller
{
    public function __construct(private readonly ReecadastroPublicoService $reecadastroPublicoService) {}

    /**
     * Consultar recadastro pelo CPF.
     *
     * Informa se o estudante esta no sistema, se o periodo esta aberto e quais
     * documentos ainda faltam. Abre a solicitacao do periodo quando necessario.
     */
    public function consulta(ConsultaCpfRequest $request)
    {
        return $this->reecadastroPublicoService->consulta($request->validated()['cpf']);
    }

    /**
     * Enviar documento do recadastro.
     *
     * Envia ou reenvia um dos documentos exigidos no periodo corrente.
     *
     * @urlParam solicitacao integer required ID da solicitacao. Example: 1
     */
    public function documento(EnviarDocumentoRequest $request, string $id)
    {
        return $this->reecadastroPublicoService->enviarDocumento($request, $id);
    }

    /**
     * Finalizar o recadastro.
     *
     * Confere os documentos exigidos, registra os pedidos de prazo adicional e
     * coloca a solicitacao em analise.
     *
     * @urlParam solicitacao integer required ID da solicitacao. Example: 1
     */
    public function finalizar(FinalizarReecadastroRequest $request, string $id)
    {
        return $this->reecadastroPublicoService->finalizar($request->validated(), $id);
    }
}
