@extends('layouts.app')

@section('title', 'Gestión de Ventas')

@section('content')
<div class="space-y-6" id="salesRoot" data-currency-symbol="{{ $currency->symbol }}">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Gestión de Ventas</h1>
        </div>
        <div class="flex items-center space-x-3">
            @can('sales.report')
                <a href="{{ route('admin.sales.report') }}" class="btn-outline" target="_blank">
                    <i class="fas fa-file-pdf mr-2"></i>
                    Reporte
                </a>
            @endcan
            @if ($cashCount)
                @can('sales.create')
                    <a href="{{ route('admin.sales.create') }}" class="btn-primary">
                        <i class="fas fa-plus-circle mr-2"></i>
                        Nueva Venta
                    </a>
                @endcan
            @else
                @can('cash-counts.create')
                    <a href="{{ route('admin.cash-counts.create') }}" class="btn-danger">
                        <i class="fas fa-cash-register mr-2"></i>
                        Abrir Caja
                    </a>
                @endcan
            @endif
        </div>
    </div>

    {{-- Dashboard de Estadísticas Moderno --}}
    <div class="stats-dashboard">
        <div class="stats-grid">
            <div class="stat-card stat-primary" title="Porcentaje de ventas de esta semana respecto al total vendido desde que se abrió la caja actual">
                <div class="stat-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="stat-glow"></div>
                <div class="stat-content">
                    <div class="stat-value">{{ $currency->symbol }} {{ number_format($totalSalesAmountThisWeek, 2) }}</div>
                    <div class="stat-label">Ventas esta semana</div>
                    <div class="stat-trend">
                        @if($salesPercentageThisWeek > 0)
                            <i class="fas fa-arrow-up"></i>
                            <span>+{{ $salesPercentageThisWeek }}%</span>
                        @elseif($salesPercentageThisWeek < 0)
                            <i class="fas fa-arrow-down"></i>
                            <span>{{ $salesPercentageThisWeek }}%</span>
                        @else
                            <i class="fas fa-minus"></i>
                            <span>0%</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="stat-card stat-success" title="Porcentaje de ganancias de esta semana respecto al total de ganancias desde que se abrió la caja actual">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-glow"></div>
                <div class="stat-content">
                    <div class="stat-value">{{ $currency->symbol }} {{ number_format($totalProfitThisWeek, 2) }}</div>
                    <div class="stat-label">Ganancias esta semana</div>
                    <div class="stat-trend">
                        @if($profitPercentageThisWeek > 0)
                            <i class="fas fa-arrow-up"></i>
                            <span>+{{ $profitPercentageThisWeek }}%</span>
                        @elseif($profitPercentageThisWeek < 0)
                            <i class="fas fa-arrow-down"></i>
                            <span>{{ $profitPercentageThisWeek }}%</span>
                        @else
                            <i class="fas fa-minus"></i>
                            <span>0%</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="stat-card stat-warning" title="Porcentaje de cantidad de ventas de esta semana respecto al total de ventas desde que se abrió la caja actual">
                <div class="stat-icon">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="stat-glow"></div>
                <div class="stat-content">
                    <div class="stat-value">{{ $salesCountThisWeek }}</div>
                    <div class="stat-label">Ventas realizadas</div>
                    <div class="stat-trend">
                        @if($salesCountPercentageThisWeek > 0)
                            <i class="fas fa-arrow-up"></i>
                            <span>+{{ $salesCountPercentageThisWeek }}%</span>
                        @elseif($salesCountPercentageThisWeek < 0)
                            <i class="fas fa-arrow-down"></i>
                            <span>{{ $salesCountPercentageThisWeek }}%</span>
                        @else
                            <i class="fas fa-minus"></i>
                            <span>0%</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="stat-card stat-info" title="Variación del ticket promedio de esta semana respecto al promedio desde que se abrió la caja actual">
                <div class="stat-icon">
                    <i class="fas fa-calculator"></i>
                </div>
                <div class="stat-glow"></div>
                <div class="stat-content">
                    <div class="stat-value">{{ $currency->symbol }} {{ number_format($averageTicket, 2) }}</div>
                    <div class="stat-label">Ticket promedio</div>
                    <div class="stat-trend">
                        @if($averageTicketPercentage > 0)
                            <i class="fas fa-arrow-up"></i>
                            <span>+{{ $averageTicketPercentage }}%</span>
                        @elseif($averageTicketPercentage < 0)
                            <i class="fas fa-arrow-down"></i>
                            <span>{{ $averageTicketPercentage }}%</span>
                        @else
                            <i class="fas fa-minus"></i>
                            <span>0%</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
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
                        <input type="text" id="salesSearch" placeholder="Buscar por cliente o fecha...">
                        <div class="search-suggestions" id="searchSuggestions"></div>
                    </div>
                </div>

                <div class="view-toggles">
                    <button type="button" class="view-toggle active" data-view="table">
                        <i class="fas fa-table"></i>
                    </button>
                    <button type="button" class="view-toggle" data-view="cards">
                        <i class="fas fa-th-large"></i>
                    </button>
                </div>
            </div>
        </div>
        
        {{-- Filtros Avanzados --}}
        <div class="filters-section">
            <div class="filters-header">
                <div class="filters-title">
                    <i class="fas fa-filter"></i>
                    <span>Filtros Avanzados</span>
                </div>
                <button type="button" class="filters-toggle" id="filtersToggle">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
            
            <div class="filters-content" id="filtersContent">
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
                                <input type="date" id="dateFrom" class="filter-input">
                            </div>
                            <div class="date-input">
                                <label>Hasta:</label>
                                <input type="date" id="dateTo" class="filter-input">
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
                                    <input type="number" id="amountMin" class="filter-input" placeholder="0.00" step="0.01" min="0">
                                </div>
                            </div>
                            <div class="amount-input">
                                <label>Máximo:</label>
                                <div class="input-with-symbol">
                                    <span class="currency-symbol">{{ $currency->symbol }}</span>
                                    <input type="number" id="amountMax" class="filter-input" placeholder="999999.99" step="0.01" min="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="filters-actions">
                    <div class="filters-status" id="filtersStatus" style="display: none;">
                        <span class="status-text">Filtros activos:</span>
                        <span class="active-filters" id="activeFiltersList"></span>
                    </div>
                    <div class="filters-buttons">
                        <button type="button" class="btn-filter btn-apply" id="applyFilters">
                            <i class="fas fa-search"></i>
                            <span>Aplicar Filtros</span>
                        </button>
                        <button type="button" class="btn-filter btn-clear" id="clearFilters">
                            <i class="fas fa-times"></i>
                            <span>Limpiar Filtros</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="modern-card-body">
            {{-- Vista de tabla moderna --}}
            <div class="table-view" id="tableView">
                <div class="modern-table-container">
                    <table id="salesTable" class="modern-table">
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
                        <tbody>
                            @foreach ($sales as $sale)
                                <tr class="table-row">
                                    <td>
                                        <div class="row-number">
                                            {{ $loop->iteration }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="customer-info">
                                            <div class="customer-avatar">
                                                <i class="fas fa-user-circle"></i>
                                            </div>
                                            <div class="customer-details">
                                                <span class="customer-name">{{ $sale->customer->name }}</span>
                                                <span class="customer-email">{{ $sale->customer->email }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="date-info">
                                            <span class="date-main">{{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y') }}</span>
                                            <span class="date-time">{{ \Carbon\Carbon::parse($sale->sale_date)->format('H:i') }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="products-info">
                                            <div class="product-badge unique">
                                                <i class="fas fa-boxes"></i>
                                                <span>{{ $sale->saleDetails->count() }} únicos</span>
                                            </div>
                                            <div class="product-badge total">
                                                <i class="fas fa-cubes"></i>
                                                <span>{{ $sale->saleDetails->sum('quantity') }} totales</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="price-info">
                                            <span class="price-amount">{{ $currency->symbol }} {{ number_format($sale->total_price, 2) }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <button type="button" class="btn-modern btn-primary view-details bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center gap-2" 
                                                data-id="{{ $sale->id }}" 
                                                onclick="showSaleDetails({{ $sale->id }})">
                                            <i class="fas fa-list text-white"></i>
                                            <span class="text-white hidden sm:inline">Ver Detalle</span>
                                        </button>
                                    </td>
                                    <td>
                                        <div class="action-buttons flex gap-2">
                                            @can('sales.edit')
                                                <button type="button" class="btn-action btn-edit bg-yellow-500 hover:bg-yellow-600 text-white w-8 h-8 rounded-lg flex items-center justify-center transition-colors duration-200" 
                                                        data-id="{{ $sale->id }}" 
                                                        title="Editar venta"
                                                        onclick="editSale({{ $sale->id }})">
                                                    <i class="fas fa-edit text-white"></i>
                                                </button>
                                            @endcan
                                            @can('sales.destroy')
                                                <button type="button" class="btn-action btn-delete delete-sale bg-red-500 hover:bg-red-600 text-white w-8 h-8 rounded-lg flex items-center justify-center transition-colors duration-200" 
                                                        data-id="{{ $sale->id }}" 
                                                        title="Eliminar venta"
                                                        onclick="deleteSale({{ $sale->id }})">
                                                    <i class="fas fa-trash text-white"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Vista móvil optimizada --}}
            <div class="mobile-view" id="mobileView">
                <div class="mobile-sales-list space-y-3 p-3">
                    @foreach ($sales as $sale)
                        <div class="mobile-sale-card bg-white border border-gray-200 rounded-lg shadow-sm" data-sale-id="{{ $sale->id }}">
                            {{-- Header compacto --}}
                            <div class="flex items-center justify-between p-3 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-t-lg">
                                <div class="flex items-center gap-3">
                                    <span class="bg-white text-blue-600 px-2 py-1 rounded text-sm font-bold">#{{ str_pad($loop->iteration, 3, '0', STR_PAD_LEFT) }}</span>
                                    <div class="flex items-center gap-1">
                                        <span class="w-2 h-2 bg-green-400 rounded-full"></span>
                                        <span class="text-white text-sm">Completada</span>
                                    </div>
                                </div>
                                <div class="text-white font-bold text-lg">
                                    {{ $currency->symbol }} {{ number_format($sale->total_price, 2) }}
                                </div>
                            </div>

                            {{-- Body compacto --}}
                            <div class="p-3">
                                {{-- Cliente --}}
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-user text-blue-600"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-gray-900 font-semibold text-sm truncate">{{ $sale->customer->name }}</h4>
                                        <p class="text-gray-500 text-xs truncate">{{ $sale->customer->email }}</p>
                                    </div>
                                </div>

                                {{-- Detalles compactos --}}
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between text-sm">
                                        <div class="flex items-center gap-2 text-gray-600">
                                            <i class="fas fa-calendar text-blue-500 text-xs"></i>
                                            <span>Fecha</span>
                                        </div>
                                        <span class="text-gray-900 font-medium">{{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y H:i') }}</span>
                                    </div>
                                    
                                    <div class="flex items-center justify-between text-sm">
                                        <div class="flex items-center gap-2 text-gray-600">
                                            <i class="fas fa-boxes text-green-500 text-xs"></i>
                                            <span>Productos</span>
                                        </div>
                                        <div class="flex gap-1">
                                            <span class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full text-xs">{{ $sale->saleDetails->count() }} únicos</span>
                                            <span class="bg-green-100 text-green-800 px-2 py-0.5 rounded-full text-xs">{{ $sale->saleDetails->sum('quantity') }} totales</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Footer compacto --}}
                            <div class="p-3 bg-gray-50 rounded-b-lg border-t border-gray-200">
                                <div class="flex gap-2">
                                    <button type="button" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-sm font-medium transition-colors duration-200 flex items-center justify-center gap-2"
                                            data-id="{{ $sale->id }}" 
                                            onclick="showSaleDetails({{ $sale->id }})">
                                        <i class="fas fa-list text-white text-xs"></i>
                                        <span class="text-white hidden sm:inline">Ver Detalle</span>
                                    </button>
                                    
                                    @can('sales.edit')
                                        <button type="button" class="bg-yellow-500 hover:bg-yellow-600 text-white w-8 h-8 rounded flex items-center justify-center transition-colors duration-200"
                                                data-id="{{ $sale->id }}" 
                                                title="Editar venta"
                                                onclick="editSale({{ $sale->id }})">
                                            <i class="fas fa-edit text-white text-xs"></i>
                                        </button>
                                    @endcan
                                    @can('sales.destroy')
                                        <button type="button" class="bg-red-500 hover:bg-red-600 text-white w-8 h-8 rounded flex items-center justify-center transition-colors duration-200"
                                                data-id="{{ $sale->id }}" 
                                                title="Eliminar venta"
                                                onclick="deleteSale({{ $sale->id }})">
                                            <i class="fas fa-trash text-white text-xs"></i>
                                        </button>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Contenedor de paginación personalizada --}}
            <div class="modern-pagination-container"></div>

            {{-- Vista de tarjetas moderna --}}
            <div class="cards-view" id="cardsView" style="display: none;">
                <div class="modern-cards-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 p-6">
                    @foreach ($sales as $sale)
                        <div class="modern-sale-card bg-white border border-gray-200 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
                            <div class="sale-card-header bg-gradient-to-r from-blue-600 to-indigo-600 text-black p-4 rounded-t-xl">
                                <div class="sale-number text-black font-bold text-lg">
                                    #{{ str_pad($loop->iteration, 3, '0', STR_PAD_LEFT) }}
                                </div>
                                <div class="sale-status flex items-center gap-2">
                                    <span class="status-dot active w-3 h-3 bg-green-400 rounded-full"></span>
                                    <span class="status-text text-black font-medium">Completada</span>
                                </div>
                            </div>

                            <div class="sale-card-body p-6">
                                <div class="customer-section flex items-center gap-4 mb-6">
                                    <div class="customer-avatar-large w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-user-circle text-white text-2xl"></i>
                                    </div>
                                    <div class="customer-info-card flex-1">
                                        <h4 class="customer-name text-gray-900 font-bold text-lg mb-1">{{ $sale->customer->name }}</h4>
                                        <p class="customer-email text-gray-600 text-sm">{{ $sale->customer->email }}</p>
                                    </div>
                                </div>

                                <div class="sale-details space-y-4">
                                    <div class="detail-row flex justify-between items-center">
                                        <div class="detail-label flex items-center gap-2 text-gray-700 font-medium">
                                            <i class="fas fa-calendar-alt text-blue-500"></i>
                                            <span>Fecha</span>
                                        </div>
                                        <div class="detail-value text-gray-900 font-semibold">
                                            {{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y H:i') }}
                                        </div>
                                    </div>

                                    <div class="detail-row flex justify-between items-center">
                                        <div class="detail-label flex items-center gap-2 text-gray-700 font-medium">
                                            <i class="fas fa-boxes text-green-500"></i>
                                            <span>Productos</span>
                                        </div>
                                        <div class="detail-value">
                                            <div class="product-badges flex gap-2">
                                                <span class="mini-badge unique bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-semibold">{{ $sale->saleDetails->count() }} únicos</span>
                                                <span class="mini-badge total bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-semibold">{{ $sale->saleDetails->sum('quantity') }} totales</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="detail-row total-row flex justify-between items-center pt-4 border-t border-gray-200">
                                        <div class="detail-label flex items-center gap-2 text-gray-700 font-medium">
                                            <i class="fas fa-dollar-sign text-green-500"></i>
                                            <span>Total</span>
                                        </div>
                                        <div class="detail-value total-amount text-black font-bold text-xl">
                                            {{ $currency->symbol }} {{ number_format($sale->total_price, 2) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="sale-card-footer p-4 bg-gray-50 rounded-b-xl border-t border-gray-200">
                                <button type="button" class="btn-card-primary view-details bg-blue-600 hover:bg-blue-700 text-black px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center gap-2 w-full justify-center"
                                        data-id="{{ $sale->id }}" 
                                        onclick="showSaleDetails({{ $sale->id }})">
                                    <i class="fas fa-list text-black"></i>
                                    <span class="text-black hidden sm:inline">Ver Detalle</span>
                                </button>

                                <div class="card-actions flex justify-center gap-2 mt-3">
                                    @can('sales.edit')
                                        <button type="button" class="btn-card-action btn-edit bg-yellow-500 hover:bg-yellow-600 text-white w-10 h-10 rounded-lg flex items-center justify-center transition-colors duration-200"
                                                data-id="{{ $sale->id }}" 
                                                title="Editar venta"
                                                onclick="editSale({{ $sale->id }})">
                                            <i class="fas fa-edit text-white"></i>
                                        </button>
                                    @endcan
                                    @can('sales.destroy')
                                        <button type="button" class="btn-card-action delete delete-sale bg-red-500 hover:bg-red-600 text-white w-10 h-10 rounded-lg flex items-center justify-center transition-colors duration-200"
                                                data-id="{{ $sale->id }}" 
                                                title="Eliminar venta"
                                                onclick="deleteSale({{ $sale->id }})">
                                            <i class="fas fa-trash text-white"></i>
                                        </button>
                                    @endcan
                                    @can('sales.print')
                                        <button type="button" class="btn-card-action print bg-gray-500 hover:bg-gray-600 text-white w-10 h-10 rounded-lg flex items-center justify-center transition-colors duration-200" 
                                                title="Imprimir venta"
                                                onclick="printSale({{ $sale->id }})">
                                            <i class="fas fa-print text-white"></i>
                                        </button>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Modal moderno para mostrar detalles --}}
    <div class="modal-overlay" id="modalOverlay" style="display: none;">
        <div class="modal-container" id="modalContainer" style="display: none;">
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
                    <button type="button" class="modal-close-btn" onclick="closeModal()" aria-label="Close">
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
                                <div class="info-card-content" id="customerInfo">
                                    <!-- Se llena dinámicamente -->
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
                                <div class="info-card-content" id="saleDate">
                                    <!-- Se llena dinámicamente -->
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
                            <tbody id="saleDetailsTableBody">
                                <!-- Los detalles se cargarán aquí dinámicamente -->
                            </tbody>
                        </table>
                    </div>

                    {{-- Total destacado y Nota --}}
                    <div class="total-section">
                        <!-- Campo de Nota -->
                        <div class="note-card" id="noteCard" style="display: none;">
                            <div class="note-icon">
                                <i class="fas fa-sticky-note"></i>
                            </div>
                            <div class="note-content">
                                <span class="note-label">Nota de la Venta</span>
                                <div class="note-text" id="noteText"></div>
                            </div>
                        </div>
                        
                        <!-- Total de la venta -->
                        <div class="total-card">
                            <div class="total-icon">
                                <i class="fas fa-receipt"></i>
                            </div>
                            <div class="total-content">
                                <span class="total-label">Total de la Venta</span>
                                <span class="total-amount"><span id="modalTotal">0.00</span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer moderno --}}
            <div class="modal-footer-modern">
                <div class="footer-actions">
                    @can('sales.print')
                        <button type="button" class="btn-modal-action btn-print print-details">
                            <i class="fas fa-print"></i>
                            <span>Imprimir</span>
                        </button>
                    @endcan
                    <button type="button" class="btn-modal-action btn-secondary" onclick="closeModal()">
                        <i class="fas fa-times"></i>
                        <span>Cerrar</span>
                    </button>
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
    <script src="{{ asset('vendor/config.js') }}"></script>
    <script src="{{ asset('js/admin/sales/index.js') }}" defer></script>
@endpush 