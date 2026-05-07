<?php

namespace App\Http\Controllers\Api\Estudante;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inscricao\StoreInscricaoRequest;
use App\Http\Requests\Inscricao\UpdateInscricaoRequest;
use App\Services\Inscricao\InscricaoService;

/**
 * @group Inscricoes
 *
 * Rotas para criar, acompanhar e atualizar inscricoes de estudantes.
 *
 * @authenticated
 */
class InscricaoController extends Controller
{
    public function __construct(private readonly InscricaoService $inscricaoService) {}

    /**
     * Listar inscricoes.
     *
     * Retorna todas as inscricoes cadastradas.
     */
    public function index()
    {
        return $this->inscricaoService->index();
    }

    /**
     * Criar inscricao.
     *
     * Cria uma nova inscricao e recalcula o status conforme os dados enviados.
     */
    public function store(StoreInscricaoRequest $request)
    {
        return $this->inscricaoService->store($request->validated());
    }

    /**
     * Exibir inscricao.
     *
     * Retorna os dados de uma inscricao especifica.
     *
     * @urlParam inscricao integer required ID da inscricao. Example: 1
     */
    public function show(string $id)
    {
        return $this->inscricaoService->show($id);
    }

    /**
     * Atualizar inscricao.
     *
     * Atualiza uma inscricao existente quando ela ainda nao esta em analise.
     *
     * @urlParam inscricao integer required ID da inscricao. Example: 1
     */
    public function update(UpdateInscricaoRequest $request, $id)
    {
        return $this->inscricaoService->update($request->validated(), $id);
    }

    /**
     * Remover inscricao.
     *
     * Remove uma inscricao cadastrada.
     *
     * @urlParam inscricao integer required ID da inscricao. Example: 1
     */
    public function destroy(string $id)
    {
        return $this->inscricaoService->destroy($id);
    }

    /**
     * Ativar recadastro.
     *
     * Redefine o status das inscricoes para incompleto.
     */
    public function recadastro()
    {
        return $this->inscricaoService->recadastro();
    }
}
