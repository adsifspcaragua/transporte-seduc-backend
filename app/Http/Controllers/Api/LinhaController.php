<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Linha\StoreLinhaRequest;
use App\Http\Requests\Linha\UpdateLinhaRequest;
use App\Services\Linha\LinhaService;

/**
 * @group Linhas
 *
 * Rotas para gerenciamento das linhas de transporte.
 *
 * @authenticated
 */
class LinhaController extends Controller
{
    public function __construct(private readonly LinhaService $linhaService) {}

    /**
     * Listar linhas.
     *
     * Retorna todas as linhas cadastradas.
     */
    public function index()
    {
        return $this->linhaService->index();
    }

    /**
     * Cadastrar linha.
     *
     * Cria uma nova linha de transporte.
     */
    public function store(StoreLinhaRequest $request)
    {
        return $this->linhaService->store($request->validated());
    }

    /**
     * Exibir linha.
     *
     * Retorna os dados de uma linha especifica.
     *
     * @urlParam linha integer required ID da linha. Example: 1
     */
    public function show(string $id)
    {
        return $this->linhaService->show($id);
    }

    /**
     * Atualizar linha.
     *
     * Atualiza os dados de uma linha existente.
     *
     * @urlParam linha integer required ID da linha. Example: 1
     */
    public function update(UpdateLinhaRequest $request, string $id)
    {
        return $this->linhaService->update($request->validated(), $id);
    }

    /**
     * Remover linha.
     *
     * Remove uma linha cadastrada.
     *
     * @urlParam linha integer required ID da linha. Example: 1
     */
    public function destroy(string $id)
    {
        return $this->linhaService->destroy($id);
    }
}
