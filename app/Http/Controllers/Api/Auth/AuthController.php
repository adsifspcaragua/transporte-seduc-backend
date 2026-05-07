<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Autenticacao
 *
 * Rotas para login por sessao, emissao de token Bearer e encerramento de acesso.
 */
class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    /**
     * Login com sessao.
     *
     * Autentica um usuario usando e-mail ou CPF e cria uma sessao web.
     *
     * @unauthenticated
     *
     * @bodyParam login string required E-mail ou CPF do usuario. Example: usuario@example.com
     * @bodyParam password string required Senha do usuario. Example: password
     */
    public function login(Request $request): JsonResponse
    {
        return $this->authService->login($request);
    }

    /**
     * Gerar token de acesso.
     *
     * Autentica um usuario usando e-mail ou CPF e retorna um token Bearer para uso nas rotas protegidas.
     *
     * @unauthenticated
     *
     * @bodyParam login string required E-mail ou CPF do usuario. Example: usuario@example.com
     * @bodyParam password string required Senha do usuario. Example: password
     * @bodyParam device_name string Nome do dispositivo usado para identificar o token. Example: insomnia
     */
    public function tokenLogin(Request $request): JsonResponse
    {
        return $this->authService->tokenLogin($request);
    }

    /**
     * Encerrar sessao.
     *
     * Encerra a sessao web autenticada.
     */
    public function logout(Request $request): JsonResponse
    {
        return $this->authService->logout($request);
    }

    /**
     * Revogar token atual.
     *
     * Revoga o token Bearer enviado na requisicao atual.
     */
    public function tokenLogout(Request $request): JsonResponse
    {
        return $this->authService->tokenLogout($request);
    }
}
