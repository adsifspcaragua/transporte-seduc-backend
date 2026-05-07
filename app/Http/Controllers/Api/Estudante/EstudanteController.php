<?php

namespace App\Http\Controllers\Api\Estudante;

use App\Http\Controllers\Controller;
use App\Http\Requests\Estudante\StoreEstudanteRequest;
use App\Http\Requests\Estudante\UpdateEstudanteRequest;
use App\Services\Estudante\EstudanteService;

/**
 * @group Estudantes
 *
 * Rotas para cadastrar, consultar, atualizar, remover e contar estudantes.
 *
 * @authenticated
 */
class EstudanteController extends Controller
{
    public function __construct(private readonly EstudanteService $estudanteService) {}

    /**
     * Listar estudantes.
     *
     * Retorna a lista paginada de estudantes cadastrados.
     */
    public function index()
    {
        return $this->estudanteService->index();
    }

    /**
     * Cadastrar estudante.
     *
     * Cria um estudante e define o status inicial como "Em espera".
     */
    public function store(StoreEstudanteRequest $request)
    {
        return $this->estudanteService->store($request->validated());
    }

    /**
     * Exibir estudante.
     *
     * Retorna os dados de um estudante especifico.
     *
     * @urlParam estudante integer required ID do estudante. Example: 1
     */
    public function show(string $id)
    {
        return $this->estudanteService->show($id);
    }

    /**
     * Atualizar estudante.
     *
     * Atualiza os dados de um estudante existente.
     *
     * @urlParam estudante integer required ID do estudante. Example: 1
     */
    public function update(UpdateEstudanteRequest $request, string $id)
    {
        return $this->estudanteService->update($request->validated(), $id);
    }

    /**
     * Remover estudante.
     *
     * Remove um estudante do cadastro.
     *
     * @urlParam estudante integer required ID do estudante. Example: 1
     */
    public function destroy(string $id)
    {
        return $this->estudanteService->destroy($id);
    }

    /**
     * Contar estudantes.
     *
     * Retorna o total de estudantes cadastrados.
     */
    public function countEstudantes()
    {
        return $this->estudanteService->countEstudantes();
    }

    /**
     * Listar estudantes ativos.
     *
     * Retorna estudantes com status ATIVO.
     */
    public function estudantesAtivos()
    {
        return $this->estudanteService->estudantesAtivos();
    }
}
