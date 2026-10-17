<?php

namespace App\Http\Middleware;

use App\Models\Estudante;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Liga a conta autenticada ao cadastro do estudante (estudantes.user_id).
 *
 * As rotas do estudante nunca recebem o id dele pela URL ou pelo corpo: ele
 * sai daqui, da sessao/token, e fica em `$request->attributes('estudante')`.
 */
class ResolveEstudanteDaConta
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $estudante = $user ? Estudante::where('user_id', $user->id)->first() : null;

        if (! $estudante) {
            return response()->json([
                'message' => 'Esta conta não está vinculada a um cadastro de estudante.',
            ], 403);
        }

        $request->attributes->set('estudante', $estudante);

        return $next($request);
    }
}
