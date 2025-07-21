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
                <div class="stats-card stats-card-primary">
                    <div class="stats-card-body">
                        <div class="stats-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                        <div class="stats-content">
                            <h3 class="stats-value">{{ $currency->symbol }}
                                {{ number_format($totalSalesAmountThisWeek, 2) }}</h3>
                            <p class="stats-label">Ventas esta semana</p>
                            <div class="stats-trend">
                                <i class="fas fa-arrow-up"></i>
                                <span>+12.5%</span>
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
                <div class="stats-card stats-card-success">
                    <div class="stats-card-body">
                        <div class="stats-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                        <div class="stats-content">
                            <h3 class="stats-value">{{ $currency->symbol }} {{ number_format($totalProfitThisWeek, 2) }}
                            </h3>
                            <p class="stats-label">Ganancias esta semana</p>
                            <div class="stats-trend">
                                <i class="fas fa-arrow-up"></i>
                                <span>+8.3%</span>
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
                <div class="stats-card stats-card-warning">
                    <div class="stats-card-body">
                        <div class="stats-icon">
                    <i class="fas fa-receipt"></i>
                </div>
                        <div class="stats-content">
                            <h3 class="stats-value">{{ $salesCountThisWeek }}</h3>
                            <p class="stats-label">Ventas realizadas</p>
                            <div class="stats-trend">
                                <i class="fas fa-arrow-up"></i>
                                <span>+5.2%</span>
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
                <div class="stats-card stats-card-info">
                    <div class="stats-card-body">
                        <div class="stats-icon">
                            <i class="fas fa-calculator"></i>
                        </div>
                        <div class="stats-content">
                            <h3 class="stats-value">{{ $currency->symbol }} {{ number_format($averageTicket, 2) }}</h3>
                            <p class="stats-label">Ticket promedio</p>
                            <div class="stats-trend">
                                <i class="fas fa-arrow-up"></i>
                                <span>+3.1%</span>
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
                                                <button type="button" class="btn-action btn-edit" data-toggle="tooltip"
                                                    title="Editar">
                                            <i class="fas fa-edit"></i>
                                                </button>
                                    @endcan
                                    @can('sales.destroy')
                                                <button type="button" class="btn-action btn-delete delete-sale"
                                            data-id="{{ $sale->id }}" data-toggle="tooltip" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endcan
                                    {{-- @can('sales.print')
                                                <button type="button" class="btn-action btn-print" data-toggle="tooltip"
                                                    title="Imprimir">
                                            <i class="fas fa-print"></i>
                                                </button>
                                    @endcan --}}
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
                </div>
            </div>

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
                                        <button type="button" class="btn-card-action edit" data-toggle="tooltip"
                                            title="Editar">
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
    <div class="modal fade" id="saleDetailsModal" tabindex="-1" role="dialog" aria-labelledby="saleDetailsModalLabel"
        aria-hidden="true">
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

                        {{-- Total destacado --}}
                        <div class="total-section">
                            <div class="total-card">
                                <div class="total-icon">
                                    <i class="fas fa-receipt"></i>
                                </div>
                                <div class="total-content">
                                    <span class="total-label">Total de la Venta</span>
                                    <span class="total-amount">{{ $currency->symbol }}<span id="modalTotal">0.00</span></span>
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
    <style>
        /* Variables CSS */
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #48bb78;
            --warning-color: #ed8936;
            --danger-color: #f56565;
            --info-color: #4299e1;
            --dark-color: #2d3748;
            --light-color: #f7fafc;
            --border-radius: 12px;
            --box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-success: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            --gradient-warning: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);
            --gradient-info: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
        }

        /* Header Moderno */
        .modern-header {
            background: var(--gradient-primary);
            position: relative;
            overflow: hidden;
            margin: -15px -15px 20px -15px;
            padding: 2rem 0;
            border-radius: 0 0 20px 20px;
        }

        .header-gradient {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
        }

        .header-content {
            display: flex;
            align-items: center;
            gap: 1rem;
            position: relative;
            z-index: 2;
        }

        .header-icon {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: white;
            backdrop-filter: blur(10px);
        }

        .header-title {
            color: white;
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .header-subtitle {
            color: rgba(255, 255, 255, 0.9);
            margin: 0;
            font-size: 1rem;
        }

        .header-actions {
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
            position: relative;
            z-index: 2;
        }

        .btn-modern {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-modern:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            color: white;
            text-decoration: none;
        }

        .btn-primary-modern {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            border: none;
        }

        .btn-danger-modern {
            background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
            border: none;
        }

        .btn-report {
            background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
            border: none;
        }

        /* Dashboard de Estadísticas */
        .stats-dashboard {
            margin-bottom: 2rem;
        }

        .stats-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
            box-shadow: var(--box-shadow);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            height: 140px;
        }

        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .stats-card-body {
            display: flex;
            align-items: center;
            gap: 1rem;
            position: relative;
            z-index: 2;
        }

        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .stats-card-primary .stats-icon {
            background: var(--gradient-primary);
        }

        .stats-card-success .stats-icon {
            background: var(--gradient-success);
        }

        .stats-card-warning .stats-icon {
            background: var(--gradient-warning);
        }

        .stats-card-info .stats-icon {
            background: var(--gradient-info);
        }

        .stats-content {
            flex: 1;
        }

        .stats-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-color);
            margin: 0;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .stats-label {
            color: #4a5568;
            font-size: 0.9rem;
            margin: 0.25rem 0;
            font-weight: 600;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .stats-trend {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.8rem;
            font-weight: 700;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.9);
            color: var(--success-color);
            text-shadow: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .stats-wave {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 40px;
            opacity: 0.1;
        }

        .stats-card-primary .stats-wave path {
            fill: var(--primary-color);
        }

        .stats-card-success .stats-wave path {
            fill: var(--success-color);
        }

        .stats-card-warning .stats-wave path {
            fill: var(--warning-color);
        }

        .stats-card-info .stats-wave path {
            fill: var(--info-color);
        }

        /* Mejores contrastes para cada tipo de tarjeta */
        .stats-card-success {
            background: linear-gradient(135deg, #f0fff4 0%, #c6f6d5 100%);
            border: 1px solid #9ae6b4;
        }

        .stats-card-success .stats-value {
            color: #22543d;
            font-weight: 800;
        }

        .stats-card-success .stats-label {
            color: #2f855a;
            font-weight: 700;
        }

        .stats-card-success .stats-trend {
            background: rgba(34, 84, 61, 0.1);
            color: #22543d;
            border: 1px solid #9ae6b4;
        }

        .stats-card-primary {
            background: linear-gradient(135deg, #ebf8ff 0%, #bee3f8 100%);
            border: 1px solid #90cdf4;
        }

        .stats-card-primary .stats-value {
            color: #1a365d;
            font-weight: 800;
        }

        .stats-card-primary .stats-label {
            color: #2c5282;
            font-weight: 700;
        }

        .stats-card-primary .stats-trend {
            background: rgba(26, 54, 93, 0.1);
            color: #1a365d;
            border: 1px solid #90cdf4;
        }

        .stats-card-warning {
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            border: 1px solid #fbbf24;
        }

        .stats-card-warning .stats-value {
            color: #92400e;
            font-weight: 800;
        }

        .stats-card-warning .stats-label {
            color: #b45309;
            font-weight: 700;
        }

        .stats-card-warning .stats-trend {
            background: rgba(146, 64, 14, 0.1);
            color: #92400e;
            border: 1px solid #fbbf24;
        }

        .stats-card-info {
            background: linear-gradient(135deg, #f0f9ff 0%, #bfdbfe 100%);
            border: 1px solid #60a5fa;
        }

        .stats-card-info .stats-value {
            color: #1e3a8a;
            font-weight: 800;
        }

        .stats-card-info .stats-label {
            color: #1d4ed8;
            font-weight: 700;
        }

        .stats-card-info .stats-trend {
            background: rgba(30, 58, 138, 0.1);
            color: #1e3a8a;
            border: 1px solid #60a5fa;
        }

        /* Tarjeta Moderna */
        .modern-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .modern-card-header {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .modern-card-title {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .title-icon {
            width: 50px;
            height: 50px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }

        .title-content h3 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-color);
        }

        .title-content p {
            margin: 0;
            color: #718096;
            font-size: 0.9rem;
        }

        .modern-card-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .search-container {
            position: relative;
        }

        .search-box {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-box i {
            position: absolute;
            left: 1rem;
            color: #a0aec0;
            z-index: 2;
        }

        .search-box input {
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 2px solid #e2e8f0;
            border-radius: var(--border-radius);
            width: 300px;
            background: white;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .view-toggles {
            display: flex;
            background: #f1f5f9;
            border-radius: var(--border-radius);
            padding: 0.25rem;
        }

        .view-toggle {
            padding: 0.5rem 1rem;
            border: none;
            background: transparent;
            color: #64748b;
            border-radius: calc(var(--border-radius) - 2px);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .view-toggle.active {
            background: white;
            color: var(--primary-color);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .modern-card-body {
            padding: 1.5rem;
        }

        /* Tabla Moderna */
        .modern-table-container {
            overflow-x: auto;
            border-radius: var(--border-radius);
            border: 1px solid #e2e8f0;
        }

        .modern-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        .modern-table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .modern-table th {
            padding: 1rem;
            text-align: left;
            border: none;
            position: relative;
        }

        .th-content {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .modern-table td {
            padding: 1rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .table-row {
            transition: background-color 0.2s ease;
        }

        .table-row:hover {
            background: #f8fafc;
        }

        .row-number {
            width: 40px;
            height: 40px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .customer-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .customer-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e0 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            font-size: 1.2rem;
        }

        .customer-details {
            display: flex;
            flex-direction: column;
        }

        .customer-name {
            font-weight: 600;
            color: var(--dark-color);
            font-size: 0.9rem;
        }

        .customer-email {
            color: #718096;
            font-size: 0.8rem;
        }

        .date-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .date-main {
            font-weight: 600;
            color: var(--dark-color);
        }

        .date-time {
            color: #718096;
            font-size: 0.8rem;
        }

        .products-info {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .product-badge {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.5rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .product-badge.unique {
            background: linear-gradient(135deg, #e6fffa 0%, #b2f5ea 100%);
            color: #234e52;
        }

        .product-badge.total {
            background: linear-gradient(135deg, #ebf8ff 0%, #bee3f8 100%);
            color: #2c5282;
        }

        .price-info {
            text-align: right;
        }

        .price-amount {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--success-color);
        }

        .btn-modern.btn-primary {
            background: var(--gradient-primary);
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-modern.btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-action {
            width: 36px;
            height: 36px;
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .btn-edit {
            background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);
            color: white;
        }

        .btn-delete {
            background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
            color: white;
        }

        .btn-print {
            background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
            color: white;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        /* Vista de Tarjetas */
        .modern-cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
        }

        .modern-sale-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .modern-sale-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .sale-card-header {
            background: var(--gradient-primary);
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .sale-number {
            color: white;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .sale-status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #48bb78;
        }

        .status-text {
            color: white;
            font-size: 0.8rem;
        }

        .sale-card-body {
            padding: 1.5rem;
        }

        .customer-section {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .customer-avatar-large {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e0 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            font-size: 1.5rem;
        }

        .customer-info-card h4 {
            margin: 0;
            color: var(--dark-color);
            font-size: 1.1rem;
            font-weight: 600;
        }

        .customer-info-card p {
            margin: 0;
            color: #718096;
            font-size: 0.9rem;
        }

        .sale-details {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            background: #f8fafc;
            border-radius: var(--border-radius);
        }

        .detail-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #64748b;
            font-weight: 500;
        }

        .detail-value {
            font-weight: 600;
            color: var(--dark-color);
        }

        .total-row {
            background: linear-gradient(135deg, #f0fff4 0%, #c6f6d5 100%);
            border: 1px solid #9ae6b4;
        }

        .total-amount {
            font-size: 1.2rem;
            color: var(--success-color);
        }

        .product-badges {
            display: flex;
            gap: 0.5rem;
        }

        .mini-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 500;
        }

        .mini-badge.unique {
            background: linear-gradient(135deg, #e6fffa 0%, #b2f5ea 100%);
            color: #234e52;
        }

        .mini-badge.total {
            background: linear-gradient(135deg, #ebf8ff 0%, #bee3f8 100%);
            color: #2c5282;
        }

        .sale-card-footer {
            padding: 1rem 1.5rem;
            background: #f8fafc;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-card-primary {
            background: var(--gradient-primary);
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }

        .btn-card-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .card-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-card-action {
            width: 36px;
            height: 36px;
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .btn-card-action.edit {
            background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);
            color: white;
        }

        .btn-card-action.delete {
            background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
            color: white;
        }

        .btn-card-action.print {
            background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
            color: white;
        }

        .btn-card-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .modern-header {
                margin: -15px -15px 15px -15px;
                padding: 1.5rem 0;
            }

            .header-title {
                font-size: 1.5rem;
            }

            .header-actions {
                width: 100%;
                justify-content: center;
                margin-top: 1rem;
            }

            .modern-card-header {
                flex-direction: column;
                align-items: stretch;
            }

            .modern-card-actions {
                justify-content: center;
            }

            .search-box input {
                width: 100%;
            }

            .modern-cards-grid {
                grid-template-columns: 1fr;
            }

            .stats-dashboard .row {
                margin: 0 -0.5rem;
            }

            .stats-dashboard .col-lg-3 {
                padding: 0 0.5rem;
                margin-bottom: 1rem;
            }

            .stats-card {
                height: auto;
                min-height: 120px;
            }

            .modern-table-container {
                overflow-x: auto;
            }

            .table-row:hover {
                transform: none;
            }
        }

        @media (max-width: 576px) {
            .header-content {
                flex-direction: column;
                text-align: center;
            }

            .btn-modern {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }

            .stats-value {
                font-size: 1.2rem;
            }

            .customer-info {
                flex-direction: column;
                text-align: center;
            }

            .action-buttons {
                flex-wrap: wrap;
                justify-content: center;
            }
        }

        /* Optimización: Animaciones simplificadas */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .stats-card {
            animation: fadeIn 0.3s ease-out;
        }

        .modern-card {
            animation: fadeIn 0.3s ease-out;
        }

        /* Efectos de carga */
        .loading-shimmer {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% {
                background-position: -200% 0;
            }

            100% {
                background-position: 200% 0;
            }
        }

        /* Paginación Moderna */
        .modern-pagination-container {
            margin-top: 2rem;
            padding: 1.5rem;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: var(--border-radius);
            border: 1px solid #e2e8f0;
        }

        .modern-pagination-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .pagination-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .records-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .records-text {
            color: #64748b;
            font-weight: 500;
        }

        .records-numbers {
            background: var(--gradient-primary);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.8rem;
        }

        .records-total {
            background: var(--gradient-success);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.8rem;
        }

        .pagination-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .pagination-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            border: 2px solid transparent;
            background: white;
            color: #64748b;
            border-radius: var(--border-radius);
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .pagination-btn:hover:not(.disabled) {
            background: var(--gradient-primary);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .pagination-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background: #f1f5f9;
            color: #cbd5e0;
        }

        .pagination-numbers {
            display: flex;
            align-items: center;
                gap: 0.25rem;
            margin: 0 0.5rem;
        }

        .pagination-number {
            width: 40px;
            height: 40px;
            border: 2px solid transparent;
            background: white;
            color: #64748b;
            border-radius: 50%;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
            display: flex;
            align-items: center;
                justify-content: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .pagination-number:hover {
            background: var(--gradient-primary);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .pagination-number.active {
            background: var(--gradient-primary);
            color: white;
            border-color: var(--primary-color);
            transform: scale(1.1);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .pagination-ellipsis {
            color: #a0aec0;
            font-weight: 600;
            padding: 0 0.5rem;
            display: flex;
            align-items: center;
            height: 40px;
        }

        .pagination-options {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .page-size-selector {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .page-size-selector label {
            color: #64748b;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .page-size-select {
            padding: 0.5rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: var(--border-radius);
            background: white;
            color: #2d3748;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 80px;
        }

        .page-size-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .page-size-select:hover {
            border-color: var(--primary-color);
        }

        /* Optimización: Animaciones de paginación simplificadas */
        .modern-pagination-container {
            animation: fadeIn 0.3s ease-out;
        }

        /* Responsive para paginación */
        @media (max-width: 768px) {
            .modern-pagination-wrapper {
                flex-direction: column;
                align-items: stretch;
                gap: 1.5rem;
            }

            .pagination-info {
                justify-content: center;
            }

            .pagination-controls {
                justify-content: center;
                flex-wrap: wrap;
            }

            .pagination-btn {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }

            .pagination-btn span {
                display: none;
            }

            .pagination-numbers {
                margin: 0.5rem 0;
            }

            .pagination-options {
                justify-content: center;
            }
        }

        @media (max-width: 576px) {
            .modern-pagination-container {
                padding: 1rem;
                margin-top: 1rem;
            }

            .pagination-number {
                width: 35px;
                height: 35px;
                font-size: 0.9rem;
            }

            .pagination-btn {
                padding: 0.5rem;
                min-width: 45px;
            }

            .records-info {
                flex-direction: column;
                text-align: center;
                gap: 0.25rem;
            }

            .records-info>span {
                font-size: 0.8rem;
            }
        }

        /* Efectos optimizados para paginación */
        .pagination-btn:active {
            transform: scale(0.98);
        }

        .pagination-number:active {
            transform: scale(0.95);
        }

        /* ===== MODAL MODERNO ===== */
        .modern-modal {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        /* Header del Modal */
        .modal-header-modern {
            position: relative;
            padding: 0;
            border: none;
            overflow: hidden;
            height: 120px;
        }

        .modal-header-background {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--gradient-primary);
        }

        .modal-header-gradient {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
        }

        .modal-header-pattern {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            overflow: hidden;
        }

        .pattern-circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
        }

        .pattern-circle:nth-child(1) {
            width: 80px;
            height: 80px;
            top: -40px;
            right: 20px;
        }

        .pattern-circle:nth-child(2) {
            width: 60px;
            height: 60px;
            top: 20px;
            right: -30px;
        }

        .pattern-circle:nth-child(3) {
            width: 40px;
            height: 40px;
            top: 70px;
            right: 80px;
        }

        .modal-header-content {
            position: relative;
            z-index: 2;
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 100%;
        }

        .modal-title-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .modal-icon {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            backdrop-filter: blur(10px);
        }

        .modal-title-main {
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .modal-subtitle {
            color: rgba(255, 255, 255, 0.9);
            margin: 0;
                font-size: 0.9rem;
            }

        .modal-close-btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .modal-close-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }

        /* Cuerpo del Modal */
        .modal-body-modern {
            padding: 2rem;
            background: #f8fafc;
        }

        .sale-info-section {
            margin-bottom: 2rem;
        }

        .info-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            height: 100%;
        }

        .info-card-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .info-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
        }

        .customer-icon {
            background: var(--gradient-info);
        }

        .date-icon {
            background: var(--gradient-success);
        }

        .info-title {
            color: var(--dark-color);
            font-weight: 600;
            margin: 0;
            font-size: 1rem;
        }

        .info-card-content {
            color: #64748b;
            line-height: 1.6;
        }

        /* Sección de Productos */
        .products-section {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .section-icon {
            width: 40px;
            height: 40px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
        }

        .section-title {
            color: var(--dark-color);
            font-weight: 600;
            margin: 0;
            font-size: 1.2rem;
        }

        /* Tabla Moderna del Modal */
        .modern-table-wrapper {
            overflow-x: auto;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .modern-details-table {
                width: 100%;
            border-collapse: collapse;
            background: white;
        }

        .modern-details-table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .modern-details-table thead th {
            padding: 1rem;
            text-align: left;
            border: none;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .modern-details-table thead th .th-content {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .modern-details-table tbody tr {
            border-bottom: 1px solid #e2e8f0;
            transition: background-color 0.3s ease;
        }

        .modern-details-table tbody tr:hover {
            background-color: rgba(102, 126, 234, 0.05);
        }

        .modern-details-table tbody td {
            padding: 1rem;
            color: #475569;
            font-size: 0.9rem;
        }

        .modern-details-table tbody tr:last-child {
            border-bottom: none;
        }

        /* Sección de Total */
        .total-section {
            margin-top: 2rem;
            display: flex;
            justify-content: flex-end;
        }

        .total-card {
            background: linear-gradient(135deg, #f0fff4 0%, #c6f6d5 100%);
            border: 2px solid #9ae6b4;
            color: #1a202c;
            padding: 1.5rem 2rem;
            border-radius: 16px;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 8px 25px rgba(72, 187, 120, 0.2);
            min-width: 280px;
            position: relative;
            overflow: hidden;
        }

        .total-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(72, 187, 120, 0.1) 0%, rgba(56, 161, 105, 0.05) 100%);
            z-index: 1;
        }

        .total-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: white;
            box-shadow: 0 4px 12px rgba(72, 187, 120, 0.3);
            position: relative;
            z-index: 2;
        }

        .total-content {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            position: relative;
            z-index: 2;
        }

        .total-label {
            font-size: 0.9rem;
            color: #22543d;
            font-weight: 600;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .total-amount {
            font-size: 1.5rem;
            font-weight: 800;
            color: #1a365d;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Footer del Modal */
        .modal-footer-modern {
            background: #f8fafc;
            border: none;
            padding: 1.5rem 2rem;
            border-radius: 0 0 20px 20px;
        }

        .footer-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }

        .btn-modal-action {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-modal-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            text-decoration: none;
        }

        .btn-modal-action.btn-print {
            background: var(--gradient-info);
            color: white;
        }

        .btn-modal-action.btn-secondary {
            background: #e2e8f0;
            color: #475569;
        }

        .btn-modal-action.btn-secondary:hover {
            background: #cbd5e0;
            color: #2d3748;
        }

        /* Responsive del Modal */
        @media (max-width: 768px) {
            .modal-header-content {
                padding: 1rem;
            }

            .modal-title-main {
                font-size: 1.2rem;
            }

            .modal-body-modern {
                padding: 1rem;
            }

            .info-card {
                padding: 1rem;
            }

            .products-section {
                padding: 1rem;
            }

            .total-card {
                min-width: auto;
                width: 100%;
            }

            .footer-actions {
                flex-direction: column;
            }

            .btn-modal-action {
                justify-content: center;
            }
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Inicializar DataTable con configuración optimizada
            const table = $('#salesTable').DataTable({
                responsive: true,
                language: {
                    "sProcessing":     "Procesando...",
                    "sLengthMenu":     "Mostrar _MENU_ registros",
                    "sZeroRecords":    "No se encontraron resultados",
                    "sEmptyTable":     "Ningún dato disponible en esta tabla",
                    "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                    "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
                    "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
                    "sInfoPostFix":    "",
                    "sSearch":         "Buscar:",
                    "sUrl":            "",
                    "sInfoThousands":  ",",
                    "sLoadingRecords": "Cargando...",
                    "oPaginate": {
                        "sFirst":    "Primero",
                        "sLast":     "Último",
                        "sNext":     "Siguiente",
                        "sPrevious": "Anterior"
                    },
                    "oAria": {
                        "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                    },
                    "buttons": {
                        "copy": "Copiar",
                        "colvis": "Visibilidad"
                    }
                },
                pageLength: 10,
                dom: 'rt<"modern-pagination-container">',
                deferRender: true,
                processing: false,
                serverSide: false,
                drawCallback: function() {
                    // Crear paginación personalizada (sin animaciones pesadas)
                    createModernPagination();
                }
            });

            // Función optimizada para crear paginación moderna
            function createModernPagination() {
                const info = table.page.info();
                const totalPages = info.pages;
                const currentPage = info.page + 1;
                const totalRecords = info.recordsTotal;
                const startRecord = info.start + 1;
                const endRecord = info.end;

                // Usar template strings más eficientes
                const paginationHTML = `
                    <div class="modern-pagination-wrapper">
                        <div class="pagination-info">
                            <div class="records-info">
                                <span class="records-text">Mostrando</span>
                                <span class="records-numbers">${startRecord} - ${endRecord}</span>
                                <span class="records-text">de</span>
                                <span class="records-total">${totalRecords}</span>
                                <span class="records-text">registros</span>
                            </div>
                        </div>
                        <div class="pagination-controls">
                            <button class="pagination-btn pagination-prev ${currentPage === 1 ? 'disabled' : ''}" 
                                    data-page="${currentPage - 1}" ${currentPage === 1 ? 'disabled' : ''}>
                                <i class="fas fa-chevron-left"></i>
                                <span>Anterior</span>
                            </button>
                            
                            <div class="pagination-numbers">
                                ${generatePageNumbers(currentPage, totalPages)}
                            </div>
                            
                            <button class="pagination-btn pagination-next ${currentPage === totalPages ? 'disabled' : ''}" 
                                    data-page="${currentPage + 1}" ${currentPage === totalPages ? 'disabled' : ''}>
                                <span>Siguiente</span>
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                        
                        <div class="pagination-options">
                            <div class="page-size-selector">
                                <label for="pageSize">Mostrar:</label>
                                <select id="pageSize" class="page-size-select">
                                    <option value="10" ${info.length === 10 ? 'selected' : ''}>10</option>
                                    <option value="25" ${info.length === 25 ? 'selected' : ''}>25</option>
                                    <option value="50" ${info.length === 50 ? 'selected' : ''}>50</option>
                                    <option value="100" ${info.length === 100 ? 'selected' : ''}>100</option>
                                </select>
                            </div>
                        </div>
                    </div>
                `;

                $('.modern-pagination-container').html(paginationHTML);
            }

            // Función auxiliar optimizada para generar números de página
            function generatePageNumbers(currentPage, totalPages) {
                let html = '';
                let startPage = Math.max(1, currentPage - 2);
                let endPage = Math.min(totalPages, currentPage + 2);

                if (startPage > 1) {
                    html += `<button class="pagination-number" data-page="1">1</button>`;
                    if (startPage > 2) {
                        html += `<span class="pagination-ellipsis">...</span>`;
                    }
                }

                for (let i = startPage; i <= endPage; i++) {
                    html +=
                        `<button class="pagination-number ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</button>`;
                }

                if (endPage < totalPages) {
                    if (endPage < totalPages - 1) {
                        html += `<span class="pagination-ellipsis">...</span>`;
                    }
                    html += `<button class="pagination-number" data-page="${totalPages}">${totalPages}</button>`;
                }

                return html;
            }

            // Manejar clicks en paginación
            $(document).on('click', '.pagination-number, .pagination-prev, .pagination-next', function() {
                if (!$(this).hasClass('disabled')) {
                    const page = parseInt($(this).data('page')) - 1;
                    table.page(page).draw('page');
                }
            });

            // Manejar cambio de tamaño de página
            $(document).on('change', '#pageSize', function() {
                const pageSize = parseInt($(this).val());
                table.page.len(pageSize).draw();
            });

            // Función para paginar tarjetas en vista de cards
            function paginateCards() {
                const currentView = $('.view-toggle.active').data('view');
                if (currentView === 'cards') {
                    const info = table.page.info();
                    const startIndex = info.start;
                    const endIndex = info.end;

                    $('.modern-sale-card').each(function(index) {
                        if (index >= startIndex && index < endIndex) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    });
                }
            }

            // Llamar paginateCards cuando se cambie de página
            $(document).on('click', '.pagination-number, .pagination-prev, .pagination-next', function() {
                setTimeout(paginateCards, 100);
            });

            // Alternar entre vistas
            $('.view-toggle').click(function() {
                const view = $(this).data('view');

                $('.view-toggle').removeClass('active');
                $(this).addClass('active');

                if (view === 'table') {
                    $('#tableView').show();
                    $('#cardsView').hide();
                } else {
                    $('#tableView').hide();
                    $('#cardsView').show();
                    // Aplicar paginación a las tarjetas cuando se cambia a vista de cards
                    setTimeout(paginateCards, 100);
                }
            });

            // Búsqueda avanzada
            $('#salesSearch').on('keyup', function() {
                const searchTerm = $(this).val().toLowerCase();
                
                // Búsqueda en DataTable
                table.search(this.value).draw();
                
                // Búsqueda optimizada en tarjetas
                if ($('.view-toggle.active').data('view') === 'cards') {
                    $('.modern-sale-card').each(function() {
                        const customerName = $(this).find('.customer-name').text().toLowerCase();
                        const customerEmail = $(this).find('.customer-email').text().toLowerCase();
                        const saleDate = $(this).find('.detail-value').first().text().toLowerCase();

                        if (customerName.includes(searchTerm) ||
                            customerEmail.includes(searchTerm) ||
                            saleDate.includes(searchTerm)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
                }
            });

            // Sugerencias de búsqueda
            $('#salesSearch').on('focus', function() {
                // Aquí podrías implementar sugerencias de búsqueda
                console.log('Búsqueda enfocada');
            });

            // Inicializar tooltips con configuración moderna
            $('[data-toggle="tooltip"]').tooltip({
                trigger: 'hover',
                placement: 'top',
                html: true
            });

            // Ver detalles de la venta optimizado
            $(document).on('click', '.view-details', function() {
                const saleId = $(this).data('id');
                const button = $(this);

                // Indicador de carga simple
                button.html('<i class="fas fa-spinner fa-spin"></i> <span>Cargando...</span>');
                button.prop('disabled', true);

                $('#saleDetailsTableBody').empty();

                $.ajax({
                    url: `/sales/${saleId}/details`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            let total = 0;

                            // Actualizar información del cliente y fecha
                            const customerPhone = response.sale.customer_phone || '-';
                            $('#customerInfo').html(`
                                <strong>Cliente:</strong> ${response.sale.customer_name}<br>
                                <strong>Teléfono:</strong> ${customerPhone}
                            `);
                            $('#saleDate').text(response.sale.date);

                            response.details.forEach(function(detail) {
                                const quantity = parseFloat(detail.quantity);
                                const price = parseFloat(detail.product_price);
                                const subtotal = quantity * price;
                                total += subtotal;
                                $('#saleDetailsTableBody').append(`
                                    <tr>
                                        <td>${detail.product.code || ''}</td>
                                        <td>${detail.product.name || ''}</td>
                                        <td>${detail.product.category || 'Sin categoría'}</td>
                                        <td class="text-center">${quantity}</td>
                                        <td class="text-right">{{ $currency->symbol }} ${price.toFixed(2)}</td>
                                        <td class="text-right">{{ $currency->symbol }} ${subtotal.toFixed(2)}</td>
                                    </tr>
                                `);
                            });

                            const formattedTotal = total.toFixed(2).replace(
                                /\B(?=(\d{3})+(?!\d))/g, ",");
                            $('#modalTotal').text(formattedTotal);
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: response.message ||
                                    'Error al cargar los detalles',
                                icon: 'error',
                                confirmButtonColor: '#667eea'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Error',
                            text: 'No se pudieron cargar los detalles',
                            icon: 'error',
                            confirmButtonColor: '#667eea'
                        });
                    },
                    complete: function() {
                        // Restaurar botón
                        button.html('<i class="fas fa-list"></i> <span>Ver Detalle</span>');
                        button.prop('disabled', false);
                    }
                });
            });

            // Eliminar venta con confirmación moderna
            $(document).on('click', '.delete-sale', function() {
                const id = $(this).data('id');

                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "Esta acción no se puede revertir",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#f56565',
                    cancelButtonColor: '#718096',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true,
                    customClass: {
                        popup: 'swal-modern',
                        confirmButton: 'btn-modern-danger',
                        cancelButton: 'btn-modern-secondary'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Mostrar loading
                        Swal.fire({
                            title: 'Eliminando...',
                            text: 'Por favor espere',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: `/sales/delete/${id}`,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: '¡Eliminado!',
                                        text: response.message,
                                        icon: 'success',
                                        confirmButtonColor: '#48bb78',
                                        timer: 2000,
                                        showConfirmButton: false
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Error',
                                        text: response.message,
                                        icon: 'error',
                                        confirmButtonColor: '#667eea'
                                    });
                                }
                            },
                            error: function() {
                                Swal.fire({
                                    title: 'Error',
                                    text: 'No se pudo eliminar la venta',
                                    icon: 'error',
                                    confirmButtonColor: '#667eea'
                                });
                            }
                        });
                    }
                });
            });

            // Imprimir venta
            $(document).on('click', '.print-sale, .print-details, .btn-print', function() {
                window.print();
            });

            // Editar venta
            $(document).on('click', '.btn-edit', function() {
                const saleId = $(this).closest('tr').find('.view-details').data('id') ||
                    $(this).closest('.modern-sale-card').find('.view-details').data('id');

                if (saleId) {
                    window.location.href = `/sales/edit/${saleId}`;
                }
            });

            // Optimización: Simplificar efectos hover
            // Remover animaciones complejas para mejor rendimiento

            // Optimización: Remover animaciones pesadas que ralentizan la carga
            // Solo mantener efectos hover básicos para mejor rendimiento
        });
    </script>
@stop