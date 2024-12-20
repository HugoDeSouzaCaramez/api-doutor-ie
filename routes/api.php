<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LivroController;
use App\Http\Controllers\IndiceController;
use App\Http\Controllers\UserController;

Route::prefix('v1')->group(function () {
    Route::post('auth/token', [AuthController::class, 'token']);
    Route::post('users', [UserController::class, 'store']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('livros', [LivroController::class, 'index']);
        Route::post('livros', [LivroController::class, 'store']);
        Route::post('livros/{livroId}/importar-indices-xml', [LivroController::class, 'importarIndicesXml']);

        Route::get('indices/{indiceId}', [IndiceController::class, 'show']);
        Route::put('indices/{indiceId}', [IndiceController::class, 'update']);
        Route::delete('indices/{indiceId}', [IndiceController::class, 'destroy']);
    });
});