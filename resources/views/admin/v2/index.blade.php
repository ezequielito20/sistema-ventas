@extends('layouts.app')

@section('title', 'Dashboard v2')

@section('content')
    <div class="space-y-6">
        <div class="ui-panel">
            <div class="ui-panel__header">
                <div>
                    <h1 class="ui-panel__title">Dashboard Ejecutivo v2</h1>
                    <p class="ui-panel__subtitle">Nueva vista paralela, moderna y desacoplada de la versión legacy.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="ui-badge ui-badge-success">Live</span>
                    <span class="ui-badge ui-badge-warning">{{ now()->format('d/m/Y H:i') }}</span>
                </div>
            </div>
            <div class="ui-panel__body">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div class="ui-widget ui-widget--info">
                        <div class="ui-widget__top">
                            <span class="ui-widget__icon"><i class="fas fa-wallet"></i></span>
                            <span class="ui-widget__trend"><i class="fas fa-arrow-trend-up"></i> Hoy</span>
                        </div>
                        <p class="ui-widget__label">Ventas del dia</p>
                        <p class="ui-widget__value">{{ $currency->symbol }}{{ number_format($todaySales ?? 0, 2) }}</p>
                        <p class="ui-widget__meta">Semanal: {{ $currency->symbol }}{{ number_format($weeklySales ?? 0, 2) }}</p>
                    </div>

                    <div class="ui-widget ui-widget--success">
                        <div class="ui-widget__top">
                            <span class="ui-widget__icon"><i class="fas fa-users"></i></span>
                            <span class="ui-widget__trend"><i class="fas fa-check"></i> Clientes</span>
                        </div>
                        <p class="ui-widget__label">Clientes totales</p>
                        <p class="ui-widget__value">{{ number_format($totalCustomers ?? 0) }}</p>
                        <p class="ui-widget__meta">Verificados: {{ number_format($verifiedCustomers ?? 0) }}</p>
                    </div>

                    <div class="ui-widget ui-widget--warning">
                        <div class="ui-widget__top">
                            <span class="ui-widget__icon"><i class="fas fa-boxes-stacked"></i></span>
                            <span class="ui-widget__trend"><i class="fas fa-triangle-exclamation"></i> Stock</span>
                        </div>
                        <p class="ui-widget__label">Productos</p>
                        <p class="ui-widget__value">{{ number_format($productsCount ?? 0) }}</p>
                        <p class="ui-widget__meta">Bajo stock: {{ number_format($lowStockCount ?? 0) }}</p>
                    </div>

                    <div class="ui-widget ui-widget--danger">
                        <div class="ui-widget__top">
                            <span class="ui-widget__icon"><i class="fas fa-chart-pie"></i></span>
                            <span class="ui-widget__trend"><i class="fas fa-bolt"></i> Margen</span>
                        </div>
                        <p class="ui-widget__label">Ganancia teorica</p>
                        <p class="ui-widget__value">{{ $currency->symbol }}{{ number_format($totalProfit ?? 0, 2) }}</p>
                        <p class="ui-widget__meta">Mes: {{ $currency->symbol }}{{ number_format($monthlySales ?? 0, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div class="ui-panel">
                <div class="ui-panel__header">
                    <div>
                        <h2 class="ui-panel__title">Tendencia mensual</h2>
                        <p class="ui-panel__subtitle">Ventas y ganancia de los ultimos meses.</p>
                    </div>
                </div>
                <div class="ui-panel__body">
                    <div class="h-80">
                        <canvas id="dashboardV2SalesChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="ui-panel">
                <div class="ui-panel__header">
                    <div>
                        <h2 class="ui-panel__title">Ventas por categoria</h2>
                        <p class="ui-panel__subtitle">Distribucion comercial principal.</p>
                    </div>
                </div>
                <div class="ui-panel__body">
                    <div class="h-80">
                        <canvas id="dashboardV2CategoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="ui-panel">
            <div class="ui-panel__header">
                <div>
                    <h2 class="ui-panel__title">Top productos vendidos</h2>
                    <p class="ui-panel__subtitle">Vista optimizada con estilo unificado v2.</p>
                </div>
            </div>
            <div class="ui-panel__body">
                @php
                    $topProductsV2 = collect($topSellingProducts ?? []);
                @endphp
                <div class="ui-table-wrap">
                    <table class="ui-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Veces vendido</th>
                                <th>Cantidad</th>
                                <th>Precio</th>
                                <th>Ingresos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($topProductsV2 as $product)
                                <tr>
                                    <td>{{ data_get($product, 'name', 'N/A') }}</td>
                                    <td>{{ number_format((float) data_get($product, 'times_sold', 0)) }}</td>
                                    <td>{{ number_format((float) data_get($product, 'total_quantity', 0)) }}</td>
                                    <td>{{ $currency->symbol }}{{ number_format((float) data_get($product, 'sale_price', 0), 2) }}</td>
                                    <td>{{ $currency->symbol }}{{ number_format((float) data_get($product, 'total_revenue', 0), 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">Sin datos de productos vendidos.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof Chart === 'undefined') return;

            Chart.defaults.color = '#cbd5e1';
            Chart.defaults.font.family = "'Inter', 'Nunito', system-ui, sans-serif";

            const salesCtx = document.getElementById('dashboardV2SalesChart')?.getContext('2d');
            const categoryCtx = document.getElementById('dashboardV2CategoryChart')?.getContext('2d');
            if (!salesCtx || !categoryCtx) return;

            new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: @json($salesMonthlyLabels ?? []),
                    datasets: [{
                            label: 'Ventas',
                            data: @json($salesMonthlyData ?? []),
                            borderColor: '#22d3ee',
                            backgroundColor: 'rgba(34, 211, 238, 0.2)',
                            fill: true,
                            tension: 0.35,
                            pointRadius: 0,
                        },
                        {
                            label: 'Ganancia',
                            data: @json($profitMonthlyData ?? []),
                            borderColor: '#a78bfa',
                            backgroundColor: 'rgba(167, 139, 250, 0.12)',
                            fill: true,
                            tension: 0.35,
                            pointRadius: 0,
                        }
                    ],
                },
                options: {
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            labels: {
                                usePointStyle: true
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                color: 'rgba(148, 163, 184, 0.12)'
                            }
                        },
                        y: {
                            grid: {
                                color: 'rgba(148, 163, 184, 0.12)'
                            }
                        },
                    },
                },
            });

            new Chart(categoryCtx, {
                type: 'doughnut',
                data: {
                    labels: @json($salesByCategoryLabels ?? []),
                    datasets: [{
                        data: @json($salesByCategoryData ?? []),
                        backgroundColor: ['#22d3ee', '#60a5fa', '#6366f1', '#a78bfa', '#34d399', '#fb7185'],
                        borderColor: 'rgba(15, 23, 42, 0.8)',
                        borderWidth: 2,
                        hoverOffset: 8,
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    cutout: '58%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true
                            }
                        }
                    },
                },
            });
        });
    </script>
@endpush
