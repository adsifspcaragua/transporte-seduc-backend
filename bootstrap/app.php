<?php

use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\VerifyInscricaoToken;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\UnauthorizedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
        $middleware->alias([
            'permission' => CheckPermission::class,
            'inscricao.token' => VerifyInscricaoToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // As mensagens que o proprio framework gera chegavam em ingles
        // ("Unauthenticated.", "Too Many Attempts.") e algumas expunham o
        // caminho interno da aplicacao ("No query results for model [App\...]").
        // Aqui elas passam a falar a mesma lingua do resto da API.
        $emJson = fn (Request $request) => $request->is('api/*') || $request->expectsJson();

        $responder = fn (string $mensagem, int $status, array $headers = []) => response()->json(
            ['message' => $mensagem],
            $status,
            $headers,
        );

        $exceptions->render(fn (AuthenticationException $e, Request $request) => $emJson($request)
            ? $responder('Não autenticado. Faça login para continuar.', 401)
            : null);

        $exceptions->render(fn (AuthorizationException $e, Request $request) => $emJson($request)
            ? $responder('Acesso negado.', 403)
            : null);

        $exceptions->render(fn (UnauthorizedException $e, Request $request) => $emJson($request)
            ? $responder('Acesso negado.', 403)
            : null);

        $exceptions->render(fn (AccessDeniedHttpException $e, Request $request) => $emJson($request)
            ? $responder('Acesso negado.', 403)
            : null);

        // Lancada direto por findOrFail() dentro de um service.
        $exceptions->render(fn (ModelNotFoundException $e, Request $request) => $emJson($request)
            ? $responder('Registro não encontrado.', 404)
            : null);

        // O route binding nao lanca ModelNotFoundException ate o fim: o Laravel
        // a converte em NotFoundHttpException e guarda a original em `previous`.
        // Sem olhar ali, um id inexistente responderia "Rota não encontrada".
        $exceptions->render(function (NotFoundHttpException $e, Request $request) use ($emJson, $responder) {
            if (! $emJson($request)) {
                return null;
            }

            return $e->getPrevious() instanceof ModelNotFoundException
                ? $responder('Registro não encontrado.', 404)
                : $responder('Rota não encontrada.', 404);
        });

        $exceptions->render(fn (MethodNotAllowedHttpException $e, Request $request) => $emJson($request)
            ? $responder('Método não permitido nesta rota.', 405)
            : null);

        $exceptions->render(function (ThrottleRequestsException $e, Request $request) use ($emJson, $responder) {
            if (! $emJson($request)) {
                return null;
            }

            $segundos = (int) ($e->getHeaders()['Retry-After'] ?? 0);

            return $responder(
                $segundos > 0
                    ? "Muitas tentativas. Tente novamente em {$segundos} segundos."
                    : 'Muitas tentativas. Aguarde um momento e tente novamente.',
                429,
                $e->getHeaders(),
            );
        });

        // A API e stateful (Sanctum): o cookie de sessao pode expirar.
        $exceptions->render(fn (TokenMismatchException $e, Request $request) => $emJson($request)
            ? $responder('Sessão expirada. Atualize a página e tente novamente.', 419)
            : null);

        // Erro nao tratado: em producao o Laravel responde "Server Error".
        // Com APP_DEBUG ligado o retorno detalhado continua intacto.
        $exceptions->respond(function (Response $response, Throwable $e, Request $request) use ($emJson) {
            if ($response->getStatusCode() !== 500 || config('app.debug') || ! $emJson($request)) {
                return $response;
            }

            return response()->json([
                'message' => 'Erro interno no servidor. Tente novamente mais tarde.',
            ], 500);
        });
    })->create();
