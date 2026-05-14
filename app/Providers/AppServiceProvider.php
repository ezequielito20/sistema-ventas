<?php

namespace App\Providers;

use App\Services\PlanEntitlementService;
use Illuminate\Foundation\Vite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        /*
         * Livewire v4 usa /livewire-{hash}/update (derivado de APP_KEY). Esa URL de dos segmentos
         * puede chocar con proxies, cachés de ruta o reglas del host y devolver 405 aunque el
         * primer batch del componente ya haya persistido datos. Registramos un único POST estable
         * antes del boot de paquetes para que Livewire omita el endpoint por defecto.
         *
         * @see \Livewire\Mechanisms\HandleRequests\HandleRequests::boot
         */
        $this->app->booting(function () {
            Livewire::setUpdateRoute(function ($handle) {
                return Route::post('/_livewire/update', $handle)->middleware(['web']);
            });
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Cargar traducciones PHP en /lang (auth, validation, etc.); el loader por defecto solo usa resources/lang.
        if (is_dir(base_path('lang'))) {
            app('translation.loader')->addPath(base_path('lang'));
        }

        // Evita preload de hojas de estilo: ya van con <link rel="stylesheet"> y Chrome
        // advierte "preloaded but not used" con el mismo recurso.
        app(Vite::class)->usePreloadTagAttributes(function (?string $src, string $url, ?array $chunk, ?array $manifest): array|false {
            $path = parse_url($url, PHP_URL_PATH);
            if (is_string($path) && str_ends_with($path, '.css')) {
                return false;
            }

            return [];
        });

        // Usar vistas de paginación con Tailwind (por defecto en Laravel >= 8)
        // Si existen vistas personalizadas en resources/views/vendor/pagination, se usarán automáticamente
        // Paginator::useTailwind(); // Descomentarlo si deseas forzar Tailwind

        View::composer('layouts.app', function ($view) {
            $view->with('planMod', function (string $moduleKey): bool {
                $user = Auth::user();
                if (! $user) {
                    return false;
                }

                return app(PlanEntitlementService::class)->userCanAccessModule($user, $moduleKey);
            });
        });

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
