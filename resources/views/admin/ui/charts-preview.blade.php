@extends('layouts.app')

@section('title', 'Charts Experience Preview')

@section('content')
    <div class="space-y-6">
        <div class="ui-panel">
            <div class="ui-panel__header">
                <div>
                    <h1 class="ui-panel__title">Analytics Futurista</h1>
                    <p class="ui-panel__subtitle">Preview de graficas modernas, animadas y versatiles para todo el sistema.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="ui-btn ui-btn-ghost chart-range-btn is-active" data-range="7d">7 dias</button>
                    <button type="button" class="ui-btn ui-btn-ghost chart-range-btn" data-range="30d">30 dias</button>
                    <button type="button" class="ui-btn ui-btn-ghost chart-range-btn" data-range="90d">90 dias</button>
                </div>
            </div>
            <div class="ui-panel__body">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div class="ui-widget ui-widget--info">
                        <div class="ui-widget__top">
                            <span class="ui-widget__icon"><i class="fas fa-wallet"></i></span>
                            <span class="ui-widget__trend"><i class="fas fa-arrow-trend-up"></i>+14.2%</span>
                        </div>
                        <p class="ui-widget__label">Ingresos</p>
                        <p class="ui-widget__value" id="kpi-revenue">$12,480</p>
                        <p class="ui-widget__meta">Crecimiento semanal sostenido</p>
                    </div>
                    <div class="ui-widget ui-widget--success">
                        <div class="ui-widget__top">
                            <span class="ui-widget__icon"><i class="fas fa-cart-shopping"></i></span>
                            <span class="ui-widget__trend"><i class="fas fa-check"></i>Estable</span>
                        </div>
                        <p class="ui-widget__label">Ventas cerradas</p>
                        <p class="ui-widget__value" id="kpi-sales">318</p>
                        <p class="ui-widget__meta">Ticket promedio $39.2</p>
                    </div>
                    <div class="ui-widget ui-widget--warning">
                        <div class="ui-widget__top">
                            <span class="ui-widget__icon"><i class="fas fa-user-plus"></i></span>
                            <span class="ui-widget__trend"><i class="fas fa-bolt"></i>17.9%</span>
                        </div>
                        <p class="ui-widget__label">Clientes nuevos</p>
                        <p class="ui-widget__value" id="kpi-customers">84</p>
                        <p class="ui-widget__meta">Conversion 17.9%</p>
                    </div>
                    <div class="ui-widget ui-widget--danger">
                        <div class="ui-widget__top">
                            <span class="ui-widget__icon"><i class="fas fa-chart-pie"></i></span>
                            <span class="ui-widget__trend"><i class="fas fa-gauge-high"></i>Meta</span>
                        </div>
                        <p class="ui-widget__label">Margen neto</p>
                        <p class="ui-widget__value" id="kpi-margin">28.6%</p>
                        <p class="ui-widget__meta">Meta mensual 25%</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div class="ui-panel">
                <div class="ui-panel__header">
                    <div>
                        <h2 class="ui-panel__title">Tendencia de ventas</h2>
                        <p class="ui-panel__subtitle">Linea suavizada con doble dataset y animacion progresiva.</p>
                    </div>
                </div>
                <div class="ui-panel__body">
                    <div class="h-80">
                        <canvas id="salesTrendChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="ui-panel">
                <div class="ui-panel__header">
                    <div>
                        <h2 class="ui-panel__title">Canales de venta</h2>
                        <p class="ui-panel__subtitle">Distribucion visual para detectar foco comercial.</p>
                    </div>
                </div>
                <div class="ui-panel__body">
                    <div class="h-80">
                        <canvas id="salesChannelChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div class="ui-panel">
                <div class="ui-panel__header">
                    <div>
                        <h2 class="ui-panel__title">Top categorias</h2>
                        <p class="ui-panel__subtitle">Barras comparativas con hover neon.</p>
                    </div>
                </div>
                <div class="ui-panel__body">
                    <div class="h-80">
                        <canvas id="categoryBarChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="ui-panel">
                <div class="ui-panel__header">
                    <div>
                        <h2 class="ui-panel__title">Radar de salud operativa</h2>
                        <p class="ui-panel__subtitle">Vision integral de rendimiento por dimensiones clave.</p>
                    </div>
                </div>
                <div class="ui-panel__body">
                    <div class="h-80">
                        <canvas id="healthRadarChart"></canvas>
                    </div>
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

            const palette = {
                cyan: '#22d3ee',
                blue: '#60a5fa',
                indigo: '#6366f1',
                purple: '#a78bfa',
                green: '#34d399',
                rose: '#fb7185',
                amber: '#fbbf24',
                slate: '#334155',
            };

            const datasetsByRange = {
                '7d': {
                    labels: ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'],
                    revenue: [1180, 1320, 1090, 1470, 1610, 1750, 1660],
                    orders: [42, 49, 38, 55, 61, 68, 63],
                    kpi: {
                        revenue: '$12,480',
                        sales: '318',
                        customers: '84',
                        margin: '28.6%',
                    },
                },
                '30d': {
                    labels: ['S1', 'S2', 'S3', 'S4'],
                    revenue: [8420, 9130, 10080, 11240],
                    orders: [288, 306, 335, 362],
                    kpi: {
                        revenue: '$38,870',
                        sales: '1,291',
                        customers: '301',
                        margin: '27.1%',
                    },
                },
                '90d': {
                    labels: ['Mes 1', 'Mes 2', 'Mes 3'],
                    revenue: [28120, 32490, 35880],
                    orders: [1010, 1180, 1298],
                    kpi: {
                        revenue: '$96,490',
                        sales: '3,488',
                        customers: '892',
                        margin: '26.4%',
                    },
                },
            };

            const baseGrid = {
                color: 'rgba(148, 163, 184, 0.15)',
                drawBorder: false,
            };

            const makeGradient = (ctx, area, from, to) => {
                const gradient = ctx.createLinearGradient(0, area.bottom, 0, area.top);
                gradient.addColorStop(0, from);
                gradient.addColorStop(1, to);
                return gradient;
            };

            const salesTrendCtx = document.getElementById('salesTrendChart').getContext('2d');
            const salesTrendChart = new Chart(salesTrendCtx, {
                type: 'line',
                data: {
                    labels: datasetsByRange['7d'].labels,
                    datasets: [{
                            label: 'Ingresos',
                            data: datasetsByRange['7d'].revenue,
                            borderColor: palette.cyan,
                            borderWidth: 2.5,
                            tension: 0.34,
                            pointRadius: 0,
                            fill: true,
                            backgroundColor: (context) => {
                                const {
                                    chart
                                } = context;
                                const {
                                    ctx,
                                    chartArea
                                } = chart;
                                if (!chartArea) return 'rgba(34, 211, 238, 0.2)';
                                return makeGradient(ctx, chartArea, 'rgba(34, 211, 238, 0.35)', 'rgba(34, 211, 238, 0.02)');
                            },
                        },
                        {
                            label: 'Ordenes',
                            data: datasetsByRange['7d'].orders,
                            borderColor: palette.purple,
                            borderWidth: 2.2,
                            tension: 0.34,
                            pointRadius: 0,
                        },
                    ],
                },
                options: {
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    animation: {
                        duration: 1200,
                        easing: 'easeOutQuart',
                    },
                    scales: {
                        x: {
                            grid: baseGrid,
                        },
                        y: {
                            grid: baseGrid,
                        },
                    },
                    plugins: {
                        legend: {
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        }
                    },
                },
            });

            const salesChannelCtx = document.getElementById('salesChannelChart').getContext('2d');
            const salesChannelChart = new Chart(salesChannelCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Presencial', 'Delivery', 'WhatsApp', 'Web'],
                    datasets: [{
                        data: [44, 24, 21, 11],
                        backgroundColor: [palette.cyan, palette.indigo, palette.green, palette.rose],
                        borderColor: 'rgba(15, 23, 42, 0.9)',
                        borderWidth: 3,
                        hoverOffset: 8,
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    cutout: '62%',
                    animation: {
                        duration: 1300
                    },
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

            const categoryBarCtx = document.getElementById('categoryBarChart').getContext('2d');
            const categoryBarChart = new Chart(categoryBarCtx, {
                type: 'bar',
                data: {
                    labels: ['Bebidas', 'Snacks', 'Limpieza', 'Cuidado', 'Farmacia'],
                    datasets: [{
                        label: 'Ventas por categoria',
                        data: [182, 156, 143, 127, 119],
                        borderRadius: 10,
                        borderSkipped: false,
                        backgroundColor: [palette.cyan, palette.blue, palette.indigo, palette.purple, palette.green],
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    animation: {
                        duration: 1100
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            grid: baseGrid
                        },
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                },
            });

            const healthRadarCtx = document.getElementById('healthRadarChart').getContext('2d');
            const healthRadarChart = new Chart(healthRadarCtx, {
                type: 'radar',
                data: {
                    labels: ['Ventas', 'Cobranza', 'Inventario', 'Satisfaccion', 'Tiempos', 'Calidad'],
                    datasets: [{
                        label: 'Rendimiento',
                        data: [89, 76, 82, 91, 73, 88],
                        borderColor: palette.amber,
                        backgroundColor: 'rgba(251, 191, 36, 0.18)',
                        pointBackgroundColor: palette.amber,
                        pointRadius: 3,
                        borderWidth: 2,
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    animation: {
                        duration: 1400
                    },
                    scales: {
                        r: {
                            angleLines: {
                                color: 'rgba(148, 163, 184, 0.2)'
                            },
                            grid: {
                                color: 'rgba(148, 163, 184, 0.18)'
                            },
                            pointLabels: {
                                color: '#cbd5e1'
                            },
                            ticks: {
                                display: false
                            },
                        },
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                },
            });

            const updateKpis = (kpi) => {
                document.getElementById('kpi-revenue').textContent = kpi.revenue;
                document.getElementById('kpi-sales').textContent = kpi.sales;
                document.getElementById('kpi-customers').textContent = kpi.customers;
                document.getElementById('kpi-margin').textContent = kpi.margin;
            };

            const updateRange = (range) => {
                const payload = datasetsByRange[range];
                if (!payload) return;

                salesTrendChart.data.labels = payload.labels;
                salesTrendChart.data.datasets[0].data = payload.revenue;
                salesTrendChart.data.datasets[1].data = payload.orders;
                salesTrendChart.update();
                updateKpis(payload.kpi);
            };

            document.querySelectorAll('.chart-range-btn').forEach((button) => {
                button.addEventListener('click', () => {
                    document.querySelectorAll('.chart-range-btn').forEach((btn) => btn.classList.remove('is-active'));
                    button.classList.add('is-active');
                    updateRange(button.dataset.range);
                });
            });
        });
    </script>
@endpush
