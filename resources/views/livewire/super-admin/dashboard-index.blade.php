<div class="space-y-6">
    <div class="ui-panel">
        <div class="ui-panel__header flex items-center justify-between">
            <div>
                <h1 class="ui-panel__title">Panel Super Admin</h1>
                <p class="ui-panel__subtitle">Visión global del sistema y todas las empresas.</p>
            </div>
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

    {{-- Gráfico de Ingresos Mensuales --}}
    <div class="ui-panel">
        <div class="ui-panel__header">
            <h2 class="ui-panel__title">Ingresos Mensuales</h2>
            <p class="ui-panel__subtitle">Últimos 6 meses de pagos confirmados.</p>
        </div>
        <div class="ui-panel__body">
            <canvas id="monthlyRevenueChart" height="80"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
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
                                    <th>Plan</th>
                                    <th>Monto</th>
                                    <th>Vencimiento</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($upcomingPayments as $payment)
                                    <tr>
                                        <td class="font-medium text-white">{{ $payment['company_name'] }}</td>
                                        <td class="text-sm text-slate-300">{{ $payment['plan_name'] }}</td>
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

        {{-- Top 10 Empresas por Facturación --}}
        <div class="ui-panel overflow-hidden">
            <div class="ui-panel__header">
                <h2 class="ui-panel__title">Top Empresas por Facturación</h2>
                <p class="ui-panel__subtitle">Las que más facturan este mes.</p>
            </div>
            <div class="ui-panel__body p-0">
                @if (empty($topCompanies))
                    <p class="px-4 py-8 text-center text-sm text-slate-400">Sin datos de facturación.</p>
                @else
                    <div class="ui-table-wrap border-0 rounded-none">
                        <table class="ui-table ui-table--nowrap-actions">
                            <thead>
                                <tr>
                                    <th class="w-12">#</th>
                                    <th>Empresa</th>
                                    <th class="text-right">Facturación (mes)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($topCompanies as $index => $company)
                                    <tr>
                                        <td class="text-sm text-slate-400">{{ $index + 1 }}</td>
                                        <td>
                                            <a href="{{ route('super-admin.companies.show', $company['id']) }}" class="font-medium text-cyan-400 hover:text-cyan-300">
                                                {{ $company['name'] }}
                                            </a>
                                        </td>
                                        <td class="text-right font-medium text-white">$ {{ number_format($company['total_revenue'], 2) }}</td>
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
