@extends('adminlte::page')

@section('title', 'Gestión de Clientes')

@section('content_header')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
        <div class="mb-3 mb-md-0">
            <h1 class="text-dark font-weight-bold">Gestión de Clientes</h1>
            <p class="mb-0 d-none d-md-block">Administra y visualiza todos tus clientes en un solo lugar</p>
        </div>
        <div class="btn-group-mobile">
            @can('customers.report')
                <a href="{{ route('admin.customers.report') }}" class="btn btn-info btn-sm" target="_blank">
                    <i class="fas fa-file-pdf mr-1 d-md-inline d-none"></i><span class="d-md-inline d-none">Reporte</span><i class="fas fa-file-pdf d-md-none"></i>
                </a>
            @endcan
            @can('customers.report')
                <a href="#" class="btn btn-danger btn-sm" id="debtReportBtn">
                    <i class="fas fa-file-invoice-dollar mr-1 d-md-inline d-none"></i><span class="d-md-inline d-none">Deudas</span><i class="fas fa-file-invoice-dollar d-md-none"></i>
                </a>
            @endcan
            @can('customers.report')
                <a href="{{ route('admin.customers.payment-history') }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-history mr-1 d-md-inline d-none"></i><span class="d-md-inline d-none">Pagos</span><i class="fas fa-history d-md-none"></i>
                </a>
            @endcan
            @can('customers.create')
                <a href="{{ route('admin.customers.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus-circle mr-1 d-md-inline d-none"></i><span class="d-md-inline d-none">Nuevo</span><i class="fas fa-plus-circle d-md-none"></i>
                </a>
            @endcan
        </div>
    </div>
@stop

