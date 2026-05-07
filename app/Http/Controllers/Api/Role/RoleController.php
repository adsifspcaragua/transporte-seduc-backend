<?php

namespace App\Http\Controllers\Api\Role;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Services\Role\RoleService;

/**
 * @group Cargos
 *
 * Rotas para gerenciamento dos cargos de usuarios.
 *
 * @authenticated
 */
class RoleController extends Controller
{
    public function __construct(private readonly RoleService $roleService) {}

    /**
     * Listar cargos.
     *
     * Retorna todos os cargos cadastrados.
     */
    public function index()
    {
        return $this->roleService->index();
    }

    /**
     * Cadastrar cargo.
     *
     * Cria um novo cargo.
     */
    public function store(StoreRoleRequest $request)
    {
        return $this->roleService->store($request->validated());
    }

    /**
     * Exibir cargo.
     *
     * Retorna os dados de um cargo especifico.
     *
     * @urlParam role integer required ID do cargo. Example: 1
     */
    public function show(string $id)
    {
        return $this->roleService->show($id);
    }

    /**
     * Atualizar cargo.
     *
     * Atualiza os dados de um cargo existente.
     *
     * @urlParam role integer required ID do cargo. Example: 1
     */
    public function update(UpdateRoleRequest $request, string $id)
    {
        return $this->roleService->update($request->validated(), $id);
    }

    /**
     * Remover cargo.
     *
     * Remove um cargo cadastrado.
     *
     * @urlParam role integer required ID do cargo. Example: 1
     */
    public function destroy(string $id)
    {
        return $this->roleService->destroy($id);
    }
}
