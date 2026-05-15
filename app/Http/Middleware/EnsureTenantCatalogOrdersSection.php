<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\PlanEntitlementService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rutas pedidos/catálogo: permite administradores tenant con gestión de roles cuando el contrato
 * incluye el módulo, aún si roles Spatie legacy no llevan abilities orders.*.
 */
class EnsureTenantCatalogOrdersSection
{
    public function handle(Request $request, Closure $next, string $intent): Response
    {
        /** @var User|null $user */
        $user = $request->user();
        abort_unless($user, 403);

        $svc = app(PlanEntitlementService::class);
        $allowed = match ($intent) {
            'browse' => $svc->tenantUserMayBrowseOrdersConsole($user),
            'configure' => $svc->tenantUserMayConfigureOrdersConsole($user),
            default => false,
        };

        abort_unless($allowed, 403);

        return $next($request);
    }
}
