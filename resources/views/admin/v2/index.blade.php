@extends('layouts.app')

@section('title', 'Dashboard v2')

@section('content')
<div class="space-y-6">

    {{-- ================================================================ --}}
    {{-- 1. HEADER PRINCIPAL                                              --}}
    {{-- ================================================================ --}}
    <div class="ui-panel overflow-hidden" x-data="{
        currentTime: '{{ date('H:i') }}',
        currentDate: '{{ date('d/m/Y') }}',
        init() {
            setInterval(() => {
                const now = new Date();
                this.currentTime = now.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit' });
                this.currentDate = now.toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric' });
            }, 60000);
        }
    }">
        {{-- Línea de acento superior con gradiente --}}
        <div class="h-1 bg-gradient-to-r from-cyan-400 via-purple-500 to-transparent"></div>

        <div class="ui-panel__header flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between !border-0">
            <div class="flex items-center gap-4">
                <div class="relative flex-shrink-0">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl border border-cyan-500/30 bg-gradient-to-br from-cyan-500/20 to-purple-600/20 text-cyan-400 shadow-lg shadow-cyan-500/10">
                        <i class="fas fa-rocket text-xl"></i>
                    </div>
                    <span class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 ring-2 ring-slate-900">
                        <span class="h-1.5 w-1.5 rounded-full bg-white animate-pulse"></span>
                    </span>
                </div>
                <div>
                    <h1 class="ui-panel__title !text-lg sm:!text-xl">Dashboard Ejecutivo</h1>
                    <p class="ui-panel__subtitle">Panel de control inteligente — {{ $company->name ?? 'Sistema' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2.5 rounded-xl border border-slate-700/60 bg-slate-800/50 px-4 py-2.5 min-w-[100px]">
                    <i class="far fa-clock text-cyan-400"></i>
                    <span class="text-sm font-semibold tabular-nums text-slate-200" x-text="currentTime">{{ date('H:i') }}</span>
                </div>
                <div class="flex items-center gap-2.5 rounded-xl border border-slate-700/60 bg-slate-800/50 px-4 py-2.5 min-w-[130px]">
                    <i class="far fa-calendar-alt text-purple-400"></i>
                    <span class="text-sm font-semibold tabular-nums text-slate-200" x-text="currentDate">{{ date('d/m/Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- 2. ARQUEO DE CAJA - Reactivo con selector de arqueo             --}}
    {{-- ================================================================ --}}
    <div class="ui-panel ui-panel--overflow-visible" style="z-index: 35;" x-data="{
        cashDataMode: 'current',
        currentCashData: @js($currentCashData ?? []),
        historicalData: @js($historicalData ?? []),
        closedCashCountsData: @js($closedCashCountsData ?? []),

        formatCurrency(amount) {
            return '{{ $currency->symbol }}' + parseFloat(amount || 0).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        get selectedCashData() {
            if (this.cashDataMode === 'current') return this.currentCashData;
            if (this.cashDataMode === 'historical') return this.historicalData;
            if (this.cashDataMode.startsWith('cash_')) {
                const id = this.cashDataMode.replace('cash_', '');
                return this.closedCashCountsData.find(item => item.id == id) || {};
            }
            return {};
        },

        cashDropdownOpen: false,
        toggleDropdown() { this.cashDropdownOpen = !this.cashDropdownOpen; },
        closeDropdown() { this.cashDropdownOpen = false; },
        selectCashMode(mode) {
            this.cashDataMode = mode;
            this.cashDropdownOpen = false;
        },

        cashCountOptions() {
            return (this.closedCashCountsData || []).map(cc => ({
                id: 'cash_' + cc.id,
                name: (cc.opening_date_formatted || '') + ' - ' + (cc.closing_date_formatted || ''),
            }));
        },

        currentCashLabel() {
            if (this.cashDataMode === 'current') return '📊 Arqueo Actual';
            if (this.cashDataMode === 'historical') return '📈 Histórico Completo';
            const opt = this.cashCountOptions().find(o => o.id === this.cashDataMode);
            return opt ? '📋 ' + opt.name : 'Seleccionar...';
        }
    }">
        {{-- Header --}}
        <div class="ui-panel__header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="ui-panel__title">Arqueo de Caja</h2>
                <p class="ui-panel__subtitle">Control financiero y gestión de efectivo</p>
            </div>
            <div class="relative" @click.away="closeDropdown()">
                <button type="button" @click="toggleDropdown()"
                    class="ui-btn ui-btn-ghost text-sm min-w-[200px] justify-between">
                    <span x-text="currentCashLabel()"></span>
                    <i class="fas fa-chevron-down text-xs transition-transform" :class="cashDropdownOpen ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="cashDropdownOpen" x-transition
                    class="absolute right-0 z-50 mt-1 w-72 rounded-xl border border-slate-600/50 bg-slate-900/95 shadow-2xl backdrop-blur-xl py-1.5 max-h-72 overflow-y-auto"
                    style="display: none;">
                    <button @click="selectCashMode('current')"
                        class="w-full flex items-center gap-3 pl-4 pr-3.5 py-2.5 text-left text-sm text-slate-200 hover:bg-slate-800/80 transition"
                        :class="cashDataMode === 'current' ? 'bg-cyan-500/10 text-cyan-300 font-semibold' : ''">
                        <i class="fas fa-chart-bar w-4 text-center"></i> 📊 Arqueo Actual
                    </button>
                    <button @click="selectCashMode('historical')"
                        class="w-full flex items-center gap-3 pl-4 pr-3.5 py-2.5 text-left text-sm text-slate-200 hover:bg-slate-800/80 transition"
                        :class="cashDataMode === 'historical' ? 'bg-cyan-500/10 text-cyan-300 font-semibold' : ''">
                        <i class="fas fa-history w-4 text-center"></i> 📈 Histórico Completo
                    </button>
                    <template x-if="cashCountOptions().length">
                        <div>
                            <div class="my-1 border-t border-slate-700/50"></div>
                            <template x-for="opt in cashCountOptions()" :key="opt.id">
                                <button @click="selectCashMode(opt.id)"
                                    class="w-full flex items-center gap-3 pl-4 pr-3.5 py-2.5 text-left text-sm text-slate-200 hover:bg-slate-800/80 transition"
                                    :class="cashDataMode === opt.id ? 'bg-cyan-500/10 text-cyan-300 font-semibold' : ''">
                                    <i class="fas fa-cash-register w-4 text-center"></i>
                                    <span x-text="opt.name"></span>
                                </button>
                            </template>
                        </div>
                    </template>
                </div>
                @if ($currentCashCount)
                    <span class="ui-badge ui-badge-success text-xs ml-2">Caja Abierta</span>
                @else
                    <span class="ui-badge ui-badge-danger text-xs ml-2">Caja Cerrada</span>
                @endif
            </div>
        </div>

        {{-- Widgets Grid --}}
        <div class="ui-panel__body">
            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                {{-- Balance --}}
                <div class="ui-widget ui-widget--dense ui-widget--info">
                    <div class="ui-widget__top">
                        <span class="ui-widget__icon"><i class="fas fa-balance-scale"></i></span>
                        <span class="ui-widget__trend">Balance</span>
                    </div>
                    <p class="ui-widget__label">Balance Actual</p>
                    <p class="ui-widget__value" x-text="formatCurrency(selectedCashData.balance || 0)">{{ $currency->symbol }}0.00</p>
                    <p class="ui-widget__meta">Período seleccionado</p>
                </div>

                {{-- Ventas --}}
                <div class="ui-widget ui-widget--dense ui-widget--success">
                    <div class="ui-widget__top">
                        <span class="ui-widget__icon"><i class="fas fa-chart-line"></i></span>
                        <span class="ui-widget__trend">{{ $currency->symbol }}{{ number_format($monthlySales ?? 0, 2) }}</span>
                    </div>
                    <p class="ui-widget__label">Ventas del Período</p>
                    <p class="ui-widget__value" x-text="formatCurrency(selectedCashData.sales || 0)">{{ $currency->symbol }}{{ number_format($monthlySales ?? 0, 2) }}</p>
                    <p class="ui-widget__meta">Compras: <span x-text="formatCurrency(selectedCashData.purchases || 0)">{{ $currency->symbol }}0.00</span></p>
                </div>

                {{-- Por Cobrar --}}
                <div class="ui-widget ui-widget--dense ui-widget--warning">
                    <div class="ui-widget__top">
                        <span class="ui-widget__icon"><i class="fas fa-hourglass-half"></i></span>
                        <span class="ui-widget__trend">Pendiente</span>
                    </div>
                    <p class="ui-widget__label">Por Cobrar</p>
                    <p class="ui-widget__value" x-text="formatCurrency(selectedCashData.debt || 0)">{{ $currency->symbol }}0.00</p>
                    <p class="ui-widget__meta">Deudas pendientes</p>
                </div>

                {{-- Pagos de Deuda --}}
                <div class="ui-widget ui-widget--dense ui-widget--neutral" style="border-color: rgba(167,139,250,0.35); background: linear-gradient(145deg, rgba(46,16,101,0.9), rgba(76,29,149,0.82));">
                    <div class="ui-widget__top">
                        <span class="ui-widget__icon"><i class="fas fa-hand-holding-usd"></i></span>
                        <span class="ui-widget__trend">Recibidos</span>
                    </div>
                    <p class="ui-widget__label">Pagos de Deuda</p>
                    <p class="ui-widget__value" x-text="formatCurrency(selectedCashData.debt_payments || 0)">{{ $currency->symbol }}0.00</p>
                    <p class="ui-widget__meta">Este período</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- 3. ANÁLISIS DE VENTAS - Reactivo con selector de arqueo         --}}
    {{-- ================================================================ --}}
    <div class="ui-panel ui-panel--overflow-visible" style="z-index: 32;" x-data="{
        salesDataMode: 'current',
        currentSalesData: @js($currentSalesData ?? []),
        historicalSalesData: @js($historicalSalesData ?? []),
        closedSalesData: @js($closedSalesData ?? []),
        closedCashCountsData: @js($closedCashCountsData ?? []),

        formatCurrency(amount) {
            return '{{ $currency->symbol }}' + parseFloat(amount || 0).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        get selectedSalesData() {
            if (this.salesDataMode === 'current') return this.currentSalesData;
            if (this.salesDataMode === 'historical') return this.historicalSalesData;
            if (this.salesDataMode.startsWith('cash_')) {
                const id = this.salesDataMode.replace('cash_', '');
                return this.closedSalesData.find(item => item.id == id) || {};
            }
            return {};
        },

        salesDropdownOpen: false,
        toggleDropdown() { this.salesDropdownOpen = !this.salesDropdownOpen; },
        closeDropdown() { this.salesDropdownOpen = false; },
        selectSalesMode(mode) {
            this.salesDataMode = mode;
            this.salesDropdownOpen = false;
        },

        cashCountOptions() {
            return (this.closedCashCountsData || []).map(cc => ({
                id: 'cash_' + cc.id,
                name: (cc.opening_date_formatted || '') + ' - ' + (cc.closing_date_formatted || ''),
            }));
        },

        currentSalesLabel() {
            if (this.salesDataMode === 'current') return '📊 Arqueo Actual';
            if (this.salesDataMode === 'historical') return '📈 Histórico Completo';
            const opt = this.cashCountOptions().find(o => o.id === this.salesDataMode);
            return opt ? '📋 ' + opt.name : 'Seleccionar...';
        }
    }">
        {{-- Header --}}
        <div class="ui-panel__header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="ui-panel__title">Análisis de Ventas</h2>
                <p class="ui-panel__subtitle">Métricas y rendimiento comercial</p>
            </div>
            <div class="relative" @click.away="closeDropdown()">
                <button type="button" @click="toggleDropdown()"
                    class="ui-btn ui-btn-ghost text-sm min-w-[200px] justify-between">
                    <span x-text="currentSalesLabel()"></span>
                    <i class="fas fa-chevron-down text-xs transition-transform" :class="salesDropdownOpen ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="salesDropdownOpen" x-transition
                    class="absolute right-0 z-50 mt-1 w-72 rounded-xl border border-slate-600/50 bg-slate-900/95 shadow-2xl backdrop-blur-xl py-1.5 max-h-72 overflow-y-auto"
                    style="display: none;">
                    <button @click="selectSalesMode('current')"
                        class="w-full flex items-center gap-3 pl-4 pr-3.5 py-2.5 text-left text-sm text-slate-200 hover:bg-slate-800/80 transition"
                        :class="salesDataMode === 'current' ? 'bg-cyan-500/10 text-cyan-300 font-semibold' : ''">
                        <i class="fas fa-chart-bar w-4 text-center"></i> 📊 Arqueo Actual
                    </button>
                    <button @click="selectSalesMode('historical')"
                        class="w-full flex items-center gap-3 pl-4 pr-3.5 py-2.5 text-left text-sm text-slate-200 hover:bg-slate-800/80 transition"
                        :class="salesDataMode === 'historical' ? 'bg-cyan-500/10 text-cyan-300 font-semibold' : ''">
                        <i class="fas fa-history w-4 text-center"></i> 📈 Histórico Completo
                    </button>
                    <template x-if="cashCountOptions().length">
                        <div>
                            <div class="my-1 border-t border-slate-700/50"></div>
                            <template x-for="opt in cashCountOptions()" :key="opt.id">
                                <button @click="selectSalesMode(opt.id)"
                                    class="w-full flex items-center gap-3 pl-4 pr-3.5 py-2.5 text-left text-sm text-slate-200 hover:bg-slate-800/80 transition"
                                    :class="salesDataMode === opt.id ? 'bg-cyan-500/10 text-cyan-300 font-semibold' : ''">
                                    <i class="fas fa-cash-register w-4 text-center"></i>
                                    <span x-text="opt.name"></span>
                                </button>
                            </template>
                        </div>
                    </template>
                </div>
                <span class="ui-badge ui-badge-success text-xs">Tiempo Real</span>
            </div>
        </div>

        {{-- Widgets Grid --}}
        <div class="ui-panel__body">
            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                {{-- Ventas de la Semana --}}
                <div class="ui-widget ui-widget--dense ui-widget--info">
                    <div class="ui-widget__top">
                        <span class="ui-widget__icon"><i class="fas fa-calendar-week"></i></span>
                        <span class="ui-widget__trend">Semanal</span>
                    </div>
                    <p class="ui-widget__label">Ventas de la Semana</p>
                    <p class="ui-widget__value" x-text="formatCurrency(selectedSalesData.weekly_sales || 0)">
                        {{ $currency->symbol }}{{ number_format($salesAnalysisWidgetsInitial['weekly_sales'] ?? 0, 2) }}
                    </p>
                    <p class="ui-widget__meta">Hoy: {{ $currency->symbol }}{{ number_format($todaySales ?? 0, 2) }}</p>
                </div>

                {{-- Ticket Promedio --}}
                <div class="ui-widget ui-widget--dense ui-widget--success">
                    <div class="ui-widget__top">
                        <span class="ui-widget__icon"><i class="fas fa-receipt"></i></span>
                        <span class="ui-widget__trend">Promedio</span>
                    </div>
                    <p class="ui-widget__label">Ticket Promedio</p>
                    <p class="ui-widget__value" x-text="formatCurrency(selectedSalesData.average_customer_spend || 0)">
                        {{ $currency->symbol }}{{ number_format($salesAnalysisWidgetsInitial['average_customer_spend'] ?? 0, 2) }}
                    </p>
                    <p class="ui-widget__meta">Por venta en el período</p>
                </div>

                {{-- Ganancia Total Teórica --}}
                <div class="ui-widget ui-widget--dense ui-widget--warning">
                    <div class="ui-widget__top">
                        <span class="ui-widget__icon"><i class="fas fa-chart-pie"></i></span>
                        <span class="ui-widget__trend">Margen</span>
                    </div>
                    <p class="ui-widget__label">Ganancia Total Teórica</p>
                    <p class="ui-widget__value" x-text="formatCurrency(selectedSalesData.total_profit || 0)">
                        {{ $currency->symbol }}{{ number_format($salesAnalysisWidgetsInitial['total_profit'] ?? 0, 2) }}
                    </p>
                    <p class="ui-widget__meta">Margen de productos vendidos</p>
                </div>

                {{-- Rendimiento Mensual --}}
                <div class="ui-widget ui-widget--dense ui-widget--danger">
                    <div class="ui-widget__top">
                        <span class="ui-widget__icon"><i class="fas fa-calendar-alt"></i></span>
                        <span class="ui-widget__trend">Mensual</span>
                    </div>
                    <p class="ui-widget__label">Rendimiento Mensual</p>
                    <p class="ui-widget__value" x-text="formatCurrency(selectedSalesData.monthly_sales || 0)">
                        {{ $currency->symbol }}{{ number_format($salesAnalysisWidgetsInitial['monthly_sales'] ?? 0, 2) }}
                    </p>
                    <p class="ui-widget__meta">Mes calendario actual</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- 4. GRÁFICOS DE TENDENCIAS                                        --}}
    {{-- ================================================================ --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        {{-- Ventas Mensuales --}}
        <div class="ui-panel">
            <div class="ui-panel__header">
                <div>
                    <h2 class="ui-panel__title">Rendimiento Mensual de Ventas</h2>
                    <p class="ui-panel__subtitle">Ingresos, ganancias y volumen de transacciones</p>
                </div>
                <div class="flex items-center gap-3 text-sm text-slate-400">
                    <span>Prom: {{ $currency->symbol }}{{ number_format(collect($salesMonthlyData)->avg(), 2) }}</span>
                    <span>Máx: {{ $currency->symbol }}{{ number_format(collect($salesMonthlyData)->max(), 2) }}</span>
                </div>
            </div>
            <div class="ui-panel__body">
                <div class="h-80">
                    <canvas id="salesTrendsChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Compras Mensuales --}}
        <div class="ui-panel">
            <div class="ui-panel__header">
                <div>
                    <h2 class="ui-panel__title">Tendencia de Compras Mensuales</h2>
                    <p class="ui-panel__subtitle">Evolución de compras e inventario</p>
                </div>
            </div>
            <div class="ui-panel__body">
                <div class="h-80">
                    <canvas id="purchaseTrendsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- 5. CLIENTES                                                      --}}
    {{-- ================================================================ --}}
    <div class="ui-panel">
        <div class="ui-panel__header">
            <div>
                <h2 class="ui-panel__title">Información de Clientes</h2>
                <p class="ui-panel__subtitle">Gestión y análisis de clientes</p>
            </div>
        </div>
        <div class="ui-panel__body">
            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                <x-ui.stat-card variant="info" icon="fas fa-users"
                    trend="{{ $customerGrowth >= 0 ? '+' : '' }}{{ $customerGrowth }}%"
                    label="Total Clientes"
                    :value="number_format($totalCustomers ?? 0)"
                    meta="Comparado con mes anterior" />

                <x-ui.stat-card variant="success" icon="fas fa-user-plus"
                    trend="Nuevos"
                    label="Nuevos Clientes"
                    :value="number_format($newCustomers ?? 0)"
                    meta="Registrados este mes" />

                <x-ui.stat-card variant="warning" icon="fas fa-check-circle"
                    trend="Verificados"
                    label="Clientes Verificados"
                    :value="number_format($verifiedCustomers ?? 0)"
                    :meta="$verifiedPercentage . '% del total'" />

                <x-ui.stat-card variant="danger" icon="fas fa-chart-pulse"
                    trend="Actividad"
                    label="Deuda Pendiente"
                    :value="$currency->symbol . ' ' . number_format($totalPendingDebt ?? 0, 2)"
                    meta="Total por cobrar" />
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- 6. TOP 10 PRODUCTOS + TOP 5 CLIENTES                             --}}
    {{-- ================================================================ --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        {{-- Top 10 Productos --}}
        <div class="ui-panel">
            <div class="ui-panel__header">
                <div>
                    <h2 class="ui-panel__title">Top 10 Productos Más Vendidos</h2>
                    <p class="ui-panel__subtitle">Ranking de productos con mejor rendimiento</p>
                </div>
            </div>
            <div class="ui-panel__body !p-0">
                {{-- Vista móvil: cards --}}
                <div class="md:hidden space-y-1.5 p-2">
                    @forelse ($topSellingProducts as $index => $product)
                        <div class="relative flex items-center gap-3 rounded-xl border border-slate-700/50 bg-slate-800/40 px-3 py-2.5 transition hover:bg-slate-800/70 overflow-hidden
                            {{ $index < 3 ? 'border-l-[3px] border-l-amber-500/80' : '' }}">
                            {{-- Glow top 3 --}}
                            @if ($index < 3)
                                <div class="absolute inset-0 bg-gradient-to-r from-amber-500/5 to-transparent pointer-events-none"></div>
                            @endif
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-[11px] font-bold flex-shrink-0
                                {{ $index < 3 ? 'bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-md shadow-amber-500/20' : 'bg-slate-700 text-slate-400' }}">
                                {{ $index + 1 }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <div class="text-[13px] font-semibold text-slate-100 truncate">{{ $product->name }}</div>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="inline-flex items-center gap-1 rounded-md bg-amber-500/10 px-1.5 py-px text-[10px] font-semibold text-amber-400">
                                        <i class="fas fa-chart-line text-[9px]"></i> {{ $product->times_sold }}x
                                    </span>
                                    <span class="inline-flex items-center gap-1 rounded-md bg-emerald-500/10 px-1.5 py-px text-[10px] font-semibold text-emerald-400">
                                        <i class="fas fa-cubes text-[9px]"></i> {{ $product->total_quantity }}
                                    </span>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <div class="text-sm font-bold tabular-nums bg-gradient-to-r from-cyan-400 to-purple-400 bg-clip-text text-transparent">{{ $currency->symbol }}{{ number_format($product->total_revenue, 2) }}</div>
                                <div class="text-[10px] text-slate-500 tabular-nums mt-px">c/u {{ $currency->symbol }}{{ number_format($product->sale_price, 2) }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="px-3 py-8 text-center text-slate-500 text-sm">Sin datos de productos vendidos.</div>
                    @endforelse
                </div>

                {{-- Vista desktop: tabla --}}
                <div class="hidden md:block ui-table-wrap !rounded-none !border-0">
                    <table class="ui-table">
                        <thead>
                            <tr>
                                <th class="w-12 text-center">#</th>
                                <th>Producto</th>
                                <th class="text-center">Veces</th>
                                <th class="text-center">Cant.</th>
                                <th class="text-right">Precio</th>
                                <th class="text-right">Ingresos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($topSellingProducts as $index => $product)
                                <tr>
                                    <td class="text-center">
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold
                                            {{ $index < 3 ? 'bg-gradient-to-br from-amber-500 to-orange-600 text-white' : 'bg-slate-700 text-slate-400' }}">
                                            {{ $index + 1 }}
                                        </span>
                                    </td>
                                    <td class="font-medium">{{ $product->name }}</td>
                                    <td class="text-center">
                                        <span class="ui-badge ui-badge-warning text-xs">{{ $product->times_sold }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="ui-badge ui-badge-success text-xs">{{ $product->total_quantity }}</span>
                                    </td>
                                    <td class="text-right tabular-nums">{{ $currency->symbol }}{{ number_format($product->sale_price, 2) }}</td>
                                    <td class="text-right tabular-nums font-semibold">
                                        {{ $currency->symbol }}{{ number_format($product->total_revenue, 2) }}
                                        <div class="mt-1 h-1.5 w-full rounded-full bg-slate-700/60 overflow-hidden">
                                            <div class="h-full rounded-full bg-gradient-to-r from-cyan-500 to-purple-600"
                                                style="width: {{ $topSellingProducts->max('total_revenue') > 0 ? min(100, ($product->total_revenue / $topSellingProducts->max('total_revenue')) * 100) : 0 }}%">
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-slate-500 py-8">Sin datos de productos vendidos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Top 5 Clientes + Ventas por Categoría --}}
        <div class="space-y-6">
            {{-- Top 5 Clientes --}}
            <div class="ui-panel">
                <div class="ui-panel__header">
                    <div>
                        <h2 class="ui-panel__title">Top 5 Clientes</h2>
                        <p class="ui-panel__subtitle">Mayor volumen de compras</p>
                    </div>
                    <a href="{{ route('admin.customers.index') }}" class="ui-btn ui-btn-ghost text-xs">
                        <i class="fas fa-eye"></i> Ver Todos
                    </a>
                </div>
                <div class="ui-panel__body !p-0">
                    {{-- Vista móvil: cards --}}
                    <div class="md:hidden space-y-1.5 p-2">
                        @forelse ($topCustomers as $index => $customer)
                            <div class="relative flex items-center gap-3 rounded-xl border border-slate-700/50 bg-slate-800/40 px-3 py-2.5 transition hover:bg-slate-800/70 overflow-hidden
                                {{ $index < 3 ? 'border-l-[3px] border-l-amber-500/80' : '' }}">
                                @if ($index < 3)
                                    <div class="absolute inset-0 bg-gradient-to-r from-amber-500/5 to-transparent pointer-events-none"></div>
                                @endif
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-[11px] font-bold flex-shrink-0
                                    {{ $index < 3 ? 'bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-md shadow-amber-500/20' : 'bg-slate-700 text-slate-400' }}">
                                    {{ $index + 1 }}
                                </span>
                                <div class="flex-1 min-w-0">
                                    <div class="text-[13px] font-semibold text-slate-100 truncate">{{ $customer->name }}</div>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span class="inline-flex items-center gap-1 rounded-md bg-purple-500/10 px-1.5 py-px text-[10px] font-semibold text-purple-400">
                                            <i class="fas fa-shopping-bag text-[9px]"></i> {{ $customer->total_products }}
                                        </span>
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <div class="text-sm font-bold tabular-nums bg-gradient-to-r from-emerald-400 to-teal-400 bg-clip-text text-transparent">{{ $currency->symbol }}{{ number_format($customer->total_spent, 2) }}</div>
                                    <div class="text-[10px] text-slate-500 mt-px">gastado</div>
                                </div>
                            </div>
                        @empty
                            <div class="px-3 py-8 text-center text-slate-500 text-sm">Sin datos de clientes.</div>
                        @endforelse
                    </div>

                    {{-- Vista desktop: tabla --}}
                    <div class="hidden md:block ui-table-wrap !rounded-none !border-0">
                        <table class="ui-table">
                            <thead>
                                <tr>
                                    <th class="w-12 text-center">#</th>
                                    <th>Cliente</th>
                                    <th class="text-right">Total Gastado</th>
                                    <th class="text-center">Prod.</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($topCustomers as $index => $customer)
                                    <tr>
                                        <td class="text-center">
                                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold
                                                {{ $index < 3 ? 'bg-gradient-to-br from-amber-500 to-orange-600 text-white' : 'bg-slate-700 text-slate-400' }}">
                                                {{ $index + 1 }}
                                            </span>
                                        </td>
                                        <td class="font-medium">{{ $customer->name }}</td>
                                        <td class="text-right tabular-nums font-semibold">
                                            {{ $currency->symbol }}{{ number_format($customer->total_spent, 2) }}
                                        </td>
                                        <td class="text-center">
                                            <span class="text-slate-300">{{ $customer->total_products }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-slate-500 py-8">Sin datos de clientes.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Ventas por Categoría Chart --}}
            <div class="ui-panel">
                <div class="ui-panel__header">
                    <div>
                        <h2 class="ui-panel__title">Ventas por Categoría</h2>
                        <p class="ui-panel__subtitle">Distribución comercial principal</p>
                    </div>
                </div>
                <div class="ui-panel__body">
                    <div class="h-72">
                        <canvas id="salesByCategoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- 7. FLUJO DE CAJA - Reactivo con selector e ingreso/egreso        --}}
    {{-- ================================================================ --}}
    <div class="ui-panel ui-panel--overflow-visible" x-data="{
        cashFlowMode: 'historical',
        historicalChartData: @js($chartData ?? []),
        currentCashCount: @js($currentCashCount ?? null),
        closedCashCounts: @js($closedCashCountsData ?? []),
        summary: { income: 0, expenses: 0, balance: 0 },

        init() { this.updateSummary('historical'); },

        formatCurrency(amount) {
            return '{{ $currency->symbol }}' + parseFloat(amount || 0).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        updateSummary(mode) {
            const dailyMovements = this.historicalChartData.daily_movements || [];
            let income = 0, expenses = 0;
            if (mode === 'historical') {
                income = (this.historicalChartData.income || []).reduce((a, b) => a + b, 0);
                expenses = (this.historicalChartData.expenses || []).reduce((a, b) => a + b, 0);
            } else if (mode === 'current') {
                const openId = this.currentCashCount?.id || null;
                const filtered = dailyMovements.filter(m => m.cash_count_id == openId);
                income = filtered.reduce((s, m) => s + parseFloat(m.income || 0), 0);
                expenses = filtered.reduce((s, m) => s + parseFloat(m.expense || 0), 0);
            } else if (mode.startsWith('cash_')) {
                const id = mode.replace('cash_', '');
                const filtered = dailyMovements.filter(m => m.cash_count_id == id);
                income = filtered.reduce((s, m) => s + parseFloat(m.income || 0), 0);
                expenses = filtered.reduce((s, m) => s + parseFloat(m.expense || 0), 0);
            }
            this.summary.income = income;
            this.summary.expenses = expenses;
            this.summary.balance = income - expenses;
            window.dispatchEvent(new CustomEvent('cashFlowModeChanged', { detail: { mode } }));
        },

        flowDropdownOpen: false,
        toggleDropdown() { this.flowDropdownOpen = !this.flowDropdownOpen; },
        closeDropdown() { this.flowDropdownOpen = false; },
        selectFlowMode(mode) {
            this.cashFlowMode = mode;
            this.flowDropdownOpen = false;
            this.updateSummary(mode);
        },

        cashCountOptions() {
            return (this.closedCashCounts || []).map(cc => ({
                id: 'cash_' + cc.id,
                name: (cc.opening_date_formatted || '') + ' - ' + (cc.closing_date_formatted || ''),
            }));
        },

        currentFlowLabel() {
            if (this.cashFlowMode === 'historical') return '📈 Histórico Completo';
            if (this.cashFlowMode === 'current') return '📊 Arqueo Actual';
            const opt = this.cashCountOptions().find(o => o.id === this.cashFlowMode);
            return opt ? '📋 ' + opt.name : 'Seleccionar...';
        }
    }">
        {{-- Header --}}
        <div class="ui-panel__header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="ui-panel__title">El Pulso del Dinero</h2>
                <p class="ui-panel__subtitle">Tendencia de Ingresos vs Egresos por Arqueo</p>
            </div>
            <div class="relative" @click.away="closeDropdown()">
                <button type="button" @click="toggleDropdown()"
                    class="ui-btn ui-btn-ghost text-sm min-w-[200px] justify-between">
                    <span x-text="currentFlowLabel()"></span>
                    <i class="fas fa-chevron-down text-xs transition-transform" :class="flowDropdownOpen ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="flowDropdownOpen" x-transition
                    class="absolute right-0 z-50 mt-1 w-72 rounded-xl border border-slate-600/50 bg-slate-900/95 shadow-2xl backdrop-blur-xl py-1.5 max-h-72 overflow-y-auto"
                    style="display: none;">
                    <button @click="selectFlowMode('historical')"
                        class="w-full flex items-center gap-3 pl-4 pr-3.5 py-2.5 text-left text-sm text-slate-200 hover:bg-slate-800/80 transition"
                        :class="cashFlowMode === 'historical' ? 'bg-cyan-500/10 text-cyan-300 font-semibold' : ''">
                        <i class="fas fa-history w-4 text-center"></i> 📈 Histórico Completo
                    </button>
                    <button @click="selectFlowMode('current')"
                        class="w-full flex items-center gap-3 pl-4 pr-3.5 py-2.5 text-left text-sm text-slate-200 hover:bg-slate-800/80 transition"
                        :class="cashFlowMode === 'current' ? 'bg-cyan-500/10 text-cyan-300 font-semibold' : ''">
                        <i class="fas fa-chart-bar w-4 text-center"></i> 📊 Arqueo Actual
                    </button>
                    <template x-if="cashCountOptions().length">
                        <div>
                            <div class="my-1 border-t border-slate-700/50"></div>
                            <template x-for="opt in cashCountOptions()" :key="opt.id">
                                <button @click="selectFlowMode(opt.id)"
                                    class="w-full flex items-center gap-3 pl-4 pr-3.5 py-2.5 text-left text-sm text-slate-200 hover:bg-slate-800/80 transition"
                                    :class="cashFlowMode === opt.id ? 'bg-cyan-500/10 text-cyan-300 font-semibold' : ''">
                                    <i class="fas fa-cash-register w-4 text-center"></i>
                                    <span x-text="opt.name"></span>
                                </button>
                            </template>
                        </div>
                    </template>
                </div>
                <span class="ui-badge ui-badge-warning text-xs">Tiempo Real</span>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="ui-panel__body">
            <div class="grid grid-cols-1 gap-3 md:grid-cols-3 mb-4">
                <div class="ui-widget ui-widget--dense ui-widget--success">
                    <div class="ui-widget__top">
                        <span class="ui-widget__icon"><i class="fas fa-arrow-up"></i></span>
                        <span class="ui-widget__trend">Ingresos</span>
                    </div>
                    <p class="ui-widget__label">Total Ingresos</p>
                    <p class="ui-widget__value" x-text="formatCurrency(summary.income)">0</p>
                </div>
                <div class="ui-widget ui-widget--dense ui-widget--danger">
                    <div class="ui-widget__top">
                        <span class="ui-widget__icon"><i class="fas fa-arrow-down"></i></span>
                        <span class="ui-widget__trend">Egresos</span>
                    </div>
                    <p class="ui-widget__label">Total Egresos</p>
                    <p class="ui-widget__value" x-text="formatCurrency(summary.expenses)">0</p>
                </div>
                <div class="ui-widget ui-widget--dense ui-widget--info">
                    <div class="ui-widget__top">
                        <span class="ui-widget__icon"><i class="fas fa-equals"></i></span>
                        <span class="ui-widget__trend">Balance</span>
                    </div>
                    <p class="ui-widget__label">Balance Neto</p>
                    <p class="ui-widget__value" x-text="formatCurrency(summary.balance)">0</p>
                </div>
            </div>

            {{-- Cash Flow Chart --}}
            <div class="h-[36rem] lg:h-[42rem]">
                <canvas id="cashFlowChart"></canvas>
            </div>
        </div>
    </div>

</div>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart === 'undefined') {
        console.error('[Dashboard v2] Chart.js no está cargado.');
        return;
    }

    Chart.defaults.color = '#94a3b8';
    Chart.defaults.font.family = "'Inter', 'Nunito', system-ui, sans-serif";

    initSalesTrendsChart();
    initPurchaseTrendsChart();
    initSalesByCategoryChart();
    initCashFlowChart();
});

// ============================================
// Gráfico 1: Rendimiento Mensual de Ventas
// ============================================
function initSalesTrendsChart() {
    const canvas = document.getElementById('salesTrendsChart');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');

    const labels = @json($salesMonthlyLabels ?? []);
    const salesData = @json($salesMonthlyData ?? []);
    const profitData = @json($profitMonthlyData ?? []);
    const transactionsData = @json($transactionsMonthlyData ?? []);
    const currencySymbol = '{{ $currency->symbol }}';

    const gradientSales = ctx.createLinearGradient(0, 0, 0, 350);
    gradientSales.addColorStop(0, 'rgba(34, 211, 238, 0.35)');
    gradientSales.addColorStop(1, 'rgba(34, 211, 238, 0.02)');

    const gradientProfit = ctx.createLinearGradient(0, 0, 0, 350);
    gradientProfit.addColorStop(0, 'rgba(167, 139, 250, 0.35)');
    gradientProfit.addColorStop(1, 'rgba(167, 139, 250, 0.02)');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Ventas Totales',
                    data: salesData,
                    type: 'line',
                    borderColor: '#22d3ee',
                    backgroundColor: gradientSales,
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#22d3ee',
                    pointBorderColor: '#0f172a',
                    pointBorderWidth: 2,
                    pointHoverRadius: 7,
                    pointRadius: 4,
                    order: 1
                },
                {
                    label: 'Ganancia Neta',
                    data: profitData,
                    type: 'line',
                    borderColor: '#a78bfa',
                    backgroundColor: gradientProfit,
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#a78bfa',
                    pointBorderColor: '#0f172a',
                    pointBorderWidth: 2,
                    pointHoverRadius: 7,
                    pointRadius: 4,
                    order: 2
                },
                {
                    label: 'Transacciones',
                    data: transactionsData,
                    type: 'bar',
                    backgroundColor: 'rgba(251, 191, 36, 0.35)',
                    borderColor: 'rgba(251, 191, 36, 0.8)',
                    borderWidth: 1.5,
                    borderRadius: 6,
                    barPercentage: 0.5,
                    yAxisID: 'y1',
                    order: 3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    labels: { usePointStyle: true, padding: 20, font: { size: 11 } }
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.95)',
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) label += ': ';
                            if (context.dataset.yAxisID === 'y1') {
                                label += context.parsed.y + ' ventas';
                            } else {
                                label += currencySymbol + (context.parsed.y || 0).toLocaleString('es-PE', { minimumFractionDigits: 2 });
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
                    title: { display: true, text: 'Monto (' + currencySymbol + ')', font: { size: 11 } },
                    grid: { color: 'rgba(148, 163, 184, 0.08)' },
                    ticks: { callback: v => currencySymbol + v.toLocaleString('es-PE') }
                },
                y1: {
                    beginAtZero: true,
                    position: 'right',
                    title: { display: true, text: 'N° de Ventas', font: { size: 11 } },
                    grid: { drawOnChartArea: false }
                },
                x: { grid: { display: false } }
            }
        }
    });
}

