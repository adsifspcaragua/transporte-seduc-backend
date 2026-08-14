<?php

namespace App\Http\Middleware;

use App\Models\Inscricao;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyInscricaoToken
{
    /**
     * Protege as rotas públicas da lista de espera.
     *
     * O estudante não faz login: ao criar a inscrição ele recebe um token que
     * funciona como a credencial daquela inscrição. Sem ele, o ID sozinho não
     * dá acesso aos dados pessoais nem permite alterá-los.
     *
     * O token pode vir no cabeçalho `X-Inscricao-Token` ou no campo `token`
     * (query string ou corpo da requisição).
     *
     * Usuários autenticados com permissão de inscrições seguem pelo caminho
     * administrativo e não precisam do token.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $inscricaoId = $request->route('inscricao') ?? $request->route('inscricao_id');

        $inscricao = $inscricaoId instanceof Inscricao
            ? $inscricaoId
            : Inscricao::find($inscricaoId);

        if (! $inscricao) {
            return response()->json(['message' => 'Inscricao não encontrada'], 404);
        }

        $user = $request->user();

        if ($user && $user->ativo && ($user->hasRole('admin') || $user->hasPermission('inscricoes.view'))) {
            return $next($request);
        }

        $token = (string) ($request->header('X-Inscricao-Token') ?? $request->input('token', ''));

        if ($token === '' || ! $inscricao->access_token || ! hash_equals($inscricao->access_token, $token)) {
            return response()->json([
                'message' => 'Token da inscrição inválido ou ausente.',
            ], 401);
        }

        return $next($request);
    }
}
