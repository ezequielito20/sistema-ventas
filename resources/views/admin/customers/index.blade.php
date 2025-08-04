@extends('adminlte::page')

@section('title', 'Gestión de Clientes')

@section('content_header')
    <div class="hero-section mb-4">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-lg-8 col-md-7 col-12">
                    <div class="hero-content">
                        <h1 class="hero-title">
                            <i class="fas fa-users-gradient"></i>
                            Gestión de Clientes
                        </h1>
                        <p class="hero-subtitle">Administra y visualiza todos tus clientes con herramientas avanzadas de control</p>
                        <div class="hero-stats"></div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-5 col-12">
                    <div class="hero-action-buttons d-flex justify-content-lg-end justify-content-center align-items-center gap-3 flex-wrap">
                        @can('customers.report')
                            <button 
                                class="hero-btn hero-btn-info" 
                                id="debtReportBtn" 
                                data-toggle="tooltip" 
                                title="Reporte de Deudas"
                            >
                                <i class="fas fa-file-invoice-dollar"></i>
                                <span class="d-none d-md-inline">Deudas</span>
                            </button>
                        @endcan
                        @can('customers.report')
                            <a href="{{ route('admin.customers.report') }}" class="hero-btn hero-btn-secondary" target="_blank" data-toggle="tooltip" title="Reporte PDF">
                                <i class="fas fa-file-pdf"></i>
                                <span class="d-none d-md-inline">Reporte PDF</span>
                            </a>
                        @endcan
                        @can('customers.report')
                            <a href="{{ route('admin.customers.payment-history') }}" class="hero-btn hero-btn-warning" data-toggle="tooltip" title="Historial de Pagos">
                                <i class="fas fa-history"></i>
                                <span class="d-none d-md-inline">Historial</span>
                            </a>
                        @endcan
                        @can('customers.create')
                            <a href="{{ route('admin.customers.create') }}" class="hero-btn hero-btn-primary" data-toggle="tooltip" title="Nuevo Cliente">
                                <i class="fas fa-plus"></i>
                                <span class="d-none d-md-inline">Nuevo Cliente</span>
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
    .hero-action-buttons {
        gap: 1rem !important;
    }
    .hero-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(255,255,255,0.85);
        color: var(--primary-color);
        border: none;
        border-radius: 12px;
        font-weight: 600;
        font-size: 1.1rem;
        padding: 0.7rem 1.2rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        transition: all 0.2s;
        cursor: pointer;
        min-width: 44px;
        min-height: 44px;
        position: relative;
        text-decoration: none;
        outline: none;
    }
    .hero-btn i {
        font-size: 1.3rem;
        color: var(--primary-color);
        margin-right: 0.2rem;
    }
    .hero-btn-info { color: #3b82f6; }
    .hero-btn-info i { color: #3b82f6; }
    .hero-btn-secondary { color: #f5576c; }
    .hero-btn-secondary i { color: #f5576c; }
    .hero-btn-warning { color: #38f9d7; }
    .hero-btn-warning i { color: #38f9d7; }
    .hero-btn-primary { color: #764ba2; }
    .hero-btn-primary i { color: #764ba2; }
    .hero-btn:hover, .hero-btn:focus {
        background: #fff;
        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
        transform: translateY(-2px) scale(1.04);
        color: var(--primary-color);
        text-decoration: none;
    }
    .hero-btn:active {
        transform: scale(0.97);
    }
    .hero-btn span {
        font-size: 1rem;
        font-weight: 600;
        color: inherit;
        white-space: nowrap;
    }
    @media (max-width: 991px) {
        .hero-action-buttons {
            justify-content: center !important;
        }
    }
    @media (max-width: 767px) {
        .hero-btn span {
            display: none !important;
        }
        .hero-btn {
            padding: 0.7rem !important;
            min-width: 44px;
        }
    }
    </style>
@stop

@section('content')
    {{-- Widgets de Estadísticas Rediseñados --}}
    <div class="stats-grid mb-4">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <div class="stat-header">
                    <h3 class="stat-number">{{ $totalCustomers }}</h3>
                    @if ($customerGrowth > 0)
                        <span class="growth-badge growth-positive">
                            <i class="fas fa-arrow-up"></i>
                            {{ $customerGrowth }}%
                        </span>
                    @endif
                </div>
                <p class="stat-label">Total de Clientes</p>
                <div class="stat-progress">
                    <div class="progress-bar" style="width: 100%"></div>
                </div>
            </div>
        </div>

        <div class="stat-card stat-card-success">
            <div class="stat-icon">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="stat-content">
                <div class="stat-header">
                    <h3 class="stat-number">{{ $activeCustomers }}/{{ $totalCustomers }}</h3>
                </div>
                <p class="stat-label">Clientes Activos</p>
                <div class="stat-progress">
                    <div class="progress-bar" style="width: {{ $totalCustomers > 0 ? ($activeCustomers / $totalCustomers) * 100 : 0 }}%"></div>
                </div>
            </div>
        </div>

        <div class="stat-card stat-card-warning">
            <div class="stat-icon">
                <i class="fas fa-user-plus"></i>
            </div>
            <div class="stat-content">
                <div class="stat-header">
                    <h3 class="stat-number">{{ $newCustomers }}</h3>
                </div>
                <p class="stat-label">Nuevos este Mes</p>
                <div class="stat-progress">
                    <div class="progress-bar" style="width: {{ $totalCustomers > 0 ? ($newCustomers / $totalCustomers) * 100 : 0 }}%"></div>
                </div>
            </div>
        </div>

        <div class="stat-card stat-card-purple">
            <div class="stat-icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="stat-content">
                <div class="stat-header">
                    <h3 class="stat-number">{{ $currency->symbol }} {{ number_format($totalRevenue, 2) }}</h3>
                </div>
                <p class="stat-label">Ingresos Totales</p>
                <div class="stat-progress">
                    <div class="progress-bar" style="width: 100%"></div>
                </div>
            </div>
        </div>

        <div class="stat-card stat-card-danger">
            <div class="stat-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-header">
                    <h3 class="stat-number">{{ $defaultersCount }}</h3>
                </div>
                <p class="stat-label">Clientes Morosos</p>
                <div class="stat-progress">
                    <div class="progress-bar" style="width: {{ $totalCustomers > 0 ? ($defaultersCount / $totalCustomers) * 100 : 0 }}%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bloque unificado de Tipo de Cambio + Filtros + Búsqueda --}}
    <div class="exchange-filters-card mb-4" x-data="exchangeFilters()">
        <div class="exchange-filters-content redesigned">
            <div class="exchange-block redesigned-left">
                <div class="rate-row redesigned-rate-row">
                    <span class="currency-symbol">1 USD =</span>
                    <input 
                        type="number" 
                        id="exchangeRate" 
                        class="rate-input" 
                        x-model="exchangeRate"
                        step="0.01" 
                        min="0" 
                        @cannot('customers.edit') readonly @endcannot
                        @keyup.enter="updateExchangeRate()"
                    >
                    <span class="currency-code">VES</span>
                    @can('customers.edit')
                        <button 
                            type="button" 
                            class="update-rate-btn update-exchange-rate ml-2"
                            @click="updateExchangeRate()"
                            :disabled="updating"
                        >
                            <i class="fas fa-sync-alt" :class="{ 'fa-spin': updating }"></i>
                            <span x-text="updating ? 'Actualizando...' : 'Actualizar'"></span>
                        </button>
                    @endcan
                </div>
            </div>
            <div class="filters-block redesigned-right">
                <div class="filters-search-row">
                    <div class="filters-btns filters-btns-scroll mb-0">
                        <button 
                            type="button" 
                            class="filter-btn filter-btn-all active" 
                            data-filter="all"
                        >
                            <i class="fas fa-list"></i>
                            <span class="d-none d-sm-inline">Todos</span>
                        </button>
                        <button 
                            type="button" 
                            class="filter-btn filter-btn-active" 
                            data-filter="active"
                        >
                            <i class="fas fa-check-circle"></i>
                            <span class="d-none d-sm-inline">Activos</span>
                        </button>
                        <button 
                            type="button" 
                            class="filter-btn filter-btn-inactive" 
                            data-filter="inactive"
                        >
                            <i class="fas fa-times-circle"></i>
                            <span class="d-none d-sm-inline">Inactivos</span>
                        </button>
                        <button 
                            type="button" 
                            class="filter-btn filter-btn-defaulters" 
                            data-filter="defaulters"
                        >
                            <i class="fas fa-exclamation-triangle"></i>
                            <span class="d-none d-sm-inline">Morosos</span>
                        </button>
                    </div>
                    <div class="search-group redesigned-search-group ml-3">
                        <div class="search-container">
                            <i class="fas fa-search search-icon"></i>
                            <input 
                                type="text" 
                                class="search-input" 
                                id="mobileSearch" 
                                placeholder="Buscar cliente..."
                            >
                            <button 
                                type="button" 
                                class="search-clear" 
                                id="clearSearch"
                            >
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
    .exchange-filters-card {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        padding: 2rem 2rem 1.5rem 2rem;
        margin-bottom: 2rem;
    }
    .exchange-filters-content {
        display: flex;
        gap: 2rem;
        align-items: flex-start;
        flex-wrap: wrap;
        justify-content: space-between;
    }
    .exchange-block {
        flex: 1 1 340px;
        min-width: 260px;
        max-width: 420px;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    .header-icon {
        width: 50px;
        height: 50px;
        background: var(--primary-gradient);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
        margin-bottom: 0.5rem;
    }
    .header-text h4 {
        margin: 0;
        font-weight: 600;
        color: var(--dark-color);
    }
    .header-text p {
        margin: 0 0 0.5rem 0;
        color: #666;
        font-size: 0.95rem;
    }
    .rate-row {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 0.5rem;
        flex-wrap: wrap;
    }
    .rate-label {
        font-size: 0.95rem;
        color: #666;
        margin-right: 0.5rem;
    }
    .rate-input {
        border: 2px solid #e9ecef;
        border-radius: var(--border-radius-sm);
        padding: 0.75rem;
        font-size: 1.1rem;
        font-weight: 600;
        text-align: center;
        transition: var(--transition);
        width: 120px;
    }
    .rate-input:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    .currency-symbol, .currency-code {
        font-weight: 600;
        color: var(--dark-color);
    }
    .update-rate-btn {
        background: var(--primary-gradient);
        color: white;
        border: none;
        border-radius: var(--border-radius-sm);
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-left: 0.5rem;
    }
    .update-rate-btn:hover {
        filter: brightness(1.1);
        transform: translateY(-2px);
        box-shadow: var(--shadow-hover);
    }
    .filters-block.redesigned-right {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: flex-start;
        flex: 2 1 500px;
        min-width: 260px;
        max-width: 700px;
        padding-left: 2rem;
    }
    .filters-title {
        font-weight: 600;
        color: var(--dark-color);
        font-size: 1rem;
        margin-bottom: 0.5rem;
    }
    .filters-search-row {
        display: flex;
        align-items: center;
        gap: 1.1rem;
        width: 100%;
        justify-content: flex-start;
    }
    .filters-btns {
        display: flex;
        gap: 0.7rem;
        margin-bottom: 0;
        flex-wrap: wrap;
    }
    .redesigned-search-group {
        max-width: 260px;
        min-width: 120px;
        width: 100%;
        margin-left: 0.7rem;
        flex: 0 0 auto;
    }
    .search-container {
        position: relative;
        display: flex;
        align-items: center;
        width: 100%;
    }
    @media (max-width: 991px) {
        .filters-search-row {
            flex-direction: column;
            align-items: stretch;
            gap: 1rem;
        }
        .redesigned-search-group {
            margin-left: 0;
            max-width: 100%;
        }
    }
    </style>

    {{-- Tabla de Clientes Rediseñada --}}
    <div class="customers-container">
        {{-- Vista de tabla para pantallas grandes --}}
        <div class="table-view d-none d-lg-block">
            <div class="table-container">
                <table id="customersTable" class="customers-table">
                    <thead>
                        <tr>
                            <th class="th-customer">Cliente</th>
                            <th class="th-contact">Contacto</th>
                            <th class="th-id">C.I</th>
                            <th class="th-sales">Total en Compras</th>
                            <th class="th-debt">Deuda Total</th>
                            <th class="th-debt-bs">Deuda en Bs</th>
                            <th class="th-status">Estado</th>
                            <th class="th-actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($customers as $customer)
                            <tr class="customer-row" data-status="{{ $customer->sales->count() > 0 ? 'active' : 'inactive' }}">
                                <td class="td-customer">
                                    <div class="customer-info">
                                        <div class="customer-avatar">
                                            <div class="avatar-circle">
                                                {{ strtoupper(substr($customer->name, 0, 1)) }}
                                            </div>
                                        </div>
                                        <div class="customer-details">
                                            <div class="customer-name">{{ $customer->name }}</div>
                                            <div class="customer-email">
                                                <i class="fas fa-envelope"></i>
                                                {{ $customer->email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="td-contact">
                                    <div class="contact-info">
                                        <i class="fas fa-phone"></i>
                                        <span>{{ $customer->phone }}</span>
                                    </div>
                                </td>
                                <td class="td-id">
                                    <span class="id-badge">{{ $customer->nit_number }}</span>
                                </td>
                                <td class="td-sales">
                                    @if ($customer->sales->count() > 0)
                                        <div class="sales-info">
                                            <div class="sales-amount">{{ $currency->symbol }} {{ number_format($customer->sales->sum('total_price'), 2) }}</div>
                                            <div class="sales-count">{{ $customer->sales->count() }} venta(s)</div>
                                        </div>
                                    @else
                                        <span class="no-sales">Sin ventas</span>
                                    @endif
                                </td>
                                <td class="td-debt">
                                    @if ($customer->total_debt > 0)
                                        <div class="debt-info">
                                            <div class="debt-amount debt-value" 
                                                 data-customer-id="{{ $customer->id }}" 
                                                 data-original-value="{{ $customer->total_debt }}">
                                                {{ $currency->symbol }}
                                                <span class="debt-amount-value">{{ number_format($customer->formatted_total_debt, 2) }}</span>
                                                @if ($customersData[$customer->id]['isDefaulter'])
                                                    <span class="debt-warning-badge" title="Cliente con deudas de arqueos anteriores">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                    </span>
                                                @endif
                                            </div>
                                            @can('customers.edit')
                                                <button class="edit-debt-btn">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    @else
                                        <div class="debt-info">
                                            <span class="no-debt-badge">Sin deuda</span>
                                            @can('customers.edit')
                                                <button class="edit-debt-btn">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    @endif
                                </td>
                                <td class="td-debt-bs">
                                    @if ($customer->total_debt > 0)
                                        <span class="bs-debt" data-debt="{{ $customer->total_debt }}">
                                            Bs. {{ number_format($customer->total_debt, 2) }}
                                        </span>
                                    @else
                                        <span class="no-debt-badge">Sin deuda</span>
                                    @endif
                                </td>
                                <td class="td-status">
                                    @if ($customer->sales->count() > 0)
                                        <span class="status-badge status-active">
                                            <i class="fas fa-check-circle"></i>
                                            Activo
                                        </span>
                                    @else
                                        <span class="status-badge status-inactive">
                                            <i class="fas fa-times-circle"></i>
                                            Inactivo
                                        </span>
                                    @endif
                                </td>
                                <td class="td-actions">
                                    <div class="action-buttons">
                                        @can('customers.show')
                                            <button type="button" class="action-btn action-btn-view show-customer"
                                                data-id="{{ $customer->id }}" data-toggle="tooltip" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        @endcan
                                        @can('customers.edit')
                                            <a href="{{ route('admin.customers.edit', $customer->id) }}"
                                                class="action-btn action-btn-edit" data-toggle="tooltip" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan
                                        @can('customers.destroy')
                                            <button type="button" class="action-btn action-btn-delete delete-customer"
                                                data-id="{{ $customer->id }}" data-toggle="tooltip" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endcan
                                        @can('sales.create')
                                            <a href="{{ route('admin.sales.create', ['customer_id' => $customer->id]) }}"
                                                class="action-btn action-btn-sale" data-toggle="tooltip" title="Nueva venta">
                                                <i class="fas fa-cart-plus"></i>
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Vista de tarjetas para móviles rediseñada --}}
        <div class="cards-view d-lg-none">
            <div class="cards-container" id="mobileCustomersContainer">
                @foreach ($customers as $customer)
                    <div 
                        class="customer-card" 
                        data-status="{{ $customer->sales->count() > 0 ? 'active' : 'inactive' }}" 
                        data-defaulter="{{ $customersData[$customer->id]['isDefaulter'] ? 'true' : 'false' }}"

                    >
                        <div class="card-header">
                            <div class="customer-avatar">
                                <div class="avatar-circle">
                                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                                </div>
                            </div>
                            <div class="customer-info">
                                <h6 class="customer-name">{{ $customer->name }}</h6>
                                <div class="customer-email">
                                    <i class="fas fa-envelope"></i>
                                    {{ $customer->email }}
                                </div>
                            </div>
                            <div class="status-indicator">
                                @if ($customer->sales->count() > 0)
                                    <span class="status-badge status-active">
                                        <i class="fas fa-check-circle"></i>
                                    </span>
                                @else
                                    <span class="status-badge status-inactive">
                                        <i class="fas fa-times-circle"></i>
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-phone"></i>
                                        Teléfono
                                    </div>
                                    <div class="info-value">{{ $customer->phone }}</div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-id-card"></i>
                                        C.I
                                    </div>
                                    <div class="info-value">
                                        <span class="id-badge">{{ $customer->nit_number }}</span>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-shopping-cart"></i>
                                        Total Compras
                                    </div>
                                    <div class="info-value">
                                        @if ($customer->sales->count() > 0)
                                            {{ $currency->symbol }} {{ number_format($customer->sales->sum('total_price'), 2) }}
                                            <small>({{ $customer->sales->count() }} ventas)</small>
                                        @else
                                            <span class="no-sales">Sin ventas</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-money-bill-wave"></i>
                                        Deuda
                                    </div>
                                    <div class="info-value">
                                        @if ($customer->total_debt > 0)
                                            <div class="debt-info">
                                                <div class="debt-amount debt-value" 
                                                     data-customer-id="{{ $customer->id }}" 
                                                     data-original-value="{{ $customer->total_debt }}">
                                                    {{ $currency->symbol }}
                                                    <span class="debt-amount-value">{{ number_format($customer->formatted_total_debt, 2) }}</span>
                                                </div>
                                                <div class="bs-debt" data-debt="{{ $customer->total_debt }}">
                                                    Bs. {{ number_format($customer->total_debt, 2) }}
                                                </div>
                                                <div class="debt-type-info">
                                                    @if ($customersData[$customer->id]['isDefaulter'])
                                                        <span class="debt-type-badge debt-type-defaulters" title="Cliente con deudas de arqueos de caja anteriores">
                                                            <i class="fas fa-exclamation-triangle"></i>
                                                            Moroso
                                                        </span>
                                                    @else
                                                        <span class="debt-type-badge debt-type-current" title="Cliente con deudas del arqueo de caja actual">
                                                            <i class="fas fa-clock"></i>
                                                            Actual
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <span class="no-debt-badge">Sin deuda</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-actions">
                            <div class="action-buttons">
                                @can('customers.show')
                                    <button type="button" class="action-btn action-btn-view show-customer"
                                        data-id="{{ $customer->id }}">
                                        <i class="fas fa-eye"></i>
                                        <span>Ver</span>
                                    </button>
                                @endcan
                                @can('customers.edit')
                                    <a href="{{ route('admin.customers.edit', $customer->id) }}"
                                        class="action-btn action-btn-edit">
                                        <i class="fas fa-edit"></i>
                                        <span>Editar</span>
                                    </a>
                                @endcan
                                @if ($customer->total_debt > 0)
                                    <button class="action-btn action-btn-payment edit-debt-btn">
                                        <i class="fas fa-dollar-sign"></i>
                                        <span>Pagar</span>
                                    </button>
                                @endif
                                @can('sales.create')
                                    <a href="{{ route('admin.sales.create', ['customer_id' => $customer->id]) }}"
                                        class="action-btn action-btn-sale">
                                        <i class="fas fa-cart-plus"></i>
                                        <span>Venta</span>
                                    </a>
                                @endcan
                                @can('customers.destroy')
                                    <button type="button" class="action-btn action-btn-delete delete-customer"
                                        data-id="{{ $customer->id }}">
                                        <i class="fas fa-trash"></i>
                                        <span>Eliminar</span>
                                    </button>
                                @endcan
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Modal de Detalles del Cliente Rediseñado --}}
    <div class="modal fade" id="showCustomerModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content modern-modal">
                <div class="modal-header">
                    <div class="modal-title-section">
                        <div class="title-icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="title-content">
                            <h5 class="modal-title">Detalles del Cliente</h5>
                            <p class="modal-subtitle">Información completa y historial de ventas</p>
                        </div>
                    </div>
                    <button type="button" class="modal-close" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="customer-details-container">
                        <div class="sales-history-section">
                            <div class="section-header">
                                <div class="section-icon">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <div class="section-title">
                                    <h6>Historial de Ventas</h6>
                                    <p>Cliente: <span id="customerName" class="customer-name-highlight"></span></p>
                                </div>
                            </div>
                            
                            <div class="filters-section">
                                <div class="filters-grid">
                                    <div class="filter-group">
                                        <label class="filter-label">Rango de Fechas</label>
                                        <div class="date-range">
                                            <div class="date-input">
                                                <i class="fas fa-calendar"></i>
                                                <input type="date" id="dateFrom" placeholder="Desde">
                                            </div>
                                            <div class="date-separator">hasta</div>
                                            <div class="date-input">
                                                <i class="fas fa-calendar"></i>
                                                <input type="date" id="dateTo" placeholder="Hasta">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="filter-group">
                                        <label class="filter-label">Rango de Monto</label>
                                        <div class="amount-range">
                                            <div class="amount-input">
                                                <span class="currency-symbol">{{ $currency->symbol }}</span>
                                                <input type="number" id="amountFrom" placeholder="Mínimo" step="0.01" min="0">
                                            </div>
                                            <div class="amount-separator">-</div>
                                            <div class="amount-input">
                                                <span class="currency-symbol">{{ $currency->symbol }}</span>
                                                <input type="number" id="amountTo" placeholder="Máximo" step="0.01" min="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="filter-actions">
                                    <button type="button" class="filter-btn filter-btn-apply" id="applyFilters">
                                        <i class="fas fa-filter"></i>
                                        <span>Aplicar Filtros</span>
                                    </button>
                                    <button type="button" class="filter-btn filter-btn-clear" id="clearFilters">
                                        <i class="fas fa-times"></i>
                                        <span>Limpiar</span>
                                    </button>
                                </div>
                            </div>

                            <div class="sales-table-container">
                                <div class="table-wrapper">
                                    <table class="sales-table">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Productos</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody id="salesHistoryTable">
                                            <tr>
                                                <td colspan="3" class="empty-state">
                                                    <div class="empty-icon">
                                                        <i class="fas fa-info-circle"></i>
                                                    </div>
                                                    <p>No hay ventas registradas</p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="table-footer">
                                    <div class="sales-count">
                                        <span id="salesCount">0</span> ventas mostradas
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal para el reporte de deudas rediseñado --}}
    <div class="modal fade" id="debtReportModal" tabindex="-1" role="dialog" aria-labelledby="debtReportModalLabel">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content modern-modal">
                <div class="modal-body">
                    <div class="loading-container">
                        <div class="loading-spinner">
                            <div class="spinner-ring"></div>
                        </div>
                        <div class="loading-text">
                            <h5>Cargando reporte de deudas</h5>
                            <p>Preparando información detallada...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal para registrar pagos de deuda rediseñado --}}
    <div class="modal fade" id="debtPaymentModal" tabindex="-1" role="dialog" aria-labelledby="debtPaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modern-modal">
                <div class="modal-header">
                    <div class="modal-title-section">
                        <div class="title-icon payment-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="title-content">
                            <h5 class="modal-title">Registrar Pago de Deuda</h5>
                            <p class="modal-subtitle">Gestiona los pagos de tus clientes de forma eficiente</p>
                        </div>
                    </div>
                    <button type="button" class="modal-close" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="debtPaymentForm">
                    <div class="modal-body">
                        <input type="hidden" id="payment_customer_id" name="customer_id">
                        
                        <div class="form-sections">
                            <div class="form-section">
                                <div class="section-title">
                                    <i class="fas fa-user"></i>
                                    <span>Información del Cliente</span>
                                </div>
                                <div class="form-group">
                                    <label for="customer_name">Cliente</label>
                                    <div class="input-wrapper">
                                        <i class="fas fa-user input-icon"></i>
                                        <input type="text" class="form-control modern-input" id="customer_name" readonly>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <div class="section-title">
                                    <i class="fas fa-chart-line"></i>
                                    <span>Estado de Deuda</span>
                                </div>
                                <div class="debt-status-card">
                                    <div class="debt-current">
                                        <label>Deuda Actual</label>
                                        <div class="debt-amount-display">
                                            <span class="currency-symbol">{{ $currency->symbol }}</span>
                                            <input type="text" class="form-control modern-input" id="current_debt" readonly>
                                        </div>
                                    </div>
                                    <div class="debt-remaining">
                                        <label>Deuda Restante</label>
                                        <div class="debt-amount-display">
                                            <span class="currency-symbol">{{ $currency->symbol }}</span>
                                            <input type="text" class="form-control modern-input" id="remaining_debt" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <div class="section-title">
                                    <i class="fas fa-credit-card"></i>
                                    <span>Detalles del Pago</span>
                                </div>
                                <div class="payment-details-grid">
                                    <div class="form-group">
                                        <label for="payment_amount">Monto del Pago</label>
                                        <div class="input-wrapper">
                                            <i class="fas fa-dollar-sign input-icon"></i>
                                            <input type="number" class="form-control modern-input" id="payment_amount" name="payment_amount" step="0.01" min="0.01" required>
                                        </div>
                                        <small class="form-text">El monto no puede ser mayor que la deuda actual</small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="payment_date">Fecha del Pago</label>
                                        <div class="input-wrapper">
                                            <i class="fas fa-calendar input-icon"></i>
                                            <input type="date" class="form-control modern-input" id="payment_date" name="payment_date" required>
                                        </div>
                                        <small class="form-text">La fecha no puede ser mayor a hoy</small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="payment_time">Hora del Pago</label>
                                        <div class="input-wrapper">
                                            <i class="fas fa-clock input-icon"></i>
                                            <input type="time" class="form-control modern-input" id="payment_time" name="payment_time" required>
                                        </div>
                                        <small class="form-text">Hora en que se realizó el pago</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <div class="section-title">
                                    <i class="fas fa-sticky-note"></i>
                                    <span>Notas Adicionales</span>
                                </div>
                                <div class="form-group">
                                    <label for="payment_notes">Notas</label>
                                    <div class="textarea-wrapper">
                                        <textarea class="form-control modern-textarea" id="payment_notes" name="notes" rows="3" placeholder="Detalles adicionales sobre este pago..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary modern-btn" data-dismiss="modal">
                            <i class="fas fa-times"></i>
                            <span>Cancelar</span>
                        </button>
                        <button type="submit" class="btn btn-primary modern-btn">
                            <i class="fas fa-save"></i>
                            <span>Registrar Pago</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap4.min.css">
    <style>
        /* ===== VARIABLES Y CONFIGURACIÓN GLOBAL ===== */
        :root {
            --primary-color: #667eea;
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-color: #f093fb;
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-color: #4facfe;
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --warning-color: #43e97b;
            --warning-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            --danger-color: #fa709a;
            --danger-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            --purple-color: #a8edea;
            --purple-gradient: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            --dark-color: #2c3e50;
            --light-color: #ecf0f1;
            --border-radius: 12px;
            --border-radius-sm: 8px;
            --shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 12px 40px rgba(0, 0, 0, 0.15);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ===== MEJORAS CON TAILWIND ===== */
        /* Mejoras en la accesibilidad y focus states */
        .hero-btn:focus,
        .filter-btn:focus,
        .search-input:focus,
        .rate-input:focus {
            @apply ring-2 ring-blue-500 ring-offset-2 outline-none;
        }

        /* Mejoras en las transiciones */
        .stat-card,
        .hero-btn,
        .filter-btn,
        .action-btn {
            @apply transition-all duration-300 ease-in-out;
        }

        /* Mejoras en el hover de las tarjetas */
        .stat-card:hover {
            @apply transform -translate-y-1 shadow-lg;
        }

        /* Mejoras en los botones de acción */
        .action-btn:hover {
            @apply transform scale-105 shadow-md;
        }

        /* Mejoras en la tabla */
        .customers-table tbody tr {
            @apply transition-colors duration-200;
        }

        .customers-table tbody tr:hover {
            @apply bg-gray-50;
        }

        /* Mejoras en los badges */
        .status-badge,
        .growth-badge,
        .debt-warning-badge {
            @apply transition-all duration-200;
        }

        /* Mejoras en los inputs */
        .search-input,
        .rate-input {
            @apply focus:ring-2 focus:ring-blue-500 focus:border-blue-500;
        }

        /* Mejoras en los modales */
        .modern-modal {
            @apply backdrop-blur-sm;
        }

        /* Mejoras en la responsividad */
        @media (max-width: 640px) {
            .hero-title {
                @apply text-2xl;
            }
            
            .stat-number {
                @apply text-xl;
            }
        }

        /* ===== ANIMACIONES Y ESTADOS DE CARGA ===== */
        /* Spinner personalizado */
        .spinner-custom {
            @apply animate-spin rounded-full border-2 border-gray-300 border-t-blue-600;
        }

        /* Animación de fade in para las tarjetas */
        .customer-card {
            @apply animate-fade-in;
        }

        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fade-in 0.3s ease-out;
        }

        /* Mejoras en los estados de hover */
        .customer-card:hover {
            @apply transform -translate-y-1 shadow-lg;
        }

        /* Mejoras en los botones de acción */
        .action-btn {
            @apply transition-all duration-200 ease-in-out;
        }

        .action-btn:hover {
            @apply transform scale-110 shadow-lg;
        }

        /* Mejoras en los filtros */
        .filter-btn {
            @apply transition-all duration-200 ease-in-out;
        }

        .filter-btn:hover:not(.active) {
            @apply transform -translate-y-0.5 shadow-md;
        }

        /* Mejoras en el tipo de cambio */
        .update-rate-btn:disabled {
            @apply opacity-50 cursor-not-allowed;
        }

        /* Mejoras en la búsqueda */
        .search-clear {
            @apply transition-all duration-200 ease-in-out;
        }

        .search-clear:hover {
            @apply transform scale-110 bg-gray-200;
        }

        /* ===== HERO SECTION ===== */
        .hero-section {
            background: var(--primary-gradient);
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-bottom: 2rem;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="60" r="0.5" fill="white" opacity="0.1"/><circle cx="90" cy="40" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .hero-title i {
            font-size: 3rem;
            background: linear-gradient(45deg, #fff, #f0f0f0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 1.5rem;
        }

        .hero-stats {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            display: block;
            font-size: 2rem;
            font-weight: 700;
            line-height: 1;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .action-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--border-radius-sm);
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            color: white;
            position: relative;
            overflow: hidden;
        }

        .action-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .action-btn:hover::before {
            left: 100%;
        }

        .action-btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .action-btn-secondary {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .action-btn-info {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .action-btn-warning {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }

        /* ===== STATS GRID ===== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
        }

        .stat-card-primary::before { background: var(--primary-gradient); }
        .stat-card-success::before { background: var(--success-gradient); }
        .stat-card-warning::before { background: var(--warning-gradient); }
        .stat-card-purple::before { background: var(--purple-gradient); }
        .stat-card-danger::before { background: var(--danger-gradient); }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 1rem;
        }

        .stat-card-primary .stat-icon { background: var(--primary-gradient); }
        .stat-card-success .stat-icon { background: var(--success-gradient); }
        .stat-card-warning .stat-icon { background: var(--warning-gradient); }
        .stat-card-purple .stat-icon { background: var(--purple-gradient); }
        .stat-card-danger .stat-icon { background: var(--danger-gradient); }

        .stat-content {
            position: relative;
        }

        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-color);
            margin: 0;
        }

        .growth-badge {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.5rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .growth-positive {
            background: rgba(76, 175, 80, 0.1);
            color: #4caf50;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .stat-progress {
            height: 4px;
            background: #f0f0f0;
            border-radius: 2px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: var(--primary-gradient);
            border-radius: 2px;
            transition: width 1s ease-in-out;
        }

        /* ===== EXCHANGE RATE CARD ===== */
        .exchange-rate-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 1.5rem;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .header-content {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-icon {
            width: 50px;
            height: 50px;
            background: var(--primary-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }

        .header-text h4 {
            margin: 0;
            font-weight: 600;
            color: var(--dark-color);
        }

        .header-text p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }

        .collapse-btn {
            background: none;
            border: none;
            color: #666;
            font-size: 1.1rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .collapse-btn:hover {
            color: var(--primary-color);
            transform: scale(1.1);
        }

        .card-body {
            padding: 1.5rem;
        }

        .exchange-rate-content {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 2rem;
            align-items: center;
        }

        .rate-input-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .rate-display {
            flex: 1;
        }

        .rate-label {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 0.5rem;
        }

        .rate-value {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .currency-symbol {
            font-weight: 600;
            color: var(--dark-color);
        }

        .rate-input {
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius-sm);
            padding: 0.75rem;
            font-size: 1.1rem;
            font-weight: 600;
            text-align: center;
            transition: var(--transition);
            width: 120px;
        }

        .rate-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .currency-code {
            font-weight: 600;
            color: var(--dark-color);
        }

        .update-rate-btn {
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: var(--border-radius-sm);
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .update-rate-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }

        .rate-info {
            max-width: 300px;
        }

        .info-card {
            background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
            border-radius: var(--border-radius-sm);
            padding: 1rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .info-card i {
            color: var(--primary-color);
            font-size: 1.2rem;
            margin-top: 0.1rem;
        }

        .info-content h6 {
            margin: 0 0 0.5rem 0;
            color: var(--dark-color);
            font-weight: 600;
        }

        .info-content p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
            line-height: 1.4;
        }

        /* ===== FILTERS SECTION ===== */
        .filters-section {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }

        .filters-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .filter-label {
            font-weight: 600;
            color: var(--dark-color);
            white-space: nowrap;
        }

        .filter-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .filter-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius-sm);
            background: white;
            color: #666;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
        }

        .filter-btn:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
            transform: translateY(-1px);
        }

        .filter-btn.active {
            background: var(--primary-gradient);
            border-color: transparent;
            color: white;
            box-shadow: var(--shadow);
        }

        .filter-btn-all.active { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .filter-btn-active.active { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .filter-btn-inactive.active { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
        .filter-btn-defaulters.active { background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); }

        .search-group {
            flex: 1;
            max-width: 400px;
        }

        .search-container {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            color: #666;
            z-index: 2;
        }

        .search-input {
            width: 100%;
            min-width: 0;
            max-width: 260px;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius-sm);
            font-size: 1rem;
            transition: var(--transition);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .search-clear {
            position: absolute;
            right: 0.75rem;
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 50%;
            transition: var(--transition);
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e0e0e0;
        }

        .search-clear:hover {
            background: #bdbdbd;
            color: #222;
        }

        /* ===== CUSTOMERS CONTAINER ===== */
        .customers-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        /* ===== TABLE VIEW ===== */
        .table-container {
            overflow-x: auto;
        }

        .customers-table {
            width: 100%;
            border-collapse: collapse;
        }

        .customers-table th {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: var(--dark-color);
            border-bottom: 2px solid #dee2e6;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .customers-table td {
            padding: 1rem;
            border-bottom: 1px solid #f8f9fa;
            vertical-align: middle;
        }

        .customers-table tr:hover {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .customer-row {
            transition: var(--transition);
        }

        .customer-row:hover {
            transform: scale(1.01);
            box-shadow: var(--shadow);
        }

        /* Customer Info */
        .customer-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .customer-avatar {
            flex-shrink: 0;
        }

        .avatar-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.2rem;
            transition: var(--transition);
        }

        .avatar-circle:hover {
            transform: scale(1.1);
        }

        .customer-details {
            flex: 1;
        }

        .customer-name {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.25rem;
        }

        .customer-email {
            color: #666;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Contact Info */
        .contact-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #666;
        }

        /* ID Badge */
        .id-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.5rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        /* Sales Info */
        .sales-info {
            text-align: center;
        }

        .sales-amount {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.25rem;
        }

        .sales-count {
            color: #666;
            font-size: 0.8rem;
        }

        .no-sales {
            color: #999;
            font-style: italic;
        }

        /* Debt Info */
        .debt-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .debt-amount {
            font-weight: 600;
            color: #dc3545;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .debt-amount-value {
            font-size: 1.1rem;
        }

        .debt-warning-badge {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            color: #fff;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            cursor: help;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .debt-warning-badge:hover {
            transform: scale(1.1);
            box-shadow: 0 3px 6px rgba(0,0,0,0.15);
        }

        .debt-status {
            font-size: 0.8rem;
            color: #666;
            margin-top: 0.25rem;
        }

        .no-debt-badge {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 0.5rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .edit-debt-btn {
            background: none;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .edit-debt-btn:hover {
            background: var(--primary-color);
            color: white;
            transform: scale(1.1);
        }

        /* Status Badge */
        .status-badge {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-active {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }

        .status-inactive {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            color: white;
        }

        /* Debt Type Badge - Solo para móviles */
        .debt-type-badge {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .debt-type-defaulters {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: white;
        }

        .debt-type-current {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }

        .debt-type-none {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
        }

        /* Debt Type Info for Mobile Cards */
        .debt-type-info {
            margin-top: 0.5rem;
        }

        .debt-type-info .debt-type-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        /* Tooltip styles for debt type explanation */
        .debt-type-badge {
            cursor: help;
            position: relative;
        }

        .debt-type-badge[title]:hover::after {
            content: attr(title);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            white-space: nowrap;
            z-index: 1000;
            margin-bottom: 0.5rem;
        }

        /* Ocultar badges de tipo de deuda en pantallas grandes */
        @media (min-width: 992px) {
            .debt-type-badge {
                display: none !important;
            }
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .action-btn {
            width: 36px;
            height: 36px;
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            color: white;
            font-size: 0.9rem;
        }

        .action-btn-view {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .action-btn-edit {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }

        .action-btn-delete {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        .action-btn-sale {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .action-btn:hover {
            transform: scale(1.1);
            box-shadow: var(--shadow-hover);
        }

        /* ===== CARDS VIEW (MOBILE) ===== */
        .cards-container {
            padding: 1.5rem;
            display: grid;
            gap: 1.5rem;
        }

        .customer-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: var(--transition);
            border-left: 4px solid var(--primary-color);
        }

        .customer-card[data-status="active"] {
            border-left-color: #4facfe;
        }

        .customer-card[data-status="inactive"] {
            border-left-color: #fa709a;
        }

        .customer-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .card-header {
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            border-bottom: 1px solid #f8f9fa;
        }

        .customer-avatar {
            flex-shrink: 0;
        }

        .avatar-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.5rem;
            transition: var(--transition);
        }

        .customer-info {
            flex: 1;
        }

        .customer-name {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.25rem;
            font-size: 1.1rem;
        }

        .customer-email {
            color: #666;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-indicator {
            flex-shrink: 0;
        }

        .card-body {
            padding: 1.5rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .info-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: #666;
            font-weight: 500;
        }

        .info-value {
            font-weight: 600;
            color: var(--dark-color);
        }

        .info-value small {
            font-weight: normal;
            color: #666;
        }

        .card-actions {
            padding: 1.5rem;
            border-top: 1px solid #f8f9fa;
            background: #f8f9fa;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 0.75rem;
        }

        .action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem;
            border: none;
            border-radius: var(--border-radius-sm);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            color: white;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .action-btn-view {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .action-btn-edit {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }

        .action-btn-payment {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .action-btn-sale {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            color: var(--dark-color);
        }

        .action-btn-delete {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }

        /* ===== MODALS ===== */
        .modern-modal {
            border-radius: var(--border-radius);
            border: none;
            box-shadow: var(--shadow-hover);
        }

        .modal-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 1px solid #dee2e6;
            padding: 1.5rem;
        }

        .modal-title-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .title-icon {
            width: 50px;
            height: 50px;
            background: var(--primary-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }

        .payment-icon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .title-content h5 {
            margin: 0;
            font-weight: 600;
            color: var(--dark-color);
        }

        .modal-subtitle {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }

        .modal-close {
            background: none;
            border: none;
            color: #666;
            font-size: 1.2rem;
            cursor: pointer;
            transition: var(--transition);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-close:hover {
            background: #f8f9fa;
            color: #333;
        }

        .modal-body {
            padding: 2rem;
        }

        /* Loading Container */
        .loading-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            text-align: center;
        }

        .loading-spinner {
            margin-bottom: 1.5rem;
        }

        .spinner-ring {
            width: 60px;
            height: 60px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loading-text h5 {
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }

        .loading-text p {
            color: #666;
        }

        /* Customer Details Modal */
        .customer-details-container {
            max-height: 70vh;
            overflow-y: auto;
        }

        .sales-history-section {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .section-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            border-bottom: 1px solid #dee2e6;
        }

        .section-icon {
            width: 40px;
            height: 40px;
            background: var(--primary-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1rem;
        }

        .section-title h6 {
            margin: 0;
            font-weight: 600;
            color: var(--dark-color);
        }

        .section-title p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }

        .customer-name-highlight {
            color: var(--primary-color);
            font-weight: 600;
        }

        .filters-section {
            padding: 1.5rem;
            border-bottom: 1px solid #f8f9fa;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .filter-label {
            font-weight: 600;
            color: var(--dark-color);
            font-size: 0.9rem;
        }

        .date-range, .amount-range {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .date-input, .amount-input {
            position: relative;
            flex: 1;
        }

        .date-input i, .amount-input .currency-symbol {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            z-index: 2;
        }

        .date-input input, .amount-input input {
            width: 100%;
            padding: 0.75rem 0.75rem 0.75rem 2rem;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius-sm);
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .amount-input input {
            padding-left: 2.5rem;
        }

        .date-input input:focus, .amount-input input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .date-separator, .amount-separator {
            color: #666;
            font-weight: 500;
        }

        .filter-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }

        .filter-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius-sm);
            background: white;
            color: #666;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        .filter-btn-apply {
            background: var(--primary-gradient);
            border-color: transparent;
            color: white;
        }

        .filter-btn-clear {
            background: white;
            border-color: #e9ecef;
            color: #666;
        }

        .filter-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .sales-table-container {
            padding: 1.5rem;
        }

        .table-wrapper {
            max-height: 400px;
            overflow-y: auto;
            border-radius: var(--border-radius-sm);
            border: 1px solid #e9ecef;
        }

        .sales-table {
            width: 100%;
            border-collapse: collapse;
        }

        .sales-table th {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: var(--dark-color);
            border-bottom: 1px solid #dee2e6;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .sales-table td {
            padding: 1rem;
            border-bottom: 1px solid #f8f9fa;
        }

        .sales-table tr:hover {
            background: #f8f9fa;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
        }

        .empty-icon {
            font-size: 3rem;
            color: #ccc;
            margin-bottom: 1rem;
        }

        .empty-state p {
            color: #666;
            margin: 0;
        }

        .table-footer {
            padding: 1rem 0 0 0;
            text-align: center;
            border-top: 1px solid #f8f9fa;
            margin-top: 1rem;
        }

        .sales-count {
            color: #666;
            font-size: 0.9rem;
        }

        /* Payment Modal */
        .form-sections {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .form-section {
            background: #f8f9fa;
            border-radius: var(--border-radius-sm);
            padding: 1.5rem;
        }

        .form-section .section-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
            font-weight: 600;
            color: var(--dark-color);
        }

        .form-section .section-title i {
            color: var(--primary-color);
        }

        .debt-status-card {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .debt-current, .debt-remaining {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .debt-current label, .debt-remaining label {
            font-weight: 600;
            color: var(--dark-color);
            font-size: 0.9rem;
        }

        .debt-amount-display {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .payment-details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-group label {
            font-weight: 600;
            color: var(--dark-color);
            font-size: 0.9rem;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            z-index: 2;
        }

        .modern-input {
            width: 100%;
            padding: 0.75rem 0.75rem 0.75rem 2.5rem;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius-sm);
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .modern-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .modern-textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius-sm);
            font-size: 0.9rem;
            transition: var(--transition);
            resize: vertical;
        }

        .modern-textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-text {
            color: #666;
            font-size: 0.8rem;
        }

        .modal-footer {
            padding: 1.5rem;
            border-top: 1px solid #dee2e6;
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }

        .modern-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--border-radius-sm);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-primary.modern-btn {
            background: var(--primary-gradient);
            color: white;
        }

        .btn-secondary.modern-btn {
            background: #6c757d;
            color: white;
        }

        .modern-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        /* ===== RESPONSIVE DESIGN ===== */
        @media (max-width: 768px) {
            .hero-section {
                padding: 1.5rem;
            }

            .hero-title {
                font-size: 2rem;
            }

            .hero-stats {
                gap: 1rem;
            }

            .stat-number {
                font-size: 1.5rem;
            }

            .action-buttons {
                flex-direction: row;
                gap: 0.5rem;
            }

            .action-btn {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .exchange-rate-content {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .rate-input-section {
                flex-direction: column;
                align-items: stretch;
            }

            .filters-container {
                flex-direction: column;
                gap: 1rem;
            }

            .search-group {
                max-width: none;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                grid-template-columns: repeat(2, 1fr);
            }

            .debt-status-card {
                grid-template-columns: 1fr;
            }

            .payment-details-grid {
                grid-template-columns: 1fr;
            }

            .filter-actions {
                justify-content: center;
            }
        }

        @media (max-width: 576px) {
            .hero-title {
                font-size: 1.5rem;
            }

            .hero-subtitle {
                font-size: 1rem;
            }

            .stat-card {
                padding: 1rem;
            }

            .stat-icon {
                width: 50px;
                height: 50px;
                font-size: 1.2rem;
            }

            .stat-number {
                font-size: 1.5rem;
            }

            .action-btn {
                padding: 0.5rem;
                font-size: 0.8rem;
            }

            .customer-card .card-header {
                padding: 1rem;
            }

            .avatar-circle {
                width: 50px;
                height: 50px;
                font-size: 1.2rem;
            }

            .action-buttons {
                grid-template-columns: 1fr;
            }
        }

        /* ===== ANIMATIONS ===== */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .customer-card, .stat-card, .exchange-rate-card {
            animation: fadeInUp 0.6s ease-out;
        }

        .customer-card:nth-child(1) { animation-delay: 0.1s; }
        .customer-card:nth-child(2) { animation-delay: 0.2s; }
        .customer-card:nth-child(3) { animation-delay: 0.3s; }
        .customer-card:nth-child(4) { animation-delay: 0.4s; }
        .customer-card:nth-child(5) { animation-delay: 0.5s; }

        /* ===== SCROLLBAR STYLING ===== */
        .table-wrapper::-webkit-scrollbar,
        .customer-details-container::-webkit-scrollbar {
            width: 8px;
        }

        .table-wrapper::-webkit-scrollbar-track,
        .customer-details-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .table-wrapper::-webkit-scrollbar-thumb,
        .customer-details-container::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 4px;
        }

        .table-wrapper::-webkit-scrollbar-thumb:hover,
        .customer-details-container::-webkit-scrollbar-thumb:hover {
            background: #5a6fd8;
        }

        /* Firefox scrollbar */
        .table-wrapper,
        .customer-details-container {
            scrollbar-width: thin;
            scrollbar-color: var(--primary-color) #f1f1f1;
        }

        /* ===== ESTILOS ADICIONALES PARA LA TABLA DE VENTAS ===== */
        .sale-date, .sale-products, .sale-amount {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
        }

        .sale-date {
            color: var(--dark-color);
        }

        .sale-products {
            color: #666;
        }

        .sale-amount {
            color: #28a745;
            font-weight: 600;
        }

        .sale-date i, .sale-products i, .sale-amount i {
            color: var(--primary-color);
            font-size: 0.9rem;
        }

        /* ===== MEJORAS EN LA EXPERIENCIA DE USUARIO ===== */
        .counter {
            animation: countUp 2s ease-out;
        }

        @keyframes countUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Efecto de hover mejorado para las tarjetas */
        .customer-card:hover .avatar-circle {
            transform: scale(1.1) rotate(5deg);
        }

        /* Efecto de pulso para los botones de acción */
        .action-btn:active {
            transform: scale(0.95);
        }

        /* Mejora en la legibilidad de los textos */
        .customer-name, .stat-number, .debt-amount {
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        /* Efecto de sombra dinámica */
        .stat-card:hover, .customer-card:hover, .exchange-rate-card:hover {
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }

        /* Transiciones suaves para todos los elementos interactivos */
        * {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Mejora en el contraste de colores */
        .text-muted {
            color: #6c757d !important;
        }

        .text-success {
            color: #28a745 !important;
        }

        .text-danger {
            color: #dc3545 !important;
        }

        .text-warning {
            color: #ffc107 !important;
        }

        .text-info {
            color: #17a2b8 !important;
        }

        /* Efecto de carga para los botones */
        .action-btn.loading {
            position: relative;
            pointer-events: none;
        }

        .action-btn.loading::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            margin: auto;
            border: 2px solid transparent;
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        /* Mejora en la accesibilidad */
        .action-btn:focus,
        .filter-btn:focus,
        .search-input:focus,
        .modern-input:focus {
            outline: 2px solid var(--primary-color);
            outline-offset: 2px;
        }

        /* Efecto de profundidad para los modales */
        .modal-backdrop {
            backdrop-filter: blur(5px);
        }

        .modern-modal {
            transform: scale(0.9);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .modal.show .modern-modal {
            transform: scale(1);
            opacity: 1;
        }

        /* --- BOTONES DE ACCIÓN EN TABLA --- */
        .td-actions .action-buttons {
            display: flex !important;
            flex-direction: row !important;
            gap: 0.5rem !important;
            justify-content: flex-start;
            align-items: center;
        }
        .action-btn {
            width: 36px;
            height: 36px;
            min-width: 36px;
            min-height: 36px;
            border: none;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            color: white;
            font-size: 1.1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            background: #bdbdbd;
            padding: 0;
        }
        .action-btn-view { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .action-btn-edit { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
        .action-btn-delete { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
        .action-btn-sale { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: var(--dark-color); }
        .action-btn:hover, .action-btn:focus {
            filter: brightness(1.1) drop-shadow(0 2px 8px rgba(0,0,0,0.08));
            transform: scale(1.08);
            outline: none;
        }
        .action-btn i {
            margin: 0;
            font-size: 1.1rem;
        }
        /* --- BOTONES GENERALES --- */
        .action-btn, .modern-btn, .update-rate-btn, .filter-btn, .search-clear {
            border-radius: 10px !important;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        .modern-btn, .update-rate-btn {
            padding: 0.6rem 1.2rem;
            font-size: 1rem;
        }
        .update-rate-btn {
            background: var(--primary-gradient);
            color: #fff;
        }
        .update-rate-btn:hover {
            filter: brightness(1.1);
        }
        .filter-btn {
            font-size: 0.95rem;
            padding: 0.5rem 1.1rem;
        }
        .search-clear {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e0e0e0;
            color: #666;
        }
        .search-clear:hover {
            background: #bdbdbd;
            color: #222;
        }
        /* --- Ajuste para iconos en botones --- */
        .action-btn span, .action-btn i {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        /* --- Responsive para botones en tabla --- */
        @media (max-width: 768px) {
            .td-actions .action-buttons {
                flex-wrap: wrap;
                gap: 0.3rem !important;
            }
            .action-btn {
                width: 32px;
                height: 32px;
                min-width: 32px;
                min-height: 32px;
                font-size: 1rem;
            }
        }
        .redesigned-rate-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
            flex-wrap: wrap;
        }
        .update-rate-btn {
            margin-left: 1rem;
            align-self: stretch;
            height: 48px;
            display: flex;
            align-items: center;
        }
        @media (max-width: 767px) {
            .redesigned-rate-row {
                flex-direction: column;
                align-items: stretch;
                gap: 0.5rem;
            }
            .update-rate-btn {
                margin-left: 0 !important;
                width: 100%;
                height: auto;
            }
        }
        .exchange-block.redesigned-left {
            display: flex;
            flex-direction: row;
            align-items: center;
            min-width: 320px;
            max-width: 420px;
            flex: 1 1 350px;
            padding-right: 2rem;
            border-right: 1.5px solid #f0f0f0;
            gap: 0;
        }
        .redesigned-rate-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
            flex-wrap: nowrap;
            width: 100%;
        }
        .update-rate-btn {
            margin-left: 1rem;
            height: 48px;
            display: flex;
            align-items: center;
            padding-top: 0;
            padding-bottom: 0;
        }
        @media (max-width: 991px) {
            .exchange-block.redesigned-left {
                flex-direction: column;
                align-items: stretch;
                padding-right: 0;
                border-right: none;
            }
            .redesigned-rate-row {
                flex-direction: column;
                align-items: stretch;
                gap: 0.5rem;
                width: 100%;
            }
            .update-rate-btn {
                margin-left: 0 !important;
                width: 100%;
                height: auto;
            }
        }
        @media (max-width: 576px) {
            .card-actions {
                padding: 0.75rem 0.5rem;
            }
            .action-buttons {
                display: flex !important;
                flex-direction: row !important;
                gap: 0.4rem !important;
                overflow-x: auto;
                justify-content: flex-start;
                align-items: center;
                padding-bottom: 0.2rem;
                scrollbar-width: none; /* Firefox */
            }
            .action-buttons::-webkit-scrollbar {
                display: none; /* Chrome/Safari */
            }
            .action-btn {
                min-width: 44px;
                min-height: 44px;
                width: 44px;
                height: 44px;
                font-size: 1.3rem;
                padding: 0;
                border-radius: 12px !important;
                justify-content: center;
            }
            .action-btn span {
                display: none !important;
            }
            .action-btn:active {
                transform: scale(0.93);
                box-shadow: 0 2px 8px rgba(0,0,0,0.10);
            }
        }
        @media (max-width: 576px) {
            .redesigned-rate-row {
                flex-direction: row !important;
                align-items: center !important;
                gap: 0.5rem !important;
                flex-wrap: nowrap !important;
                justify-content: flex-start !important;
            }
            .update-rate-btn {
                margin-left: 0.5rem !important;
                width: auto !important;
                min-width: 44px;
                height: 44px !important;
                padding: 0 1.2rem !important;
                align-self: auto !important;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .rate-input {
                width: 90px !important;
                min-width: 70px;
                height: 44px !important;
                padding: 0.5rem !important;
                font-size: 1.1rem !important;
            }
            .currency-symbol, .currency-code {
                font-size: 1rem !important;
            }
            .filters-search-row {
                flex-direction: row !important;
                align-items: center !important;
                gap: 0.5rem !important;
                flex-wrap: nowrap !important;
                justify-content: flex-start !important;
            }
            .filters-btns {
                flex-direction: row !important;
                gap: 0.3rem !important;
                flex-wrap: nowrap !important;
            }
            .redesigned-search-group {
                margin-left: 0.5rem !important;
                max-width: 140px !important;
                min-width: 80px !important;
            }
            .search-input {
                font-size: 0.95rem !important;
                padding: 0.5rem 1rem 0.5rem 2.2rem !important;
                height: 38px !important;
            }
        }
        @media (max-width: 576px) {
            /* ...otros estilos responsivos... */
            .update-rate-btn span {
                display: none !important;
            }
            .filters-search-row {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 0.5rem !important;
            }
            .redesigned-search-group {
                margin-left: 0 !important;
                max-width: 100% !important;
                min-width: 0 !important;
            }
        }
        /* --- FILTROS: BOTONES RESPONSIVOS Y CENTRADOS --- */
        .filters-btns-scroll {
            display: flex;
            gap: 0.7rem;
            flex-wrap: nowrap;
        }
        .redesigned-search-group {
            max-width: 260px;
            min-width: 120px;
            width: 100%;
            margin-left: 0.7rem;
            flex: 0 0 auto;
        }
        .search-container {
             position: relative;
             display: flex;
             align-items: center;
             width: 100%;
         }
        .search-input {
            width: 100%;
            min-width: 0;
            max-width: 260px;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius-sm);
            font-size: 1rem;
            transition: var(--transition);
        }
        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .search-clear {
            position: absolute;
            right: 0.75rem;
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 50%;
            transition: var(--transition);
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e0e0e0;
        }
        .search-clear:hover {
            background: #bdbdbd;
            color: #222;
        }
        /* Ocultar campo de búsqueda visual de DataTables pero mantener funcionalidad */
        .dataTables_filter {
            display: none !important;
        }
        
        /* Ocultar también el label "Search:" si aparece */
        .dataTables_filter label {
            display: none !important;
        }
        
        /* Ocultar el input de búsqueda nativo de DataTables */
        .dataTables_filter input {
            display: none !important;
        }
        
        /* Estilos responsivos para botones de filtro */
        @media (max-width: 575px) {
            .filters-btns-scroll {
                gap: 0.5rem;
                flex-wrap: nowrap;
                width: 100%;
                overflow-x: auto;
                padding-bottom: 0.5rem;
                scrollbar-width: none; /* Firefox */
            }
            .filters-btns-scroll::-webkit-scrollbar {
                display: none; /* Chrome/Safari */
            }
            
            .filter-btn {
                min-width: 44px;
                width: 44px;
                height: 44px;
                padding: 0.5rem;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                margin: 0;
            }
            
            .filter-btn i {
                font-size: 1.1rem;
                margin: 0;
            }
            
            .filter-btn span {
                display: none !important;
            }
        }
@stop

@section('js')
    <script>
        // Funciones de Alpine.js

        function exchangeFilters() {
            return {
                exchangeRate: 120.00,
                updating: false,
                
                init() {
                    // Cargar el tipo de cambio guardado
                    const savedRate = localStorage.getItem('exchangeRate');
                    if (savedRate) {
                        this.exchangeRate = parseFloat(savedRate);
                    }
                },
                
                updateExchangeRate() {
                    if (this.exchangeRate <= 0) return;
                    
                    this.updating = true;
                    
                    // Simular actualización
                    setTimeout(() => {
                        currentExchangeRate = this.exchangeRate;
                        localStorage.setItem('exchangeRate', this.exchangeRate);
                        updateBsValues(this.exchangeRate);
                        
                        this.updating = false;
                        
                        // Mostrar notificación
                        Swal.fire({
                            icon: 'success',
                            title: 'Tipo de cambio actualizado',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }, 500);
                }
            }
        }



        // Variable global para almacenar el tipo de cambio actual
        let currentExchangeRate = 1.0;

        $(document).ready(function() {
            // Inicializar DataTable
            const table = $('#customersTable').DataTable({
                responsive: true,
                autoWidth: false,
                stateSave: true, // Guarda la página y el estado del paginador
                searching: true, // Mantener búsqueda habilitada para filtros personalizados
                lengthChange: false,
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
                }
            });

            // Conectar el campo de búsqueda con DataTables (vista desktop)
            $('#mobileSearch').on('keyup', function() {
                applyFiltersAndSearch();
            });

            // Mostrar/ocultar botón de limpiar búsqueda
            $('#mobileSearch').on('input', function() {
                const hasValue = $(this).val().length > 0;
                $('#clearSearch').toggle(hasValue);
            });

            // Cargar el tipo de cambio guardado en localStorage (si existe)
            const savedRate = localStorage.getItem('exchangeRate');
            if (savedRate) {
                currentExchangeRate = parseFloat(savedRate);
                $('#exchangeRate').val(currentExchangeRate);
                updateBsValues(currentExchangeRate);
            } else {
                // Si no hay valor guardado, usar el valor por defecto del input
                currentExchangeRate = parseFloat($('#exchangeRate').val());
                updateBsValues(currentExchangeRate);
            }
            
            // Actualizar valores en Bs cuando se cambia el tipo de cambio - Usar delegación de eventos
            $(document).on('click', '.update-exchange-rate', function() {
                const rate = parseFloat($('#exchangeRate').val());
                if (rate > 0) {
                    currentExchangeRate = rate;
                    // Guardar en localStorage para futuras visitas
                    localStorage.setItem('exchangeRate', rate);
                    updateBsValues(rate);
                    
                    // Mostrar mensaje de éxito
                    Swal.fire({
                        icon: 'success',
                        title: 'Tipo de cambio actualizado',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            });
            
            // Función para actualizar todos los valores en Bs
            function updateBsValues(rate) {
                $('.bs-debt').each(function() {
                    const debtUsd = parseFloat($(this).data('debt'));
                    const debtBs = debtUsd * rate;
                    $(this).html('Bs. ' + debtBs.toLocaleString('es-VE', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                });
            }
            
            // Botón de reporte de deudas
            $('#debtReportBtn').click(function() {
                // Mostrar el modal de carga inmediatamente
                $('#debtReportModal').modal('show');
                
                // Remover aria-hidden cuando el modal se muestra
                $('#debtReportModal').removeAttr('aria-hidden');
                
                // Cargar el reporte mediante AJAX con timeout
                $.ajax({
                    url: '{{ route("admin.customers.debt-report") }}',
                    type: 'GET',
                    data: {
                        exchange_rate: currentExchangeRate
                    },
                    timeout: 30000, // 30 segundos de timeout
                    success: function(response) {
                        // Llenar el modal con la respuesta
                        $('#debtReportModal .modal-content').html(response);
                        
                        // Pasar el tipo de cambio actual al modal
                        $('#debtReportModal').data('exchangeRate', currentExchangeRate);
                    },
                    error: function(xhr, status, error) {
                        // Cerrar el modal de carga
                        $('#debtReportModal').modal('hide');
                        
                        if (status === 'timeout') {
                            Swal.fire('Error', 'El reporte tardó demasiado en cargar. Inténtalo de nuevo.', 'error');
                        } else {
                            Swal.fire('Error', 'No se pudo cargar el reporte de deudas', 'error');
                        }
                    }
                });
            });

            // Escuchar el evento de modal mostrado
            $(document).on('shown.bs.modal', '#debtReportModal', function() {
                console.log('Modal mostrado, estableciendo tipo de cambio:', currentExchangeRate);
                
                // Asegurar que aria-hidden esté removido
                $('#debtReportModal').removeAttr('aria-hidden');
                
                // Establecer el valor del tipo de cambio en el modal
                $('#modalExchangeRate').val(currentExchangeRate);
                
                // Actualizar los valores en Bs en el modal
                updateModalBsValues(currentExchangeRate);
            });
            
            // Escuchar el evento de modal oculto para restaurar aria-hidden
            $(document).on('hidden.bs.modal', '#debtReportModal', function() {
                // Restaurar aria-hidden cuando el modal se cierra
                $('#debtReportModal').attr('aria-hidden', 'true');
            });
            
            // Función para actualizar los valores en Bs en el modal
            function updateModalBsValues(rate) {
                // Actualizar el resumen total
                $('.modal-bs-debt').each(function() {
                    const debtUsd = parseFloat($(this).data('debt'));
                    const debtBs = debtUsd * rate;
                    $(this).html('Bs. ' + debtBs.toLocaleString('es-VE', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                });
                
                // Actualizar cada fila de la tabla en el modal
                $('#debtReportModal .bs-debt').each(function() {
                    const debtUsd = parseFloat($(this).data('debt'));
                    const debtBs = debtUsd * rate;
                    $(this).html('Bs. ' + debtBs.toLocaleString('es-VE', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                });
                
                // Actualizar el total de la tabla
                $('#totalBsDebt').each(function() {
                    const totalDebtElement = $('#debtReportModal .modal-bs-debt');
                    if (totalDebtElement.length > 0) {
                        const totalDebtUsd = parseFloat(totalDebtElement.data('debt'));
                        const totalDebtBs = totalDebtUsd * rate;
                        $(this).html('Bs. ' + totalDebtBs.toLocaleString('es-VE', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    }
                });
            }
            
            // Escuchar el evento de clic en el botón de actualizar en el modal
            $(document).on('click', '#updateModalExchangeRate', function() {
                const rate = parseFloat($('#modalExchangeRate').val());
                if (rate > 0) {
                    console.log('Actualizando tipo de cambio desde el modal:', rate);
                    
                    // Actualizar la variable global
                    currentExchangeRate = rate;
                    
                    // Actualizar el input en la tabla principal
                    $('#exchangeRate').val(rate);
                    
                    // Guardar en localStorage
                    localStorage.setItem('exchangeRate', rate);
                    
                    // Actualizar valores en Bs en el modal
                    updateModalBsValues(rate);
                    
                    // Actualizar valores en Bs en la tabla principal
                    updateBsValues(rate);
                    
                    // Mostrar mensaje de éxito
                    Swal.fire({
                        icon: 'success',
                        title: 'Tipo de cambio actualizado',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            });

            // Animación de contadores
            $('.counter').each(function() {
                const $this = $(this);
                const countTo = parseInt($this.text());

                $({
                    countNum: 0
                }).animate({
                    countNum: countTo
                }, {
                    duration: 1000,
                    easing: 'swing',
                    step: function() {
                        $this.text(Math.floor(this.countNum));
                    },
                    complete: function() {
                        $this.text(this.countNum);
                    }
                });
            });

            // Variable para mantener el filtro actual
            let currentFilter = 'all';
            
            // Filtros de estado - Mantener compatibilidad con Alpine.js
            $(document).on('click touchstart', '.filter-btn', function(e) {
                e.stopPropagation();
                
                // Si Alpine.js está disponible, usar su sistema
                if (window.Alpine && window.Alpine.store) {
                    const filter = $(this).data('filter');
                    if (window.Alpine.store('filters')) {
                        window.Alpine.store('filters').currentFilter = filter;
                    }
                } else {
                    // Fallback al sistema original
                    $('.filter-btn').removeClass('active');
                    $(this).addClass('active');
                    currentFilter = $(this).data('filter');
                }
                
                applyFiltersAndSearch();
            });
            
            // Función para aplicar filtros y búsqueda
            function applyFiltersAndSearch() {
                // Obtener el término de búsqueda
                const searchTerm = $('#mobileSearch').val();
                
                // Obtener el filtro actual
                let currentFilter = 'all';
                
                // Verificar si hay un botón activo
                const activeButton = $('.filter-btn.active');
                if (activeButton.length > 0) {
                    currentFilter = activeButton.data('filter');
                }
                
                // Si Alpine.js está disponible, usar su estado
                if (window.Alpine && window.Alpine.store && window.Alpine.store('filters')) {
                    currentFilter = window.Alpine.store('filters').currentFilter || currentFilter;
                }
                
                // Filtrar tabla (vista desktop)
                if (table) {
                    // Limpiar filtros previos
                    table.search('').columns().search('').draw();
                    
                    // Aplicar filtro de estado
                    if (currentFilter === 'active') {
                        table.column(6).search('Activo').draw();
                    } else if (currentFilter === 'inactive') {
                        table.column(6).search('Inactivo').draw();
                    } else if (currentFilter === 'defaulters') {
                        // Filtrar clientes morosos usando una función personalizada
                        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                            // Verificar si la fila tiene el icono de advertencia (moroso)
                            const row = table.row(dataIndex).node();
                            const hasWarningIcon = $(row).find('.debt-warning-badge').length > 0;
                            return hasWarningIcon;
                        });
                        table.draw();
                        // Remover el filtro personalizado después de aplicarlo
                        $.fn.dataTable.ext.search.pop();
                    }
                    
                    // Aplicar búsqueda si hay término de búsqueda
                    if (searchTerm) {
                        table.search(searchTerm).draw();
                    }
                }
                
                // Filtrar tarjetas móviles
                $('.customer-card').each(function() {
                    const $card = $(this);
                    const cardStatus = $card.data('status');
                    const dataDefaulter = $card.data('defaulter');
                    const isDefaulter = dataDefaulter === true || dataDefaulter === 'true';
                    let shouldShow = false;
                    
                    // Aplicar filtro de estado
                    if (currentFilter === 'all') {
                        shouldShow = true;
                    } else if (currentFilter === 'active' && cardStatus === 'active') {
                        shouldShow = true;
                    } else if (currentFilter === 'inactive' && cardStatus === 'inactive') {
                        shouldShow = true;
                    } else if (currentFilter === 'defaulters' && isDefaulter) {
                        shouldShow = true;
                    }
                    
                    // Aplicar búsqueda si hay término de búsqueda
                    if (shouldShow && searchTerm) {
                        const customerName = $card.find('.customer-name').text().toLowerCase();
                        const customerEmail = $card.find('.customer-email').text().toLowerCase();
                        const customerPhone = $card.find('.info-value').text().toLowerCase();
                        
                        shouldShow = customerName.includes(searchTerm.toLowerCase()) || 
                                   customerEmail.includes(searchTerm.toLowerCase()) || 
                                   customerPhone.includes(searchTerm.toLowerCase());
                    }
                    
                    // Mostrar/ocultar tarjeta
                    if (shouldShow) {
                        $card.show();
                    } else {
                        $card.hide();
                    }
                });
            }



            // Limpiar búsqueda
            $('#clearSearch').click(function() {
                $('#mobileSearch').val('');
                applyFiltersAndSearch();
            });

            // Tooltips
            $('[data-toggle="tooltip"]').tooltip();
            
            // Inicializar filtros al cargar la página
            applyFiltersAndSearch();



                                        // Variable global para almacenar las ventas del cliente actual
                            let currentCustomerSales = [];

            // Ver detalles del cliente - Usar delegación de eventos para funcionar con DataTable
            $(document).on('click', '.show-customer', function() {
                const customerId = $(this).data('id');

                $.ajax({
                    url: `/customers/${customerId}`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            const customer = response.customer;

                            // Mostrar nombre del cliente en el encabezado
                            $('#customerName').text(customer.name);

                            // Guardar las ventas globalmente para filtrado
                            currentCustomerSales = customer.sales || [];

                            // Llenar tabla de historial de ventas
                            displaySales(currentCustomerSales);

                            // Limpiar filtros
                            $('#dateFrom').val('');
                            $('#dateTo').val('');
                            $('#amountFrom').val('');
                            $('#amountTo').val('');

                            $('#showCustomerModal').modal('show');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'No se pudieron cargar los detalles del cliente', 'error');
                    }
                });
            });

                            // Función para mostrar las ventas
                            function displaySales(sales) {
                                const salesTable = $('#salesHistoryTable');
                                salesTable.empty();
                                
                                if (sales && sales.length > 0) {
                                    sales.forEach(function(sale) {
                                        const row = `
                                            <tr>
                                                <td>
                                                    <div class="sale-date">
                                                        <i class="fas fa-calendar-alt"></i>
                                                        ${sale.date}
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="sale-products">
                                                        <i class="fas fa-box"></i>
                                                        ${sale.total_products} productos
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="sale-amount">
                                                        <i class="fas fa-dollar-sign"></i>
                                                        {{ $currency->symbol }}${parseFloat(sale.total_amount).toLocaleString('es-PE', {
                                                            minimumFractionDigits: 2,
                                                            maximumFractionDigits: 2
                                                        })}
                                                    </div>
                                                </td>
                                            </tr>
                                        `;
                                        salesTable.append(row);
                                    });
                                    
                                    // Actualizar contador
                                    $('#salesCount').text(sales.length);
                                } else {
                                    salesTable.html(`
                                        <tr>
                                            <td colspan="3" class="empty-state">
                                                <div class="empty-icon">
                                                    <i class="fas fa-info-circle"></i>
                                                </div>
                                                <p>No hay ventas que coincidan con los filtros</p>
                                            </td>
                                        </tr>
                                    `);
                                    $('#salesCount').text('0');
                                }
                            }

                            // Función para filtrar ventas
                            function filterSales() {
                                const dateFrom = $('#dateFrom').val();
                                const dateTo = $('#dateTo').val();
                                const amountFrom = parseFloat($('#amountFrom').val()) || 0;
                                const amountTo = parseFloat($('#amountTo').val()) || Infinity;

                                let filteredSales = currentCustomerSales.filter(function(sale) {
                                    // Convertir fecha de venta a objeto Date para comparación
                                    const saleDate = new Date(sale.date.split('/').reverse().join('-'));
                                    
                                    // Filtro de fecha
                                    let dateMatch = true;
                                    if (dateFrom) {
                                        const fromDate = new Date(dateFrom);
                                        dateMatch = dateMatch && saleDate >= fromDate;
                                    }
                                    if (dateTo) {
                                        const toDate = new Date(dateTo);
                                        dateMatch = dateMatch && saleDate <= toDate;
                                    }
                                    
                                    // Filtro de monto
                                    const amountMatch = sale.total_amount >= amountFrom && sale.total_amount <= amountTo;
                                    
                                    return dateMatch && amountMatch;
                                });

                                displaySales(filteredSales);
                            }

                            // Aplicar filtros
                            $('#applyFilters').click(function() {
                                filterSales();
                            });

                            // Limpiar filtros
                            $('#clearFilters').click(function() {
                                $('#dateFrom').val('');
                                $('#dateTo').val('');
                                $('#amountFrom').val('');
                                $('#amountTo').val('');
                                displaySales(currentCustomerSales);
                            });

                            // Aplicar filtros al presionar Enter en los inputs
                            $('#dateFrom, #dateTo, #amountFrom, #amountTo').keypress(function(e) {
                                if (e.which === 13) {
                                    filterSales();
                                }
            });

            // Eliminar cliente - Usar delegación de eventos para funcionar con DataTable
            $(document).on('click', '.delete-customer', function() {
                const id = $(this).data('id');

                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "Esta acción no se puede revertir",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/customers/delete/${id}`,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: '¡Eliminado!',
                                        text: response.message,
                                        icon: response.icons
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    // Mostrar mensaje de error con formato mejorado y botones adicionales
                                    let showCancelButton = false;
                                    let cancelButtonText = '';
                                    let confirmButtonText = 'Entendido';
                                    
                                    // Si tiene ventas, mostrar botón para ir a ventas
                                    if (response.has_sales) {
                                        showCancelButton = true;
                                        cancelButtonText = 'Ver Ventas';
                                        confirmButtonText = 'Entendido';
                                    }
                                    
                                    Swal.fire({
                                        title: response.icons === 'warning' ? 'No se puede eliminar' : 'Error',
                                        html: response.message.replace(/\n/g, '<br>'),
                                        icon: response.icons,
                                        showCancelButton: showCancelButton,
                                        confirmButtonColor: response.icons === 'warning' ? '#ed8936' : '#667eea',
                                        cancelButtonColor: '#667eea',
                                        confirmButtonText: confirmButtonText,
                                        cancelButtonText: cancelButtonText
                                    }).then((result) => {
                                        if (result.dismiss === Swal.DismissReason.cancel && response.has_sales) {
                                            // Redirigir a la página de ventas con filtro por cliente
                                            window.location.href = '/sales?search=' + encodeURIComponent(response.customer_name || '');
                                        }
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                let errorMessage = 'No se pudo eliminar el cliente';
                                let iconType = 'error';
                                
                                // Intentar obtener el mensaje de error del servidor
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                    // Determinar el tipo de icono basado en la respuesta del servidor
                                    if (xhr.responseJSON.icons === 'warning') {
                                        iconType = 'warning';
                                    }
                                } else if (xhr.status === 422) {
                                    errorMessage = 'No se puede eliminar este cliente debido a restricciones del sistema';
                                } else if (xhr.status === 404) {
                                    errorMessage = 'El cliente no fue encontrado';
                                } else if (xhr.status === 403) {
                                    errorMessage = 'No tienes permisos para eliminar este cliente';
                                } else if (xhr.status === 500) {
                                    errorMessage = 'Error interno del servidor al eliminar el cliente';
                                }
                                
                                Swal.fire({
                                    title: iconType === 'warning' ? 'No se puede eliminar' : 'Error',
                                    html: errorMessage.replace(/\n/g, '<br>'),
                                    icon: iconType,
                                    confirmButtonColor: iconType === 'warning' ? '#ed8936' : '#667eea',
                                    confirmButtonText: 'Entendido'
                                });
                            }
                        });
                    }
                });
            });

            // Exportar clientes
            $('#exportCustomers').click(function() {
                Swal.fire({
                    title: 'Exportar Clientes',
                    text: 'Seleccione el formato de exportación',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Excel',
                    cancelButtonText: 'PDF'
                }).then((result) => {
                    const format = result.isConfirmed ? 'excel' : 'pdf';
                    window.location.href = `/customers/export/${format}`;
                });
            });

            // Manejar el botón de editar deuda
            $(document).on('click', '.edit-debt-btn', function() {
                let $debtValue, customerId, currentDebt, customerName;
                
                // Verificar si estamos en la vista de escritorio (tabla) o móvil (tarjetas)
                if ($(this).closest('td').length > 0) {
                    // Vista de escritorio (tabla)
                    $debtValue = $(this).closest('td').find('.debt-value');
                    customerId = $debtValue.data('customer-id');
                    currentDebt = parseFloat($debtValue.data('original-value'));
                    customerName = $(this).closest('tr').find('.customer-name').text();
                } else {
                    // Vista móvil (tarjetas)
                    $debtValue = $(this).closest('.customer-card').find('.debt-value');
                    customerId = $debtValue.data('customer-id');
                    currentDebt = parseFloat($debtValue.data('original-value'));
                    customerName = $(this).closest('.customer-card').find('.customer-name').text();
                }
                
                // Obtener la fecha actual en formato YYYY-MM-DD usando la fecha del servidor
                const todayString = '{{ date('Y-m-d') }}';
                const currentTime = '{{ date('H:i') }}';
                
                // Llenar el modal con los datos del cliente
                $('#payment_customer_id').val(customerId);
                $('#customer_name').val(customerName);
                $('#current_debt').val(currentDebt.toFixed(2));
                $('#payment_amount').val('').attr('max', currentDebt);
                $('#remaining_debt').val('');
                $('#payment_notes').val('');
                $('#payment_date').val(todayString).attr('max', todayString);
                $('#payment_time').val(currentTime); // Establecer hora actual del servidor
                
                // Mostrar el modal
                $('#debtPaymentModal').modal('show');
            });
            
            // Calcular deuda restante al cambiar el monto del pago
            $('#payment_amount').on('input', function() {
                const currentDebt = parseFloat($('#current_debt').val());
                const paymentAmount = parseFloat($(this).val()) || 0;
                
                if (paymentAmount > currentDebt) {
                    $(this).val(currentDebt);
                    const remainingDebt = 0;
                    $('#remaining_debt').val(remainingDebt.toFixed(2));
                } else {
                    const remainingDebt = currentDebt - paymentAmount;
                    $('#remaining_debt').val(remainingDebt.toFixed(2));
                }
            });
            
            // Manejar el envío del formulario de pago
            $('#debtPaymentForm').submit(function(e) {
                e.preventDefault();
                
                const customerId = $('#payment_customer_id').val();
                const paymentAmount = parseFloat($('#payment_amount').val());
                const paymentDate = $('#payment_date').val();
                const paymentTime = $('#payment_time').val();
                const notes = $('#payment_notes').val();
                
                // Validar que la fecha no sea mayor a hoy usando la fecha del servidor
                const todayString = '{{ date('Y-m-d') }}';
                const selectedDate = new Date(paymentDate);
                const todayDate = new Date(todayString);
                
                if (selectedDate > todayDate) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Fecha inválida',
                        text: 'La fecha del pago no puede ser mayor a hoy',
                        confirmButtonText: 'Aceptar'
                    });
                    return;
                }
                
                // Mostrar indicador de carga
                Swal.fire({
                    title: 'Procesando pago...',
                    html: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Cargando...</span></div>',
                    showConfirmButton: false,
                    allowOutsideClick: false
                });
                
                $.ajax({
                    url: `/admin/customers/${customerId}/register-payment`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        payment_amount: paymentAmount,
                        payment_date: paymentDate,
                        payment_time: paymentTime,
                        notes: notes
                    },
                    success: function(response) {
                        console.log('Respuesta del servidor:', response);
                        
                        // Actualizar la deuda en todas las vistas (tabla y tarjetas)
                        const $debtValues = $(`.debt-value[data-customer-id="${customerId}"]`);
                        $debtValues.each(function() {
                            $(this).data('original-value', response.new_debt);
                            $(this).find('.debt-amount').text(response.formatted_new_debt);
                        });
                        
                        // Actualizar la deuda en Bs para todas las vistas
                        const $bsDebts = $(`.bs-debt[data-debt]`).filter(function() {
                            return $(this).data('debt') !== undefined;
                        });
                        
                        $bsDebts.each(function() {
                            // Verificar si esta deuda pertenece al cliente actual
                            const $relatedDebtValue = $(this).closest('tr, .customer-card').find(`.debt-value[data-customer-id="${customerId}"]`);
                            if ($relatedDebtValue.length > 0) {
                                $(this).data('debt', response.new_debt);
                                
                                // Recalcular el valor en Bs con el tipo de cambio actual
                                const rate = parseFloat($('#exchangeRate').val());
                                const debtBs = response.new_debt * rate;
                                $(this).html('Bs. ' + debtBs.toLocaleString('es-VE', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                            }
                        });
                        
                        // Cerrar el modal
                        $('#debtPaymentModal').modal('hide');
                        
                        // Mostrar mensaje de éxito
                        Swal.fire({
                            icon: 'success',
                            title: 'Pago registrado',
                            text: 'El pago ha sido registrado correctamente',
                            confirmButtonText: 'Aceptar'
                        });
                    },
                    error: function(xhr) {
                        console.error('Error en la solicitud:', xhr);
                        
                        let errorMessage = 'Ha ocurrido un error al registrar el pago';
                        
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            errorMessage = Object.values(xhr.responseJSON.errors)[0][0];
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMessage,
                            confirmButtonText: 'Aceptar'
                        });
                    }
                });
            });
        });
    </script>
@stop