// ============================================
// Gráfico 2: Tendencia de Compras Mensuales
// ============================================
function initPurchaseTrendsChart() {
    const canvas = document.getElementById('purchaseTrendsChart');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');

    const gradient = ctx.createLinearGradient(0, 0, 0, 350);
    gradient.addColorStop(0, 'rgba(96, 165, 250, 0.5)');
    gradient.addColorStop(1, 'rgba(96, 165, 250, 0.05)');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($purchaseMonthlyLabels ?? []),
            datasets: [{
                label: 'Compras Mensuales',
                data: @json($purchaseMonthlyData ?? []),
                backgroundColor: gradient,
                borderColor: '#60a5fa',
                borderWidth: 1.5,
                borderRadius: 8,
                barPercentage: 0.6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(148, 163, 184, 0.08)' },
                    ticks: { callback: v => '{{ $currency->symbol }}' + v.toLocaleString('es-PE') }
                },
                x: { grid: { display: false } }
            }
        }
    });
}

// ============================================
// Gráfico 3: Ventas por Categoría
// ============================================
function initSalesByCategoryChart() {
    const canvas = document.getElementById('salesByCategoryChart');
    if (!canvas) return;

    new Chart(canvas.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: @json($salesByCategoryLabels ?? []),
            datasets: [{
                data: @json($salesByCategoryData ?? []),
                backgroundColor: ['#22d3ee', '#60a5fa', '#6366f1', '#a78bfa', '#34d399', '#fb7185'],
                borderColor: 'rgba(15, 23, 42, 0.8)',
                borderWidth: 2,
                hoverOffset: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '58%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 16,
                        font: { size: 11 }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': {{ $currency->symbol }}' + context.parsed.toLocaleString('es-PE', { minimumFractionDigits: 2 });
                        }
                    }
                }
            }
        }
    });
}

