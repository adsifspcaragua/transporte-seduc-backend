<?php

namespace App\Http\Controllers\Api\Reecadastro;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reecadastro\Solicitacao\AnaliseSolicitacaoRequest;
use App\Services\Reecadastro\SolicitacaoReecadastroService;
use Illuminate\Http\Request;

/**
 * @group Recadastro
 *
 * Homologacao das solicitacoes de recadastro enviadas pelos estudantes.
 *
 * @authenticated
 */
class SolicitacaoReecadastroController extends Controller
{
    public function __construct(private readonly SolicitacaoReecadastroService $solicitacaoReecadastroService) {}

    /**
     * Listar solicitacoes de recadastro.
     *
     * Retorna as solicitacoes paginadas, com filtros de periodo, situacao e busca.
     *
     * @queryParam periodo_id integer Filtra por periodo de recadastro. Example: 1
     * @queryParam status string Filtra pela situacao (Pendente, Em analise, Pendencia, Aprovado, Rejeitado). Example: Em analise
     * @queryParam busca string Busca por nome ou CPF do estudante. Example: Maria
     */
    public function index(Request $request)
    {
        return $this->solicitacaoReecadastroService->index(
            $request->only(['periodo_id', 'status', 'busca']),
        );
    }

    /**
     * Exibir solicitacao de recadastro.
     *
     * Retorna a solicitacao com o estudante, o periodo e os documentos enviados.
     *
     * @urlParam solicitacao integer required ID da solicitacao. Example: 1
     */
    public function show(string $id)
    {
        return $this->solicitacaoReecadastroService->show($id);
    }

    /**
     * Analisar solicitacao de recadastro.
     *
     * Aprova, rejeita ou devolve documentos para reenvio.
     *
     * @urlParam solicitacao integer required ID da solicitacao. Example: 1
     */
    public function analise(AnaliseSolicitacaoRequest $request, string $id)
    {
        return $this->solicitacaoReecadastroService->analise($request->validated(), $id);
    }

    /**
     * Remover solicitacao de recadastro.
     *
     * Remove a solicitacao e os arquivos enviados nela.
     *
     * @urlParam solicitacao integer required ID da solicitacao. Example: 1
     */
    public function destroy(string $id)
    {
        return $this->solicitacaoReecadastroService->destroy($id);
    }
}
