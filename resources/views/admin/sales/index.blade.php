@extends('layouts.app')

@section('title', 'Gestión de Ventas')

@section('content')
    {{-- Script con datos iniciales --}}
    <script>
        window.salesData = @json($sales->items());
        window.currencySymbol = '{{ $currency->symbol }}';
    </script>

<div class="space-y-6" 
     id="salesRoot" 
     data-currency-symbol="{{ $currency->symbol }}"
     x-data="salesSPA()"
     x-init="init()">
    
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Gestión de Ventas</h1>
        </div>
        <div class="flex items-center space-x-3">
            @if ($permissions['can_report'])
                <a href="{{ route('admin.sales.report') }}" class="btn-outline" target="_blank">
                    <i class="fas fa-file-pdf mr-2"></i>
                    Reporte
                </a>
            @endif
            @if ($cashCount)
                @if ($permissions['can_create'])
                    <a href="{{ route('admin.sales.create') }}" class="btn-primary">
                        <i class="fas fa-plus-circle mr-2"></i>
                        Nueva Venta
                    </a>
                @endif
            @else
                @if ($permissions['can_create'])
                    <a href="{{ route('admin.cash-counts.create') }}" class="btn-danger">
                        <i class="fas fa-cash-register mr-2"></i>
                        Abrir Caja
                    </a>
                @endif
            @endif
        </div>
    </div>

    {{-- Dashboard de Estadísticas Moderno --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-4 gap-2 sm:gap-3 mb-6">
        <!-- Ventas Esta Semana (muestra montos de arqueo/semana/hoy) -->
        <x-dashboard-widget 
            title="Total en Ventas"
            value="{{ $totalSalesAmountSinceCashOpen ?? 0 }}"
            valueType="currency"
            currencySymbol="{{ $currency->symbol }}"
            icon="fas fa-shopping-bag"
            trend="{{ $salesPercentageThisWeek > 0 ? '+' . $salesPercentageThisWeek : $salesPercentageThisWeek }}%"
            trendIcon="{{ $salesPercentageThisWeek > 0 ? 'fas fa-arrow-up' : ($salesPercentageThisWeek < 0 ? 'fas fa-arrow-down' : 'fas fa-minus') }}"
            trendColor="{{ $salesPercentageThisWeek > 0 ? 'text-green-300' : ($salesPercentageThisWeek < 0 ? 'text-red-300' : 'text-gray-300') }}"
            :subtitle="'Arqueo Actual '  . ' | Semana: ' . number_format($totalSalesAmountThisWeek ?? 0, 2) . ' | Hoy: ' . number_format($totalSalesAmountToday ?? 0, 2)"
            subtitleIcon="fas fa-calendar-week"
            gradientFrom="from-blue-500"
            gradientTo="to-blue-600"
            progressWidth="100%"
            progressGradientFrom="from-blue-400"
            progressGradientTo="to-blue-500"
        />

        <!-- Ganancias Esta Semana (muestra arqueo/semana/hoy) -->
        <x-dashboard-widget 
            title="Ganancias Totales"
            value="{{ $totalProfitSinceCashOpen ?? 0 }}"
            valueType="currency"
            currencySymbol="{{ $currency->symbol }}"
            icon="fas fa-chart-line"
            trend="{{ $profitPercentageThisWeek > 0 ? '+' . $profitPercentageThisWeek : $profitPercentageThisWeek }}%"
            trendIcon="{{ $profitPercentageThisWeek > 0 ? 'fas fa-arrow-up' : ($salesPercentageThisWeek < 0 ? 'fas fa-arrow-down' : 'fas fa-minus') }}"
            trendColor="{{ $profitPercentageThisWeek > 0 ? 'text-green-300' : ($profitPercentageThisWeek < 0 ? 'text-red-300' : 'text-gray-300') }}"
            :subtitle="'Arqueo Actual | Semana: ' . number_format($totalProfitThisWeek ?? 0, 2) . ' | Hoy: ' . number_format($totalProfitToday ?? 0, 2)"
            subtitleIcon="fas fa-calendar-week"
            gradientFrom="from-green-500"
            gradientTo="to-emerald-600"
            progressWidth="100%"
            progressGradientFrom="from-green-400"
            progressGradientTo="to-emerald-500"
        />

        <!-- Ventas Realizadas -->
        <x-dashboard-widget 
            title="Ventas Realizadas"
            value="{{ $salesCountSinceCashOpen }}"
            valueType="number"
            icon="fas fa-receipt"
            trend="{{ $salesCountPercentageThisWeek > 0 ? '+' . $salesCountPercentageThisWeek : $salesCountPercentageThisWeek }}%"
            trendIcon="{{ $salesCountPercentageThisWeek > 0 ? 'fas fa-arrow-up' : ($salesCountPercentageThisWeek < 0 ? 'fas fa-arrow-down' : 'fas fa-minus') }}"
            trendColor="{{ $salesCountPercentageThisWeek > 0 ? 'text-green-300' : ($salesCountPercentageThisWeek < 0 ? 'text-red-300' : 'text-gray-300') }}"
            :subtitle="'Arqueo actual | Semana: ' . $salesCountThisWeek . ' | Hoy: ' . $salesCountToday"
            subtitleIcon="fas fa-calendar-week"
            gradientFrom="from-yellow-500"
            gradientTo="to-orange-500"
            progressWidth="100%"
            progressGradientFrom="from-yellow-400"
            progressGradientTo="to-orange-400"
        />

        <!-- Productos Vendidos (sustituye Ticket Promedio) -->
        <x-dashboard-widget 
            title="Productos Vendidos"
            value="{{ $productsQtySinceCashOpen }}"
            valueType="number"
            icon="fas fa-boxes"
            trend=""
            trendIcon="fas fa-minus"
            trendColor="text-gray-300"
            :subtitle="'Arqueo actual | Semana: ' . $productsQtyThisWeek . ' | Hoy: ' . $productsQtyToday"
            subtitleIcon="fas fa-calendar-week"
            gradientFrom="from-purple-500"
            gradientTo="to-indigo-600"
            progressWidth="100%"
            progressGradientFrom="from-purple-400"
            progressGradientTo="to-indigo-500"
        />
    </div>

    {{-- Tabla de Ventas Moderna --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <div class="modern-card-title">
                <div class="title-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="title-content">
                    <h3>Lista de Ventas</h3>
                    <p>Gestiona y visualiza todas las ventas registradas</p>
                </div>
            </div>

            <div class="modern-card-actions">
                <div class="search-container">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" 
                               x-model="searchTerm" 
                               @input.debounce.300ms="filterSales()"
                               placeholder="Buscar por cliente, producto, fecha (dd/mm/aa), monto, teléfono..."
                               class="search-input">
                        <button type="button" 
                                class="search-clear-btn"
                                x-show="searchTerm.length > 0"
                                @click="clearSearch()"
                                title="Limpiar búsqueda">
                            <i class="fas fa-times"></i>
                        </button>
                        <div class="search-suggestions" 
                             x-show="searchSuggestions.length > 0" 
                             x-transition>
                            <template x-for="suggestion in searchSuggestions" :key="suggestion.id">
                                <div class="suggestion-item" 
                                     @click="selectSuggestion(suggestion)"
                                     x-text="suggestion.text"></div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="view-toggles">
                    <button type="button" 
                            class="view-toggle" 
                            :class="{ 'active': currentView === 'table' }"
                            @click="changeView('table')"
                            title="Vista de tabla"
                            x-show="!isMobileView()">
                        <i class="fas fa-table"></i>
                    </button>
                    <button type="button" 
                            class="view-toggle" 
                            :class="{ 'active': currentView === 'cards' }"
                            @click="changeView('cards')"
                            title="Vista de tarjetas"
                            x-show="!isMobileView()">
                        <i class="fas fa-th-large"></i>
                    </button>
                </div>
            </div>
        </div>
        
        {{-- Filtros Avanzados --}}
        <div class="filters-section">
            <div class="filters-header" @click="toggleFilters()">
                <div class="filters-title">
                    <i class="fas fa-filter"></i>
                    <span>Filtros Avanzados</span>
                </div>
                <button type="button" class="filters-toggle">
                    <i class="fas fa-chevron-down" :class="{ 'rotate-180': filtersOpen }"></i>
                </button>
            </div>
            
            <div class="filters-content" :class="{ 'show': filtersOpen }">
                <div class="filters-grid">
                    <!-- Filtro de Fecha -->
                    <div class="filter-group">
                        <label class="filter-label">
                            <i class="fas fa-calendar-alt"></i>
                            Rango de Fechas
                        </label>
                        <div class="date-range">
                            <div class="date-input">
                                <label>Desde:</label>
                                <input type="date" x-model="filters.dateFrom" @change="filterSales()" class="filter-input">
                            </div>
                            <div class="date-input">
                                <label>Hasta:</label>
                                <input type="date" x-model="filters.dateTo" @change="filterSales()" class="filter-input">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Filtro de Monto -->
                    <div class="filter-group">
                        <label class="filter-label">
                            <i class="fas fa-dollar-sign"></i>
                            Rango de Montos
                        </label>
                        <div class="amount-range">
                            <div class="amount-input">
                                <label>Mínimo:</label>
                                <div class="input-with-symbol">
                                    <span class="currency-symbol">{{ $currency->symbol }}</span>
                                    <input type="number" 
                                           x-model="filters.amountMin" 
                                           @input.debounce.500ms="filterSales()"
                                           class="filter-input" 
                                           placeholder="0.00" 
                                           step="0.01" 
                                           min="0">
                                </div>
                            </div>
                            <div class="amount-input">
                                <label>Máximo:</label>
                                <div class="input-with-symbol">
                                    <span class="currency-symbol">{{ $currency->symbol }}</span>
                                    <input type="number" 
                                           x-model="filters.amountMax" 
                                           @input.debounce.500ms="filterSales()"
                                           class="filter-input" 
                                           placeholder="999999.99" 
                                           step="0.01" 
                                           min="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="filters-actions">
                    <div class="filters-status" x-show="activeFiltersCount > 0">
                        <span class="status-text">Filtros activos:</span>
                        <span class="active-filters" x-text="activeFiltersCount"></span>
                    </div>
                    <div class="filters-buttons">
                        <button type="button" class="btn-filter btn-clear" @click="clearFilters()">
                            <i class="fas fa-times"></i>
                            <span>Limpiar Filtros</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="modern-card-body">
            {{-- Loading State --}}
            <div x-show="loading" class="loading-container">
                <div class="loading-spinner"></div>
                <p class="loading-text">Cargando ventas...</p>
            </div>

            {{-- No Results State --}}
            <div x-show="!loading && {{ $sales->count() }} === 0" class="no-results">
                <div class="no-results-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3 class="no-results-title">No se encontraron ventas</h3>
                <p class="no-results-text">Intenta ajustar los filtros o términos de búsqueda</p>
            </div>

            {{-- Vista de tabla moderna --}}
            <div class="table-view" x-show="!loading && currentView === 'table' && {{ $sales->count() }} > 0">
                <div class="modern-table-container">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>
                                    <div class="th-content">
                                        <span>#</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="fas fa-user"></i>
                                        <span>Cliente</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="fas fa-calendar"></i>
                                        <span>Fecha</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="fas fa-boxes"></i>
                                        <span>Productos</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="fas fa-dollar-sign"></i>
                                        <span>Total</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="fas fa-list"></i>
                                        <span>Detalle</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="fas fa-cogs"></i>
                                        <span>Acciones</span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="salesTableBody">
                            @forelse ($sales as $index => $sale)
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $sales->firstItem() + $index }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                    <i class="fas fa-user text-blue-600"></i>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $sale->customer->name ?? 'Cliente no especificado' }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $sale->customer->phone ?? 'Sin teléfono' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div class="flex items-center">
                                            <i class="fas fa-calendar text-gray-400 mr-2"></i>
                                            {{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y') }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ \Carbon\Carbon::parse($sale->sale_date)->format('H:i') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div class="flex items-center">
                                            <i class="fas fa-boxes text-gray-400 mr-2"></i>
                                            {{ $sale->products_count }} productos
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        <div class="flex items-center">
                                            <i class="fas fa-dollar-sign text-green-500 mr-1"></i>
                                            {{ $currency->symbol }}{{ number_format($sale->total_price, 2) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div class="flex items-center">
                                            <i class="fas fa-list text-gray-400 mr-2"></i>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $sale->total_quantity }} unidades
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-2">
                                            @if ($permissions['can_show'])
                                                <button type="button" 
                                                        class="text-blue-600 hover:text-blue-900 transition-colors duration-200"
                                                        @click="showSaleDetails({{ $sale->id }})"
                                                        title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            @endif
                                            @if ($permissions['can_edit'])
                                                <a href="{{ route('admin.sales.edit', $sale->id) }}" 
                                                   class="text-indigo-600 hover:text-indigo-900 transition-colors duration-200"
                                                   title="Editar venta">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                            @if ($permissions['can_print'])
                                                <button type="button" 
                                                        class="text-green-600 hover:text-green-900 transition-colors duration-200"
                                                        @click="printSale({{ $sale->id }})"
                                                        title="Imprimir">
                                                    <i class="fas fa-print"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-shopping-bag text-gray-300 text-4xl mb-4"></i>
                                            <h3 class="text-lg font-medium text-gray-900 mb-2">No hay ventas registradas</h3>
                                            <p class="text-gray-500">Comienza registrando tu primera venta</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Vista de tarjetas moderna --}}
            <div class="cards-view" x-show="!loading && currentView === 'cards' && {{ $sales->count() }} > 0">
                <div class="modern-cards-grid" id="salesCardsGrid">
                    @forelse ($sales as $index => $sale)
                        <div class="modern-card sale-card">
                            <div class="card-header">
                                <div class="card-icon">
                                    <i class="fas fa-shopping-bag"></i>
                                </div>
                                <div class="card-title">
                                    <h4>Venta #{{ $sales->firstItem() + $index }}</h4>
                                    <p>{{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                            
                            <div class="card-content">
                                <div class="info-row">
                                    <div class="info-item">
                                        <i class="fas fa-user text-blue-500"></i>
                                        <span class="info-label">Cliente:</span>
                                        <span class="info-value">{{ $sale->customer->name ?? 'Cliente no especificado' }}</span>
                                    </div>
                                </div>
                                
                                <div class="info-row">
                                    <div class="info-item">
                                        <i class="fas fa-boxes text-green-500"></i>
                                        <span class="info-label">Productos:</span>
                                        <span class="info-value">{{ $sale->products_count }} productos</span>
                                    </div>
                                </div>
                                
                                <div class="info-row">
                                    <div class="info-item">
                                        <i class="fas fa-list text-purple-500"></i>
                                        <span class="info-label">Unidades:</span>
                                        <span class="info-value">{{ $sale->total_quantity }} unidades</span>
                                    </div>
                                </div>
                                
                                <div class="total-section">
                                    <div class="total-amount">
                                        <i class="fas fa-dollar-sign"></i>
                                        <span>{{ $currency->symbol }}{{ number_format($sale->total_price, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card-actions">
                                @if ($permissions['can_show'])
                                    <button type="button" 
                                            class="btn-action btn-view"
                                            @click="showSaleDetails({{ $sale->id }})"
                                            title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                @endif
                                @if ($permissions['can_edit'])
                                    <a href="{{ route('admin.sales.edit', $sale->id) }}" 
                                       class="btn-action btn-edit"
                                       title="Editar venta">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif
                                @if ($permissions['can_print'])
                                    <button type="button" 
                                            class="btn-action btn-print"
                                            @click="printSale({{ $sale->id }})"
                                            title="Imprimir">
                                        <i class="fas fa-print"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full">
                            <div class="text-center py-12">
                                <i class="fas fa-shopping-bag text-gray-300 text-6xl mb-4"></i>
                                <h3 class="text-xl font-medium text-gray-900 mb-2">No hay ventas registradas</h3>
                                <p class="text-gray-500">Comienza registrando tu primera venta</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Paginación Inteligente --}}
            @if ($sales->hasPages())
                <div class="mt-8 px-6">
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <div class="custom-pagination">
                            <div class="pagination-info">
                                <span id="paginationInfo">Mostrando
                                    {{ $sales->firstItem() ?? 0 }}-{{ $sales->lastItem() ?? 0 }} de
                                    {{ $sales->total() }} ventas</span>
                            </div>
                            <div class="pagination-controls">
                                <!-- Botón Anterior -->
                                @if ($sales->onFirstPage())
                                    <button class="pagination-btn" disabled>
                                        <i class="fas fa-chevron-left"></i>
                                        Anterior
                                    </button>
                                @else
                                    <a href="{{ $sales->previousPageUrl() }}" class="pagination-btn">
                                        <i class="fas fa-chevron-left"></i>
                                        Anterior
                                    </a>
                                @endif

                                <!-- Números de página -->
                                <div class="pagination-numbers">
                                    @php
                                        $currentPage = $sales->currentPage();
                                        $lastPage = $sales->lastPage();
                                        $start = max(1, $currentPage - 2);
                                        $end = min($lastPage, $currentPage + 2);
                                    @endphp
                                    
                                    @if($start > 1)
                                        <a href="{{ $sales->url(1) }}" class="pagination-number">1</a>
                                        @if($start > 2)
                                            <span class="page-separator">...</span>
                                        @endif
                                    @endif
                                    
                                    @for($i = $start; $i <= $end; $i++)
                                        @if($i == $currentPage)
                                            <span class="pagination-number active">{{ $i }}</span>
                                        @else
                                            <a href="{{ $sales->url($i) }}" class="pagination-number">{{ $i }}</a>
                                        @endif
                                    @endfor
                                    
                                    @if($end < $lastPage)
                                        @if($end < $lastPage - 1)
                                            <span class="page-separator">...</span>
                                        @endif
                                        <a href="{{ $sales->url($lastPage) }}" class="pagination-number">{{ $lastPage }}</a>
                                    @endif
                                </div>

                                <!-- Botón Siguiente -->
                                @if ($sales->hasMorePages())
                                    <a href="{{ $sales->nextPageUrl() }}" class="pagination-btn">
                                        Siguiente
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                @else
                                    <button class="pagination-btn" disabled>
                                        Siguiente
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal moderno para mostrar detalles --}}
    <div class="modal-overlay modal-compact" 
         x-show="modalOpen" 
         x-cloak
         x-ref="salesModal"
         style="display: none;"
         :style="modalOpen ? 'display: flex !important;' : ''"
         @click="closeModal()">
        <div class="modal-container" 
             @click.stop>
            <div class="modal-content modern-modal">
                {{-- Header moderno con gradiente --}}
                <div class="modal-header-modern">
                    <div class="modal-header-background">
                        <div class="modal-header-gradient"></div>
                        <div class="modal-header-pattern">
                            <div class="pattern-circle"></div>
                            <div class="pattern-circle"></div>
                            <div class="pattern-circle"></div>
                        </div>
                    </div>
                    <div class="modal-header-content">
                        <div class="modal-title-section">
                            <div class="modal-icon">
                                <i class="fas fa-receipt"></i>
                            </div>
                            <div class="modal-title-text">
                                <h4 class="modal-title-main">Detalle de la Venta</h4>
                                <p class="modal-subtitle">Información completa de la transacción</p>
                            </div>
                        </div>
                        <button type="button" class="modal-close-btn" @click="closeModal()" aria-label="Close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                {{-- Cuerpo del modal --}}
                <div class="modal-body-modern">
                    {{-- Información del cliente y venta --}}
                    <div class="sale-info-section">
                        <div class="flex flex-wrap -mx-4">
                            <div class="w-full md:w-1/2 px-4">
                                <div class="info-card customer-info-card">
                                    <div class="info-card-header">
                                        <div class="info-icon customer-icon">
                                            <i class="fas fa-user-circle"></i>
                                        </div>
                                        <h6 class="info-title">Información del Cliente</h6>
                                    </div>
                                    <div class="info-card-content">
                                        <div class="info-item">
                                            <span class="info-label">Nombre:</span>
                                            <span class="info-value" x-text="selectedSale?.customer?.name || 'N/A'"></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Email:</span>
                                            <span class="info-value" x-text="selectedSale?.customer?.email || 'No especificado'"></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Teléfono:</span>
                                            <span class="info-value" x-text="selectedSale?.customer?.phone || 'No especificado'"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="w-full md:w-1/2 px-4">
                                <div class="info-card date-info-card">
                                    <div class="info-card-header">
                                        <div class="info-icon date-icon">
                                            <i class="fas fa-calendar-alt"></i>
                                        </div>
                                        <h6 class="info-title">Fecha de Venta</h6>
                                    </div>
                                    <div class="info-card-content">
                                        <div class="info-item">
                                            <span class="info-label">Fecha:</span>
                                            <span class="info-value" x-text="selectedSale ? formatDate(selectedSale.sale_date) : 'N/A'"></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Hora:</span>
                                            <span class="info-value" x-text="selectedSale ? formatTime(selectedSale.sale_date) : 'N/A'"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tabla de productos moderna --}}
                    <div class="products-section">
                        <div class="section-header">
                            <div class="section-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <h5 class="section-title">Productos Vendidos</h5>
                        </div>

                        <div class="modern-table-wrapper">
                            <table class="modern-details-table">
                                <thead>
                                    <tr>
                                        <th>
                                            <div class="th-content">
                                                <i class="fas fa-barcode"></i>
                                                <span>Código</span>
                                            </div>
                                        </th>
                                        <th>
                                            <div class="th-content">
                                                <i class="fas fa-box"></i>
                                                <span>Producto</span>
                                            </div>
                                        </th>
                                        <th>
                                            <div class="th-content">
                                                <i class="fas fa-tags"></i>
                                                <span>Categoría</span>
                                            </div>
                                        </th>
                                        <th class="text-center">
                                            <div class="th-content">
                                                <i class="fas fa-sort-numeric-up"></i>
                                                <span>Cantidad</span>
                                            </div>
                                        </th>
                                        <th class="text-right">
                                            <div class="th-content">
                                                <i class="fas fa-dollar-sign"></i>
                                                <span>Precio Unit.</span>
                                            </div>
                                        </th>
                                        <th class="text-right">
                                            <div class="th-content">
                                                <i class="fas fa-calculator"></i>
                                                <span>Subtotal</span>
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="detail in selectedSale?.sale_details || []" :key="detail.id">
                                        <tr>
                                            <td x-text="detail.product?.code || 'N/A'"></td>
                                            <td x-text="detail.product?.name || 'N/A'"></td>
                                            <td x-text="detail.product?.category?.name || 'Sin categoría'"></td>
                                            <td class="text-center" x-text="detail.quantity"></td>
                                            <td class="text-right" x-text="formatCurrency(detail.unit_price)"></td>
                                            <td class="text-right" x-text="formatCurrency(detail.subtotal)"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        {{-- Total destacado y Nota --}}
                        <div class="total-section">
                            <!-- Campo de Nota -->
                            <div class="note-card" x-show="selectedSale?.note">
                                <div class="note-icon">
                                    <i class="fas fa-sticky-note"></i>
                                </div>
                                <div class="note-content">
                                    <span class="note-label">Nota de la Venta</span>
                                    <div class="note-text" x-text="selectedSale?.note || ''"></div>
                                </div>
                            </div>
                            
                            <!-- Total de la venta -->
                            <div class="total-card">
                                <div class="total-icon">
                                    <i class="fas fa-receipt"></i>
                                </div>
                                <div class="total-content">
                                    <span class="total-label">Total de la Venta</span>
                                    <span class="total-amount bg-green-500" x-text="selectedSale ? formatCurrency(selectedSale.total_price) : '$0.00'"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer moderno --}}
                <div class="modal-footer-modern">
                    <div class="footer-actions">
                        @if ($permissions['can_print'])
                            <button type="button" 
                                    class="btn-modal-action btn-print"
                                    @click="printSale(selectedSale?.id)">
                                <i class="fas fa-print"></i>
                                <span>Imprimir</span>
                            </button>
                        @endif
                        <button type="button" class="btn-modal-action btn-secondary" @click="closeModal()">
                            <i class="fas fa-times"></i>
                            <span>Cerrar</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/sales/index.css') }}">
@endpush

@push('js')
    <script src="{{ asset('js/admin/sales/index.js') }}"></script>
@endpush 