// ============================================
// Gráfico 4: Flujo de Caja (REACTIVO)
// ============================================
let cashFlowChart;

function initCashFlowChart() {
    const canvas = document.getElementById('cashFlowChart');
    if (!canvas) return;

    if (window.cashFlowChart instanceof Chart) {
        window.cashFlowChart.destroy();
    }

    const ctx = canvas.getContext('2d');
    const data = @js($chartData ?? []);

    window.cashFlowChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: (data.labels || []),
            datasets: [
                {
                    label: 'Ingresos',
                    data: (data.income || []),
                    backgroundColor: 'rgba(34, 211, 238, 0.75)',
                    borderColor: 'rgba(34, 211, 238, 0.9)',
                    borderWidth: 1,
                    borderRadius: 5,
                    order: 2,
                    yAxisID: 'y'
                },
                {
                    label: 'Egresos',
                    data: (data.expenses || []),
                    backgroundColor: 'rgba(251, 113, 133, 0.65)',
                    borderColor: 'rgba(251, 113, 133, 0.85)',
                    borderWidth: 1,
                    borderRadius: 5,
                    order: 2,
                    yAxisID: 'y'
                },
                {
                    label: 'Balance acumulado',
                    data: [],
                    type: 'line',
                    borderColor: '#a78bfa',
                    backgroundColor: 'rgba(167, 139, 250, 0.08)',
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: '#a78bfa',
                    order: 1,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        color: '#94a3b8',
                        font: { size: 11 }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.95)',
                    padding: 10,
                    cornerRadius: 6,
                    callbacks: {
                        label: function(context) {
                            let val = context.parsed.y;
                            const label = context.dataset.label + ': ';
                            return label + '{{ $currency->symbol }}' + val.toLocaleString('es-PE', { minimumFractionDigits: 2 });
                        }
                    }
                }
            },
            scales: {
                y: {
                    position: 'left',
                    beginAtZero: true,
                    grid: { color: 'rgba(148, 163, 184, 0.06)' },
                    border: { display: false },
                    ticks: {
                        color: '#94a3b8',
                        font: { size: 10 },
                        callback: val => '{{ $currency->symbol }}' + val.toLocaleString('es-PE')
                    }
                },
                y1: {
                    position: 'right',
                    beginAtZero: true,
                    grid: { drawOnChartArea: false },
                    border: { display: false },
                    ticks: {
                        color: '#a78bfa',
                        font: { size: 10 },
                        callback: val => '{{ $currency->symbol }}' + val.toLocaleString('es-PE')
                    }
                },
                x: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: { color: '#94a3b8', font: { size: 10 } }
                }
            }
        }
    });

    updateCashFlowChart('historical');
}

