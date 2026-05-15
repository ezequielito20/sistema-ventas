<?php

use App\Http\Middleware\EnsureCompanyIsActive;
use App\Http\Middleware\EnsureSecurityQuestionsSetUp;
use App\Http\Middleware\EnsureTenantCatalogCheckoutModule;
use App\Http\Middleware\EnsureTenantCatalogOrdersSection;
use App\Http\Middleware\EnsureUserIsSuperAdmin;
use App\Http\Middleware\OptimizeResponseMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(OptimizeResponseMiddleware::class);
        $middleware->web(append: [
            EnsureSecurityQuestionsSetUp::class,
            EnsureCompanyIsActive::class,
        ]);
        $middleware->alias([
            'superadmin' => EnsureUserIsSuperAdmin::class,
            'tenant.catalog-checkout' => EnsureTenantCatalogCheckoutModule::class,
            'tenant.orders' => EnsureTenantCatalogOrdersSection::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
