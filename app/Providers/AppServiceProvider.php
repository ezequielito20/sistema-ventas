<?php

namespace App\Providers;

use Illuminate\Foundation\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Evita preload de hojas de estilo: ya van con <link rel="stylesheet"> y Chrome
        // advierte "preloaded but not used" con el mismo recurso.
        app(Vite::class)->usePreloadTagAttributes(function (string $src, string $url, ?array $chunk, ?array $manifest): array|false {
            $path = parse_url($url, PHP_URL_PATH);
            if (is_string($path) && str_ends_with($path, '.css')) {
                return false;
            }

            return [];
        });

        // Usar vistas de paginación con Tailwind (por defecto en Laravel >= 8)
        // Si existen vistas personalizadas en resources/views/vendor/pagination, se usarán automáticamente
        // Paginator::useTailwind(); // Descomentarlo si deseas forzar Tailwind

        // Comentado temporalmente para evitar N+1 queries
        // La variable company se manejará directamente en los controladores
        /*
        View::composer(['admin.*', 'auth.*', 'layouts.*'], function ($view) {
            if (Auth::check()) {
                $company = \Illuminate\Support\Facades\DB::table('companies')
                    ->where('id', Auth::user()->company_id)
                    ->first();
                $view->with('company', $company);
            }
        });
        */
    }
}
