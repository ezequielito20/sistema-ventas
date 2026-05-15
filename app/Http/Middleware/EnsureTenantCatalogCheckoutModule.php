<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\PlanEntitlementService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Checkout del catálogo (pagos / entregas): requiere el contrato de catálogo+pedidos y permiso granular o compat. con orders.settings.
 */
class EnsureTenantCatalogCheckoutModule
{
    public function handle(Request $request, Closure $next, string $moduleKey): Response
    {
        /** @var User|null $user */
        $user = $request->user();
        abort_if(! $user instanceof User, 403);

        $svc = app(PlanEntitlementService::class);

        $allowed = match ($moduleKey) {
            'catalog_payment_methods' => $svc->tenantUserMayBrowseCatalogPayments($user),
            'catalog_delivery_methods' => $svc->tenantUserMayBrowseCatalogDeliveries($user),
            default => false,
        };

        abort_unless($allowed, 403);

        return $next($request);
    }
}
