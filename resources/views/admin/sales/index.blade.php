@extends('adminlte::page')

@section('title', 'Gestión de Ventas')

@section('content_header')
    <div class="modern-header">
        <div class="header-gradient"></div>
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="header-text">
                            <h1 class="header-title">Gestión de Ventas</h1>
                            <p class="header-subtitle">Panel de control y análisis de ventas</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="header-actions">
                        @can('sales.report')
                            <a href="{{ route('admin.sales.report') }}" class="btn btn-modern btn-report" target="_blank">
                                <i class="fas fa-file-pdf"></i>
                                <span>Reporte</span>
                            </a>
                        @endcan
                        @if ($cashCount)
                            @can('sales.create')
                                <a href="{{ route('admin.sales.create') }}" class="btn btn-modern btn-primary-modern">
                                    <i class="fas fa-plus-circle"></i>
                                    <span>Nueva Venta</span>
                                </a>
                            @endcan
                        @else
                            @can('cash-counts.create')
                                <a href="{{ route('admin.cash-counts.create') }}" class="btn btn-modern btn-danger-modern">
                                    <i class="fas fa-cash-register"></i>
                                    <span>Abrir Caja</span>
                                </a>
                            @endcan
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
    {{-- Dashboard de Estadísticas Moderno --}}
    <div class="stats-dashboard">
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="stats-card stats-card-primary" title="Porcentaje de ventas de esta semana respecto al total vendido desde que se abrió la caja actual">
                    <div class="stats-card-body">
                        <div class="stats-icon">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <div class="stats-content">
                            <h3 class="stats-value">{{ $currency->symbol }}
                                {{ number_format($totalSalesAmountThisWeek, 2) }}</h3>
                            <p class="stats-label">Ventas esta semana</p>
                            <div class="stats-trend @if($salesPercentageThisWeek < 0) negative @elseif($salesPercentageThisWeek == 0) neutral @endif">
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
                    <div class="stats-wave">
                        <svg viewBox="0 0 1200 120" preserveAspectRatio="none">
                            <path
                                d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z"
                                opacity=".25"></path>
                            <path
                                d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z"
                                opacity=".5"></path>
                            <path
                                d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="stats-card stats-card-success" title="Porcentaje de ganancias de esta semana respecto al total de ganancias desde que se abrió la caja actual">
                    <div class="stats-card-body">
                        <div class="stats-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stats-content">
                            <h3 class="stats-value">{{ $currency->symbol }} {{ number_format($totalProfitThisWeek, 2) }}
                            </h3>
                            <p class="stats-label">Ganancias esta semana</p>
                            <div class="stats-trend @if($profitPercentageThisWeek < 0) negative @elseif($profitPercentageThisWeek == 0) neutral @endif">
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
                    <div class="stats-wave">
                        <svg viewBox="0 0 1200 120" preserveAspectRatio="none">
                            <path
                                d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z"
                                opacity=".25"></path>
                            <path
                                d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z"
                                opacity=".5"></path>
                            <path
                                d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="stats-card stats-card-warning" title="Porcentaje de cantidad de ventas de esta semana respecto al total de ventas desde que se abrió la caja actual">
                    <div class="stats-card-body">
                        <div class="stats-icon">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <div class="stats-content">
                            <h3 class="stats-value">{{ $salesCountThisWeek }}</h3>
                            <p class="stats-label">Ventas realizadas</p>
                            <div class="stats-trend @if($salesCountPercentageThisWeek < 0) negative @elseif($salesCountPercentageThisWeek == 0) neutral @endif">
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
                    <div class="stats-wave">
                        <svg viewBox="0 0 1200 120" preserveAspectRatio="none">
                            <path
                                d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z"
                                opacity=".25"></path>
                            <path
                                d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z"
                                opacity=".5"></path>
                            <path
                                d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="stats-card stats-card-info" title="Variación del ticket promedio de esta semana respecto al promedio desde que se abrió la caja actual">
                    <div class="stats-card-body">
                        <div class="stats-icon">
                            <i class="fas fa-calculator"></i>
                        </div>
                        <div class="stats-content">
                            <h3 class="stats-value">{{ $currency->symbol }} {{ number_format($averageTicket, 2) }}</h3>
                            <p class="stats-label">Ticket promedio</p>
                            <div class="stats-trend @if($averageTicketPercentage < 0) negative @elseif($averageTicketPercentage == 0) neutral @endif">
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
                    <div class="stats-wave">
                        <svg viewBox="0 0 1200 120" preserveAspectRatio="none">
                            <path
                                d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z"
                                opacity=".25"></path>
                            <path
                                d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z"
                                opacity=".5"></path>
                            <path
                                d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z">
                            </path>
                        </svg>
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
                                            <span
                                                class="date-main">{{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y') }}</span>
                                            <span
                                                class="date-time">{{ \Carbon\Carbon::parse($sale->sale_date)->format('H:i') }}</span>
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
                                            <span class="price-amount">{{ $currency->symbol }}
                                                {{ number_format($sale->total_price, 2) }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <button type="button" class="btn-modern btn-primary view-details"
                                            data-id="{{ $sale->id }}" data-toggle="modal"
                                            data-target="#saleDetailsModal">
                                            <i class="fas fa-list"></i>
                                            <span>Ver Detalle</span>
                                        </button>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            @can('sales.edit')
                                                <button type="button" class="btn-action btn-edit"
                                                    data-id="{{ $sale->id }}" data-toggle="tooltip" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            @endcan
                                            @can('sales.destroy')
                                                <button type="button" class="btn-action btn-delete delete-sale"
                                                    data-id="{{ $sale->id }}" data-toggle="tooltip" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
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
                <div class="mobile-sales-list">
                    @foreach ($sales as $sale)
                        <div class="mobile-sale-card" data-sale-id="{{ $sale->id }}">
                            <div class="mobile-card-header">
                                <div class="mobile-sale-number">
                                    <span class="number-badge">#{{ $loop->iteration }}</span>
                                </div>
                                <div class="mobile-sale-total">
                                    <span class="total-amount-mobile">{{ $currency->symbol }} {{ number_format($sale->total_price, 2) }}</span>
                                </div>
                            </div>
                            
                            <div class="mobile-card-body">
                                <div class="mobile-customer-section">
                                    <div class="mobile-customer-avatar">
                                        <i class="fas fa-user-circle"></i>
                                    </div>
                                    <div class="mobile-customer-info">
                                        <h4 class="mobile-customer-name">{{ $sale->customer->name }}</h4>
                                        <p class="mobile-customer-email">{{ $sale->customer->email }}</p>
                                    </div>
                                </div>
                                
                                <div class="mobile-sale-details">
                                    <div class="mobile-detail-row">
                                        <div class="mobile-detail-label">
                                            <i class="fas fa-calendar-alt"></i>
                                            <span>Fecha</span>
                                        </div>
                                        <div class="mobile-detail-value">
                                            {{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y H:i') }}
                                        </div>
                                    </div>
                                    
                                    <div class="mobile-detail-row">
                                        <div class="mobile-detail-label">
                                            <i class="fas fa-boxes"></i>
                                            <span>Productos</span>
                                        </div>
                                        <div class="mobile-detail-value">
                                            <div class="mobile-product-badges">
                                                <span class="mobile-badge unique">{{ $sale->saleDetails->count() }} únicos</span>
                                                <span class="mobile-badge total">{{ $sale->saleDetails->sum('quantity') }} totales</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mobile-card-footer">
                                <div class="mobile-actions">
                                    <button type="button" class="mobile-btn mobile-btn-primary view-details"
                                        data-id="{{ $sale->id }}" data-toggle="modal" data-target="#saleDetailsModal">
                                        <i class="fas fa-list"></i>
                                        <span>Ver Detalle</span>
                                    </button>
                                    
                                    <div class="mobile-action-buttons">
                                        @can('sales.edit')
                                            <button type="button" class="mobile-btn-action btn-edit"
                                                data-id="{{ $sale->id }}" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        @endcan
                                        @can('sales.destroy')
                                            <button type="button" class="mobile-btn-action delete-sale"
                                                data-id="{{ $sale->id }}" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endcan
                                    </div>
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
                <div class="modern-cards-grid">
                    @foreach ($sales as $sale)
                        <div class="modern-sale-card">
                            <div class="sale-card-header">
                                <div class="sale-number">
                                    #{{ str_pad($loop->iteration, 3, '0', STR_PAD_LEFT) }}
                                </div>
                                <div class="sale-status">
                                    <span class="status-dot active"></span>
                                    <span class="status-text">Completada</span>
                                </div>
                            </div>

                            <div class="sale-card-body">
                                <div class="customer-section">
                                    <div class="customer-avatar-large">
                                        <i class="fas fa-user-circle"></i>
                                    </div>
                                    <div class="customer-info-card">
                                        <h4 class="customer-name">{{ $sale->customer->name }}</h4>
                                        <p class="customer-email">{{ $sale->customer->email }}</p>
                                    </div>
                                </div>

                                <div class="sale-details">
                                    <div class="detail-row">
                                        <div class="detail-label">
                                            <i class="fas fa-calendar-alt"></i>
                                            <span>Fecha</span>
                                        </div>
                                        <div class="detail-value">
                                            {{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y H:i') }}
                                        </div>
                                    </div>

                                    <div class="detail-row">
                                        <div class="detail-label">
                                            <i class="fas fa-boxes"></i>
                                            <span>Productos</span>
                                        </div>
                                        <div class="detail-value">
                                            <div class="product-badges">
                                                <span class="mini-badge unique">{{ $sale->saleDetails->count() }}
                                                    únicos</span>
                                                <span class="mini-badge total">{{ $sale->saleDetails->sum('quantity') }}
                                                    totales</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="detail-row total-row">
                                        <div class="detail-label">
                                            <i class="fas fa-dollar-sign"></i>
                                            <span>Total</span>
                                        </div>
                                        <div class="detail-value total-amount">
                                            {{ $currency->symbol }} {{ number_format($sale->total_price, 2) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="sale-card-footer">
                                <button type="button" class="btn-card-primary view-details"
                                    data-id="{{ $sale->id }}" data-toggle="modal" data-target="#saleDetailsModal">
                                    <i class="fas fa-list"></i>
                                    <span>Ver Detalle</span>
                                </button>

                                <div class="card-actions">
                                    @can('sales.edit')
                                        <button type="button" class="btn-card-action btn-edit"
                                            data-id="{{ $sale->id }}" data-toggle="tooltip" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    @endcan
                                    @can('sales.destroy')
                                        <button type="button" class="btn-card-action delete delete-sale"
                                            data-id="{{ $sale->id }}" data-toggle="tooltip" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endcan
                                    @can('sales.print')
                                        <button type="button" class="btn-card-action print" data-toggle="tooltip"
                                            title="Imprimir">
                                            <i class="fas fa-print"></i>
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
    <div class="modal fade" id="saleDetailsModal" tabindex="-1" role="dialog"
        aria-labelledby="saleDetailsModalLabel">
        <div class="modal-dialog modal-xl" role="document">
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
                                <h4 class="modal-title-main" id="saleDetailsModalLabel">Detalle de la Venta</h4>
                                <p class="modal-subtitle">Información completa de la transacción</p>
                            </div>
                        </div>
                        <button type="button" class="modal-close-btn" data-dismiss="modal" aria-label="Close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                {{-- Cuerpo del modal --}}
                <div class="modal-body-modern">
                    {{-- Información del cliente y venta --}}
                    <div class="sale-info-section">
                        <div class="row">
                            <div class="col-md-6">
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
                            <div class="col-md-6">
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
                                    <span class="total-amount">{{ $currency->symbol }}<span
                                            id="modalTotal">0.00</span></span>
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
                        <button type="button" class="btn-modal-action btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i>
                            <span>Cerrar</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin/sales/index.css') }}">
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/admin/sales/index.js') }}"></script>
@stop 