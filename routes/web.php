<?php

use Illuminate\Support\Facades\Route;

// 1. Rota Catch-All para o Vue.js
// Ela captura a raiz ('/') e qualquer outra URL digitada (como '/painel', '/login')
// e entrega o controle para o Vue Router fazer a mágica dele na tela.
Route::get('/{any?}', function () {
    $path = public_path('index.html');
    
    // Verifica se o arquivo compilado do Vue está lá
    if (file_exists($path)) {
        return file_get_contents($path);
    }
    
    return response('Frontend não compilado ou não encontrado na pasta public.', 404);
})->where('any', '.*');

// 2. MANTEMOS O SEU ARQUIVO AUTH!
// Como você tem rotas de autenticação separadas, isso garante que 
// o seu login/registro continue funcionando perfeitamente por baixo dos panos.
require __DIR__.'/auth.php';