function updateCashFlowChart(mode) {
    if (!window.cashFlowChart) return;

    const dailyMovements = @js($chartData['daily_movements'] ?? []);
    const currentCash = @js($currentCashCount ?? null);

    let filteredData = [];

    if (mode === 'historical') {
        filteredData = dailyMovements;
    } else if (mode === 'current') {
        const openId = currentCash ? currentCash.id : null;
        filteredData = dailyMovements.filter(m => m.cash_count_id == openId);
    } else if (mode.startsWith('cash_')) {
        const id = mode.replace('cash_', '');
        filteredData = dailyMovements.filter(m => m.cash_count_id == id);
    }

    const chart = window.cashFlowChart;

    if (!filteredData || filteredData.length === 0) {
        chart.data.labels = ['Sin datos'];
        chart.data.datasets[0].data = [0];
        chart.data.datasets[1].data = [0];
        chart.data.datasets[2].data = [0];
        chart.update();
        return;
    }

    const isHistorical = mode === 'historical';
    let aggregated = {};

    filteredData.forEach(curr => {
        const key = isHistorical ? curr.date.substring(0, 7) : curr.date;
        if (!aggregated[key]) aggregated[key] = { income: 0, expense: 0 };
        aggregated[key].income += parseFloat(curr.income || 0);
        aggregated[key].expense += parseFloat(curr.expense || 0);
    });

    const keys = Object.keys(aggregated).sort();
    const incomeVals = keys.map(k => aggregated[k].income);
    const expenseVals = keys.map(k => aggregated[k].expense);

    // Labels
    const monthNames = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    const labels = keys.map(k => {
        if (isHistorical) {
            const [y, m] = k.split('-');
            return monthNames[parseInt(m) - 1] + " '" + y.slice(2);
        }
        const [y, m, d] = k.split('-');
        return d + '/' + m;
    });

    // Balance acumulado
    let acc = 0;
    const balanceData = keys.map(k => {
        acc += (aggregated[k].income - aggregated[k].expense);
        return acc;
    });

    // Eje Y principal: stepSize dinámico (~12 divisiones)
    const combined = [...incomeVals, ...expenseVals];
    const dataMax = Math.max(...combined, 1);
    const targetTicks = 12;
    const rawStep = dataMax / targetTicks;
    const mag = Math.pow(10, Math.floor(Math.log10(rawStep || 1)));
    const step = Math.max(Math.round(rawStep / mag) * mag, mag);
    chart.options.scales.y.max = undefined;
    chart.options.scales.y.ticks.stepSize = step;
    chart.options.scales.y.ticks.maxTicksLimit = targetTicks + 2;

    // Balance en eje Y secundario
    const balAbsMax = Math.max(Math.abs(Math.max(...balanceData, 0)), Math.abs(Math.min(...balanceData, 0)), 1);
    const balRaw = balAbsMax / targetTicks;
    const balMag = Math.pow(10, Math.floor(Math.log10(balRaw || 1)));
    const balStep = Math.max(Math.round(balRaw / balMag) * balMag, balMag);
    chart.options.scales.y1.max = undefined;
    chart.options.scales.y1.min = undefined;
    chart.options.scales.y1.ticks.stepSize = balStep;
    chart.options.scales.y1.ticks.maxTicksLimit = targetTicks + 2;

    // Ajustar ancho de barras
    const barW = keys.length <= 12 ? 0.75 : 0.9;
    chart.data.datasets[0].barPercentage = barW;
    chart.data.datasets[1].barPercentage = barW;

    chart.data.labels = labels;
    chart.data.datasets[0].data = incomeVals;
    chart.data.datasets[1].data = expenseVals;
    chart.data.datasets[2].data = balanceData;

    chart.update();
}

// Listener global para cambios de modo de Flujo de Caja
window.addEventListener('cashFlowModeChanged', function(event) {
    if (typeof updateCashFlowChart === 'function') {
        updateCashFlowChart(event.detail.mode);
    }
});
</script>
@endpush