@section('content')
    {{-- Widgets de Estadísticas con Animación --}}
    <div class="row">
        <div class="col-lg-3 col-md-6 col-12 mb-3">
            <div class="small-box bg-gradient-info shadow-sm">
                <div class="inner">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="counter mb-1">{{ $totalCustomers }}</h3>
                            <p class="mb-0 small">Total Clientes</p>
                        </div>
                        <div class="d-flex align-items-center">
                            @if ($customerGrowth > 0)
                                <span class="badge badge-success badge-sm">
                                    <i class="fas fa-arrow-up mr-1"></i>
                                    {{ $customerGrowth }}%
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-12 mb-3">
            <div class="small-box bg-gradient-success shadow-sm">
                <div class="inner">
                    <h3 class="mb-1">{{ $activeCustomers }}/{{ $totalCustomers }}</h3>
                    <p class="mb-0 small">Clientes Activos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-check"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-12 mb-3">
            <div class="small-box bg-gradient-warning shadow-sm">
                <div class="inner">
                    <h3 class="mb-1">{{ $newCustomers }}</h3>
                    <p class="mb-0 small">Nuevos este mes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-plus"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-12 mb-3">
            <div class="small-box bg-gradient-purple shadow-sm">
                <div class="inner">
                    <h3 class="mb-1 text-truncate">{{ $currency->symbol }} {{ number_format($totalRevenue, 2) }}</h3>
                    <p class="mb-0 small">Ingresos Totales</p>
                </div>
                <div class="icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Configuración de tipo de cambio --}}
    <div class="card card-outline card-info mb-3 shadow-sm">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-money-bill-wave mr-2"></i>
                <span class="d-none d-md-inline">Configuración de Tipo de Cambio</span>
                <span class="d-md-none">Tipo de Cambio</span>
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
                <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3 mb-md-0">
                    <div class="d-flex align-items-start">
                        <div class="input-group flex-grow-1">
                            <div class="input-group-prepend">
                                <span class="input-group-text">1 USD = </span>
                            </div>
                            <input type="number" id="exchangeRate" class="form-control" value="120.00" step="0.01" min="0" @cannot('customers.edit') readonly @endcannot>
                            <div class="input-group-append">
                                <span class="input-group-text d-none d-sm-inline">VES</span>
                            </div>
                        </div>
                        @can('customers.edit')
                            <button type="button" class="btn btn-primary d-none d-md-inline-block ml-2 update-exchange-rate">
                                <i class="fas fa-sync-alt mr-1"></i>Actualizar
                            </button>
                        @endcan
                    </div>
                    <!-- Botón para móviles debajo del input -->
                    @can('customers.edit')
                        <button type="button" class="btn btn-primary btn-sm mt-2 w-100 d-md-none update-exchange-rate">
                            <i class="fas fa-sync-alt mr-1"></i>Actualizar
                        </button>
                    @endcan
                </div>
                <div class="col-md-6">
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle mr-2"></i>
                        <span class="d-none d-md-inline">El tipo de cambio se utiliza para calcular las deudas en bolívares (VES).</span>
                        <span class="d-md-none">Para calcular deudas en VES.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla de Clientes con Filtros Avanzados --}}
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-user-tie mr-2"></i>
                Lista de Clientes
            </h3>
            <div class="card-tools">
                <div class="d-flex align-items-center">
                    <div class="btn-group-mobile mr-2">
                        <button type="button" class="btn btn-sm btn-outline-primary active filter-btn"
                            data-filter="all">Todos</button>
                        <button type="button" class="btn btn-sm btn-outline-success filter-btn"
                            data-filter="active">Activo</button>
                        <button type="button" class="btn btn-sm btn-outline-danger filter-btn"
                            data-filter="inactive">Inactivo</button>
                    </div>
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            {{-- Vista de tabla para pantallas grandes --}}
            <div class="d-none d-lg-block">
                <table id="customersTable" class="table table-striped table-hover">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th>Cliente</th>
                            <th>Contacto</th>
                            <th>C.I</th>
                            <th>Total en Compras</th>
                            <th>Deuda Total</th>
                            <th>Deuda en Bs</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($customers as $customer)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="customer-avatar mr-2">
                                            <div class="rounded-circle bg-gradient-primary text-white d-flex align-items-center justify-content-center"
                                                style="width: 40px; height: 40px; font-size: 1.2em;">
                                                {{ strtoupper(substr($customer->name, 0, 1)) }}
                                            </div>
                                        </div>
                                        <div>
                                            <strong>{{ $customer->name }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-envelope mr-1"></i>
                                                {{ $customer->email }}
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <i class="fas fa-phone mr-1"></i>
                                    {{ $customer->phone }}
                                </td>
                                <td>
                                    <span class="badge badge-info">
                                        {{ $customer->nit_number }}
                                    </span>
                                </td>
                                <td>
                                    @if ($customer->sales->count() > 0)
                                        <div>
                                            {{ $currency->symbol }}
                                            {{ number_format($customer->sales->sum('total_price'), 2) }}
                                            <br>
                                            <small class="text-muted">
                                                {{ $customer->sales->count() }} venta(s)
                                            </small>
                                        </div>
                                    @else
                                        <span class="text-muted">Sin ventas</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($customer->total_debt > 0)
                                        <div>
                                            <span class="text-danger font-weight-bold debt-value" 
                                                  data-customer-id="{{ $customer->id }}" 
                                                  data-original-value="{{ $customer->total_debt }}">
                                                {{ $currency->symbol }}
                                                <span class="debt-amount">{{ number_format($customer->formatted_total_debt, 2) }}</span>
                                            </span>
                                            @can('customers.edit')
                                                <button class="btn btn-sm btn-outline-primary edit-debt-btn ml-2">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            @endcan
                                            <br>
                                            <small class="text-muted">
                                                Pendiente de pago
                                            </small>
                                        </div>
                                    @else
                                        <div>
                                            <span class="badge badge-success">Sin deuda</span>
                                            @can('customers.edit')
                                                <button class="btn btn-sm btn-outline-primary edit-debt-btn ml-2">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if ($customer->total_debt > 0)
                                        <span class="text-danger font-weight-bold bs-debt" data-debt="{{ $customer->total_debt }}">
                                            Bs. {{ number_format($customer->total_debt, 2) }}
                                        </span>
                                    @else
                                        <span class="badge badge-success">Sin deuda</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($customer->sales->count() > 0)
                                        <span class="badge badge-success">Activo</span>
                                    @else
                                        <span class="badge badge-danger">Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        @can('customers.show')
                                            <button type="button" class="btn btn-info btn-sm show-customer"
                                                data-id="{{ $customer->id }}" data-toggle="tooltip" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        @endcan
                                        @can('customers.edit')
                                            <a href="{{ route('admin.customers.edit', $customer->id) }}"
                                                class="btn btn-warning btn-sm" data-toggle="tooltip" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan
                                        @can('customers.destroy')
                                            <button type="button" class="btn btn-danger btn-sm delete-customer"
                                                data-id="{{ $customer->id }}" data-toggle="tooltip" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endcan
                                        @can('sales.create')
                                            <a href="{{ route('admin.sales.create', ['customer_id' => $customer->id]) }}"
                                                class="btn btn-success btn-sm" data-toggle="tooltip" title="Nueva venta">
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

            {{-- Vista de tarjetas para móviles --}}
            <div class="d-lg-none">
                {{-- Barra de búsqueda para móviles --}}
                <div class="mb-3">
                    <div class="input-group">
                        <input type="text" class="form-control" id="mobileSearch" placeholder="Buscar cliente...">
                        <div class="input-group-append">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="row" id="mobileCustomersContainer">
                    @foreach ($customers as $customer)
                        <div class="col-12 mb-3 customer-card" data-status="{{ $customer->sales->count() > 0 ? 'active' : 'inactive' }}">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between mb-2">
                                        <div class="d-flex align-items-center">
                                            <div class="customer-avatar mr-3">
                                                <div class="rounded-circle bg-gradient-primary text-white d-flex align-items-center justify-content-center"
                                                    style="width: 45px; height: 45px; font-size: 1.3em;">
                                                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                                                </div>
                                            </div>
                                            <div>
                                                <h6 class="mb-1 font-weight-bold">{{ $customer->name }}</h6>
                                                <small class="text-muted">
                                                    <i class="fas fa-envelope mr-1"></i>
                                                    {{ $customer->email }}
                                                </small>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            @if ($customer->sales->count() > 0)
                                                <span class="badge badge-success">Activo</span>
                                            @else
                                                <span class="badge badge-danger">Inactivo</span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <small class="text-muted">Teléfono:</small>
                                            <div class="font-weight-bold">
                                                <i class="fas fa-phone mr-1"></i>
                                                {{ $customer->phone }}
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">C.I:</small>
                                            <div>
                                                <span class="badge badge-info">
                                                    {{ $customer->nit_number }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <small class="text-muted">Total Compras:</small>
                                            <div class="font-weight-bold">
                                                @if ($customer->sales->count() > 0)
                                                    {{ $currency->symbol }}
                                                    {{ number_format($customer->sales->sum('total_price'), 2) }}
                                                    <br>
                                                    <small class="text-muted">
                                                        {{ $customer->sales->count() }} venta(s)
                                                    </small>
                                                @else
                                                    <span class="text-muted">Sin ventas</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Deuda:</small>
                                            <div>
                                                @if ($customer->total_debt > 0)
                                                    <span class="text-danger font-weight-bold debt-value" 
                                                          data-customer-id="{{ $customer->id }}" 
                                                          data-original-value="{{ $customer->total_debt }}">
                                                        {{ $currency->symbol }}
                                                        <span class="debt-amount">{{ number_format($customer->formatted_total_debt, 2) }}</span>
                                                    </span>
                                                    <br>
                                                    <span class="text-danger font-weight-bold bs-debt" data-debt="{{ $customer->total_debt }}">
                                                        Bs. {{ number_format($customer->total_debt, 2) }}
                                                    </span>
                                                @else
                                                    <span class="badge badge-success">Sin deuda</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="btn-group-mobile">
                                        @can('customers.show')
                                            <button type="button" class="btn btn-info btn-sm show-customer"
                                                data-id="{{ $customer->id }}">
                                                <i class="fas fa-eye d-md-none"></i><span class="d-none d-md-inline">Ver</span>
                                            </button>
                                        @endcan
                                        @can('customers.edit')
                                            <a href="{{ route('admin.customers.edit', $customer->id) }}"
                                                class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit d-md-none"></i><span class="d-none d-md-inline">Editar</span>
                                            </a>
                                        @endcan
                                        @if ($customer->total_debt > 0)
                                            @can('customers.register-payment')
                                                <button class="btn btn-outline-primary btn-sm edit-debt-btn">
                                                    <i class="fas fa-dollar-sign d-md-none"></i><span class="d-none d-md-inline">Pagar</span>
                                                </button>
                                            @endcan
                                        @endif
                                        @can('sales.create')
                                            <a href="{{ route('admin.sales.create', ['customer_id' => $customer->id]) }}"
                                                class="btn btn-success btn-sm">
                                                <i class="fas fa-cart-plus d-md-none"></i><span class="d-none d-md-inline">Venta</span>
                                            </a>
                                        @endcan
                                        @can('customers.destroy')
                                            <button type="button" class="btn btn-danger btn-sm delete-customer"
                                                data-id="{{ $customer->id }}">
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
        </div>
    </div>

    {{-- Modal de Detalles del Cliente --}}
    <div class="modal fade" id="showCustomerModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-user-tie mr-2"></i>
                        Detalles del Cliente
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        {{-- Información Personal --}}
                        <div class="col-md-6">
                            <div class="card shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="fas fa-user mr-2"></i>
                                        Información Personal
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <th>Nombre:</th>
                                            <td id="customerName"></td>
                                        </tr>
                                        <tr>
                                            <th>Email:</th>
                                            <td id="customerEmail"></td>
                                        </tr>
                                        <tr>
                                            <th>Teléfono:</th>
                                            <td id="customerPhone"></td>
                                        </tr>
                                        <tr>
                                            <th>Cédula:</th>
                                            <td id="customerNit"></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Historial de Ventas --}}
                        <div class="col-md-6">
                            <div class="card shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="fas fa-shopping-cart mr-2"></i>
                                        Historial de Ventas
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                        <table class="table table-sm table-hover">
                                            <thead class="thead-light sticky-top bg-light">
                                                <tr>
                                                    <th>Fecha</th>
                                                    <th>Productos</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody id="salesHistoryTable">
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted">
                                                        <i class="fas fa-info-circle mr-1"></i>
                                                        No hay ventas registradas
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="mt-2 text-center">
                                        <small class="text-muted">
                                            <span id="salesCount">0</span> ventas mostradas
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>

    <!-- Modal para el reporte de deudas -->
    <div class="modal fade" id="debtReportModal" tabindex="-1" role="dialog" aria-labelledby="debtReportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <!-- El contenido se cargará mediante AJAX -->
                <div class="modal-body text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
                    <p class="mt-2">Cargando reporte...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para registrar pagos de deuda -->
    <div class="modal fade" id="debtPaymentModal" tabindex="-1" role="dialog" aria-labelledby="debtPaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="debtPaymentModalLabel">
                        <i class="fas fa-money-bill-wave mr-2"></i>Registrar Pago de Deuda
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="debtPaymentForm">
                    <div class="modal-body">
                        <input type="hidden" id="payment_customer_id" name="customer_id">
                        
                        <div class="form-group">
                            <label for="customer_name">Cliente:</label>
                            <input type="text" class="form-control" id="customer_name" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="current_debt">Deuda Actual:</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">{{ $currency->symbol }}</span>
                                </div>
                                <input type="text" class="form-control" id="current_debt" readonly>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="payment_amount">Monto del Pago:</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">{{ $currency->symbol }}</span>
                                </div>
                                <input type="number" class="form-control" id="payment_amount" name="payment_amount" step="0.01" min="0.01" required>
                            </div>
                            <small class="form-text text-muted">El monto no puede ser mayor que la deuda actual.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="payment_date">Fecha del Pago:</label>
                            <input type="date" class="form-control" id="payment_date" name="payment_date" required>
                            <small class="form-text text-muted">La fecha no puede ser mayor a hoy.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="payment_time">Hora del Pago:</label>
                            <input type="time" class="form-control" id="payment_time" name="payment_time" required>
                            <small class="form-text text-muted">Hora en que se realizó el pago.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="remaining_debt">Deuda Restante:</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">{{ $currency->symbol }}</span>
                                </div>
                                <input type="text" class="form-control" id="remaining_debt" readonly>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="payment_notes">Notas:</label>
                            <textarea class="form-control" id="payment_notes" name="notes" rows="3" placeholder="Detalles adicionales sobre este pago..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>Registrar Pago
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
        .small-box {
            transition: all .3s ease-in-out;
        }

        .small-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .customer-avatar {
            transition: transform .3s ease-in-out;
        }

        .customer-avatar:hover {
            transform: scale(1.1);
        }

        .table td {
            vertical-align: middle !important;
        }

        .badge {
            padding: 8px 12px;
        }

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

        .filter-btn {
            transition: all .2s ease-in-out;
        }

        .filter-btn:hover {
            transform: translateY(-2px);
        }

        .card {
            transition: all .3s ease-in-out;
        }

        .card:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        /* Estilos responsive adicionales */
        @media (max-width: 768px) {
            .small-box .inner h3 {
                font-size: 1.5rem;
            }
            
            .small-box .inner p {
                font-size: 0.875rem;
            }
        }

        @media (max-width: 576px) {
            .small-box .inner h3 {
                font-size: 1.25rem;
            }
            
            .customer-card .btn {
                font-size: 0.8rem;
                padding: 0.375rem 0.5rem;
            }
        }

        /* Mantener botones en línea en móviles */
        .btn-group-mobile {
            display: flex;
            flex-wrap: wrap;
            gap: 0.25rem;
        }

        .btn-group-mobile .btn {
            flex: 1;
            min-width: auto;
            font-size: 0.875rem;
            padding: 0.375rem 0.5rem;
            white-space: nowrap;
        }

        @media (max-width: 576px) {
            .btn-group-mobile .btn {
                font-size: 0.75rem;
                padding: 0.25rem 0.4rem;
                min-width: 40px;
            }
            
            /* Para los botones de acción en las tarjetas, permitir que se ajusten mejor */
            .customer-card .btn-group-mobile .btn {
                flex: 0 1 auto;
                min-width: 35px;
            }
        }

        /* Estilos específicos para filtros */
        .filter-btn {
            border-radius: 0.25rem !important;
        }

        .filter-btn.active {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Estilos para las tarjetas de móvil */
        .customer-card {
            transition: transform 0.2s ease-in-out;
        }

        .customer-card:hover {
            transform: translateY(-2px);
        }

        .customer-card .card {
            border-left: 4px solid #007bff;
        }

        .customer-card[data-status="inactive"] .card {
            border-left-color: #dc3545;
        }

        .customer-card[data-status="active"] .card {
            border-left-color: #28a745;
        }

        /* Mejoras en la tabla para tablets */
        @media (min-width: 769px) and (max-width: 1199px) {
            .table-responsive {
                font-size: 0.875rem;
            }
            
            .btn-group .btn {
                padding: 0.25rem 0.5rem;
                font-size: 0.8rem;
            }
        }

        /* Ocultar columnas menos importantes en tablets */
        @media (min-width: 992px) and (max-width: 1199px) {
            .table th:nth-child(6),
            .table td:nth-child(6) {
                display: none;
            }
        }

        /* Estilos para el input del tipo de cambio deshabilitado */
        #exchangeRate[readonly] {
            background-color: #e9ecef;
            opacity: 0.6;
            cursor: not-allowed;
        }

        #exchangeRate[readonly]:focus {
            box-shadow: none;
            border-color: #ced4da;
        }

        /* Estilos para la tabla de historial de ventas */
        .sticky-top {
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        /* Personalizar scrollbar para la tabla */
        .table-responsive::-webkit-scrollbar {
            width: 6px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Para Firefox */
        .table-responsive {
            scrollbar-width: thin;
            scrollbar-color: #c1c1c1 #f1f1f1;
        }
    </style>
@stop

@section('js')
    <script>
        // Variable global para almacenar el tipo de cambio actual
        let currentExchangeRate = 1.0;

        $(document).ready(function() {
            // Inicializar DataTable
            const table = $('#customersTable').DataTable({
                responsive: true,
                autoWidth: false,
                stateSave: true, // Guarda la página y el estado del paginador
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json',
                }
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
            
            // Actualizar valores en Bs cuando se cambia el tipo de cambio
            $('.update-exchange-rate').click(function() {
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
                // Mostrar un indicador de carga
                Swal.fire({
                    title: 'Cargando reporte...',
                    html: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Cargando...</span></div>',
                    showConfirmButton: false,
                    allowOutsideClick: false
                });

                // Cargar el reporte mediante AJAX
                $.ajax({
                    url: '{{ route("admin.customers.debt-report") }}',
                    type: 'GET',
                    success: function(response) {
                        // Cerrar el indicador de carga
                        Swal.close();
                        
                        // Crear un modal dinámico
                        if (!$('#debtReportModal').length) {
                            $('body').append('<div class="modal fade" id="debtReportModal" tabindex="-1" role="dialog" aria-labelledby="debtReportModalLabel" aria-hidden="true"><div class="modal-dialog modal-xl"><div class="modal-content"></div></div></div>');
                        }
                        
                        // Llenar el modal con la respuesta
                        $('#debtReportModal .modal-content').html(response);
                        
                        // Mostrar el modal
                        $('#debtReportModal').modal('show');
                        
                        // Pasar el tipo de cambio actual al modal
                        $('#debtReportModal').data('exchangeRate', currentExchangeRate);
                    },
                    error: function() {
                        Swal.fire('Error', 'No se pudo cargar el reporte de deudas', 'error');
                    }
                });
            });

            // Escuchar el evento de modal mostrado
            $(document).on('shown.bs.modal', '#debtReportModal', function() {
                console.log('Modal mostrado, estableciendo tipo de cambio:', currentExchangeRate);
                
                // Establecer el valor del tipo de cambio en el modal
                $('#modalExchangeRate').val(currentExchangeRate);
                
                // Actualizar los valores en Bs en el modal
                updateModalBsValues(currentExchangeRate);
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

            // Filtros de estado
            $('.filter-btn').click(function() {
                $('.filter-btn').removeClass('active');
                $(this).addClass('active');

                const filter = $(this).data('filter');
                
                // Filtrar tabla (vista desktop)
                if (table) {
                    // Limpiar filtros previos
                    table.search('').columns().search('').draw();
                    
                    if (filter === 'active') {
                        // Filtrar clientes activos (los que tienen ventas)
                        table.column(6).search('Activo').draw();
                    } else if (filter === 'inactive') {
                        // Filtrar clientes inactivos (los que no tienen ventas)
                        table.column(6).search('Inactivo').draw();
                    } else {
                        // Mostrar todos
                        table.draw();
                    }
                }
                
                // Filtrar tarjetas móviles
                $('.customer-card').each(function() {
                    const cardStatus = $(this).data('status');
                    
                    if (filter === 'all') {
                        $(this).show();
                    } else if (filter === 'active' && cardStatus === 'active') {
                        $(this).show();
                    } else if (filter === 'inactive' && cardStatus === 'inactive') {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // Tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Búsqueda en vista móvil
            $('#mobileSearch').on('keyup', function() {
                const searchTerm = $(this).val().toLowerCase();
                
                $('.customer-card').each(function() {
                    const customerName = $(this).find('h6').text().toLowerCase();
                    const customerEmail = $(this).find('small').text().toLowerCase();
                    const customerPhone = $(this).find('.fa-phone').parent().text().toLowerCase();
                    
                    if (customerName.includes(searchTerm) || 
                        customerEmail.includes(searchTerm) || 
                        customerPhone.includes(searchTerm)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

                                        // Ver detalles del cliente
                            $('.show-customer').click(function() {
                                const customerId = $(this).data('id');

                                $.ajax({
                                    url: `/customers/${customerId}`,
                                    method: 'GET',
                                    success: function(response) {
                                        if (response.success) {
                                            const customer = response.customer;

                                            // Información personal
                                            $('#customerName').text(customer.name);
                                            $('#customerEmail').text(customer.email);
                                            $('#customerPhone').text(customer.phone);
                                            $('#customerNit').text(customer.nit_number);

                                            // Llenar tabla de historial de ventas
                                            const salesTable = $('#salesHistoryTable');
                                            salesTable.empty();
                                            
                                            if (customer.sales && customer.sales.length > 0) {
                                                customer.sales.forEach(function(sale) {
                                                    const row = `
                                                        <tr>
                                                            <td>${sale.date}</td>
                                                            <td>${sale.total_products}</td>
                                                            <td class="text-success font-weight-bold">
                                                                {{ $currency->symbol }}${parseFloat(sale.total_amount).toLocaleString('es-PE', {
                                                                    minimumFractionDigits: 2,
                                                                    maximumFractionDigits: 2
                                                                })}
                                                            </td>
                                                        </tr>
                                                    `;
                                                    salesTable.append(row);
                                                });
                                                
                                                // Actualizar contador
                                                $('#salesCount').text(customer.sales.length);
                                            } else {
                                                salesTable.html(`
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted">
                                                            <i class="fas fa-info-circle mr-1"></i>
                                                            No hay ventas registradas
                                                        </td>
                                                    </tr>
                                                `);
                                                $('#salesCount').text('0');
                                            }

                                            $('#showCustomerModal').modal('show');
                                        }
                                    },
                                    error: function() {
                                        Swal.fire('Error', 'No se pudieron cargar los detalles del cliente',
                                            'error');
                                    }
                                });
                            });

            // Eliminar cliente
            $('.delete-customer').click(function() {
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
                                    Swal.fire('Error', response.message, response.icon);
                                }
                            },
                            error: function() {
                                Swal.fire('Error', 'No se pudo eliminar el cliente',
                                    'error');
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
            $('.edit-debt-btn').click(function() {
                let $debtValue, customerId, currentDebt, customerName;
                
                // Verificar si estamos en la vista de escritorio (tabla) o móvil (tarjetas)
                if ($(this).closest('td').length > 0) {
                    // Vista de escritorio (tabla)
                    $debtValue = $(this).closest('td').find('.debt-value');
                    customerId = $debtValue.data('customer-id');
                    currentDebt = parseFloat($debtValue.data('original-value'));
                    customerName = $(this).closest('tr').find('td:first-child strong').text();
                } else {
                    // Vista móvil (tarjetas)
                    $debtValue = $(this).closest('.customer-card').find('.debt-value');
                    customerId = $debtValue.data('customer-id');
                    currentDebt = parseFloat($debtValue.data('original-value'));
                    customerName = $(this).closest('.customer-card').find('h6').text();
                }
                
                // Obtener la fecha actual en formato YYYY-MM-DD
                const today = new Date();
                const todayString = today.toISOString().split('T')[0];
                
                // Llenar el modal con los datos del cliente
                $('#payment_customer_id').val(customerId);
                $('#customer_name').val(customerName);
                $('#current_debt').val(currentDebt.toFixed(2));
                $('#payment_amount').val('').attr('max', currentDebt);
                $('#remaining_debt').val('');
                $('#payment_notes').val('');
                $('#payment_date').val(todayString).attr('max', todayString);
                $('#payment_time').val(today.toTimeString().slice(0, 5)); // Establecer hora actual
                
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
                
                // Validar que la fecha no sea mayor a hoy
                const today = new Date();
                const selectedDate = new Date(paymentDate);
                
                if (selectedDate > today) {
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
