<div class="space-y-6">
    <div class="ui-panel">
        <div class="ui-panel__header flex items-center justify-between">
            <div>
                <h1 class="ui-panel__title">Panel Super Admin</h1>
                <p class="ui-panel__subtitle">Visión global del sistema y todas las empresas.</p>
            </div>
            <a href="{{ route('super-admin.companies.create') }}" class="ui-btn ui-btn-primary text-sm" wire:navigate>
                <i class="fas fa-plus"></i> Nuevo cliente
            </a>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 gap-2 xs:gap-3 md:grid-cols-3 xl:grid-cols-6">
        <x-ui.stat-card variant="info" icon="fas fa-building" trend="Total" label="Empresas" :value="number_format($stats['total_companies'])" meta="Registradas" />
        <x-ui.stat-card variant="success" icon="fas fa-check-circle" trend="Activas" label="Activas" :value="number_format($stats['active_subscriptions'])" meta="Al día" />
        <x-ui.stat-card variant="warning" icon="fas fa-clock" trend="Gracia" label="En Gracia" :value="number_format($stats['grace_subscriptions'])" meta="Período de gracia" />
        <x-ui.stat-card variant="danger" icon="fas fa-ban" trend="Suspendidas" label="Suspendidas" :value="number_format($stats['suspended_subscriptions'])" meta="Sin servicio" />
        <x-ui.stat-card variant="neutral" icon="fas fa-dollar-sign" trend="MRR" label="MRR" :value="'$ ' . number_format($stats['mrr'], 2)" meta="Ingreso recurrente" />
        <x-ui.stat-card variant="warning" icon="fas fa-exclamation-circle" trend="Pendiente" label="Por Cobrar" :value="'$ ' . number_format($stats['pending_payments_amount'] + $stats['overdue_payments_amount'], 2)" meta="{{ $stats['pending_payments_count'] + $stats['overdue_payments_count'] }} facturas" />
    </div>

    {{-- Tabla Principal: Todas las Empresas --}}
    <div class="ui-panel overflow-hidden">
        <div class="ui-panel__header">
            <div class="flex w-full flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="shrink-0">
                    <h2 class="ui-panel__title">Empresas</h2>
                    <p class="ui-panel__subtitle">{{ $companies->total() }} resultado(s) · Página {{ $companies->currentPage() }} de {{ $companies->lastPage() }}</p>
                </div>
                <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center lg:w-auto">
                    <div class="relative min-w-[16rem] flex-1 lg:min-w-[18rem]">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                            <i class="fas fa-search text-xs"></i>
                        </span>
                        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Buscar nombre, NIT o email..." class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 pl-9 pr-3 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500" />
                    </div>
                    <select wire:model.live="statusFilter" class="rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500">
                        <option value="">Todos los estados</option>
                        <option value="active">Activas</option>
                        <option value="trial">Trial</option>
                        <option value="suspended">Suspendidas</option>
                    </select>
                    <button type="button" wire:click="clearFilters" class="ui-btn ui-btn-ghost text-sm" title="Limpiar">
                        <i class="fas fa-eraser"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="ui-panel__body p-0">
            @if ($companies->isEmpty())
                <p class="px-4 py-10 text-center text-sm text-slate-400">No hay empresas que coincidan con los filtros.</p>
            @else
                <div class="ui-table-wrap border-0 rounded-none">
                    <table class="ui-table ui-table--nowrap-actions">
                        <thead>
                            <tr>
                                <th>Empresa</th>
                                <th>Plan</th>
                                <th class="text-center">Estado</th>
                                <th>Usuarios</th>
                                <th>Ventas</th>
                                <th>Próx. Cobro</th>
                                <th class="text-left">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($companies as $company)
                                <tr wire:key="dash-company-row-{{ $company->id }}">
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-cyan-500/15 text-sm font-semibold uppercase text-cyan-200">
                                                {{ mb_substr($company->name, 0, 2) }}
                                            </div>
                                            <div>
                                                <a href="{{ route('super-admin.companies.show', $company->id) }}" class="font-medium text-cyan-400 hover:text-cyan-300" wire:navigate>
                                                    {{ $company->name }}
                                                </a>
                                                <p class="text-xs text-slate-400">{{ $company->email }} · NIT {{ $company->nit ?? '—' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-sm text-slate-300">{{ $company->subscription?->plan?->name ?? 'Sin plan' }}</td>
                                    <td class="text-center">
                                        @if ($company->subscription_status === 'active')
                                            <span class="ui-badge ui-badge-success">Activo</span>
                                        @elseif ($company->subscription_status === 'trial')
                                            <span class="ui-badge ui-badge-info">Trial</span>
                                        @elseif ($company->subscription_status === 'suspended')
                                            <span class="ui-badge ui-badge-danger">Suspendido</span>
                                        @else
                                            <span class="ui-badge ui-badge-warning">{{ $company->subscription_status }}</span>
                                        @endif
                                    </td>
                                    <td class="text-sm text-slate-300">{{ $company->users_count }}</td>
                                    <td class="text-sm text-slate-300">{{ $company->sales_count }}</td>
                                    <td class="text-sm text-slate-300">
                                        @if ($company->subscription?->next_billing_date)
                                            {{ $company->subscription->next_billing_date->format('d/m/Y') }}
                                        @else
                                            <span class="text-slate-500">—</span>
                                        @endif
                                    </td>
                                    <td class="text-left">
                                        <div class="ui-icon-action-row flex flex-nowrap items-center justify-start gap-1.5 md:gap-2">
                                            <a href="{{ route('super-admin.companies.show', $company->id) }}" class="ui-icon-action ui-icon-action--primary" title="Gestionar" wire:navigate>
                                                <i class="fas fa-cog"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="ui-panel__body border-t border-slate-700/50">
            <x-ui.pagination :paginator="$companies" scrollIntoView=".ui-panel" />
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        {{-- Ingresos Mensuales (más chico) --}}
        <div class="ui-panel xl:col-span-2">
            <div class="ui-panel__header">
                <h2 class="ui-panel__title">Ingresos Mensuales</h2>
                <p class="ui-panel__subtitle">Últimos 6 meses de pagos confirmados.</p>
            </div>
            <div class="ui-panel__body">
                <canvas id="monthlyRevenueChart" height="70"></canvas>
            </div>
        </div>

        {{-- Próximos Vencimientos --}}
        <div class="ui-panel overflow-hidden">
            <div class="ui-panel__header">
                <h2 class="ui-panel__title">Próximos Vencimientos</h2>
                <p class="ui-panel__subtitle">Pagos pendientes en los próximos 7 días.</p>
            </div>
            <div class="ui-panel__body p-0">
                @if (empty($upcomingPayments))
                    <p class="px-4 py-8 text-center text-sm text-slate-400">No hay pagos pendientes próximos.</p>
                @else
                    <div class="ui-table-wrap border-0 rounded-none">
                        <table class="ui-table ui-table--nowrap-actions">
                            <thead>
                                <tr>
                                    <th>Empresa</th>
                                    <th>Monto</th>
                                    <th>Venc.</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($upcomingPayments as $payment)
                                    <tr>
                                        <td class="font-medium text-white">{{ $payment['company_name'] }}</td>
                                        <td class="text-sm text-slate-300">$ {{ number_format($payment['amount'], 2) }}</td>
                                        <td>
                                            <span @class([
                                                'ui-badge ui-badge-danger' => $payment['is_overdue'],
                                                'ui-badge ui-badge-warning' => !$payment['is_overdue'],
                                            ])>
                                                {{ $payment['due_date'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('monthlyRevenueChart');
        if (ctx && typeof Chart !== 'undefined') {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json(array_column($monthlyRevenue, 'month')),
                    datasets: [{
                        label: 'Ingresos (USD)',
                        data: @json(array_column($monthlyRevenue, 'revenue')),
                        backgroundColor: 'rgba(6, 182, 212, 0.5)',
                        borderColor: 'rgba(6, 182, 212, 1)',
                        borderWidth: 1.5,
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { color: '#94a3b8' },
                            grid: { color: 'rgba(148,163,184,0.1)' }
                        },
                        x: {
                            ticks: { color: '#94a3b8' },
                            grid: { display: false }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
