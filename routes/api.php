<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\PasswordResetController;
use App\Http\Controllers\Api\Curso\CursoController;
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

Route::post('/password/email', [PasswordResetController::class, 'forgot'])->middleware('throttle:5,1');
Route::post('/password/reset', [PasswordResetController::class, 'reset'])->middleware('throttle:5,1');

Route::get('instituicao', [InstituicaoController::class, 'index']);
Route::get('curso', [CursoController::class, 'index']);
Route::post('inscricoes/validar-step', [InscricaoController::class, 'validateStep']);
Route::post('inscricoes', [InscricaoController::class, 'store']);
Route::get('inscricoes/{inscricao}', [InscricaoController::class, 'show']);
Route::put('inscricoes/{inscricao}', [InscricaoController::class, 'update']);
Route::patch('inscricoes/{inscricao}', [InscricaoController::class, 'update']);
Route::post('inscricoes/{inscricao_id}/instituicoes', [InscricaoInstituicaoController::class, 'store']);
Route::put('inscricoes/{inscricao_id}/instituicoes/{instituicao}', [InscricaoInstituicaoController::class, 'update']);
Route::patch('inscricoes/{inscricao_id}/instituicoes/{instituicao}', [InscricaoInstituicaoController::class, 'update']);
Route::post('inscricoes/{inscricao}/documentos', [InscricaoDocumentoController::class, 'store']);
Route::put('inscricoes/{inscricao}/documentos/{documento}', [InscricaoDocumentoController::class, 'update']);
Route::patch('inscricoes/{inscricao}/documentos/{documento}', [InscricaoDocumentoController::class, 'update']);

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

    // Usuários (apenas admin)
    Route::apiResource('users', UserController::class)->only(['index', 'show'])->middleware('permission:users.view');
    Route::apiResource('users', UserController::class)->only(['store', 'update'])->middleware('permission:users.write');
    Route::apiResource('users', UserController::class)->only(['destroy'])->middleware('permission:users.delete');
    Route::patch('users/{user}/inativar', [UserController::class, 'inativar'])->middleware('permission:users.write');
    Route::patch('users/{user}/ativar', [UserController::class, 'ativar'])->middleware('permission:users.write');

    // Cargos (apenas admin)
    Route::apiResource('roles', RoleController::class)->only(['index', 'show'])->middleware('permission:roles.view');
    Route::apiResource('roles', RoleController::class)->only(['store', 'update'])->middleware('permission:roles.write');
    Route::apiResource('roles', RoleController::class)->only(['destroy'])->middleware('permission:roles.delete');

    // Documentos de reecadastro
    Route::apiResource('estudantes/reecadastrar', DocumentoReecadastroController::class)
        ->only(['index', 'show'])->parameters(['reecadastrar' => 'documento'])->middleware('permission:documentos.view');
    Route::apiResource('estudantes/reecadastrar', DocumentoReecadastroController::class)
        ->only(['store', 'update'])->parameters(['reecadastrar' => 'documento'])->middleware('permission:documentos.write');
    Route::apiResource('estudantes/reecadastrar', DocumentoReecadastroController::class)
        ->only(['destroy'])->parameters(['reecadastrar' => 'documento'])->middleware('permission:documentos.delete');

    // Estudantes
    Route::apiResource('estudantes', EstudanteController::class)->only(['index', 'show'])->middleware('permission:estudantes.view');
    Route::apiResource('estudantes', EstudanteController::class)->only(['store', 'update'])->middleware('permission:estudantes.write');
    Route::apiResource('estudantes', EstudanteController::class)->only(['destroy'])->middleware('permission:estudantes.delete');
    Route::get('contar-estudantes', [EstudanteController::class, 'countEstudantes'])->middleware('permission:estudantes.view');

    // Inscrições (área administrativa)
    Route::post('inscricoes/recadastro', [InscricaoController::class, 'recadastro'])->middleware('permission:inscricoes.recadastro');
    Route::put('inscricoes/analise/{id}', [InscricaoController::class, 'analise'])->middleware('permission:inscricoes.analise');
    Route::apiResource('inscricoes', InscricaoController::class)
        ->only(['index'])->parameters(['inscricoes' => 'inscricao'])->middleware('permission:inscricoes.view');
    Route::apiResource('inscricoes', InscricaoController::class)
        ->only(['destroy'])->parameters(['inscricoes' => 'inscricao'])->middleware('permission:inscricoes.delete');
    Route::apiResource('inscricoes/{inscricao_id}/instituicoes', InscricaoInstituicaoController::class)
        ->only(['index', 'show'])->parameters(['instituicoes' => 'instituicao'])->middleware('permission:inscricoes.view');
    Route::apiResource('inscricoes/{inscricao_id}/instituicoes', InscricaoInstituicaoController::class)
        ->only(['destroy'])->parameters(['instituicoes' => 'instituicao'])->middleware('permission:inscricoes.delete');
    Route::apiResource('inscricoes.documentos', InscricaoDocumentoController::class)
        ->only(['index', 'show'])->parameters(['inscricoes' => 'inscricao', 'documentos' => 'documento'])->middleware('permission:documentos.view');
    Route::apiResource('inscricoes.documentos', InscricaoDocumentoController::class)
        ->only(['destroy'])->parameters(['inscricoes' => 'inscricao', 'documentos' => 'documento'])->middleware('permission:documentos.delete');

    // Instituições (index é público; aqui apenas leitura individual/escrita/remoção)
    Route::apiResource('instituicao', InstituicaoController::class)->only(['show', 'store', 'update'])->middleware('permission:instituicoes.write');
    Route::apiResource('instituicao', InstituicaoController::class)->only(['destroy'])->middleware('permission:instituicoes.delete');

    // Linhas
    Route::apiResource('linha', LinhaController::class)->only(['index', 'show'])->middleware('permission:linhas.view');
    Route::apiResource('linha', LinhaController::class)->only(['store', 'update'])->middleware('permission:linhas.write');
    Route::apiResource('linha', LinhaController::class)->only(['destroy'])->middleware('permission:linhas.delete');

    // Solicitações de reecadastro (estudante acessa apenas as próprias)
    Route::apiResource('reecadastro/solicitacoes', SolicitacaoReecadastroController::class)
        ->only(['index', 'show'])->parameters(['solicitacoes' => 'solicitacao'])->middleware('permission:solicitacoes.view');
    Route::apiResource('reecadastro/solicitacoes', SolicitacaoReecadastroController::class)
        ->only(['store', 'update'])->parameters(['solicitacoes' => 'solicitacao'])->middleware('permission:solicitacoes.write');
    Route::apiResource('reecadastro/solicitacoes', SolicitacaoReecadastroController::class)
        ->only(['destroy'])->parameters(['solicitacoes' => 'solicitacao'])->middleware('permission:solicitacoes.delete');
});
