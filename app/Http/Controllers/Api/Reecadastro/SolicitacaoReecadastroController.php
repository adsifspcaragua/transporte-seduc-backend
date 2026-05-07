<?php

namespace App\Http\Controllers\Api\Reecadastro;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reecadastro\Solicitacao\StoreSolicitacaoRequest;
use App\Http\Requests\Reecadastro\Solicitacao\UpdateSolicitacaoRequest;
use App\Services\Reecadastro\SolicitacaoReecadastroService;

/**
 * @group Recadastro
 *
 * Rotas para documentos e solicitacoes de recadastro dos estudantes.
 *
 * @authenticated
 */
class SolicitacaoReecadastroController extends Controller
{
    public function __construct(private readonly SolicitacaoReecadastroService $solicitacaoReecadastroService) {}

    /**
     * Listar solicitacoes de recadastro.
     *
     * Retorna todas as solicitacoes de recadastro cadastradas.
     */
    public function index()
    {
        return $this->solicitacaoReecadastroService->index();
    }

    /**
     * Cadastrar solicitacao de recadastro.
     *
     * Cria uma solicitacao de recadastro para um estudante.
     */
    public function store(StoreSolicitacaoRequest $request)
    {
        return $this->solicitacaoReecadastroService->store($request->validated());
    }

    /**
     * Exibir solicitacao de recadastro.
     *
     * Retorna uma solicitacao de recadastro especifica.
     *
     * @urlParam solicitacao integer required ID da solicitacao. Example: 1
     */
    public function show(string $id)
    {
        return $this->solicitacaoReecadastroService->show($id);
    }

    /**
     * Atualizar solicitacao de recadastro.
     *
     * Atualiza os dados de uma solicitacao de recadastro.
     *
     * @urlParam solicitacao integer required ID da solicitacao. Example: 1
     */
    public function update(UpdateSolicitacaoRequest $request, string $id)
    {
        return $this->solicitacaoReecadastroService->update($request->validated(), $id);
    }

    /**
     * Remover solicitacao de recadastro.
     *
     * Remove uma solicitacao de recadastro.
     *
     * @urlParam solicitacao integer required ID da solicitacao. Example: 1
     */
    public function destroy(string $id)
    {
        return $this->solicitacaoReecadastroService->destroy($id);
    }
}
