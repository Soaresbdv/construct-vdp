<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\LeadController;

// Rotas públicas
// Listar todas as obras (Landing Page)
Route::get('/projects', [ProjectController::class, 'index']);
// Ver detalhes de uma obra (Página de Detalhes)
Route::get('/projects/{id}', [ProjectController::class, 'show']);
// Rota Pública (Qualquer um pode virar um lead)
Route::post('/leads', [App\Http\Controllers\LeadController::class, 'store']);

// Rotas protegidas por login
Route::middleware('auth:sanctum')->group(function () {

    // Saber quem sou eu 
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Baixar documentação 
    Route::get('/projects/{id}/download', function ($id) {
        return response()->json(['url' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf']);
    });

    // Contatar responsável
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
    // Listar leads 
    Route::get('/admin/leads', [App\Http\Controllers\LeadController::class, 'index']);
    
});