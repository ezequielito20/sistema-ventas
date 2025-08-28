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
            icon="fas fa-chart-line"
            trend="{{ $profitPercentageThisWeek > 0 ? '+' . $profitPercentageThisWeek : $profitPercentageThisWeek }}%"
            trendIcon="{{ $profitPercentageThisWeek > 0 ? 'fas fa-arrow-up' : ($profitPercentageThisWeek < 0 ? 'fas fa-arrow-down' : 'fas fa-minus') }}"
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
            <div x-show="!loading && filteredSales.length === 0" class="no-results">
                <div class="no-results-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3 class="no-results-title">No se encontraron ventas</h3>
                <p class="no-results-text">Intenta ajustar los filtros o términos de búsqueda</p>
            </div>

            {{-- Vista de tabla moderna --}}
            <div class="table-view" x-show="!loading && currentView === 'table' && filteredSales.length > 0">
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
                            <!-- Table content will be dynamically rendered by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Vista de tarjetas moderna --}}
            <div class="cards-view" x-show="!loading && currentView === 'cards' && filteredSales.length > 0">
                <div class="modern-cards-grid" id="salesCardsGrid">
                    <!-- Cards content will be dynamically rendered by JavaScript -->
                </div>
            </div>

            {{-- Paginación Inteligente --}}
            <div class="pagination-container" id="salesPagination">
                <!-- Pagination content will be dynamically rendered by JavaScript -->
            </div>
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