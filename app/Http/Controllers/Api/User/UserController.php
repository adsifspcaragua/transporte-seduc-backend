<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\Services\User\UserService;

/**
 * @group Usuarios
 *
 * Rotas para gerenciamento de usuarios do sistema.
 *
 * @authenticated
 */
class UserController extends Controller
{
    public function __construct(private readonly UserService $userService) {}

    /**
     * Listar usuarios.
     *
     * Retorna todos os usuarios cadastrados.
     */
    public function index()
    {
        return $this->userService->index();
    }

    /**
     * Cadastrar usuario.
     *
     * Cria um novo usuario do sistema.
     */
    public function store(StoreUserRequest $request)
    {
        return $this->userService->store($request->validated());
    }

    /**
     * Exibir usuario.
     *
     * Retorna os dados de um usuario especifico.
     *
     * @urlParam user integer required ID do usuario. Example: 1
     */
    public function show(string $id)
    {
        return $this->userService->show($id);
    }

    /**
     * Atualizar usuario.
     *
     * Atualiza os dados de um usuario existente.
     *
     * @urlParam user integer required ID do usuario. Example: 1
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        return $this->userService->update($request->validated(), $user);
    }

    /**
     * Remover usuario.
     *
     * Remove um usuario cadastrado.
     *
     * @urlParam user integer required ID do usuario. Example: 1
     */
    public function destroy(string $id)
    {
        return $this->userService->destroy($id);
    }
}
