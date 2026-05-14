<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsSuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (! $user || ! $user->canAccessPlatformConsole()) {
            abort(403, 'Acceso denegado. Solo operadores de la plataforma pueden acceder a esta sección.');
        }

        return $next($request);
    }
}
