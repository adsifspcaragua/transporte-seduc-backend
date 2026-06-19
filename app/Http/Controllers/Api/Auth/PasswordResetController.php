<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Services\Auth\PasswordResetService;
use Illuminate\Http\JsonResponse;

/**
 * @group Autenticacao
 *
 * Rotas para recuperacao de acesso (esqueci minha senha / redefinir senha).
 */
class PasswordResetController extends Controller
{
    public function __construct(private readonly PasswordResetService $passwordResetService) {}

    /**
     * Solicitar redefinicao de senha.
     *
     * Envia um link de redefinicao para o e-mail informado, caso exista uma conta associada.
     *
     * @unauthenticated
     */
    public function forgot(ForgotPasswordRequest $request): JsonResponse
    {
        return $this->passwordResetService->sendResetLink($request->validated()['email']);
    }

    /**
     * Redefinir senha.
     *
     * Redefine a senha do usuario a partir do token recebido por e-mail.
     *
     * @unauthenticated
     */
    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        return $this->passwordResetService->reset($request->validated());
    }
}
