<?php

namespace App\Http\Controllers\Api\Instituicao;

use App\Http\Controllers\Controller;
use App\Http\Requests\Instituicao\StoreInstituicaoRequest;
use App\Http\Requests\Instituicao\UpdateInstituicaoRequest;
use App\Services\Instituicao\InstituicaoService;

/**
 * @group Instituicoes
 *
 * Rotas para gerenciamento das instituicoes de ensino.
 *
 * @authenticated
 */
class InstituicaoController extends Controller
{
    public function __construct(private readonly InstituicaoService $instituicaoService) {}

    /**
     * Listar instituicoes.
     *
     * Retorna a lista paginada de instituicoes cadastradas.
     */
    public function index()
    {
        return $this->instituicaoService->index();
    }

    /**
     * Cadastrar instituicao.
     *
     * Cria uma nova instituicao de ensino.
     */
    public function store(StoreInstituicaoRequest $request)
    {
        return $this->instituicaoService->store($request->validated());
    }

    /**
     * Exibir instituicao.
     *
     * Retorna os dados de uma instituicao especifica.
     *
     * @urlParam instituicao integer required ID da instituicao. Example: 1
     */
    public function show(string $id)
    {
        return $this->instituicaoService->show($id);
    }

    /**
     * Atualizar instituicao.
     *
     * Atualiza os dados de uma instituicao existente.
     *
     * @urlParam instituicao integer required ID da instituicao. Example: 1
     */
    public function update(UpdateInstituicaoRequest $request, string $id)
    {
        return $this->instituicaoService->update($request->validated(), $id);
    }

    /**
     * Remover instituicao.
     *
     * Remove uma instituicao cadastrada.
     *
     * @urlParam instituicao integer required ID da instituicao. Example: 1
     */
    public function destroy(string $id)
    {
        return $this->instituicaoService->destroy($id);
    }
}
