<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHomeModuleEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('home.enabled', true)) {
            abort(404);
        }

        return $next($request);
    }
}
