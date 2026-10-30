<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Inativar um usuario vale na hora, mesmo com token ou sessao ja abertos.
 *
 * O CheckPermission faz a mesma checagem, mas so nas rotas que exigem
 * permissao; aqui ela cobre todo o grupo autenticado.
 */
class EnsureContaAtiva
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && ! $request->user()->ativo) {
            return response()->json(['message' => 'Usuário inativo.'], 403);
        }

        return $next($request);
    }
}
