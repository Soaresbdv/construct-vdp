<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;

// Rotas públicas
// Listar todas as obras (Landing Page)
Route::get('/projects', [ProjectController::class, 'index']);
// Ver detalhes de uma obra (Página de Detalhes)
Route::get('/projects/{id}', [ProjectController::class, 'show']);


// Rotas protegidas por login
Route::middleware('auth:sanctum')->group(function () {

    // Saber quem sou eu (usado pelo Vue para checar login)
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Baixar documentação (Simulado)
    Route::get('/projects/{id}/download', function ($id) {
        return response()->json(['url' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf']);
    });

    // Contatar engenheiro (Simulado)
    Route::post('/projects/{id}/contact', function ($id) {
        return response()->json(['message' => 'Solicitação enviada ao responsável!']);
    });
});

// Rotas administrativas
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    
    // Criar nova obra
    Route::post('/projects', [ProjectController::class, 'store']);
    // Editar obra existente
    Route::put('/projects/{id}', [ProjectController::class, 'update']);
    // Excluir obra
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy']);
    
});