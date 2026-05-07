<?php

namespace App\Http\Controllers\Api\Estudante;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inscricao\Instituicao\StoreInscricaoInstituicaoRequest;
use App\Http\Requests\Inscricao\Instituicao\UpdateInscricaoInstituicaoRequest;
use App\Services\Inscricao\Instituicao\InscricaoInstituicaoService;

/**
 * @group Instituicoes da inscricao
 *
 * Rotas para gerenciar os dados academicos vinculados a uma inscricao.
 *
 * @authenticated
 */
class InscricaoInstituicaoController extends Controller
{
    public function __construct(private readonly InscricaoInstituicaoService $inscricaoInstituicaoService) {}

    /**
     * Listar instituicoes da inscricao.
     *
     * Retorna os dados academicos vinculados a uma inscricao.
     *
     * @urlParam inscricao_id integer required ID da inscricao. Example: 1
     */
    public function index(string $inscricao_id)
    {
        return $this->inscricaoInstituicaoService->index($inscricao_id);
    }

    /**
     * Cadastrar instituicao na inscricao.
     *
     * Registra os dados academicos de uma inscricao e recalcula seu status.
     *
     * @urlParam inscricao_id integer required ID da inscricao. Example: 1
     */
    public function store(StoreInscricaoInstituicaoRequest $request, string $inscricao_id)
    {
        return $this->inscricaoInstituicaoService->store($request->validated());
    }

    /**
     * Exibir instituicao da inscricao.
     *
     * Retorna os dados academicos especificos de uma inscricao.
     *
     * @urlParam inscricao_id integer required ID da inscricao. Example: 1
     * @urlParam instituicao integer required ID do vinculo academico. Example: 1
     */
    public function show(string $inscricao_id, string $instituicao)
    {
        return $this->inscricaoInstituicaoService->show($inscricao_id, $instituicao);
    }

    /**
     * Atualizar instituicao da inscricao.
     *
     * Atualiza os dados academicos vinculados a inscricao e recalcula o status.
     *
     * @urlParam inscricao_id integer required ID da inscricao. Example: 1
     * @urlParam instituicao integer required ID do vinculo academico. Example: 1
     */
    public function update(UpdateInscricaoInstituicaoRequest $request, string $inscricao_id, string $instituicao)
    {
        return $this->inscricaoInstituicaoService->update($request->validated(), $inscricao_id, $instituicao);
    }

    /**
     * Remover instituicao da inscricao.
     *
     * Remove os dados academicos vinculados a uma inscricao.
     *
     * @urlParam inscricao_id integer required ID da inscricao. Example: 1
     * @urlParam instituicao integer required ID do vinculo academico. Example: 1
     */
    public function destroy(string $inscricao_id, string $instituicao)
    {
        return $this->inscricaoInstituicaoService->destroy($inscricao_id, $instituicao);
    }
}
