<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use App\Services\ImageUrlService;

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
        // Compartir la variable company con todas las vistas
        View::composer('*', function ($view) {
            if (Auth::check()) {
                $company = Company::find(Auth::user()->company_id);
                $view->with('company', $company);
            }
        });

        // Blade directive para URLs de imágenes
        Blade::directive('imageUrl', function ($expression) {
            return "<?php echo App\Services\ImageUrlService::getImageUrl($expression); ?>";
        });
    }
}
