<?php

namespace MiEmpresa\Core;

use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/dompdf.php', 'dompdf');
        $this->mergeConfigFrom(__DIR__.'/../config/auth.php', 'auth');
        $this->mergeConfigFrom(__DIR__.'/../config/debugbar.php', 'debugbar');
    }

    public function boot(): void
    {
        // Vistas
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'core');

        // Migraciones
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Rutas auth
        $this->loadRoutesFrom(__DIR__.'/../routes/auth.php');

        // Publicables
        $this->publishes([
            __DIR__.'/../config/dompdf.php' => config_path('dompdf.php'),
            __DIR__.'/../config/auth.php' => config_path('auth.php'),
            __DIR__.'/../config/debugbar.php' => config_path('debugbar.php'),
        ], 'laravel-core-configs');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/core'),
        ], 'laravel-core-views');

        $this->publishes([
            __DIR__.'/../resources/sass' => resource_path('sass/vendor/core'),
        ], 'laravel-core-sass');

        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/core'),
        ], 'laravel-core-assets');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'laravel-core-migrations');

        // Registrar middleware
        $this->app['router']->aliasMiddleware('security.questions', \MiEmpresa\Core\Http\Middleware\EnsureSecurityQuestionsSetUp::class);
    }
}
