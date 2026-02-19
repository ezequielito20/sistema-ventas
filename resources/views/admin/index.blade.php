@extends('layouts.app')

@section('title', 'Inicio')

@section('content_header')
    <!-- Hero Section with Modern Design -->
    <div class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 rounded-3xl p-8 mb-8 text-white shadow-2xl border border-purple-500/20"
        x-data="{
            currentTime: '{{ date('H:i') }}',
            currentDate: '{{ date('d/m/Y') }}',
            updateTime() {
                const now = new Date();
                this.currentTime = now.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit' });
                this.currentDate = now.toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric' });
            }
        }" x-init="setInterval(updateTime, 60000)">

        <!-- Animated Background -->
        <div class="absolute inset-0">
            <div class="absolute inset-0 bg-gradient-to-r from-purple-600/20 to-pink-600/20"></div>
            <div class="absolute top-0 left-0 w-72 h-72 bg-purple-500/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 right-0 w-72 h-72 bg-pink-500/10 rounded-full blur-3xl"></div>
        </div>

        <!-- Content -->
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">
            <!-- Title Section -->
            <div class="flex-1">
                <div class="flex items-center gap-4 mb-4">
                    <div
                        class="flex items-center justify-center w-20 h-20 bg-gradient-to-br from-purple-500 to-pink-500 rounded-3xl shadow-2xl border border-white/20">
                        <i class="fas fa-rocket text-3xl text-white"></i>
                    </div>
                    <div>
                        <h1
                            class="text-5xl lg:text-6xl font-black bg-gradient-to-r from-white to-purple-200 bg-clip-text text-transparent">
                            Dashboard Ejecutivo
                        </h1>
                        <p class="text-xl lg:text-2xl text-purple-200 font-medium">Panel de control inteligente</p>
                    </div>
                </div>
                <p class="text-lg text-purple-100 max-w-2xl">
                    Monitoreo en tiempo real de ventas, inventario y rendimiento empresarial con análisis predictivo
                    avanzado.
                </p>
            </div>

            <!-- Live Stats Section -->
            <div class="flex flex-col gap-4">
                <!-- Time Card -->
                <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20 shadow-xl">
                    <div class="text-center">
                        <div class="text-4xl font-black text-white mb-2" x-text="currentTime"></div>
                        <div class="text-purple-200 font-medium">Hora Actual</div>
                    </div>
                </div>

                <!-- Date Card -->
                <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20 shadow-xl">
                    <div class="text-center">
                        <div class="text-4xl font-black text-white mb-2" x-text="currentDate"></div>
                        <div class="text-purple-200 font-medium">Fecha Actual</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
    {{-- Sección de Arqueo de Caja --}}
    <div class="mb-16" x-data="{
        cashDataMode: 'current',
        currentCashData: @js($currentCashData ?? []),
        historicalData: @js($historicalData ?? []),
        closedCashCountsData: @js($closedCashCountsData ?? []),
    
        formatCurrency(amount) {
            return '{{ $currency->symbol }}' + parseFloat(amount || 0).toLocaleString('es-PE', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        },
        updateCashWidgets(selectedMode) {
            let selectedData = {};
            if (selectedMode === 'current') {
                selectedData = this.currentCashData;
            } else if (selectedMode === 'historical') {
                selectedData = this.historicalData;
            } else if (selectedMode.startsWith('cash_')) {
                const cashCountId = selectedMode.replace('cash_', '');
                selectedData = this.closedCashCountsData.find(item => item.id == cashCountId) || {};
            }
    
            // Actualizar widgets de arqueo de caja
            const balanceElement = document.querySelector('.widget-balance .widget-value');
            const salesElement = document.querySelector('.widget-sales .widget-value');
            const salesSubtitleElement = document.querySelector('.widget-sales .widget-subtitle');
            const debtElement = document.querySelector('.widget-debt .widget-value');
            const paymentsElement = document.querySelector('.widget-payments .widget-value');
    
            if (balanceElement) {
                balanceElement.textContent = this.formatCurrency(selectedData.balance || 0);
            }
            if (salesElement) {
                salesElement.textContent = this.formatCurrency(selectedData.sales || 0);
            }
            if (salesSubtitleElement) {
                salesSubtitleElement.textContent = 'Compras: ' + this.formatCurrency(selectedData.purchases || 0);
            }
            if (debtElement) {
                debtElement.textContent = this.formatCurrency(selectedData.debt || 0);
            }
            if (paymentsElement) {
                paymentsElement.textContent = this.formatCurrency(selectedData.debt_payments || 0);
            }
        }
    }" x-init="// Inicializar widgets con datos por defecto (Arqueo Actual)
    $nextTick(() => {
        updateCashWidgets('current');
    });
    
    // Listener específico para arqueo de caja
    window.addEventListener('cashDataModeChanged', function(event) {
        Alpine.store('cashDataMode', event.detail.value);
        Alpine.$data(document.querySelector('[x-data*=\'cashDataMode\']')).updateCashWidgets(event.detail.value);
    });">

        <!-- Section Header -->
        <x-section-header title="Arqueo de Caja" subtitle="Control financiero y gestión de efectivo"
            icon="fas fa-cash-register" iconBg="from-emerald-500 to-teal-600" statusIcon="fas fa-check"
            statusText="Caja Abierta" statusColor="green" dataMode="cashDataMode" :dataOptions="[]" :cashCounts="$closedCashCountsData"
            :showDataSelector="true" :showStatus="true" :showLastUpdate="true" sectionId="cash-section" />

        <!-- Ultra Simple Mini Widgets Grid - Single Row Responsive -->
        <div
            class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-4 gap-2 sm:gap-3 mb-6 items-stretch">




            <!-- Widget de Balance General -->
            <div class="widget-balance">
                <x-dashboard-widget title="Balance Actual" value="0" icon="fas fa-balance-scale" trend="+12.5%"
                    trendIcon="fas fa-trending-up" trendColor="text-green-300" subtitle="Período seleccionado"
                    subtitleIcon="fas fa-clock" gradientFrom="from-blue-500" gradientTo="to-indigo-600" progressWidth="85%"
                    progressGradientFrom="from-blue-400" progressGradientTo="to-indigo-400" />
            </div>

            <!-- Widget de Ventas desde Apertura -->
            <div class="widget-sales">
                <x-dashboard-widget title="Ventas del Período" value="{{ $monthlySales }}" icon="fas fa-chart-line"
                    trend="+18.2%" trendIcon="fas fa-rocket" trendColor="text-green-300"
                    subtitle="Compras: {{ $currency->symbol }}{{ number_format($monthlyPurchases, 2) }}"
                    subtitleIcon="fas fa-shopping-cart" gradientFrom="from-emerald-500" gradientTo="to-teal-600"
                    progressWidth="72%" progressGradientFrom="from-emerald-400" progressGradientTo="to-teal-400"
                    currencySymbol="{{ $currency->symbol }}" />
            </div>

            <!-- Widget de Deudas Dinámico -->
            <div class="widget-debt">
                <x-dashboard-widget title="Por Cobrar" value="0" icon="fas fa-hourglass-half" trend="Pendiente"
                    trendIcon="fas fa-exclamation-triangle" trendColor="text-yellow-300" subtitle="Deudas pendientes"
                    subtitleIcon="fas fa-clock" gradientFrom="from-yellow-500" gradientTo="to-orange-500"
                    progressWidth="45%" progressGradientFrom="from-yellow-400" progressGradientTo="to-orange-400" />
            </div>

            <!-- Widget de Pagos de Deuda -->
            <div class="widget-payments">
                <x-dashboard-widget title="Pagos de Deuda" value="0" icon="fas fa-hand-holding-usd" trend="Recibidos"
                    trendIcon="fas fa-check-circle" trendColor="text-green-300" subtitle="Este período"
                    subtitleIcon="fas fa-calendar-check" gradientFrom="from-purple-500" gradientTo="to-pink-600"
                    progressWidth="68%" progressGradientFrom="from-purple-400" progressGradientTo="to-pink-400" />
            </div>
        </div>
    </div>

    {{-- Sección de Ventas --}}
    <div class="mb-16" x-data="{
        salesDataMode: 'current',
        currentSalesData: @js($currentSalesData ?? []),
        historicalSalesData: @js($historicalSalesData ?? []),
        closedSalesData: @js($closedSalesData ?? []),
    
        formatCurrency(amount) {
            return '{{ $currency->symbol }}' + parseFloat(amount || 0).toLocaleString('es-PE', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        },
        updateSalesWidgets(selectedMode) {
            let selectedData = {};
            if (selectedMode === 'current') {
                selectedData = this.currentSalesData;
            } else if (selectedMode === 'historical') {
                selectedData = this.historicalSalesData;
            } else if (selectedMode.startsWith('cash_')) {
                const cashCountId = selectedMode.replace('cash_', '');
                selectedData = this.closedSalesData.find(item => item.id == cashCountId) || {};
            }
    
            // Actualizar widgets de análisis de ventas
            const weeklySalesElement = document.querySelector('.widget-weekly-sales .widget-value');
            const averageCustomerElement = document.querySelector('.widget-average-customer .widget-value');
            const totalProfitElement = document.querySelector('.widget-total-profit .widget-value');
            const monthlyPerformanceElement = document.querySelector('.widget-monthly-performance .widget-value');
    
            if (weeklySalesElement) {
                weeklySalesElement.textContent = this.formatCurrency(selectedData.weekly_sales || 0);
            }
            if (averageCustomerElement) {
                averageCustomerElement.textContent = this.formatCurrency(selectedData.average_customer_spend || 0);
            }
            if (totalProfitElement) {
                totalProfitElement.textContent = this.formatCurrency(selectedData.total_profit || 0);
            }
            if (monthlyPerformanceElement) {
                monthlyPerformanceElement.textContent = this.formatCurrency(selectedData.monthly_sales || 0);
            }
        }
    }" x-init="// Inicializar widgets con datos por defecto (Arqueo Actual)
    $nextTick(() => {
        updateSalesWidgets('current');
    });
    
    // Listener específico para análisis de ventas
    window.addEventListener('salesDataModeChanged', function(event) {
        Alpine.store('salesDataMode', event.detail.value);
        Alpine.$data(document.querySelector('[x-data*=\'salesDataMode\']')).updateSalesWidgets(event.detail.value);
    });">

        <!-- Section Header -->
        <x-section-header title="Análisis de Ventas" subtitle="Métricas y rendimiento comercial" icon="fas fa-chart-line"
            iconBg="from-violet-500 to-purple-600" statusIcon="fas fa-trending-up" statusText="Datos en Tiempo Real"
            statusColor="purple" dataMode="salesDataMode" :dataOptions="[]" :cashCounts="$closedCashCountsData" :showDataSelector="true"
            :showStatus="true" :showLastUpdate="true" :refreshButton="true" refreshButtonText="Actualizar Datos"
            refreshButtonIcon="fas fa-sync-alt" sectionId="sales-section" />

        <!-- Ultra Simple Mini Sales Widgets Grid - Single Row Responsive -->
        <div
            class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-4 gap-2 sm:gap-3 mb-6 items-stretch">
            <!-- Widget de Ventas de la Semana -->
            <div class="widget-weekly-sales">
                <x-dashboard-widget title="Ventas de la Semana" value="{{ $weeklySales }}" valueType="currency"
                    currencySymbol="{{ $currency->symbol }}" icon="fas fa-calendar-week" trend="+12%"
                    trendIcon="fas fa-trending-up" trendColor="text-green-300"
                    subtitle="Hoy: {{ $currency->symbol }}{{ number_format($todaySales, 2) }}"
                    subtitleIcon="fas fa-calendar-day" gradientFrom="from-violet-500" gradientTo="to-purple-600"
                    progressWidth="78%" progressGradientFrom="from-violet-400" progressGradientTo="to-purple-400" />
            </div>

            <!-- Widget de Promedio por Cliente -->
            <div class="widget-average-customer">
                <x-dashboard-widget title="Promedio por Cliente" value="{{ $averageCustomerSpend }}"
                    valueType="currency" currencySymbol="{{ $currency->symbol }}" icon="fas fa-user-chart"
                    trend="+8%" trendIcon="fas fa-arrow-up" trendColor="text-green-300"
                    subtitle="Ticket promedio de venta" subtitleIcon="fas fa-users" gradientFrom="from-pink-500"
                    gradientTo="to-rose-600" progressWidth="65%" progressGradientFrom="from-pink-400"
                    progressGradientTo="to-rose-400" />
            </div>

            <!-- Widget de Ganancia Teórica -->
            <div class="widget-total-profit">
                <x-dashboard-widget title="Ganancia Total Teórica" value="{{ $totalProfit }}" valueType="currency"
                    currencySymbol="{{ $currency->symbol }}" icon="fas fa-chart-pie" trend="+15%"
                    trendIcon="fas fa-percentage" trendColor="text-green-300" subtitle="Margen de productos vendidos"
                    subtitleIcon="fas fa-coins" gradientFrom="from-cyan-500" gradientTo="to-blue-600"
                    progressWidth="88%" progressGradientFrom="from-cyan-400" progressGradientTo="to-blue-400" />
            </div>

            <!-- Widget de Rendimiento Mensual -->
            <div class="widget-monthly-performance">
                <x-dashboard-widget title="Rendimiento Mensual" value="{{ $monthlySales }}" valueType="currency"
                    currencySymbol="{{ $currency->symbol }}" icon="fas fa-calendar-alt" trend="+22%"
                    trendIcon="fas fa-rocket" trendColor="text-green-300" subtitle="Comparado con mes anterior"
                    subtitleIcon="fas fa-chart-bar" gradientFrom="from-emerald-500" gradientTo="to-teal-600"
                    progressWidth="92%" progressGradientFrom="from-emerald-400" progressGradientTo="to-teal-400" />
            </div>
        </div>
    </div>

    {{-- Gráficos de Tendencias --}}
    <div class="mb-12" x-data="{
        refreshSalesChart() {
                const button = event.target.closest('button');
                const icon = button.querySelector('i');
                icon.classList.add('animate-spin');
                button.disabled = true;
    
                setTimeout(() => {
                    icon.classList.remove('animate-spin');
                    button.disabled = false;
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Gráfico actualizado',
                            text: 'El gráfico de ventas se ha actualizado correctamente',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                }, 1500);
            },
            refreshPurchasesChart() {
                const button = event.target.closest('button');
                const icon = button.querySelector('i');
                icon.classList.add('animate-spin');
                button.disabled = true;
    
                setTimeout(() => {
                    icon.classList.remove('animate-spin');
                    button.disabled = false;
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Gráfico actualizado',
                            text: 'El gráfico de compras se ha actualizado correctamente',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                }, 1500);
            },
            exportSalesChart() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Exportando gráfico',
                        text: 'Preparando exportación del gráfico de ventas...',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            },
            exportPurchasesChart() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Exportando gráfico',
                        text: 'Preparando exportación del gráfico de compras...',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            }
    }">

        <!-- Gráfico de Ventas Mensuales -->
        <div class="mb-8">
            <div class="bg-gradient-to-br from-indigo-100 via-purple-50 to-white rounded-3xl shadow-xl overflow-hidden">
                <!-- Chart Header -->
                <div class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white p-6">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                        <!-- Title -->
                        <div class="flex items-center gap-4">
                            <div
                                class="flex items-center justify-center w-16 h-16 bg-white/20 backdrop-blur-lg rounded-2xl">
                                <i class="fas fa-chart-area text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-2xl lg:text-3xl font-bold">Rendimiento Mensual de Ventas</h3>
                                <p class="text-lg opacity-90">Análisis comparativo de ingresos, ganancias y volumen de
                                    transacciones</p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-col sm:flex-row items-center gap-4">
                            <!-- Stats -->
                            <div class="flex gap-6">
                                <div class="text-center">
                                    <div class="text-sm opacity-80">Promedio</div>
                                    <div class="text-xl font-bold">
                                        {{ $currency->symbol }}{{ number_format(collect($salesMonthlyData)->avg(), 2) }}
                                    </div>
                                </div>
                                <div class="text-center">
                                    <div class="text-sm opacity-80">Máximo</div>
                                    <div class="text-xl font-bold">
                                        {{ $currency->symbol }}{{ number_format(collect($salesMonthlyData)->max(), 2) }}
                                    </div>
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div class="flex gap-2">
                                <button @click="exportSalesChart()"
                                    class="bg-white/20 backdrop-blur-lg text-white px-4 py-2 rounded-xl font-semibold hover:bg-white/30 transition-all duration-300 flex items-center gap-2">
                                    <i class="fas fa-download"></i>
                                    Exportar
                                </button>
                                <button @click="refreshSalesChart()"
                                    class="bg-white/20 backdrop-blur-lg text-white px-4 py-2 rounded-xl font-semibold hover:bg-white/30 transition-all duration-300 flex items-center gap-2">
                                    <i class="fas fa-sync-alt"></i>
                                    Actualizar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chart Content -->
                <div class="p-6">
                    <div class="bg-gray-50 rounded-2xl p-4">
                        <canvas id="salesTrendsChart" class="w-full" style="min-height: 350px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <!-- Gráfico de Compras Mensuales -->
            <div class="xl:col-span-2">
                <div class="bg-white rounded-3xl shadow-xl overflow-hidden h-full">
                    <!-- Chart Header -->
                    <div class="bg-gradient-to-r from-blue-500 to-cyan-600 text-white p-6">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                            <!-- Title -->
                            <div class="flex items-center gap-4">
                                <div
                                    class="flex items-center justify-center w-16 h-16 bg-white/20 backdrop-blur-lg rounded-2xl">
                                    <i class="fas fa-chart-line text-2xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-2xl lg:text-3xl font-bold">Tendencia de Compras Mensuales</h3>
                                    <p class="text-lg opacity-90">Evolución de compras e inventario</p>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-2">
                                <button @click="exportPurchasesChart()"
                                    class="bg-white/20 backdrop-blur-lg text-white px-4 py-2 rounded-xl font-semibold hover:bg-white/30 transition-all duration-300 flex items-center gap-2">
                                    <i class="fas fa-download"></i>
                                    Exportar
                                </button>
                                <button @click="refreshPurchasesChart()"
                                    class="bg-white/20 backdrop-blur-lg text-white px-4 py-2 rounded-xl font-semibold hover:bg-white/30 transition-all duration-300 flex items-center gap-2">
                                    <i class="fas fa-sync-alt"></i>
                                    Actualizar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Chart Content -->
                    <div class="p-6">
                        <div class="bg-gray-50 rounded-2xl p-4">
                            <canvas id="purchaseTrendsChart" class="w-full" style="min-height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>

    {{-- Información de Clientes --}}
    <div class="mb-12" x-data="{
        refreshCustomerData() {
            const button = event.target.closest('button');
            const icon = button.querySelector('i');
            icon.classList.add('animate-spin');
            button.disabled = true;
    
            setTimeout(() => {
                icon.classList.remove('animate-spin');
                button.disabled = false;
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Datos actualizados',
                        text: 'Los datos de clientes se han actualizado correctamente',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            }, 2000);
        }
    }">

        <!-- Section Header -->
        <x-section-header title="Información de Clientes" subtitle="Gestión y análisis de clientes" icon="fas fa-users"
            iconBg="from-cyan-400 to-blue-500" statusIcon="fas fa-users" statusText="Sistema Activo" statusColor="blue"
            :showDataSelector="false" :showStatus="false" :showLastUpdate="false" :refreshButton="true" refreshButtonText="Actualizar"
            refreshButtonIcon="fas fa-sync-alt" />

        <!-- Ultra Simple Mini Customer Widgets Grid - Single Row Responsive -->
        <div
            class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-4 gap-2 sm:gap-3 mb-6 items-stretch">
            <!-- Widget de Total Clientes -->
            <div class="widget-total-customers">
                <x-dashboard-widget title="Total Clientes" value="{{ $totalCustomers }}" valueType="number"
                    icon="fas fa-users" trend="{{ $customerGrowth >= 0 ? '+' : '' }}{{ $customerGrowth }}%"
                    trendIcon="fas fa-arrow-{{ $customerGrowth >= 0 ? 'up' : 'down' }}"
                    trendColor="{{ $customerGrowth >= 0 ? 'text-green-300' : 'text-red-300' }}"
                    subtitle="Comparado con mes anterior" subtitleIcon="fas fa-chart-line" gradientFrom="from-indigo-500"
                    gradientTo="to-purple-600" progressWidth="{{ min(100, ($totalCustomers / 100) * 100) }}%"
                    progressGradientFrom="from-indigo-400" progressGradientTo="to-purple-400" />
            </div>

            <!-- Widget de Nuevos Clientes -->
            <div class="widget-new-customers">
                <x-dashboard-widget title="Nuevos Clientes" value="{{ $newCustomers }}" valueType="number"
                    icon="fas fa-user-plus" trend="Nuevos" trendIcon="fas fa-star" trendColor="text-yellow-300"
                    subtitle="Registrados este mes" subtitleIcon="fas fa-calendar-alt" gradientFrom="from-blue-500"
                    gradientTo="to-indigo-600" progressWidth="{{ min(100, ($newCustomers / 10) * 100) }}%"
                    progressGradientFrom="from-blue-400" progressGradientTo="to-indigo-400" />
            </div>



            <!-- Widget de Actividad de Clientes -->
            <div class="widget-customer-activity">
                @php
                    // Obtenemos el último valor de actividad que no sea cero si es posible para mostrar algo real
                    // Pero para ser fiel a la data, si el mes actual es 0, se muestra 0.
                    $activityValues = array_filter($monthlyActivity);
                    $lastMonthWithActivity = !empty($activityValues) ? end($activityValues) : 0;
                    $currentActivity = end($monthlyActivity) ?: 0;
                @endphp
                <x-dashboard-widget title="Actividad Mensual" value="{{ $currentActivity }}" valueType="number"
                    icon="fas fa-chart-pulse" trend="Activo" trendIcon="fas fa-fire" trendColor="text-orange-300"
                    subtitle="Interacciones este mes" subtitleIcon="fas fa-bolt" gradientFrom="from-orange-500"
                    gradientTo="to-pink-600" progressWidth="75%" progressGradientFrom="from-orange-400"
                    progressGradientTo="to-pink-400" />
            </div>
        </div>
    </div>

    {{-- Top 10 Productos Más Vendidos --}}
    <div class="mb-12" x-data="{
        exportTopProducts() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Exportando productos',
                        text: 'Preparando exportación de productos más vendidos...',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            },
            refreshTopProducts() {
                const button = event.target.closest('button');
                const icon = button.querySelector('i');
                icon.classList.add('animate-spin');
                button.disabled = true;
    
                setTimeout(() => {
                    icon.classList.remove('animate-spin');
                    button.disabled = false;
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Productos actualizados',
                            text: 'Los productos se han actualizado correctamente',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                }, 1500);
            }
    }">

        <!-- Table Container -->
        <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
            <!-- Table Header -->
            <div class="bg-gradient-to-r from-yellow-500 to-orange-600 text-white p-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <!-- Title -->
                    <div class="flex items-center gap-4">
                        <div class="flex items-center justify-center w-16 h-16 bg-white/20 backdrop-blur-lg rounded-2xl">
                            <i class="fas fa-trophy text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl lg:text-3xl font-bold">Top 10 Productos Más Vendidos</h3>
                            <p class="text-lg opacity-90">Ranking de productos con mejor rendimiento</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-2">
                        <button @click="exportTopProducts()"
                            class="bg-white/20 backdrop-blur-lg text-white px-4 py-2 rounded-xl font-semibold hover:bg-white/30 transition-all duration-300 flex items-center gap-2">
                            <i class="fas fa-download"></i>
                            Exportar
                        </button>
                        <button @click="refreshTopProducts()"
                            class="bg-white/20 backdrop-blur-lg text-white px-4 py-2 rounded-xl font-semibold hover:bg-white/30 transition-all duration-300 flex items-center gap-2">
                            <i class="fas fa-sync-alt"></i>
                            Actualizar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table Content -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">#</th>
                            <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Producto</th>
                            <th class="px-6 py-4 text-center text-sm font-bold text-gray-700">Veces Vendido</th>
                            <th class="px-6 py-4 text-center text-sm font-bold text-gray-700">Cantidad Total</th>
                            <th class="px-6 py-4 text-right text-sm font-bold text-gray-700">Precio Unitario</th>
                            <th class="px-6 py-4 text-right text-sm font-bold text-gray-700">Ingresos Totales</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($topSellingProducts as $index => $product)
                            <tr class="hover:bg-gray-50 transition-all duration-300 group">
                                <!-- Rank -->
                                <td class="px-6 py-4">
                                    <div
                                        class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm mx-auto
                                                {{ $index + 1 <= 3 ? 'bg-gradient-to-r from-yellow-400 to-orange-500 text-white shadow-lg' : 'bg-gray-200 text-gray-600' }}">
                                        {{ $index + 1 }}
                                    </div>
                                </td>

                                <!-- Product -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex items-center justify-center w-10 h-10 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl">
                                            <i class="fas fa-box text-sm"></i>
                                        </div>
                                        <div>
                                            <div class="font-semibold text-gray-800">{{ $product->name }}</div>
                                            <div class="text-sm text-gray-500">Categoría principal</div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Times Sold -->
                                <td class="px-6 py-4 text-center">
                                    <div
                                        class="inline-flex items-center gap-2 bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-semibold">
                                        <i class="fas fa-chart-line"></i>
                                        {{ $product->times_sold }}
                                    </div>
                                </td>

                                <!-- Total Quantity -->
                                <td class="px-6 py-4 text-center">
                                    <div
                                        class="inline-flex items-center gap-2 bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">
                                        <i class="fas fa-cubes"></i>
                                        {{ $product->total_quantity }}
                                    </div>
                                </td>

                                <!-- Unit Price -->
                                <td class="px-6 py-4 text-right">
                                    <div class="font-bold text-gray-800">
                                        {{ $currency->symbol }}{{ number_format($product->sale_price, 2) }}
                                    </div>
                                </td>

                                <!-- Total Revenue -->
                                <td class="px-6 py-4 text-right">
                                    <div class="flex flex-col items-end gap-2">
                                        <div class="font-bold text-gray-800">
                                            {{ $currency->symbol }}{{ number_format($product->total_revenue, 2) }}
                                        </div>
                                        <div class="w-20 h-2 bg-gray-200 rounded-full overflow-hidden">
                                            <div class="h-full bg-gradient-to-r from-pink-500 to-purple-600 rounded-full transition-all duration-1000"
                                                style="width: {{ min(100, ($product->total_revenue / $topSellingProducts->max('total_revenue')) * 100) }}%">
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Top 5 Clientes --}}
    <div class="mb-12" x-data="{
        viewAllCustomers() {
            window.location.href = '{{ route('admin.customers.index') }}';
        }
    }">

        <!-- Tables Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
            <!-- Top 5 Clientes -->
            <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
                <!-- Table Header -->
                <div class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white p-6">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                        <!-- Title -->
                        <div class="flex items-center gap-4">
                            <div
                                class="flex items-center justify-center w-16 h-16 bg-white/20 backdrop-blur-lg rounded-2xl">
                                <i class="fas fa-users text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold">Top 5 Clientes</h3>
                                <p class="text-lg opacity-90">Clientes con mayor volumen de compras</p>
                            </div>
                        </div>

                        <!-- Action -->
                        <button @click="viewAllCustomers()"
                            class="bg-white/20 backdrop-blur-lg text-white px-4 py-2 rounded-xl font-semibold hover:bg-white/30 transition-all duration-300 flex items-center gap-2">
                            <i class="fas fa-eye"></i>
                            Ver Todos
                        </button>
                    </div>
                </div>

                <!-- Table Content -->
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">#</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Cliente</th>
                                <th class="px-6 py-4 text-right text-sm font-bold text-gray-700">Total Gastado</th>
                                <th class="px-6 py-4 text-center text-sm font-bold text-gray-700">Productos</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($topCustomers as $index => $customer)
                                <tr class="hover:bg-gray-50 transition-all duration-300 group">
                                    <!-- Rank -->
                                    <td class="px-6 py-4">
                                        <div
                                            class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm mx-auto
                                                    {{ $index + 1 <= 3 ? 'bg-gradient-to-r from-yellow-400 to-orange-500 text-white shadow-lg' : 'bg-gray-200 text-gray-600' }}">
                                            {{ $index + 1 }}
                                        </div>
                                    </td>

                                    <!-- Customer -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex items-center justify-center w-10 h-10 bg-gradient-to-r from-cyan-500 to-blue-600 text-white rounded-xl">
                                                <i class="fas fa-user text-sm"></i>
                                            </div>
                                            <div>
                                                <div class="font-semibold text-gray-800">{{ $customer->name }}</div>
                                                <div class="text-sm text-gray-500">Cliente VIP</div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Total Spent -->
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex flex-col items-end gap-2">
                                            <div class="font-bold text-gray-800">
                                                {{ $currency->symbol }}{{ number_format($customer->total_spent, 2) }}
                                            </div>
                                            <div class="w-20 h-2 bg-gray-200 rounded-full overflow-hidden">
                                                <div class="h-full bg-gradient-to-r from-cyan-500 to-blue-600 rounded-full transition-all duration-1000"
                                                    style="width: {{ min(100, ($customer->total_spent / $topCustomers->max('total_spent')) * 100) }}%">
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Products -->
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2 text-sm">
                                            <div class="text-center">
                                                <div class="font-bold text-gray-800">{{ $customer->unique_products }}
                                                </div>
                                                <div class="text-gray-500">Únicos</div>
                                            </div>
                                            <div class="text-gray-300">|</div>
                                            <div class="text-center">
                                                <div class="font-bold text-gray-800">{{ $customer->total_products }}</div>
                                                <div class="text-gray-500">Total</div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Análisis de Ventas -->
            <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
                <!-- Chart Header -->
                <div class="bg-gradient-to-r from-pink-500 to-purple-600 text-white p-6">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                        <!-- Title -->
                        <div class="flex items-center gap-4">
                            <div
                                class="flex items-center justify-center w-16 h-16 bg-white/20 backdrop-blur-lg rounded-2xl">
                                <i class="fas fa-chart-bar text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold">Análisis de Ventas</h3>
                                <p class="text-lg opacity-90">Ventas por categorías</p>
                            </div>
                        </div>

                        <!-- Action -->
                        <button
                            class="bg-white/20 backdrop-blur-lg text-white px-4 py-2 rounded-xl font-semibold hover:bg-white/30 transition-all duration-300 flex items-center gap-2">
                            <i class="fas fa-exchange-alt"></i>
                            Cambiar Vista
                        </button>
                    </div>
                </div>

                <!-- Chart Content -->
                <div class="p-6">
                    <div class="bg-gray-50 rounded-2xl p-4">
                        <canvas id="salesByCategoryChart" class="w-full" style="min-height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ---------------------------------------------- --}}


    {{-- Gráficos de Análisis --}}
    <div class="mb-12" x-data="{
        cashFlowMode: 'historical',
        historicalChartData: @js($chartData ?? []),
        currentCashData: @js($currentCashData ?? []),
        closedCashCounts: @js($closedCashCountsData ?? []),
        summary: {
            income: 0,
            expenses: 0,
            balance: 0
        },
    
        init() {
            this.updateSummary('historical');
        },
    
        formatCurrency(amount) {
            return '{{ $currency->symbol }}' + parseFloat(amount || 0).toLocaleString('es-PE', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        },
    
        updateSummary(mode) {
            let income = 0;
            let expenses = 0;
    
            if (mode === 'historical') {
                income = this.historicalChartData.income.reduce((a, b) => a + b, 0);
                expenses = this.historicalChartData.expenses.reduce((a, b) => a + b, 0);
            } else if (mode === 'current') {
                income = this.currentCashData.total_income || 0;
                expenses = this.currentCashData.total_expense || 0;
            } else if (mode.startsWith('cash_')) {
                const id = mode.replace('cash_', '');
                const cash = this.closedCashCounts.find(c => c.id == id);
                if (cash) {
                    income = cash.total_income || 0;
                    expenses = cash.total_expense || 0;
                }
            }
    
            this.summary.income = income;
            this.summary.expenses = expenses;
            this.summary.balance = income - expenses;
    
            // Emitir evento para que el gráfico se actualice
            window.dispatchEvent(new CustomEvent('cashFlowModeChanged', { detail: { mode: mode } }));
        },
    
        exportCashFlowChart() {
            showNotification('Preparando exportación...', 'info');
        }
    }">

        <!-- Sección de Análisis de Flujo de Caja -->
        <x-section-header title="El Pulso del Dinero" subtitle="Tendencia de Ingresos vs Egresos por Arqueo"
            icon="fas fa-chart-line" iconBg="from-indigo-500 to-blue-600" statusIcon="fas fa-pulse"
            statusText="Análisis en Tiempo Real" statusColor="indigo" dataMode="cashFlowMode" :dataOptions="[]"
            :cashCounts="$closedCashCountsData" :showDataSelector="true" :showStatus="true" :showLastUpdate="true" sectionId="cash-flow-section" />

        <div class="bg-gradient-to-br from-slate-800 to-gray-900 rounded-2xl shadow-xl overflow-hidden">
            <!-- Chart Content -->
            <div class="p-4">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <!-- Total Ingresos -->
                    <div class="bg-gradient-to-r from-green-400 to-emerald-500 p-4 rounded-xl text-white">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex items-center justify-center w-10 h-10 bg-white/20 backdrop-blur-lg rounded-lg">
                                <i class="fas fa-arrow-up text-sm"></i>
                            </div>
                            <div>
                                <div class="text-xl font-bold" x-text="formatCurrency(summary.income)"></div>
                                <div class="text-xs opacity-90">Total Ingresos</div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Egresos -->
                    <div class="bg-gradient-to-r from-red-400 to-pink-500 p-4 rounded-xl text-white">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex items-center justify-center w-10 h-10 bg-white/20 backdrop-blur-lg rounded-lg">
                                <i class="fas fa-arrow-down text-sm"></i>
                            </div>
                            <div>
                                <div class="text-xl font-bold" x-text="formatCurrency(summary.expenses)"></div>
                                <div class="text-xs opacity-90">Total Egresos</div>
                            </div>
                        </div>
                    </div>

                    <!-- Balance Neto -->
                    <div class="bg-gradient-to-r from-blue-400 to-indigo-500 p-4 rounded-xl text-white">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex items-center justify-center w-10 h-10 bg-white/20 backdrop-blur-lg rounded-lg">
                                <i class="fas fa-equals text-sm"></i>
                            </div>
                            <div>
                                <div class="text-xl font-bold" x-text="formatCurrency(summary.balance)"></div>
                                <div class="text-xs opacity-90">Balance Neto</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chart Canvas -->
                <div class="bg-gray-50 rounded-2xl p-6">
                    <canvas id="cashFlowChart" class="w-full" style="min-height: 400px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Listener global para el cambio de modo en Cash Flow
        window.addEventListener('cashFlowModeChanged', function(event) {
            if (typeof updateCashFlowChart === 'function') {
                updateCashFlowChart(event.detail.mode);
            }
        });
    </script>

