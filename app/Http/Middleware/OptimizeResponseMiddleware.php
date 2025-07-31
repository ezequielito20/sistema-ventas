<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OptimizeResponseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Solo optimizar en producción
        if (app()->environment('production')) {
            // Agregar headers de caché para recursos estáticos
            if ($request->is('*.css') || $request->is('*.js') || $request->is('*.png') || $request->is('*.jpg') || $request->is('*.jpeg') || $request->is('*.gif') || $request->is('*.svg')) {
                $response->headers->set('Cache-Control', 'public, max-age=31536000'); // 1 año
            }

            // Agregar headers de compresión
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
            $response->headers->set('X-XSS-Protection', '1; mode=block');

            // Optimizar respuestas JSON
            if ($response->headers->get('Content-Type') === 'application/json') {
                $response->headers->set('Cache-Control', 'no-cache, must-revalidate');
            }
        }

        return $response;
    }
}
