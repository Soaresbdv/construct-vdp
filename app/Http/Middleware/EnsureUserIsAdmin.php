<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Se o usuário NÃO estiver logado OU NÃO for admin...
        if (! $request->user() || ! $request->user()->is_admin) {
            // Retorna erro 403 (Proibido)
            return response()->json(['message' => 'Acesso negado. Área restrita.'], 403);
        }

        return $next($request);
    }
}