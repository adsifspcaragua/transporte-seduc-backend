<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Estudante\Documento\InscricaoDocumentoController;
use App\Http\Controllers\Api\Estudante\EstudanteController;
use App\Http\Controllers\Api\Estudante\InscricaoController;
use App\Http\Controllers\Api\Estudante\InscricaoInstituicaoController;
use App\Http\Controllers\Api\Instituicao\InstituicaoController;
use App\Http\Controllers\Api\LinhaController;
use App\Http\Controllers\Api\Reecadastro\DocumentoReecadastroController;
use App\Http\Controllers\Api\Reecadastro\SolicitacaoReecadastroController;
use App\Http\Controllers\Api\Role\RoleController;
use App\Http\Controllers\Api\User\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/auth/token', [AuthController::class, 'tokenLogin'])->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->group(function () {
    /**
     * Exibir usuario autenticado.
     *
     * Retorna os dados do usuario autenticado na requisicao atual.
     *
     * @group Autenticacao
     *
     * @authenticated
     */
    Route::get('/me', function (Request $request) {
        try {
            return $request->user();
        } catch (Exception $e) {
            return response()->json('Falha ao retornar usuário', 401);
        }
    });

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/auth/token/revoke', [AuthController::class, 'tokenLogout']);

    Route::apiResource('users', UserController::class);
    Route::apiResource('roles', RoleController::class);

    Route::apiResource('estudantes/reecadastrar', DocumentoReecadastroController::class)
        ->parameters(['reecadastrar' => 'documento']);
    Route::apiResource('estudantes', EstudanteController::class);
    Route::get('contar-estudantes', [EstudanteController::class, 'countEstudantes']);

    Route::post('inscricoes/recadastro', [InscricaoController::class, 'recadastro']);
    Route::apiResource('inscricoes', InscricaoController::class)
        ->parameters(['inscricoes' => 'inscricao']);
    Route::apiResource('inscricoes/{inscricao_id}/instituicoes', InscricaoInstituicaoController::class)
        ->parameters(['instituicoes' => 'instituicao']);

    Route::apiResource('instituicao', InstituicaoController::class);
    
    Route::apiResource('linha', LinhaController::class);
    
    Route::apiResource('inscricoes/{inscricao}/documentos', InscricaoDocumentoController::class)
    ->parameters([
        'documentos' => 'documento'
    ]);
    Route::apiResource('reecadastro/solicitacoes', SolicitacaoReecadastroController::class)
            ->parameters(['solicitacoes' => 'solicitacao']);

    Route::put('inscricoes/analise/{id}', [InscricaoController::class, 'analise']);
        });
        

    