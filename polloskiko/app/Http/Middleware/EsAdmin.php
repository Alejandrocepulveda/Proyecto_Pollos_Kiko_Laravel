<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->esAdmin(), 403, 'Solo el administrador puede entrar aquí.');

        return $next($request);
    }
}
