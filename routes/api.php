<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Estudante\{EstudanteController, InscricaoController, InscricaoInstituicaoController};
use App\Http\Controllers\Api\Estudante\Documento\DocumentoController;
use App\Http\Controllers\Api\Inscricao\Documento\InscricaoDocumentoController;
use App\Http\Controllers\Api\InstituicaoController;
use App\Http\Controllers\Api\Role\RoleController;
use App\Http\Controllers\Api\User\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;





Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', function (Request $request) {
        try{

            return $request->user();
        }
        catch(Exception $e) {
            return response()->json("Falha ao retornar usuário", 401);
        }
    });

        Route::apiResource('estudantes', EstudanteController::class);
        Route::get('contar-estudantes', [EstudanteController::class, 'countEstudantes']);
        Route::apiResource('users', UserController::class);
        Route::apiResource('roles', RoleController::class);

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('users', UserController::class);
    Route::apiResource('roles', RoleController::class);


    
    
    });
    Route::apiResource('inscricoes', InscricaoController::class);
    Route::apiResource('inscricoes/{inscricao_id}/instituicoes', InscricaoInstituicaoController::class);

    Route::apiResource('instituicao', InstituicaoController::class);
    
    
    Route::apiResource('inscricoes.documentos', InscricaoDocumentoController::class);
