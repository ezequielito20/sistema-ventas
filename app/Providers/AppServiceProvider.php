<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\Paginator;

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
        // Configurar paginación para usar Bootstrap 4
        Paginator::defaultView('pagination::bootstrap-4');
        Paginator::defaultSimpleView('pagination::simple-bootstrap-4');
        
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
