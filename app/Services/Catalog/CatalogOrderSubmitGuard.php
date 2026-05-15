<?php

namespace App\Services\Catalog;

use App\Models\Company;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class CatalogOrderSubmitGuard
{
    public function assertIpWithinLimit(Request $request, Company $company): void
    {
        $max = max(1, (int) config('catalog.order_max_per_ip_per_hour', 3));
        $key = $this->ipRateLimitKey($request, $company);
        $state = $this->ipLimitState($key);

        if ($state === null || ($state['count'] ?? 0) < $max) {
            return;
        }

        $retryAt = Carbon::createFromTimestamp($state['until'], config('app.timezone'));

        if (now()->gte($retryAt)) {
            Cache::forget($key);

            return;
        }

        throw ValidationException::withMessages([
            'cart' => 'Demasiados pedidos desde esta conexión. Debes esperar una hora desde tu último pedido. Podrás hacer un nuevo pedido a las '.$retryAt->format('H:i').' del '.$retryAt->format('d/m/Y').'.',
        ]);
    }

    public function recordSuccessfulSubmit(Request $request, Company $company): void
    {
        $decaySeconds = max(60, (int) config('catalog.order_ip_rate_limit_decay_seconds', 3600));
        $key = $this->ipRateLimitKey($request, $company);
        $state = $this->ipLimitState($key);

        if ($state !== null && now()->timestamp >= (int) $state['until']) {
            $state = null;
        }

        $count = ($state['count'] ?? 0) + 1;
        $until = now()->addSeconds($decaySeconds)->timestamp;

        Cache::put($key, ['count' => $count, 'until' => $until], $decaySeconds);
    }

    /**
     * Debe llamarse dentro de una transacción con lock para evitar carreras.
     */
    public function assertPendingPhoneLimit(Company $company, string $phoneDigits): void
    {
        $maxPending = max(1, (int) config('catalog.order_max_pending_per_phone', 1));

        // PostgreSQL no permite FOR UPDATE con COUNT(*); bloqueamos filas concretas.
        $pendingCount = Order::query()
            ->where('company_id', $company->id)
            ->where('customer_phone', $phoneDigits)
            ->where('status', 'pending')
            ->orderBy('id')
            ->lockForUpdate()
            ->limit($maxPending)
            ->get(['id'])
            ->count();

        if ($pendingCount >= $maxPending) {
            throw ValidationException::withMessages([
                'customer_phone' => 'Ya tenés un pedido pendiente con este teléfono. Cancelalo desde el enlace de resumen del pedido o esperá a que la tienda lo procese antes de enviar otro.',
            ]);
        }
    }

    private function ipRateLimitKey(Request $request, Company $company): string
    {
        $ip = $request->ip() ?? 'unknown';

        return 'catalog-order-submit:'.$company->id.':'.$ip;
    }

    /**
     * @return array{count: int, until: int}|null
     */
    private function ipLimitState(string $key): ?array
    {
        $state = Cache::get($key);

        if (! is_array($state) || ! isset($state['count'], $state['until'])) {
            return null;
        }

        return $state;
    }
}
