<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;

// Verificar a necessidade disso
Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

// Rota para listar todas as obra
Route::get('/projects', [ProjectController::class, 'index']);

// Rota para detalhes de uma obra específica
Route::get('/projects/{id}', [ProjectController::class, 'show']);

// Rotas protegidas por autenticação
Route::middleware('auth:sanctum')->group(function () {

    // Rota para obter dados do usuário autenticado
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Rota para baixar documento da obra (simulado)
    Route::get('/projects/{id}/download', function ($id) {
        return response()->json(['url' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf']);
    });

    // Rota para contatar o responsável pela obra (simulado) 
    Route::post('/projects/{id}/contact', function ($id) {
        return response()->json(['message' => 'Solicitação enviada o responsável!']);
    });
});