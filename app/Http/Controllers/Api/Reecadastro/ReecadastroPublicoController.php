<?php

namespace App\Http\Controllers\Api\Reecadastro;

use App\Http\Controllers\Controller;
use App\Http\Requests\Estudante\VerificarCodigoRequest;
use App\Http\Requests\Reecadastro\Publico\AtualizarDadosRequest;
use App\Http\Requests\Reecadastro\Publico\ConsultaCpfRequest;
use App\Http\Requests\Reecadastro\Publico\EnviarDocumentoRequest;
use App\Http\Requests\Reecadastro\Publico\FinalizarReecadastroRequest;
use App\Services\Reecadastro\ReecadastroPublicoService;

/**
 * @group Recadastro (estudante)
 *
 * Rotas publicas usadas pelo estudante para recadastrar-se sem login: o CPF
 * dispara um codigo por e-mail, e a consulta com o codigo devolve um token de
 * curta duracao exigido nas rotas seguintes.
 */
class ReecadastroPublicoController extends Controller
{
    public function __construct(private readonly ReecadastroPublicoService $reecadastroPublicoService) {}

    /**
     * Informar o CPF.
     *
     * Confere se o periodo esta aberto e se o CPF esta no sistema, e envia o
     * codigo ao e-mail do cadastro. Nao devolve nenhum dado do estudante.
     */
    public function solicitarCodigo(ConsultaCpfRequest $request)
    {
        return $this->reecadastroPublicoService->solicitarCodigo($request->validated()['cpf']);
    }

    /**
     * Consultar recadastro com o codigo.
     *
     * Informa a situacao do estudante e quais documentos ainda faltam. Abre a
     * solicitacao do periodo quando necessario e devolve o token de sessao.
     */
    public function consulta(VerificarCodigoRequest $request)
    {
        $data = $request->validated();

        return $this->reecadastroPublicoService->consulta($data['cpf'], $data['codigo']);
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

    public function dados(AtualizarDadosRequest $request, string $id)
    {
        return $this->reecadastroPublicoService->atualizarDados($request->validated(), $id);
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
