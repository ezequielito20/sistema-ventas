<?php

namespace App\Console\Commands;

use App\Models\Cart;
use App\Models\Company;
use App\Models\CompanyDeliveryMethod;
use App\Models\CompanyPaymentMethod;
use App\Models\ExchangeRate;
use App\Models\Order;
use App\Services\Catalog\CatalogOrderSubmitGuard;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class CatalogCheckoutDiagnoseCommand extends Command
{
    protected $signature = 'catalog:checkout-diagnose
                            {--company= : ID de empresa a usar en pruebas (opcional)}
                            {--phone=04142643109 : Teléfono de prueba para el guard de pending}';

    protected $description = 'Diagnóstico del checkout de catálogo sin modificar datos (lecturas + transacciones revertidas)';

    public function handle(CatalogOrderSubmitGuard $guard): int
    {
        $this->info('Diagnóstico checkout catálogo (sin borrar ni persistir cambios)');
        $this->newLine();

        $driver = DB::connection()->getDriverName();
        $database = DB::connection()->getDatabaseName();
        $this->line("Conexión: <comment>{$driver}</comment> · Base: <comment>{$database}</comment>");
        $this->newLine();

        $failures = 0;

        $failures += $this->checkSchema();
        $failures += $this->checkConfig();
        $failures += $this->checkExchangeRate();
        $failures += $this->checkPendingPhoneLockQuery();
        $failures += $this->checkPendingPhoneGuard($guard);
        $failures += $this->checkIpLimitState();

        $company = $this->resolveCompany();
        if ($company) {
            $failures += $this->checkCompanyCatalogReadiness($company);
        } else {
            $this->warn('Sin empresa para revisar métodos de pago/entrega (usa --company=ID).');
        }

        $this->newLine();
        if ($failures === 0) {
            $this->info('Todo OK: no se detectaron fallas en las comprobaciones.');

            return self::SUCCESS;
        }

        $this->error("Se encontraron {$failures} problema(s). Revisá los mensajes anteriores.");

        return self::FAILURE;
    }

    private function checkSchema(): int
    {
        $this->info('Esquema requerido');
        $tables = ['orders', 'order_items', 'carts', 'cart_items', 'company_payment_methods', 'company_delivery_methods', 'exchange_rates'];
        $missing = array_filter($tables, fn (string $t) => ! Schema::hasTable($t));
        if ($missing !== []) {
            $this->error('  Faltan tablas: '.implode(', ', $missing));

            return 1;
        }
        $this->line('  Tablas principales: OK');

        return 0;
    }

    private function checkConfig(): int
    {
        $this->info('Configuración catálogo');
        $keys = [
            'order_max_per_ip_per_hour' => config('catalog.order_max_per_ip_per_hour'),
            'order_ip_rate_limit_decay_seconds' => config('catalog.order_ip_rate_limit_decay_seconds'),
            'order_max_pending_per_phone' => config('catalog.order_max_pending_per_phone'),
            'order_public_cancel_window_minutes' => config('catalog.order_public_cancel_window_minutes'),
            'summary_link_ttl_hours' => config('catalog.summary_link_ttl_hours'),
        ];
        foreach ($keys as $name => $value) {
            $this->line("  {$name}: {$value}");
        }

        return 0;
    }

    private function checkExchangeRate(): int
    {
        $this->info('Tasa de cambio');
        try {
            $rate = ExchangeRate::current();
            if ($rate <= 0) {
                $this->error("  Tasa inválida: {$rate}");

                return 1;
            }
            $this->line("  ExchangeRate::current() = {$rate}");

            return 0;
        } catch (Throwable $e) {
            $this->error('  Error: '.$e->getMessage());

            return 1;
        }
    }

    private function checkPendingPhoneLockQuery(): int
    {
        $this->info('Consulta lock pending (compatible PostgreSQL)');
        $companyId = (int) (Company::query()->value('id') ?? 0);
        if ($companyId === 0) {
            $this->warn('  Sin empresas en BD; se omite.');

            return 0;
        }

        DB::beginTransaction();
        try {
            Order::query()
                ->where('company_id', $companyId)
                ->where('customer_phone', (string) $this->option('phone'))
                ->where('status', 'pending')
                ->orderBy('id')
                ->lockForUpdate()
                ->limit(1)
                ->get(['id']);

            DB::rollBack();
            $this->line('  SELECT ... FOR UPDATE con filas (sin COUNT): OK');

            return 0;
        } catch (Throwable $e) {
            DB::rollBack();
            $this->error('  Error SQL: '.$e->getMessage());

            return 1;
        }
    }

    private function checkPendingPhoneGuard(CatalogOrderSubmitGuard $guard): int
    {
        $this->info('CatalogOrderSubmitGuard::assertPendingPhoneLimit');
        $company = Company::query()->first();
        if (! $company) {
            $this->warn('  Sin empresas; se omite.');

            return 0;
        }

        DB::beginTransaction();
        try {
            $guard->assertPendingPhoneLimit($company, (string) $this->option('phone'));
            DB::rollBack();
            $this->line('  Guard ejecutado en transacción revertida: OK');

            return 0;
        } catch (Throwable $e) {
            DB::rollBack();
            if (str_contains($e->getMessage(), 'Ya tenés un pedido pendiente')) {
                $this->line('  Guard respondió límite por teléfono (esperado si hay pending): OK');

                return 0;
            }
            $this->error('  Error: '.$e->getMessage());

            return 1;
        }
    }

    private function checkIpLimitState(): int
    {
        $this->info('Estado rate limit IP (solo lectura caché)');
        $companyId = (int) (Company::query()->value('id') ?? 0);
        if ($companyId === 0) {
            return 0;
        }
        $this->line('  (Los contadores viven en caché; no se modifican en este diagnóstico)');

        return 0;
    }

    private function checkCompanyCatalogReadiness(Company $company): int
    {
        $this->info("Empresa #{$company->id} · {$company->name}");
        $failures = 0;

        $payments = CompanyPaymentMethod::query()
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->count();
        $deliveries = CompanyDeliveryMethod::query()
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->count();
        $pendingOrders = Order::query()->where('company_id', $company->id)->where('status', 'pending')->count();
        $carts = Cart::query()->where('company_id', $company->id)->count();

        $this->line("  Métodos de pago activos: {$payments}");
        $this->line("  Métodos de entrega activos: {$deliveries}");
        $this->line("  Pedidos pending: {$pendingOrders}");
        $this->line("  Carritos: {$carts}");

        if ($payments === 0) {
            $this->warn('  Sin métodos de pago activos: el checkout fallará.');
            $failures++;
        }
        if ($deliveries === 0) {
            $this->warn('  Sin métodos de entrega activos: el checkout fallará.');
            $failures++;
        }

        $phone = (string) $this->option('phone');
        $pendingForPhone = Order::query()
            ->where('company_id', $company->id)
            ->where('customer_phone', $phone)
            ->where('status', 'pending')
            ->count();
        if ($pendingForPhone > 0) {
            $this->warn("  Hay {$pendingForPhone} pedido(s) pending con teléfono {$phone}: no podrás crear otro hasta cancelar o procesar.");
        }

        return $failures;
    }

    private function resolveCompany(): ?Company
    {
        $id = $this->option('company');
        if ($id !== null && $id !== '') {
            return Company::query()->find((int) $id);
        }

        return Company::query()->orderBy('id')->first();
    }
}
