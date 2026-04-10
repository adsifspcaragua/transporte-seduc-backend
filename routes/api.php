<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Estudante\{EstudanteController, InscricaoController, InscricaoInstituicaoController};
use App\Http\Controllers\Api\Estudante\Documento\DocumentoController;
use App\Http\Controllers\Api\Estudante\InscricaoDocumentoController;
use App\Http\Controllers\Api\InstituicoesController;
use App\Http\Controllers\Api\Role\RoleController;
use App\Http\Controllers\Api\User\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


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
        Route::apiResource('users', UserController::class);
        Route::apiResource('roles', RoleController::class);

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('users', UserController::class);
    Route::apiResource('roles', RoleController::class);

    Route::apiResource('estudantes', EstudanteController::class);
    Route::apiResource('estudantes/{estudante_id}/documentos', DocumentoController::class);
    
    
    });
    Route::apiResource('inscricoes', InscricaoController::class);
    Route::apiResource('inscricoes/{inscricao_id}/instituicoes', InscricaoInstituicaoController::class);

    Route::apiResource('instituicoes', InstituicoesController::class);
    
    
    Route::apiResource('inscricoes/{inscricao_id}/documentos', InscricaoDocumentoController::class);