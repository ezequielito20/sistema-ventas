<?php

namespace App\Services\Catalog;

use App\Models\Company;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class CatalogOrderSubmitGuard
{
    public function assertIpWithinLimit(Request $request, Company $company): void
    {
        $max = max(1, (int) config('catalog.order_max_per_ip_per_hour', 3));
        $key = $this->ipRateLimitKey($request, $company);

        if (! RateLimiter::tooManyAttempts($key, $max)) {
            return;
        }

        $minutes = (int) ceil(RateLimiter::availableIn($key) / 60);

        throw ValidationException::withMessages([
            'cart' => $minutes > 0
                ? "Demasiados pedidos desde esta conexión. Intentá de nuevo en {$minutes} min."
                : 'Demasiados pedidos desde esta conexión. Intentá de nuevo más tarde.',
        ]);
    }

    public function recordSuccessfulSubmit(Request $request, Company $company): void
    {
        $decaySeconds = max(60, (int) config('catalog.order_ip_rate_limit_decay_seconds', 3600));

        RateLimiter::hit($this->ipRateLimitKey($request, $company), $decaySeconds);
    }

    /**
     * Debe llamarse dentro de una transacción con lock para evitar carreras.
     */
    public function assertPendingPhoneLimit(Company $company, string $phoneDigits): void
    {
        $maxPending = max(1, (int) config('catalog.order_max_pending_per_phone', 1));

        $pendingCount = Order::query()
            ->where('company_id', $company->id)
            ->where('customer_phone', $phoneDigits)
            ->where('status', 'pending')
            ->lockForUpdate()
            ->count();

        if ($pendingCount >= $maxPending) {
            throw ValidationException::withMessages([
                'customer_phone' => 'Ya tenés un pedido pendiente con este teléfono. Esperá a que la tienda lo procese o contactala antes de enviar otro.',
            ]);
        }
    }

    private function ipRateLimitKey(Request $request, Company $company): string
    {
        $ip = $request->ip() ?? 'unknown';

        return 'catalog-order-submit:'.$company->id.':'.$ip;
    }
}
