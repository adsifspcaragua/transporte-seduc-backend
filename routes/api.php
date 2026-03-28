<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{AuthController, UserController};
use App\Http\Controllers\Api\Estudante\{EstudanteController, InscricaoController};
use App\Http\Controllers\Api\Role\RoleController;


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

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('users', UserController::class);
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('estudantes', EstudanteController::class);
    Route::apiResource('inscricao', InscricaoController::class);
});