@stop

@push('css')
@endpush

@push('js')
    <script src="{{ asset('vendor/chartjs/chart.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            if (typeof Chart === 'undefined') {
                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js';
                script.onload = function() {
                    initializeCharts();
                };
                document.head.appendChild(script);
                return;
            }

            initializeCharts();
        });

        function initializeCharts() {

            try {
                initSalesTrendsChart();
            } catch (e) {
                console.error('[Dashboard] Error en salesTrendsChart:', e);
            }

            try {
                initPurchaseTrendsChart();
            } catch (e) {
                console.error('[Dashboard] Error en purchaseTrendsChart:', e);
            }

            try {
                initSalesByCategoryChart();
            } catch (e) {
                console.error('[Dashboard] Error en salesByCategoryChart:', e);
            }

            try {
                initCashFlowChart();
            } catch (e) {
                console.error('[Dashboard] Error en cashFlowChart:', e);
            }
        }

        // ============================================
        // Gráfico de Rendimiento Mensual de Ventas
        // ============================================
        function initSalesTrendsChart() {
            const canvas = document.getElementById('salesTrendsChart');
            if (!canvas) {
                return;
            }

            const ctx = canvas.getContext('2d');

            // Datos desde el servidor
            const labels = @json($salesMonthlyLabels);
            const salesData = @json($salesMonthlyData);
            const profitData = @json($profitMonthlyData);
            const transactionsData = @json($transactionsMonthlyData);

            // Gradientes
            const gradientSales = ctx.createLinearGradient(0, 0, 0, 350);
            gradientSales.addColorStop(0, 'rgba(79, 70, 229, 0.4)');
            gradientSales.addColorStop(1, 'rgba(79, 70, 229, 0.02)');

            const gradientProfit = ctx.createLinearGradient(0, 0, 0, 350);
            gradientProfit.addColorStop(0, 'rgba(16, 185, 129, 0.4)');
            gradientProfit.addColorStop(1, 'rgba(16, 185, 129, 0.02)');

            const currencySymbol = '{{ $currency->symbol }}';

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                            label: 'Ventas Totales',
                            data: salesData,
                            type: 'line',
                            borderColor: 'rgb(79, 70, 229)',
                            backgroundColor: gradientSales,
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: 'rgb(79, 70, 229)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointHoverRadius: 7,
                            pointRadius: 5,
                            order: 1
                        },
                        {
                            label: 'Ganancia Neta',
                            data: profitData,
                            type: 'line',
                            borderColor: 'rgb(16, 185, 129)',
                            backgroundColor: gradientProfit,
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: 'rgb(16, 185, 129)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointHoverRadius: 7,
                            pointRadius: 5,
                            order: 2
                        },
                        {
                            label: 'Transacciones',
                            data: transactionsData,
                            type: 'bar',
                            backgroundColor: 'rgba(245, 158, 11, 0.3)',
                            borderColor: 'rgba(245, 158, 11, 0.9)',
                            borderWidth: 2,
                            borderRadius: 8,
                            barPercentage: 0.5,
                            yAxisID: 'y1',
                            order: 3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                font: {
                                    size: 12,
                                    weight: '600'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(17, 24, 39, 0.95)',
                            padding: 14,
                            cornerRadius: 10,
                            titleFont: {
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 13
                            },
                            bodySpacing: 6,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) label += ': ';
                                    if (context.dataset.yAxisID === 'y1') {
                                        label += context.parsed.y + ' ventas';
                                    } else {
                                        const val = context.parsed.y || 0;
                                        label += currencySymbol + val.toLocaleString('es-PE', {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2
                                        });
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Monto (' + currencySymbol + ')',
                                font: {
                                    size: 12,
                                    weight: '600'
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.06)'
                            }
                        },
                        y1: {
                            beginAtZero: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'N° de Ventas',
                                font: {
                                    size: 12,
                                    weight: '600'
                                }
                            },
                            grid: {
                                drawOnChartArea: false
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

        }

        // ============================================
        // Gráfico de Compras Mensuales
        // ============================================
        function initPurchaseTrendsChart() {
            const canvas = document.getElementById('purchaseTrendsChart');
            if (!canvas) return;

            new Chart(canvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: @json($purchaseMonthlyLabels),
                    datasets: [{
                        label: 'Compras Mensuales',
                        data: @json($purchaseMonthlyData),
                        backgroundColor: 'rgba(59, 130, 246, 0.7)',
                        borderColor: 'rgb(59, 130, 246)',
                        borderWidth: 2,
                        borderRadius: 8,
                        barPercentage: 0.6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.06)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        // ============================================
        // Gráfico de Ventas por Categoría
        // ============================================
        function initSalesByCategoryChart() {
            const canvas = document.getElementById('salesByCategoryChart');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');

            const gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, 'rgba(236, 72, 153, 0.8)'); // pink-500
            gradient.addColorStop(1, 'rgba(147, 51, 234, 0.8)'); // purple-600

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json($salesByCategoryLabels),
                    datasets: [{
                        label: 'Ventas por Categoría',
                        data: @json($salesByCategoryData),
                        backgroundColor: gradient,
                        borderColor: 'rgba(236, 72, 153, 1)',
                        borderWidth: 1,
                        borderRadius: 8,
                        hoverBackgroundColor: 'rgba(147, 51, 234, 0.9)',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) label += ': ';
                                    label += '{{ $currency->symbol }}' + context.parsed.y.toLocaleString(
                                        'es-PE', {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2
                                        });
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '{{ $currency->symbol }}' + value.toLocaleString('es-PE');
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        let cashFlowChart;

        function initCashFlowChart() {
            const canvas = document.getElementById('cashFlowChart');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');
            const data = @js($chartData);

            cashFlowChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                            label: 'Ingresos (Ventas)',
                            data: data.income,
                            backgroundColor: [], // Se llena dinámicamente
                            borderRadius: 6,
                            order: 2
                        },
                        {
                            label: 'Egresos (Costo)',
                            data: data.expenses.map(e => -e), // Mostrar hacia abajo
                            backgroundColor: 'rgba(244, 63, 94, 0.7)', // Rose-500
                            borderRadius: 6,
                            order: 2
                        },
                        {
                            label: 'El Pulso (Acumulado)',
                            data: [], // Se llena dinámicamente
                            type: 'line',
                            borderColor: 'rgba(99, 102, 241, 1)', // Indigo-500
                            borderWidth: 3,
                            fill: false,
                            tension: 0.4,
                            pointRadius: 0,
                            order: 1,
                            yAxisID: 'y'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function(context) {
                                    let val = context.parsed.y;
                                    if (context.datasetIndex === 1) val = Math.abs(val);
                                    return context.dataset.label + ': ' + '{{ $currency->symbol }}' +
                                        val.toLocaleString('es-PE', {
                                            minimumFractionDigits: 2
                                        });
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(255, 255, 255, 0.05)'
                            },
                            ticks: {
                                color: 'rgba(255, 255, 255, 0.6)',
                                callback: val => '{{ $currency->symbol }}' + Math.abs(val).toLocaleString('es-PE')
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: 'rgba(255, 255, 255, 0.6)'
                            }
                        }
                    }
                }
            });

            // Primera carga
            updateCashFlowChart('historical');
        }

        function updateCashFlowChart(mode) {
            if (!cashFlowChart) return;

            const dailyMovements = @js($chartData['daily_movements'] ?? []);
            const currentCash = @js($currentCashCount);
            const closedCounts = @js($closedCashCountsData);

            let filteredData = [];
            let startDate, endDate;

            if (mode === 'historical') {
                // Total histórico: Desde el primer movimiento hasta ahora
                filteredData = dailyMovements;
                startDate = filteredData.length > 0 ? filteredData[0].date : null;
                endDate = new Date().toISOString().split('T')[0];
            } else if (mode === 'current') {
                const openId = currentCash ? currentCash.id : null;
                filteredData = dailyMovements.filter(m => m.cash_count_id == openId);
            } else if (mode.startsWith('cash_')) {
                const id = mode.replace('cash_', '');
                filteredData = dailyMovements.filter(m => m.cash_count_id == id);
            }

            if (filteredData.length === 0) {
                // Datos de fallback por si no hay nada
                cashFlowChart.data.labels = ['Sin datos'];
                cashFlowChart.data.datasets[0].data = [0];
                cashFlowChart.data.datasets[1].data = [0];
                cashFlowChart.data.datasets[2].data = [0];
                cashFlowChart.update();
                return;
            }

            // Agrupar por fecha por si hay duplicados ID/Fecha
            const grouped = filteredData.reduce((acc, curr) => {
                if (!acc[curr.date]) acc[curr.date] = {
                    income: 0,
                    expense: 0
                };
                acc[curr.date].income += parseFloat(curr.income);
                acc[curr.date].expense += parseFloat(curr.expense);
                return acc;
            }, {});

            const dates = Object.keys(grouped).sort();
            const incomeValues = dates.map(d => grouped[d].income);
            const expenseValues = dates.map(d => grouped[d].expense);

            // Calcular Promedio de Ventas (Ingresos)
            const sumIncome = incomeValues.reduce((a, b) => a + b, 0);
            const avgIncome = sumIncome / incomeValues.length;

            // Colores dinámicos: Azul si > Promedio, Verde si <= Promedio
            const bgColors = incomeValues.map(v =>
                v > avgIncome ? 'rgba(59, 130, 246, 0.8)' : 'rgba(16, 185, 129, 0.7)'
            );

            // Calcular Pulso (Acumulado Neto)
            let accumulated = 0;
            const pulseData = dates.map(d => {
                accumulated += (grouped[d].income - grouped[d].expense);
                return accumulated;
            });

            // Formatear labels (DD/MM)
            const labels = dates.map(d => {
                const [y, m, day] = d.split('-');
                return `${day}/${m}`;
            });

            cashFlowChart.data.labels = labels;
            cashFlowChart.data.datasets[0].data = incomeValues;
            cashFlowChart.data.datasets[0].backgroundColor = bgColors;
            cashFlowChart.data.datasets[1].data = expenseValues.map(v => -v);
            cashFlowChart.data.datasets[2].data = pulseData;

            cashFlowChart.update();
        }

        // Función para mostrar notificaciones (compatible con SweetAlert2)
        function showNotification(message, type = 'info') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: type,
                    title: type === 'success' ? 'Éxito' : type === 'error' ? 'Error' : 'Información',
                    text: message,
                    timer: 3000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            }
        }
    </script>
@endpush
