@extends('adminlte::page')

@section('title', 'Gestión de Caja')

@section('content_header')
    <div class="hero-section mb-4">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-lg-8 col-md-7 col-12">
                    <div class="hero-content">
                        <h1 class="hero-title">
                            <i class="fas fa-cash-register-gradient"></i>
                            Gestión de Caja
                        </h1>
                        <p class="hero-subtitle">Control y administración del flujo de efectivo de la empresa</p>
                        <div class="hero-stats">
                            @if ($currentCashCount)
                                <div class="hero-stat-item">
                                    <i class="fas fa-clock"></i>
                                    <span>Caja abierta desde: {{ \Carbon\Carbon::parse($currentCashCount->opening_date)->format('d/m/Y H:i') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-5 col-12">
                    <div class="hero-action-buttons d-flex justify-content-lg-end justify-content-center align-items-center gap-3 flex-wrap">
                        @if ($currentCashCount)
                            @can('cash-counts.store-movement')
                                <button type="button" class="hero-btn hero-btn-success" data-toggle="modal" data-target="#newMovementModal">
                                    <i class="fas fa-money-bill-wave"></i>
                                    <span class="d-none d-md-inline">Nuevo Movimiento</span>
                                </button>
                            @endcan
                            @can('cash-counts.close')
                                <button type="button" class="hero-btn hero-btn-danger" data-toggle="modal" data-target="#closeCashModal">
                                    <i class="fas fa-cash-register"></i>
                                    <span class="d-none d-md-inline">Cerrar Caja</span>
                                </button>
                            @endcan
                        @else
                            @can('cash-counts.report')
                                <a href="{{ route('admin.cash-counts.report') }}" class="hero-btn hero-btn-info" target="_blank">
                                    <i class="fas fa-file-pdf"></i>
                                    <span class="d-none d-md-inline">Reporte</span>
                                </a>
                            @endcan
                            @can('cash-counts.create')
                                <button type="button" class="hero-btn hero-btn-primary" data-toggle="modal" data-target="#openCashModal">
                                    <i class="fas fa-cash-register"></i>
                                    <span class="d-none d-md-inline">Abrir Caja</span>
                                </button>
                            @endcan
                        @endif
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
    .hero-btn-primary { color: #007bff; }
    .hero-btn-primary i { color: #007bff; }
    .hero-btn-success { color: #28a745; }
    .hero-btn-success i { color: #28a745; }
    .hero-btn-danger { color: #dc3545; }
    .hero-btn-danger i { color: #dc3545; }
    .hero-btn-info { color: #17a2b8; }
    .hero-btn-info i { color: #17a2b8; }
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
    {{-- Estado Actual de Caja --}}
    @if ($currentCashCount)
        <div class="status-card mb-4">
            <div class="status-content">
                <div class="status-icon">
                    <i class="fas fa-cash-register"></i>
                </div>
                <div class="status-info">
                    <h5 class="status-title">Caja Actual</h5>
                    <p class="status-text">
                        Abierta desde: {{ \Carbon\Carbon::parse($currentCashCount->opening_date)->format('d/m/Y H:i') }}
                        | Monto Inicial: {{ $currency->symbol }} {{ number_format($currentCashCount->initial_amount, 2) }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- Estadísticas con diseño de tarjetas modernas --}}
    <div class="stats-grid mb-4">
        <div class="stat-card stat-card-info">
            <div class="stat-icon">
                <i class="fas fa-wallet"></i>
            </div>
            <div class="stat-content">
                <div class="stat-header">
                    <h3 class="stat-number">{{ $currency->symbol }} {{ number_format($currentBalance, 2) }}</h3>
                </div>
                <p class="stat-label">Balance Actual</p>
                <div class="stat-progress">
                    <div class="progress-bar" style="width: 100%"></div>
                </div>
            </div>
        </div>
        
        <div class="stat-card stat-card-success">
            <div class="stat-icon">
                <i class="fas fa-arrow-up"></i>
            </div>
            <div class="stat-content">
                <div class="stat-header">
                    <h3 class="stat-number">{{ $currency->symbol }} {{ number_format($todayIncome, 2) }}</h3>
                </div>
                <p class="stat-label">Ingresos del Día</p>
                <div class="stat-progress">
                    <div class="progress-bar" style="width: 100%"></div>
                </div>
            </div>
        </div>
        
        <div class="stat-card stat-card-danger">
            <div class="stat-icon">
                <i class="fas fa-arrow-down"></i>
            </div>
            <div class="stat-content">
                <div class="stat-header">
                    <h3 class="stat-number">{{ $currency->symbol }} {{ number_format($todayExpenses, 2) }}</h3>
                </div>
                <p class="stat-label">Egresos del Día</p>
                <div class="stat-progress">
                    <div class="progress-bar" style="width: 100%"></div>
                </div>
            </div>
        </div>
        
        <div class="stat-card stat-card-warning">
            <div class="stat-icon">
                <i class="fas fa-exchange-alt"></i>
            </div>
            <div class="stat-content">
                <div class="stat-header">
                    <h3 class="stat-number">{{ $totalMovements }}</h3>
                </div>
                <p class="stat-label">Movimientos del Día</p>
                <div class="stat-progress">
                    <div class="progress-bar" style="width: 100%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Historial de Arqueos con diseño moderno --}}
    <div class="cash-counts-container">
        <div class="table-container">
            <table id="cashCountsTable" class="cash-counts-table">
                <thead>
                    <tr>
                        <th class="th-id">ID</th>
                        <th class="th-date">Fecha Apertura</th>
                        <th class="th-close">Fecha Cierre</th>
                        <th class="th-initial">Monto Inicial</th>
                        <th class="th-final">Monto Final</th>
                        <th class="th-difference">Diferencia</th>
                        <th class="th-status">Estado</th>
                        <th class="th-actions">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cashCounts as $cashCount)
                        <tr class="cash-count-row">
                            <td class="td-id">
                                <span class="id-badge">{{ str_pad($cashCount->id, 4, '0', STR_PAD_LEFT) }}</span>
                            </td>
                            <td class="td-date">
                                <div class="date-info">
                                    <div class="date-main">{{ \Carbon\Carbon::parse($cashCount->opening_date)->format('d/m/Y') }}</div>
                                    <div class="date-time">{{ \Carbon\Carbon::parse($cashCount->opening_date)->format('H:i') }}</div>
                                </div>
                            </td>
                            <td class="td-close">
                                @if ($cashCount->closing_date)
                                    <div class="date-info">
                                        <div class="date-main">{{ \Carbon\Carbon::parse($cashCount->closing_date)->format('d/m/Y') }}</div>
                                        <div class="date-time">{{ \Carbon\Carbon::parse($cashCount->closing_date)->format('H:i') }}</div>
                                    </div>
                                @else
                                    <span class="status-badge status-pending">En curso</span>
                                @endif
                            </td>
                            <td class="td-initial">
                                <span class="amount-badge initial-badge">{{ $currency->symbol }} {{ number_format($cashCount->initial_amount, 2) }}</span>
                            </td>
                            <td class="td-final">
                                @if ($cashCount->final_amount)
                                    <span class="amount-badge final-badge">{{ $currency->symbol }} {{ number_format($cashCount->final_amount, 2) }}</span>
                                @else
                                    <span class="status-badge status-pending">Pendiente</span>
                                @endif
                            </td>
                            <td class="td-difference">
                                @if ($cashCount->final_amount)
                                    @php
                                        $difference = $cashCount->final_amount - $cashCount->initial_amount;
                                        $badgeClass = $difference >= 0 ? 'success' : 'danger';
                                    @endphp
                                    <span class="difference-badge difference-{{ $badgeClass }}">
                                        {{ $currency->symbol }} {{ number_format(abs($difference), 2) }}
                                        <i class="fas fa-{{ $difference >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                    </span>
                                @else
                                    <span class="status-badge status-pending">Pendiente</span>
                                @endif
                            </td>
                            <td class="td-status">
                                @if ($cashCount->closing_date)
                                    <span class="status-badge status-closed">Cerrado</span>
                                @else
                                    <span class="status-badge status-open">Abierto</span>
                                @endif
                            </td>
                            <td class="td-actions">
                                <div class="action-buttons">
                                    @can('cash-counts.show')
                                        <button class="action-btn action-btn-info view-movements" 
                                                data-id="{{ $cashCount->id }}"
                                                data-toggle="tooltip" title="Ver movimientos">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="action-btn action-btn-secondary view-history" 
                                                data-id="{{ $cashCount->id }}"
                                                data-toggle="tooltip" title="Historial Completo">
                                            <i class="fas fa-history"></i>
                                        </button>
                                    @endcan
                                    @if (!$cashCount->closing_date)
                                        @can('cash-counts.edit')
                                            <a href="{{ route('admin.cash-counts.edit', $cashCount->id) }}"
                                               class="action-btn action-btn-warning"
                                               data-toggle="tooltip" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan
                                    @endif
                                    @can('cash-counts.destroy')
                                        <button class="action-btn action-btn-delete delete-cash-count" 
                                                data-id="{{ $cashCount->id }}"
                                                data-toggle="tooltip" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="empty-state">
                                    <i class="fas fa-cash-register fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No hay arqueos de caja registrados</h5>
                                    <p class="text-muted">Comienza creando tu primer arqueo de caja</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($cashCounts->hasPages())
        <div class="pagination-container">
            <div class="pagination-info">
                Mostrando {{ $cashCounts->firstItem() ?? 0 }} a {{ $cashCounts->lastItem() ?? 0 }} de {{ $cashCounts->total() }} registros
            </div>
            <div class="pagination-wrapper">
                {{ $cashCounts->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
        </div>
        @endif
    </div>

    {{-- Gráficos con diseño moderno --}}
    <div class="charts-grid mt-4">
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title">
                    <i class="fas fa-chart-line"></i>
                    Movimientos de Caja
                </h3>
            </div>
            <div class="chart-body">
                <canvas id="cashMovementsChart" height="250"></canvas>
            </div>
        </div>
        
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title">
                    <i class="fas fa-chart-pie"></i>
                    Distribución de Movimientos
                </h3>
            </div>
            <div class="chart-body">
                <canvas id="movementsDistributionChart" height="250"></canvas>
            </div>
        </div>
    </div>

    {{-- Modal para Abrir Caja --}}
    <div class="modal fade" id="openCashModal" tabindex="-1" role="dialog" aria-labelledby="openCashModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="openCashModalLabel">
                        <i class="fas fa-cash-register mr-2"></i>
                        Abrir Nueva Caja
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="openCashForm" action="{{ route('admin.cash-counts.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="initial_amount">Monto Inicial <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">{{ $currency->symbol }}</span>
                                </div>
                                <input type="number" step="0.01" class="form-control" id="initial_amount"
                                    name="initial_amount" value="0.00" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="observations">Observaciones</label>
                            <textarea class="form-control" id="observations" name="observations" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i>Abrir Caja
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal para Nuevo Movimiento --}}
    <div class="modal fade" id="newMovementModal" tabindex="-1" role="dialog" aria-labelledby="newMovementModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white" id="newMovementModalLabel">
                        <i class="fas fa-money-bill-wave mr-2"></i>
                        Nuevo Movimiento de Caja
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="newMovementForm" action="{{ route('admin.cash-counts.store-movement') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="type">Tipo de Movimiento</label>
                            <select class="form-control" id="type" name="type" required>
                                <option value="income">Ingreso</option>
                                <option value="expense">Egreso</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="amount">Monto</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">{{ $currency->symbol }}</span>
                                </div>
                                <input type="number" step="0.01" class="form-control" id="amount" name="amount"
                                    required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="description">Descripción</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save mr-2"></i>Guardar Movimiento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal para Cerrar Caja --}}
    <div class="modal fade" id="closeCashModal" tabindex="-1" role="dialog" aria-labelledby="closeCashModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white" id="closeCashModalLabel">
                        <i class="fas fa-cash-register mr-2"></i>
                        Cerrar Caja
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-money-bill-wave"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Monto Inicial</span>
                                    <span class="info-box-number">
                                        {{ $currency->symbol }}
                                        {{ number_format($currentCashCount->initial_amount ?? 0, 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Nueva tarjeta para Total de Ingresos --}}
                        <div class="col-md-6">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-arrow-up"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Ingresos</span>
                                    <span class="info-box-number">
                                        {{ $currency->symbol }}
                                        {{ number_format($todayIncome, 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Tarjeta para Productos Comprados --}}
                        <div class="col-md-6">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-primary">
                                    <i class="fas fa-shopping-cart"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Productos Comprados</span>
                                    <span class="info-box-number">
                                        {{ $totalPurchasedProducts }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Tarjeta para Total Egresos --}}
                        <div class="col-md-6">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-danger">
                                    <i class="fas fa-arrow-down"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Egresos</span>
                                    <span class="info-box-number">
                                        {{ $currency->symbol }}
                                        {{ number_format($todayExpenses, 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Nueva tarjeta para Total de Productos --}}
                        <div class="col-md-6">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-warning">
                                    <i class="fas fa-box"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Productos Vendidos</span>
                                    <span class="info-box-number">
                                        {{ $totalProducts }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Tarjeta para Balance Actual --}}
                        <div class="col-md-6">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-cash-register"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Balance Actual</span>
                                    <span class="info-box-number">
                                        {{ $currency->symbol }} {{ number_format($currentBalance, 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form id="closeCashForm" action="{{ route('admin.cash-counts.close', $currentCashCount->id ?? 0) }}"
                        method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="final_amount">Monto Final en Caja</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">{{ $currency->symbol }}</span>
                                </div>
                                <input type="number" step="0.01" class="form-control" id="final_amount"
                                    name="final_amount" value="{{ $currentBalance }}" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="closing_observations">Observaciones del Cierre</label>
                            <textarea class="form-control" id="closing_observations" name="observations" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" form="closeCashForm" class="btn btn-danger">
                        <i class="fas fa-lock mr-2"></i>Cerrar Caja
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de Detalles de Caja --}}
    <div class="modal fade" id="movementsModal" tabindex="-1" role="dialog" aria-labelledby="movementsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white" id="movementsModalLabel">
                        <i class="fas fa-chart-line mr-2"></i>
                        Estadísticas de Caja
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        {{-- Información General --}}
                        <div class="col-md-12 mb-4">
                            <h5 class="border-bottom pb-2">
                                <i class="fas fa-info-circle mr-2"></i>Resumen General
                            </h5>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="info-box bg-light">
                                        <span class="info-box-icon bg-primary">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Monto Inicial</span>
                                            <span class="info-box-number" id="initialAmount">$0</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-box bg-light">
                                        <span class="info-box-icon bg-success">
                                            <i class="fas fa-plus"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total Ingresos</span>
                                            <span class="info-box-number" id="totalIncome">$0</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-box bg-light">
                                        <span class="info-box-icon bg-danger">
                                            <i class="fas fa-minus"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total Egresos</span>
                                            <span class="info-box-number" id="totalExpense">$0</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-box bg-light">
                                        <span class="info-box-icon bg-warning">
                                            <i class="fas fa-balance-scale"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Diferencia Neta</span>
                                            <span class="info-box-number" id="netDifference">$0</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Detalles de Movimientos --}}
                        <div class="col-md-12 mb-4">
                            <div class="card">
                                <div class="card-header bg-primary">
                                    <h3 class="card-title text-white">
                                        <i class="fas fa-exchange-alt mr-2"></i>Detalle de Movimientos
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Tipo</th>
                                                    <th>Descripción</th>
                                                    <th class="text-right">Monto</th>
                                                </tr>
                                            </thead>
                                            <tbody id="movementsDetail">
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Resto del modal existente (ventas y compras) --}}
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-success">
                                    <h3 class="card-title text-white">
                                        <i class="fas fa-shopping-cart mr-2"></i>Ventas
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span>Total Ventas:</span>
                                        <span id="totalSales" class="badge badge-success">0</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span>Monto Total:</span>
                                        <span id="totalSalesAmount" class="badge badge-success">$0</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>Productos Vendidos:</span>
                                        <span id="productsSold" class="badge badge-success">0</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-primary">
                                    <h3 class="card-title text-white">
                                        <i class="fas fa-truck mr-2"></i>Compras
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span>Total Compras:</span>
                                        <span id="totalPurchases" class="badge badge-primary">0</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span>Monto Total:</span>
                                        <span id="totalPurchasesAmount" class="badge badge-primary">$0</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>Productos Comprados:</span>
                                        <span id="productsPurchased" class="badge badge-primary">0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal para Abrir Caja --}}
    <div class="modal fade" id="openCashModal" tabindex="-1" role="dialog" aria-labelledby="openCashModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content modern-modal">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-cash-register"></i>
                        Abrir Nueva Caja
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="openCashForm" action="{{ route('admin.cash-counts.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="initial_amount">Monto Inicial <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">{{ $currency->symbol }}</span>
                                </div>
                                <input type="number" step="0.01" class="form-control" id="initial_amount" name="initial_amount" value="0.00" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="observations">Observaciones</label>
                            <textarea class="form-control" id="observations" name="observations" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Abrir Caja
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal para Nuevo Movimiento --}}
    <div class="modal fade" id="newMovementModal" tabindex="-1" role="dialog" aria-labelledby="newMovementModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content modern-modal">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-money-bill-wave"></i>
                        Nuevo Movimiento de Caja
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="newMovementForm" action="{{ route('admin.cash-counts.store-movement') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="type">Tipo de Movimiento</label>
                            <select class="form-control" id="type" name="type" required>
                                <option value="income">Ingreso</option>
                                <option value="expense">Egreso</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="amount">Monto</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">{{ $currency->symbol }}</span>
                                </div>
                                <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="description">Descripción</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i>
                            Guardar Movimiento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal para Cerrar Caja --}}
    <div class="modal fade" id="closeCashModal" tabindex="-1" role="dialog" aria-labelledby="closeCashModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modern-modal">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-cash-register"></i>
                        Cerrar Caja
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="stats-grid-modal">
                        <div class="stat-card-modal">
                            <div class="stat-icon-modal">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="stat-content-modal">
                                <h4 class="stat-number-modal">{{ $currency->symbol }} {{ number_format($currentCashCount->initial_amount ?? 0, 2) }}</h4>
                                <p class="stat-label-modal">Monto Inicial</p>
                            </div>
                        </div>
                        
                        <div class="stat-card-modal">
                            <div class="stat-icon-modal">
                                <i class="fas fa-arrow-up"></i>
                            </div>
                            <div class="stat-content-modal">
                                <h4 class="stat-number-modal">{{ $currency->symbol }} {{ number_format($todayIncome, 2) }}</h4>
                                <p class="stat-label-modal">Total Ingresos</p>
                            </div>
                        </div>
                        
                        <div class="stat-card-modal">
                            <div class="stat-icon-modal">
                                <i class="fas fa-arrow-down"></i>
                            </div>
                            <div class="stat-content-modal">
                                <h4 class="stat-number-modal">{{ $currency->symbol }} {{ number_format($todayExpenses, 2) }}</h4>
                                <p class="stat-label-modal">Total Egresos</p>
                            </div>
                        </div>
                        
                        <div class="stat-card-modal">
                            <div class="stat-icon-modal">
                                <i class="fas fa-cash-register"></i>
                            </div>
                            <div class="stat-content-modal">
                                <h4 class="stat-number-modal">{{ $currency->symbol }} {{ number_format($currentBalance, 2) }}</h4>
                                <p class="stat-label-modal">Balance Actual</p>
                            </div>
                        </div>
                    </div>

                    <form id="closeCashForm" action="{{ route('admin.cash-counts.close', $currentCashCount->id ?? 0) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="final_amount">Monto Final en Caja</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">{{ $currency->symbol }}</span>
                                </div>
                                <input type="number" step="0.01" class="form-control" id="final_amount" name="final_amount" value="{{ $currentBalance }}" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="closing_observations">Observaciones del Cierre</label>
                            <textarea class="form-control" id="closing_observations" name="observations" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" form="closeCashForm" class="btn btn-danger">
                        <i class="fas fa-lock"></i>
                        Cerrar Caja
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de Detalles de Caja --}}
    <div class="modal fade" id="movementsModal" tabindex="-1" role="dialog" aria-labelledby="movementsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content modern-modal">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-chart-line"></i>
                        Estadísticas de Caja
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="stats-summary">
                        <div class="stat-summary-item">
                            <div class="stat-summary-icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="stat-summary-content">
                                <h4 class="stat-summary-number" id="initialAmount">$0</h4>
                                <p class="stat-summary-label">Monto Inicial</p>
                            </div>
                        </div>
                        
                        <div class="stat-summary-item">
                            <div class="stat-summary-icon">
                                <i class="fas fa-plus"></i>
                            </div>
                            <div class="stat-summary-content">
                                <h4 class="stat-summary-number" id="totalIncome">$0</h4>
                                <p class="stat-summary-label">Total Ingresos</p>
                            </div>
                        </div>
                        
                        <div class="stat-summary-item">
                            <div class="stat-summary-icon">
                                <i class="fas fa-minus"></i>
                            </div>
                            <div class="stat-summary-content">
                                <h4 class="stat-summary-number" id="totalExpense">$0</h4>
                                <p class="stat-summary-label">Total Egresos</p>
                            </div>
                        </div>
                        
                        <div class="stat-summary-item">
                            <div class="stat-summary-icon">
                                <i class="fas fa-balance-scale"></i>
                            </div>
                            <div class="stat-summary-content">
                                <h4 class="stat-summary-number" id="netDifference">$0</h4>
                                <p class="stat-summary-label">Diferencia Neta</p>
                            </div>
                        </div>
                    </div>

                    <div class="movements-detail">
                        <h5 class="section-title">
                            <i class="fas fa-exchange-alt"></i>
                            Detalle de Movimientos
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Descripción</th>
                                        <th class="text-right">Monto</th>
                                    </tr>
                                </thead>
                                <tbody id="movementsDetail">
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="sales-purchases-summary">
                        <div class="summary-card">
                            <div class="summary-header">
                                <h5 class="summary-title">
                                    <i class="fas fa-shopping-cart"></i>
                                    Ventas
                                </h5>
                            </div>
                            <div class="summary-content">
                                <div class="summary-item">
                                    <span>Total Ventas:</span>
                                    <span id="totalSales" class="summary-value">0</span>
                                </div>
                                <div class="summary-item">
                                    <span>Monto Total:</span>
                                    <span id="totalSalesAmount" class="summary-value">$0</span>
                                </div>
                                <div class="summary-item">
                                    <span>Productos Vendidos:</span>
                                    <span id="productsSold" class="summary-value">0</span>
                                </div>
                            </div>
                        </div>

                        <div class="summary-card">
                            <div class="summary-header">
                                <h5 class="summary-title">
                                    <i class="fas fa-truck"></i>
                                    Compras
                                </h5>
                            </div>
                            <div class="summary-content">
                                <div class="summary-item">
                                    <span>Total Compras:</span>
                                    <span id="totalPurchases" class="summary-value">0</span>
                                </div>
                                <div class="summary-item">
                                    <span>Monto Total:</span>
                                    <span id="totalPurchasesAmount" class="summary-value">$0</span>
                                </div>
                                <div class="summary-item">
                                    <span>Productos Comprados:</span>
                                    <span id="productsPurchased" class="summary-value">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de Historial Completo --}}
    <div class="modal fade" id="historyModal" tabindex="-1" role="dialog" aria-labelledby="historyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content modern-modal">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-history"></i>
                        Historial Completo de Caja
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Hero Section -->
                    <div class="hero-section">
                        <div class="hero-content">
                            <h2 class="hero-title">
                                <i class="fas fa-cash-register"></i>
                                Arqueo de Caja #<span id="historyCashCountId">0000</span>
                            </h2>
                            <p class="hero-subtitle">
                                Período: <span id="historyPeriod">01/01/2025 - 31/01/2025</span>
                            </p>
                            <div class="hero-stats">
                                <div class="hero-stat">
                                    <span class="hero-stat-label">Monto Inicial:</span>
                                    <span class="hero-stat-value" id="historyInitialAmount">$0.00</span>
                                </div>
                                <div class="hero-stat">
                                    <span class="hero-stat-label">Monto Final:</span>
                                    <span class="hero-stat-value" id="historyFinalAmount">$0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Estadísticas Principales -->
                    <div class="stats-grid-modal">
                        <div class="stat-card-modal">
                            <div class="stat-icon-modal">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="stat-number-modal" id="historyTotalSales">0</div>
                            <div class="stat-label-modal">Ventas</div>
                            <div class="stat-amount-modal" id="historyTotalSalesAmount">$0.00</div>
                        </div>
                        
                        <div class="stat-card-modal">
                            <div class="stat-icon-modal">
                                <i class="fas fa-truck"></i>
                            </div>
                            <div class="stat-number-modal" id="historyTotalPurchases">0</div>
                            <div class="stat-label-modal">Compras</div>
                            <div class="stat-amount-modal" id="historyTotalPurchasesAmount">$0.00</div>
                        </div>
                        
                        <div class="stat-card-modal">
                            <div class="stat-icon-modal">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="stat-number-modal" id="historyRealProfit">$0.00</div>
                            <div class="stat-label-modal">Ganancias Reales</div>
                        </div>
                        
                        <div class="stat-card-modal">
                            <div class="stat-icon-modal">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="stat-number-modal" id="historyPendingDebts">$0.00</div>
                            <div class="stat-label-modal">Deudas Pendientes</div>
                        </div>
                        
                        <div class="stat-card-modal">
                            <div class="stat-icon-modal">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="stat-number-modal" id="historyPaymentsReceived">$0.00</div>
                            <div class="stat-label-modal">Pagos Recibidos</div>
                        </div>
                    </div>

                    <!-- Deudas Pendientes -->
                    <div class="section-container">
                        <h5 class="section-title">
                            <i class="fas fa-exclamation-triangle text-warning"></i>
                            Deudas Pendientes en este Arqueo
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Teléfono</th>
                                        <th>Fecha Venta</th>
                                        <th class="text-right">Monto Deuda</th>
                                        <th class="text-right">Productos</th>
                                    </tr>
                                </thead>
                                <tbody id="historyPendingDebtsTable">
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No hay deudas pendientes</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Deudas de Arqueos Anteriores -->
                    <div class="section-container">
                        <h5 class="section-title">
                            <i class="fas fa-history text-info"></i>
                            Deudas de Arqueos Anteriores
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Teléfono</th>
                                        <th>Fecha Venta</th>
                                        <th class="text-right">Monto Deuda</th>
                                        <th class="text-right">Días Pendiente</th>
                                    </tr>
                                </thead>
                                <tbody id="historyPreviousDebtsTable">
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No hay deudas de arqueos anteriores</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagos de Deudas Anteriores -->
                    <div class="section-container">
                        <h5 class="section-title">
                            <i class="fas fa-check-circle text-success"></i>
                            Pagos de Deudas Anteriores en este Arqueo
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Descripción</th>
                                        <th class="text-right">Monto</th>
                                    </tr>
                                </thead>
                                <tbody id="historyPreviousPaymentsTable">
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No hay pagos de deudas anteriores</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Resumen Final -->
                    <div class="summary-section">
                        <div class="summary-card">
                            <div class="summary-header">
                                <h5 class="summary-title">
                                    <i class="fas fa-calculator"></i>
                                    Resumen General
                                </h5>
                            </div>
                            <div class="summary-content">
                                <div class="summary-item">
                                    <span>Balance Final:</span>
                                    <span id="historyFinalBalance" class="summary-value">$0.00</span>
                                </div>
                                <div class="summary-item">
                                    <span>Diferencia Neta:</span>
                                    <span id="historyNetDifference" class="summary-value">$0.00</span>
                                </div>
                                <div class="summary-item">
                                    <span>Estado del Arqueo:</span>
                                    <span id="historyStatus" class="summary-value">Cerrado</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/datatables/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/datatables/responsive.bootstrap4.min.css') }}">
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
            --warning-gradient: linear-gradient(135deg, #43e97b 0%, #38d9a9 100%);
            --danger-color: #fa709a;
            --danger-gradient: linear-gradient(135deg, #fa709a 0%, #f5576c 100%);
                    --info-color: #667eea;
        --info-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --dark-color: #2c3e50;
            --light-color: #ecf0f1;
            --border-radius: 12px;
            --border-radius-sm: 8px;
            --shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 12px 40px rgba(0, 0, 0, 0.15);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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

        .hero-stat {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }

        .hero-stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .hero-stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(45deg, #fff, #f0f0f0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* ===== SECTION CONTAINER ===== */
        .section-container {
            margin-bottom: 2rem;
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
        }

        .section-title {
            color: var(--dark-color);
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.2rem;
        }

        .section-title i {
            font-size: 1.3rem;
        }

        /* ===== SUMMARY SECTION ===== */
        .summary-section {
            margin-top: 2rem;
        }

        .stat-amount-modal {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-top: 0.5rem;
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

        .hero-stat-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            opacity: 0.8;
        }

        /* ===== STATUS CARD ===== */
        .status-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 1.5rem;
            border-left: 4px solid var(--info-color);
        }

        .status-content {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .status-icon {
            width: 50px;
            height: 50px;
            background: var(--info-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        .status-title {
            margin: 0;
            font-weight: 600;
            color: var(--dark-color);
        }

        .status-text {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
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

        .stat-card-info::before { background: var(--info-gradient); }
        .stat-card-success::before { background: var(--success-gradient); }
        .stat-card-danger::before { background: var(--danger-gradient); }
        .stat-card-warning::before { background: var(--warning-gradient); }

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

        .stat-card-info .stat-icon { background: var(--info-gradient); }
        .stat-card-success .stat-icon { background: var(--success-gradient); }
        .stat-card-danger .stat-icon { background: var(--danger-gradient); }
        .stat-card-warning .stat-icon { background: var(--warning-gradient); }

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

        /* ===== CASH COUNTS CONTAINER ===== */
        .cash-counts-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .table-container {
            overflow-x: auto;
        }

        .cash-counts-table {
            width: 100%;
            border-collapse: collapse;
        }

        .cash-counts-table th {
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

        .cash-counts-table td {
            padding: 1rem;
            border-bottom: 1px solid #f8f9fa;
            vertical-align: middle;
        }

        .cash-counts-table tr:hover {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .cash-count-row {
            transition: var(--transition);
        }

        .cash-count-row:hover {
            transform: scale(1.01);
            box-shadow: var(--shadow);
        }

        /* ===== TABLE CELLS ===== */
        .id-badge {
            background: var(--primary-gradient);
            color: white;
            padding: 0.5rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
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
            color: #666;
            font-size: 0.8rem;
        }

        .amount-badge {
            padding: 0.5rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .initial-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .final-badge {
            background: var(--success-gradient);
            color: white;
        }

        .difference-badge {
            padding: 0.5rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .difference-success {
            background: var(--success-gradient);
            color: white;
        }

        .difference-danger {
            background: var(--danger-gradient);
            color: white;
        }

        .status-badge {
            padding: 0.5rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-open {
            background: var(--warning-gradient);
            color: white;
        }

        .status-closed {
            background: var(--success-gradient);
            color: white;
        }

        .status-pending {
            background: #6c757d;
            color: white;
        }

        /* ===== ACTION BUTTONS ===== */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .action-btn {
            width: 36px;
            height: 36px;
            border: none;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            color: white;
            font-size: 0.9rem;
        }

        .action-btn-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .action-btn-warning {
            background: var(--warning-gradient);
        }

        .action-btn-delete {
            background: var(--danger-gradient);
        }

        .action-btn-secondary {
            background: var(--secondary-gradient);
        }

        .action-btn:hover {
            transform: scale(1.1);
            box-shadow: var(--shadow-hover);
        }

        /* ===== CHARTS GRID ===== */
        .charts-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
        }

        .chart-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .chart-header {
            padding: 1.5rem;
            border-bottom: 1px solid #f8f9fa;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .chart-title {
            margin: 0;
            font-weight: 600;
            color: var(--dark-color);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .chart-body {
            padding: 1.5rem;
        }

        /* ===== MODALS ===== */
        .modern-modal {
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-hover);
            border: none;
        }

        .modern-modal .modal-header {
            background: var(--primary-gradient);
            color: white;
            border-bottom: none;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
        }

        .modern-modal .modal-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 0;
        }

        .modern-modal .close {
            color: white;
            opacity: 0.8;
            transition: var(--transition);
        }

        .modern-modal .close:hover {
            opacity: 1;
            transform: scale(1.1);
        }

        /* ===== MODAL STATS ===== */
        .stats-grid-modal {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card-modal {
            background: #f8f9fa;
            border-radius: var(--border-radius-sm);
            padding: 1rem;
            text-align: center;
            transition: var(--transition);
        }

        .stat-card-modal:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow);
        }

        .stat-icon-modal {
            width: 50px;
            height: 50px;
            background: var(--primary-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            margin: 0 auto 0.5rem;
        }

        .stat-number-modal {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-color);
            margin: 0 0 0.25rem;
        }

        .stat-label-modal {
            color: #666;
            font-size: 0.9rem;
            margin: 0;
        }

        /* ===== STATS SUMMARY ===== */
        .stats-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-summary-item {
            background: #f8f9fa;
            border-radius: var(--border-radius-sm);
            padding: 1.5rem;
            text-align: center;
            transition: var(--transition);
        }

        .stat-summary-item:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow);
        }

        .stat-summary-icon {
            width: 60px;
            height: 60px;
            background: var(--primary-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin: 0 auto 1rem;
        }

        .stat-summary-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-color);
            margin: 0 0 0.5rem;
        }

        .stat-summary-label {
            color: #666;
            font-size: 0.9rem;
            margin: 0;
        }

        /* ===== SECTIONS ===== */
        .section-title {
            color: var(--dark-color);
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .movements-detail {
            margin-bottom: 2rem;
        }

        .sales-purchases-summary {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .summary-card {
            background: #f8f9fa;
            border-radius: var(--border-radius-sm);
            overflow: hidden;
        }

        .summary-header {
            background: var(--primary-gradient);
            color: white;
            padding: 1rem;
        }

        .summary-title {
            margin: 0;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .summary-content {
            padding: 1rem;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e9ecef;
        }

        .summary-item:last-child {
            border-bottom: none;
        }

        .summary-value {
            font-weight: 600;
            color: var(--primary-color);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .hero-section {
                padding: 1.5rem;
            }

            .hero-title {
                font-size: 2rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .charts-grid {
                grid-template-columns: 1fr;
            }

            .sales-purchases-summary {
                grid-template-columns: 1fr;
            }

            .stats-summary {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .hero-stats {
                flex-direction: column;
                gap: 1rem;
            }

            .stats-summary {
                grid-template-columns: 1fr;
            }

            .stats-grid-modal {
                grid-template-columns: 1fr;
            }
        }

        /* ===== DATATABLES CUSTOM STYLING ===== */
        .dataTables_wrapper {
            padding: 0;
        }

        /* ===== LENGTH MENU (Show entries) ===== */
        .dataTables_length {
            margin-bottom: 1.5rem;
        }

        .dataTables_length label {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 600;
            color: var(--dark-color);
            margin: 0;
        }

        .dataTables_length select {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius-sm);
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--dark-color);
            cursor: pointer;
            transition: var(--transition);
            min-width: 80px;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
        }

        .dataTables_length select:hover {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .dataTables_length select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
        }

        /* ===== SEARCH BOX ===== */
        .dataTables_filter {
            margin-bottom: 1.5rem;
        }

        .dataTables_filter label {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 600;
            color: var(--dark-color);
            margin: 0;
        }

        .dataTables_filter input {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius-sm);
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
            color: var(--dark-color);
            transition: var(--transition);
            min-width: 250px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .dataTables_filter input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2), 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .dataTables_filter input::placeholder {
            color: #9ca3af;
        }

        /* ===== PAGINATION ===== */
        .dataTables_paginate {
            margin-top: 1.5rem;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
        }

        .paginate_button {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius-sm);
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--dark-color);
            cursor: pointer;
            transition: var(--transition);
            min-width: 44px;
            text-align: center;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .paginate_button:hover {
            background: var(--primary-gradient);
            border-color: transparent;
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
            text-decoration: none;
        }

        .paginate_button.current {
            background: var(--primary-gradient);
            border-color: transparent;
            color: white;
            box-shadow: var(--shadow);
        }

        .paginate_button.current:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }

        .paginate_button.disabled {
            background: #f8f9fa;
            border-color: #e9ecef;
            color: #9ca3af;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .paginate_button.disabled:hover {
            background: #f8f9fa;
            border-color: #e9ecef;
            color: #9ca3af;
            transform: none;
            box-shadow: none;
        }

        /* ===== INFO ===== */
        .dataTables_info {
            text-align: center;
            margin-top: 1rem;
            font-size: 0.9rem;
            color: #6b7280;
            font-weight: 500;
        }

        /* ===== PROCESSING ===== */
        .dataTables_processing {
            background: rgba(255, 255, 255, 0.95);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 2rem;
            text-align: center;
            font-weight: 600;
            color: var(--dark-color);
        }

        /* ===== EMPTY STATE ===== */
        .dataTables_empty {
            text-align: center;
            padding: 3rem 1rem;
            color: #6b7280;
            font-style: italic;
        }

        /* ===== RESPONSIVE DATATABLES ===== */
        @media (max-width: 768px) {
            .dataTables_length,
            .dataTables_filter {
                text-align: center;
                margin-bottom: 1rem;
            }

            .dataTables_length label,
            .dataTables_filter label {
                flex-direction: column;
                gap: 0.5rem;
            }

            .dataTables_filter input {
                min-width: 200px;
            }

            .dataTables_paginate {
                flex-wrap: wrap;
                gap: 0.25rem;
            }

            .paginate_button {
                padding: 0.5rem 0.75rem;
                font-size: 0.8rem;
                min-width: 36px;
            }
        }

        @media (max-width: 576px) {
            .dataTables_filter input {
                min-width: 150px;
            }

            .dataTables_paginate {
                gap: 0.25rem;
            }

            .paginate_button {
                padding: 0.5rem;
                font-size: 0.75rem;
                min-width: 32px;
            }
        }

        /* ===== CUSTOM ANIMATIONS FOR DATATABLES ===== */
        .dataTables_wrapper .dataTables_processing {
            animation: fadeInUp 0.6s ease-out;
        }

        .paginate_button:not(.disabled):not(.current) {
            animation: fadeInUp 0.6s ease-out;
        }

        .paginate_button.current {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        /* ===== ENHANCED FOCUS STATES ===== */
        .dataTables_length select:focus,
        .dataTables_filter input:focus {
            transform: translateY(-1px);
        }

        /* ===== LOADING STATE ===== */
        .dataTables_processing::before {
            content: '';
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid var(--primary-color);
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s linear infinite;
            margin-right: 0.5rem;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* ===== PAGINACIÓN MODERNA (ESTILO PAYMENT-HISTORY) ===== */
        .pagination-container {
            padding: 1.5rem 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
            gap: 1rem;
        }

        .pagination-info {
            text-align: center;
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .pagination-wrapper {
            display: flex;
            justify-content: center;
            width: 100%;
        }

        .pagination {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            flex-wrap: wrap;
            justify-content: center;
        }

        .page-item {
            margin: 0;
        }

        .page-link {
            background: white;
            border: 2px solid #e9ecef;
            color: var(--dark-color);
            padding: 0.75rem 1rem;
            border-radius: var(--border-radius-sm);
            text-decoration: none;
            transition: var(--transition);
            font-weight: 500;
            min-width: 44px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .page-link:hover {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .page-item.active .page-link {
            background: var(--primary-gradient);
            border-color: transparent;
            color: white;
            box-shadow: var(--shadow);
        }

        .page-item.disabled .page-link {
            background: #f8f9fa;
            border-color: #e9ecef;
            color: #6c757d;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .page-item.disabled .page-link:hover {
            background: #f8f9fa;
            border-color: #e9ecef;
            color: #6c757d;
            transform: none;
            box-shadow: none;
        }

        /* Estilos específicos para los botones de navegación */
        .page-item:first-child .page-link,
        .page-item:last-child .page-link {
            font-weight: 600;
        }

        /* Responsive para paginación */
        @media (max-width: 576px) {
            .pagination-container {
                padding: 1rem;
                gap: 0.75rem;
            }

            .pagination-info {
                font-size: 0.8rem;
                text-align: center;
                line-height: 1.4;
            }

            .pagination {
                gap: 0.3rem;
                flex-wrap: wrap;
            }

            .page-link {
                padding: 0.6rem 0.8rem;
                font-size: 0.9rem;
                min-width: 40px;
            }

            /* Ocultar algunos números de página en móviles para ahorrar espacio */
            .pagination .page-item:not(.active):not(:first-child):not(:last-child):not(.disabled) {
                display: none;
            }

            .pagination .page-item.active,
            .pagination .page-item:first-child,
            .pagination .page-item:last-child,
            .pagination .page-item.disabled {
                display: block;
            }
        }

        /* ===== OCULTAR CONTROLES DE DATATABLES ===== */
        .dataTables_wrapper .dataTables_paginate,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            display: none !important;
        }

        /* Asegurar que la paginación de Laravel sea visible */
        .pagination {
            display: flex !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        /* ===== MEJORAS ADICIONALES PARA LA PAGINACIÓN ===== */
        .pagination-container {
            margin-top: 0;
        }

        /* Estilos para los números de página */
        .pagination .page-item:not(.active):not(.disabled) .page-link {
            background: white;
            color: var(--dark-color);
            border: 2px solid #e9ecef;
        }

        .pagination .page-item:not(.active):not(.disabled) .page-link:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        /* Estilos para el botón activo */
        .pagination .page-item.active .page-link {
            background: var(--primary-gradient);
            border-color: transparent;
            color: white;
            box-shadow: var(--shadow);
            font-weight: 600;
        }

        /* Estilos para botones deshabilitados */
        .pagination .page-item.disabled .page-link {
            background: #f8f9fa;
            border-color: #e9ecef;
            color: #6c757d;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .pagination .page-item.disabled .page-link:hover {
            background: #f8f9fa;
            border-color: #e9ecef;
            color: #6c757d;
            transform: none;
            box-shadow: none;
        }

        /* Animaciones para la paginación */
        .pagination .page-link {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Efecto de hover mejorado */
        .pagination .page-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        /* Estilos específicos para botones de navegación */
        .pagination .page-item:first-child .page-link,
        .pagination .page-item:last-child .page-link {
            font-weight: 600;
            min-width: 50px;
        }

        /* ===== ESTADO VACÍO ===== */
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
        }

        .empty-state i {
            color: #d1d5db;
            margin-bottom: 1rem;
        }

        .empty-state h5 {
            color: #6b7280;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: #9ca3af;
            font-size: 0.9rem;
        }

        /* Responsive mejorado */
        @media (max-width: 768px) {
            .pagination-container {
                padding: 1rem;
            }
            
            .pagination-info {
                font-size: 0.85rem;
            }
        }
    </style>
@stop

@section('js')
    <script src="{{ asset('vendor/config.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Cargar todas las librerías necesarias
            loadDataTables(function() {

                // Inicializar DataTable con configuración moderna
                $('#cashCountsTable').DataTable({
                    responsive: true,
                    autoWidth: false,
                    paging: false, // Deshabilitar paginación de DataTables
                    info: false,   // Deshabilitar información de DataTables
                    searching: false, // Deshabilitar búsqueda de DataTables
                    ordering: true,
                    order: [[0, 'desc']],
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
                    dom: 'rt', // Solo tabla y procesamiento
                    initComplete: function() {
                        // Agregar animaciones a las filas
                        $('.cash-count-row').each(function(index) {
                            $(this).css({
                                'animation-delay': (index * 0.1) + 's',
                                'animation': 'fadeInUp 0.6s ease-out forwards'
                            });
                        });
                        
                        // Mostrar la paginación de Laravel
                        $('.pagination-container').show();
                    }
                });

                // Inicializar tooltips
                $('[data-toggle="tooltip"]').tooltip();

                // Cargar Chart.js y crear gráfico
                loadChartJS(function() {
                    // Gráfico de Movimientos con diseño moderno
                    const ctx = document.getElementById('cashMovementsChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: {!! json_encode($chartData['labels']) !!},
                            datasets: [{
                                label: 'Ingresos',
                                data: {!! json_encode($chartData['income']) !!},
                                borderColor: '#4facfe',
                                backgroundColor: 'rgba(79, 172, 254, 0.1)',
                                borderWidth: 3,
                                tension: 0.4,
                                fill: true,
                                pointBackgroundColor: '#4facfe',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: 6,
                                pointHoverRadius: 8
                            }, {
                                label: 'Egresos',
                                data: {!! json_encode($chartData['expenses']) !!},
                                borderColor: '#fa709a',
                                backgroundColor: 'rgba(250, 112, 154, 0.1)',
                                borderWidth: 3,
                                tension: 0.4,
                                fill: true,
                                pointBackgroundColor: '#fa709a',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: 6,
                                pointHoverRadius: 8
                            }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                font: {
                                    size: 12,
                                    weight: '600'
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 11
                                }
                            }
                        },
                        y: {
                            grid: {
                                color: 'rgba(0,0,0,0.05)'
                            },
                            ticks: {
                                font: {
                                    size: 11
                                }
                            }
                        }
                    }
                }
            });

            // Gráfico de Distribución con diseño moderno
            const ctxPie = document.getElementById('movementsDistributionChart').getContext('2d');
            new Chart(ctxPie, {
                type: 'doughnut',
                data: {
                    labels: ['Ingresos', 'Egresos'],
                    datasets: [{
                        data: [
                            {{ $todayIncome }},
                            {{ $todayExpenses }}
                        ],
                        backgroundColor: ['#4facfe', '#fa709a'],
                        borderWidth: 0,
                        hoverBorderWidth: 3,
                        hoverBorderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                font: {
                                    size: 12,
                                    weight: '600'
                                }
                            }
                        }
                    },
                    cutout: '60%'
                }
            });

            // Manejo de eliminación con SweetAlert2 moderno
            $('.delete-cash-count').click(function() {
                const id = $(this).data('id');

                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "Esta acción no se puede revertir",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#667eea',
                    cancelButtonColor: '#fa709a',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    customClass: {
                        popup: 'modern-swal-popup',
                        confirmButton: 'modern-swal-confirm',
                        cancelButton: 'modern-swal-cancel'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Mostrar loader
                        Swal.fire({
                            title: 'Eliminando...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: `/cash-counts/delete/${id}`,
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
                                        confirmButtonColor: '#667eea'
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                } else {
                                    Swal.fire('Error', response.message, 'error');
                                }
                            },
                            error: function() {
                                Swal.fire('Error', 'No se pudo eliminar el arqueo', 'error');
                            }
                        });
                    }
                });
            });

            // Ver historial completo
            $('.view-history').click(function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const id = $(this).data('id');
                
                
                // Mostrar loader
                Swal.fire({
                    title: 'Cargando historial...',
                    text: 'Preparando datos del arqueo de caja',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                    customClass: {
                        popup: 'modern-swal-popup'
                    }
                });

                // Obtener datos del historial
                $.get(`/cash-counts/${id}/history`, function(response) {
                    if (response.success) {
                        const data = response.data;
                        const currency = response.currency;
                        
                        // Función para formatear números
                        function number_format(number, decimals) {
                            return parseFloat(number).toFixed(decimals).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                        }
                        
                        // Actualizar hero section
                        $('#historyCashCountId').text(data.cashCount.id.toString().padStart(4, '0'));
                        $('#historyPeriod').text(data.stats.opening_date + ' - ' + data.stats.closing_date);
                        $('#historyInitialAmount').text(currency.symbol + number_format(data.stats.initial_amount, 2));
                        $('#historyFinalAmount').text(currency.symbol + number_format(data.stats.final_amount, 2));
                        
                        // Actualizar estadísticas principales
                        $('#historyTotalSales').text(data.stats.total_sales);
                        $('#historyTotalSalesAmount').text(currency.symbol + number_format(data.stats.total_sales_amount, 2));
                        $('#historyTotalPurchases').text(data.stats.total_purchases);
                        $('#historyTotalPurchasesAmount').text(currency.symbol + number_format(data.stats.total_purchases_amount, 2));
                        $('#historyRealProfit').text(currency.symbol + number_format(data.stats.real_profit, 2));
                        $('#historyPendingDebts').text(currency.symbol + number_format(data.stats.debts_generated, 2));
                        $('#historyPaymentsReceived').text(currency.symbol + number_format(data.stats.payments_received, 2));
                        
                        // Actualizar tablas
                        updateHistoryTable('#historyPendingDebtsTable', data.pendingDebts, 'pending', currency);
                        updateHistoryTable('#historyPreviousDebtsTable', data.previousDebts, 'previous', currency);
                        updateHistoryTable('#historyPreviousPaymentsTable', data.previousDebtPayments, 'payments', currency);
                        
                        // Actualizar resumen final
                        $('#historyFinalBalance').text(currency.symbol + number_format(data.stats.final_amount, 2));
                        $('#historyNetDifference').text(currency.symbol + number_format(data.stats.net_difference, 2));
                        $('#historyStatus').text(data.cashCount.closing_date ? 'Cerrado' : 'Abierto');
                        
                        // Cerrar loader y mostrar modal
                        Swal.close();
                        $('#historyModal').modal('show');
                    } else {
                        Swal.fire('Error', response.message || 'No se pudieron cargar los datos del historial', 'error');
                    }
                }).fail(function() {
                    Swal.fire('Error', 'No se pudieron cargar los datos del historial', 'error');
                });
            });

            // Función para actualizar tablas del historial
            function updateHistoryTable(tableId, data, type, currency) {
                const table = $(tableId);
                table.empty();
                
                if (data && data.length > 0) {
                    data.forEach(function(item) {
                        let row = '';
                        
                        if (type === 'pending') {
                            row = `
                                <tr>
                                    <td>${item.customer_name}</td>
                                    <td>${item.customer_phone || 'No registrado'}</td>
                                    <td>${formatDate(item.sale_date)}</td>
                                    <td class="text-right font-weight-bold">${currency.symbol}${number_format(item.total_amount, 2)}</td>
                                    <td class="text-right">${item.total_products}</td>
                                </tr>
                            `;
                        } else if (type === 'previous') {
                            row = `
                                <tr>
                                    <td>${item.customer_name}</td>
                                    <td>${item.customer_phone || 'No registrado'}</td>
                                    <td>${formatDate(item.sale_date)}</td>
                                    <td class="text-right font-weight-bold">${currency.symbol}${number_format(item.total_amount, 2)}</td>
                                    <td class="text-right">${item.days_pending}</td>
                                </tr>
                            `;
                        } else if (type === 'payments') {
                            row = `
                                <tr>
                                    <td>${formatDate(item.date)}</td>
                                    <td>${item.description}</td>
                                    <td class="text-right font-weight-bold text-success">${currency.symbol}${number_format(item.amount, 2)}</td>
                                </tr>
                            `;
                        }
                        
                        table.append(row);
                    });
                } else {
                    let message = '';
                    if (type === 'pending') {
                        message = 'No hay deudas pendientes';
                    } else if (type === 'previous') {
                        message = 'No hay deudas de arqueos anteriores';
                    } else if (type === 'payments') {
                        message = 'No hay pagos de deudas anteriores';
                    }
                    
                    table.append(`
                        <tr>
                            <td colspan="5" class="text-center text-muted">${message}</td>
                        </tr>
                    `);
                }
            }

            // Función para formatear fechas
            function formatDate(dateString) {
                const date = new Date(dateString);
                return date.toLocaleDateString('es-ES', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                });
            }

            // Ver movimientos con diseño moderno
            $('.view-movements').click(function() {
                const id = $(this).data('id');

                // Mostrar loader moderno
                Swal.fire({
                    title: 'Cargando estadísticas...',
                    text: 'Preparando datos para visualización',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                    customClass: {
                        popup: 'modern-swal-popup'
                    }
                });

                // Obtener datos
                $.get(`/cash-counts/${id}`, function(response) {
                    if (response.success) {
                        const stats = response.stats;
                        const currency = response.currency;

                        // Actualizar información general con animaciones
                        animateNumber('#initialAmount', currency.symbol + number_format(stats.initial_amount || 0, 2));
                        animateNumber('#totalIncome', currency.symbol + number_format(stats.total_income, 2));
                        animateNumber('#totalExpense', currency.symbol + number_format(stats.total_expense, 2));
                        animateNumber('#netDifference', currency.symbol + number_format(stats.net_difference, 2));

                        // Limpiar y actualizar tabla de movimientos
                        const movementsTable = $('#movementsDetail');
                        movementsTable.empty();

                        // Agregar cada movimiento a la tabla con animación
                        stats.movements.forEach((movement, index) => {
                            const rowClass = movement.type === 'income' ? 'text-success' : 'text-danger';
                            const typeText = movement.type === 'income' ? 'Ingreso' : 'Egreso';
                            const icon = movement.type === 'income' ? 'fas fa-arrow-up' : 'fas fa-arrow-down';

                            const row = $(`
                                <tr style="animation-delay: ${index * 0.1}s">
                                    <td class="${rowClass}">
                                        <i class="${icon} mr-2"></i>${typeText}
                                    </td>
                                    <td>${movement.description || 'Sin descripción'}</td>
                                    <td class="text-right ${rowClass} font-weight-bold">
                                        ${currency.symbol}${number_format(movement.amount, 2)}
                                    </td>
                                </tr>
                            `);
                            
                            movementsTable.append(row);
                            row.css('animation', 'fadeInLeft 0.6s ease-out forwards');
                        });

                        // Actualizar resto de estadísticas
                        $('#totalSales').text(stats.total_sales);
                        $('#totalSalesAmount').text(currency.symbol + number_format(stats.total_sales_amount, 2));
                        $('#productsSold').text(stats.products_sold);
                        $('#totalPurchases').text(stats.total_purchases);
                        $('#totalPurchasesAmount').text(currency.symbol + number_format(stats.total_purchases_amount, 2));
                        $('#productsPurchased').text(stats.products_purchased);

                        // Cerrar loader y mostrar modal
                        Swal.close();
                        $('#movementsModal').modal('show');
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                }).fail(function() {
                    Swal.fire('Error', 'No se pudieron cargar las estadísticas', 'error');
                });
            });

                // Función para animar números
                function animateNumber(selector, finalValue) {
                    const element = $(selector);
                    const startValue = 0;
                    const duration = 1000;
                    const startTime = performance.now();

                    function updateNumber(currentTime) {
                        const elapsed = currentTime - startTime;
                        const progress = Math.min(elapsed / duration, 1);
                        
                        // Easing function
                        const easeOutQuart = 1 - Math.pow(1 - progress, 4);
                        
                        const currentValue = startValue + (parseFloat(finalValue.replace(/[^\d.-]/g, '')) - startValue) * easeOutQuart;
                        const formattedValue = finalValue.replace(/[\d.]+/, currentValue.toFixed(2));
                        
                        element.text(formattedValue);
                        
                        if (progress < 1) {
                            requestAnimationFrame(updateNumber);
                        }
                    }
                    
                    requestAnimationFrame(updateNumber);
                }

                // Función para formatear números
                function number_format(number, decimals) {
                    return new Intl.NumberFormat('es-ES', {
                        minimumFractionDigits: decimals,
                        maximumFractionDigits: decimals
                    }).format(number);
                }

                // Animaciones CSS adicionales
                $('<style>')
                    .prop('type', 'text/css')
                    .html(`
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
                        
                        @keyframes fadeInLeft {
                            from {
                                opacity: 0;
                                transform: translateX(-30px);
                            }
                            to {
                                opacity: 1;
                                transform: translateX(0);
                            }
                        }
                        
                        .modern-swal-popup {
                            border-radius: 12px !important;
                            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15) !important;
                        }
                        
                        .modern-swal-confirm {
                            border-radius: 8px !important;
                            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
                            border: none !important;
                            font-weight: 600 !important;
                        }
                        
                        .modern-swal-cancel {
                            border-radius: 8px !important;
                            background: linear-gradient(135deg, #fa709a 0%, #f5576c 100%) !important;
                            border: none !important;
                            font-weight: 600 !important;
                        }
                    `)
                    .appendTo('head');
            });
            
        });
    </script>
@stop
