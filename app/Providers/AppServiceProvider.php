<?php

namespace App\Providers;

use App\Models\Home\HomeBankConnection;
use App\Models\Home\HomeProduct;
use App\Models\Home\HomeService;
use App\Models\Home\HomeServiceBill;
use App\Models\Home\HomeShoppingList;
use App\Models\Home\HomeTransaction;
use App\Models\Order;
use App\Models\User;
use App\Policies\Home\HomeBankConnectionPolicy;
use App\Policies\Home\HomeProductPolicy;
use App\Policies\Home\HomeServiceBillPolicy;
use App\Policies\Home\HomeServicePolicy;
use App\Policies\Home\HomeShoppingListPolicy;
use App\Policies\Home\HomeTransactionPolicy;
use App\Services\PlanEntitlementService;
use Illuminate\Foundation\Vite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
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
        /*
         * Super admin (usuario plataforma / flag y rol «super-admin»): acceso ilimitado a permisos
         * y políticas; evita ítems de menú o rutas con middleware can:* bloqueadas por roles Spatie incompletos.
         */
        Gate::before(function ($user, string $ability) {
            if ($user instanceof User && $user->isSuperAdmin()) {
                return true;
            }

            return null;
        });

        Route::bind('order', function (string $value): Order {
            $user = Auth::user();
            abort_unless($user, 403);

            return Order::query()
                ->where('company_id', $user->company_id)
                ->whereKey($value)
                ->firstOrFail();
        });

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

        // Registrar políticas del Módulo Hogar
        Gate::policy(HomeProduct::class, HomeProductPolicy::class);
        Gate::policy(HomeService::class, HomeServicePolicy::class);
        Gate::policy(HomeServiceBill::class, HomeServiceBillPolicy::class);
        Gate::policy(HomeTransaction::class, HomeTransactionPolicy::class);
        Gate::policy(HomeShoppingList::class, HomeShoppingListPolicy::class);
        Gate::policy(HomeBankConnection::class, HomeBankConnectionPolicy::class);

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
