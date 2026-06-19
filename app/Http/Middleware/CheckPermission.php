<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Garante que o usuário autenticado está ativo e possui a permissão exigida.
     * O perfil "admin" tem acesso total (bypass).
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Não autenticado.'], 401);
        }

        if (! $user->ativo) {
            return response()->json(['message' => 'Usuário inativo.'], 403);
        }

        if ($user->hasRole('admin')) {
            return $next($request);
        }

        if (! $user->hasPermission($permission)) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        return $next($request);
    }
}
