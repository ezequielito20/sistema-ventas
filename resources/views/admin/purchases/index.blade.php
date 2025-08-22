@extends('layouts.app')

@section('title', 'Gestión de Compras')

@section('content')
    <!-- Background Pattern -->
    <div class="page-background"></div>

    <!-- Main Container -->
    <div class="main-container" id="purchasesRoot" data-currency-symbol="{{ $currency->symbol }}">
        <!-- Floating Header -->
        <div class="floating-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon-wrapper">
                        <div class="header-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="icon-glow"></div>
                    </div>
                    <div class="header-text">
                        <h1 class="header-title">Gestión de Compras</h1>
                        <p class="header-subtitle">Administra y controla todas las compras del sistema</p>
                    </div>
                </div>
                <div class="header-actions">
                    @if ($permissions['can_report'])
                        <a href="{{ route('admin.purchases.report') }}" class="btn-glass btn-secondary-glass" target="_blank"
                            title="Generar reporte PDF">
                            <i class="fas fa-file-pdf"></i>
                            <span>Reporte</span>
                            <div class="btn-ripple"></div>
                        </a>
                    @endif
                    @if ($cashCount)
                        @if ($permissions['can_create'])
                            <a href="{{ route('admin.purchases.create') }}" class="btn-glass btn-primary-glass"
                                title="Crear nueva compra">
                                <i class="fas fa-plus-circle"></i>
                                <span>Nueva Compra</span>
                                <div class="btn-ripple"></div>
                            </a>
                        @endif
                    @else
                        <a href="{{ route('admin.cash-counts.create') }}" class="btn-glass btn-danger-glass"
                            title="Abrir caja para realizar compras">
                            <i class="fas fa-cash-register"></i>
                            <span>Abrir Caja</span>
                            <div class="btn-ripple"></div>
                        </a>
                    @endif
                </div>
            </div>
        </div>
        <!-- Stats Dashboard -->
        <div class="stats-dashboard" role="region" aria-label="Estadísticas de compras">
            <div class="stats-grid">
                <div class="stat-card stat-primary" role="article" aria-label="Productos únicos comprados">
                    <div class="stat-icon" aria-hidden="true">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div class="stat-glow" aria-hidden="true"></div>
                    <div class="stat-content">
                        <div class="stat-value" aria-label="Total de productos únicos">{{ $totalPurchases }}</div>
                        <div class="stat-label">Productos Únicos</div>
                        <div class="stat-trend">
                            <i class="fas fa-arrow-up" aria-hidden="true"></i>
                            <span>Comprados en total</span>
                        </div>
                    </div>
                </div>

                <div class="stat-card stat-success" role="article" aria-label="Total invertido en compras">
                    <div class="stat-icon" aria-hidden="true">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-glow" aria-hidden="true"></div>
                    <div class="stat-content">
                        <div class="stat-value" aria-label="Total invertido">{{ $currency->symbol }}
                            {{ number_format($totalAmount, 2) }}</div>
                        <div class="stat-label">Total Invertido</div>
                        <div class="stat-trend">
                            <i class="fas fa-dollar-sign" aria-hidden="true"></i>
                            <span>Capital comprometido</span>
                        </div>
                    </div>
                </div>

                <div class="stat-card stat-warning" role="article" aria-label="Compras del mes actual">
                    <div class="stat-icon" aria-hidden="true">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-glow" aria-hidden="true"></div>
                    <div class="stat-content">
                        <div class="stat-value" aria-label="Compras del mes">{{ $monthlyPurchases }}</div>
                        <div class="stat-label">Compras del Mes</div>
                        <div class="stat-trend">
                            <i class="fas fa-arrow-up" aria-hidden="true"></i>
                            <span>Actividad reciente</span>
                        </div>
                    </div>
                </div>

                <div class="stat-card stat-info" role="article" aria-label="Entregas pendientes">
                    <div class="stat-icon" aria-hidden="true">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div class="stat-glow" aria-hidden="true"></div>
                    <div class="stat-content">
                        <div class="stat-value" aria-label="Entregas pendientes">{{ $pendingDeliveries }}</div>
                        <div class="stat-label">Pendientes</div>
                        <div class="stat-trend">
                            <i class="fas fa-clock" aria-hidden="true"></i>
                            <span>Por entregar</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Container -->
        <div class="data-container">
            <div class="data-header">
                <div class="header-left">
                    <div class="header-icon-wrapper">
                        <div class="header-icon">
                            <i class="fas fa-list-alt"></i>
                        </div>
                    </div>
                    <div class="header-text">
                        <h3>Lista de Compras</h3>
                        <p>Gestiona todas las transacciones de compra</p>
                    </div>
                </div>
                <div class="header-controls">
                    <div class="search-box">
                        <i class="fas fa-search" aria-hidden="true"></i>
                        <input type="text" id="purchasesSearch" placeholder="Buscar compra por recibo o fecha..."
                            aria-label="Buscar compras" autocomplete="off">
                        <button type="button" id="clearSearch" class="clear-btn" aria-label="Limpiar búsqueda">
                            <i class="fas fa-times" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div class="view-toggle" role="group" aria-label="Cambiar vista">
                        <button type="button" class="view-btn active" data-view="table" aria-label="Vista de tabla">
                            <i class="fas fa-table" aria-hidden="true"></i>
                        </button>
                        <button type="button" class="view-btn" data-view="cards" aria-label="Vista de tarjetas">
                            <i class="fas fa-th-large" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="data-content">
                <!-- Table View -->
                <div class="table-view" id="tableView">
                    <div class="table-wrapper">
                        <table id="purchasesTable" class="modern-table" role="table" aria-label="Lista de compras">
                            <thead>
                                <tr>
                                    <th scope="col" role="columnheader">#</th>
                                    <th scope="col" role="columnheader">Recibo de Pago</th>
                                    <th scope="col" role="columnheader">Fecha</th>
                                    <th scope="col" role="columnheader">Productos</th>
                                    <th scope="col" role="columnheader">Monto Total</th>
                                    <th scope="col" role="columnheader">Estado</th>
                                    <th scope="col" role="columnheader">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($purchases as $purchase)
                                    <tr>
                                        <td>
                                            <div class="number-badge">{{ $loop->iteration }}</div>
                                        </td>
                                        <td>
                                            <div class="purchase-info">
                                                <div class="info-icon">
                                                    <i class="fas fa-receipt"></i>
                                                </div>
                                                <div class="info-text">
                                                    <strong>{{ $purchase->payment_receipt ?: 'Sin recibo' }}</strong>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="purchase-info">
                                                <div class="info-icon">
                                                    <i class="fas fa-calendar-alt"></i>
                                                </div>
                                                <div class="info-text">
                                                    <div class="date-main">
                                                        {{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d/m/Y') }}
                                                    </div>
                                                    <div class="time-sub">
                                                        {{ \Carbon\Carbon::parse($purchase->purchase_date)->format('H:i') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="products-info">
                                                <div class="product-badge">
                                                    <i class="fas fa-boxes"></i>
                                                    <span>{{ $purchase->details->count() }} únicos</span>
                                                </div>
                                                <div class="product-badge">
                                                    <i class="fas fa-cubes"></i>
                                                    <span>{{ $purchase->details->sum('quantity') }} totales</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="purchase-info">
                                                <div class="info-icon">
                                                    <i class="fas fa-dollar-sign"></i>
                                                </div>
                                                <div class="info-text">
                                                    {{ $currency->symbol }} {{ number_format($purchase->total_price, 2) }}
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($purchase->payment_receipt)
                                                <div class="status-badge completed">
                                                    <i class="fas fa-check-circle"></i>
                                                    <span>Completado</span>
                                                </div>
                                            @else
                                                <div class="status-badge pending">
                                                    <i class="fas fa-clock"></i>
                                                    <span>Pendiente</span>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="actions-group" role="group"
                                                aria-label="Acciones para compra {{ $loop->iteration }}">
                                                @if ($permissions['can_show'])
                                                    <button type="button" class="action-btn details-btn view-details"
                                                        data-id="{{ $purchase->id }}" title="Ver Detalles"
                                                        aria-label="Ver detalles de la compra {{ $purchase->payment_receipt ?: 'sin recibo' }}">
                                                        <i class="fas fa-list" aria-hidden="true"></i>
                                                    </button>
                                                    <button type="button" class="action-btn supplier-btn"
                                                        onclick="showSupplierInfo({{ $purchase->supplier_id ?? 1 }})"
                                                        title="Ver Proveedor" aria-label="Ver información del proveedor">
                                                        <i class="fas fa-truck" aria-hidden="true"></i>
                                                    </button>
                                                @endif
                                                @if ($permissions['can_edit'])
                                                    <a href="{{ route('admin.purchases.edit', $purchase->id) }}"
                                                        class="action-btn edit-btn" title="Editar Compra"
                                                        aria-label="Editar compra {{ $purchase->payment_receipt ?: 'sin recibo' }}">
                                                        <i class="fas fa-edit" aria-hidden="true"></i>
                                                    </a>
                                                @endif
                                                @if ($permissions['can_destroy'])
                                                    <button type="button" class="action-btn delete-btn delete-purchase"
                                                        data-id="{{ $purchase->id }}" title="Eliminar Compra"
                                                        aria-label="Eliminar compra {{ $purchase->payment_receipt ?: 'sin recibo' }}">
                                                        <i class="fas fa-trash" aria-hidden="true"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Cards View -->
                <div class="cards-view" id="cardsView" style="display: none;">
                    <div class="cards-grid">
                        @foreach ($purchases as $purchase)
                            <div class="purchase-card-modern">
                                <!-- Header with status and number -->
                                <div class="card-header-modern">
                                    <div class="purchase-number">
                                        <div class="number-circle">
                                            <span>{{ $loop->iteration }}</span>
                                        </div>
                                    </div>
                                    <div class="purchase-status">
                                        @if ($purchase->payment_receipt)
                                            <div class="status-indicator completed">
                                                <div class="status-dot"></div>
                                                <span>Completado</span>
                                            </div>
                                        @else
                                            <div class="status-indicator pending">
                                                <div class="status-dot"></div>
                                                <span>Pendiente</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Main content -->
                                <div class="card-body-modern">
                                    <!-- Receipt and date info -->
                                    <div class="purchase-header-info">
                                        <div class="receipt-info">
                                            <div class="receipt-icon">
                                                <i class="fas fa-receipt"></i>
                                            </div>
                                            <div class="receipt-details">
                                                <h3 class="receipt-number">
                                                    {{ $purchase->payment_receipt ?: 'Sin recibo' }}</h3>
                                                <div class="purchase-date">
                                                    <i class="fas fa-calendar-alt"></i>
                                                    <span>{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d/m/Y H:i') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Statistics grid -->
                                    <div class="purchase-stats-grid">
                                        <div class="stat-box products">
                                            <div class="stat-icon-wrapper">
                                                <i class="fas fa-boxes"></i>
                                            </div>
                                            <div class="stat-info">
                                                <div class="stat-number">{{ $purchase->details->count() }}</div>
                                                <div class="stat-text">Productos<br>Únicos</div>
                                            </div>
                                        </div>

                                        <div class="stat-box units">
                                            <div class="stat-icon-wrapper">
                                                <i class="fas fa-cubes"></i>
                                            </div>
                                            <div class="stat-info">
                                                <div class="stat-number">{{ $purchase->details->sum('quantity') }}</div>
                                                <div class="stat-text">Total<br>Unidades</div>
                                            </div>
                                        </div>

                                        <div class="stat-box amount">
                                            <div class="stat-icon-wrapper">
                                                <i class="fas fa-dollar-sign"></i>
                                            </div>
                                            <div class="stat-info">
                                                <div class="stat-number">
                                                    {{ $currency->symbol }}<br>{{ number_format($purchase->total_price, 2) }}
                                                </div>
                                                <div class="stat-text">Monto<br>Total</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action buttons -->
                                <div class="card-actions-modern" role="group"
                                    aria-label="Acciones para compra {{ $loop->iteration }}">
                                    @if ($permissions['can_show'])
                                        <button type="button" class="modern-action-btn primary view-details"
                                            data-id="{{ $purchase->id }}" title="Ver Detalles"
                                            aria-label="Ver detalles de la compra {{ $purchase->payment_receipt ?: 'sin recibo' }}">
                                            <i class="fas fa-eye" aria-hidden="true"></i>
                                        </button>
                                    @endif

                                    @if ($permissions['can_show'])
                                        <button type="button" class="modern-action-btn info"
                                            onclick="showSupplierInfo({{ $purchase->supplier_id ?? 1 }})"
                                            title="Ver Proveedor" aria-label="Ver información del proveedor">
                                            <i class="fas fa-truck" aria-hidden="true"></i>
                                        </button>
                                    @endif

                                    @if ($permissions['can_edit'])
                                        <a href="{{ route('admin.purchases.edit', $purchase->id) }}"
                                            class="modern-action-btn secondary" title="Editar"
                                            aria-label="Editar compra {{ $purchase->payment_receipt ?: 'sin recibo' }}">
                                            <i class="fas fa-edit" aria-hidden="true"></i>
                                        </a>
                                    @endif

                                    @if ($permissions['can_destroy'])
                                        <button type="button" class="modern-action-btn danger delete-purchase"
                                            data-id="{{ $purchase->id }}" title="Eliminar"
                                            aria-label="Eliminar compra {{ $purchase->payment_receipt ?: 'sin recibo' }}">
                                            <i class="fas fa-trash" aria-hidden="true"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para Detalles -->
        <div class="modal-overlay" id="purchaseDetailsModal" style="display: none;" role="dialog"
            aria-labelledby="purchaseDetailsTitle" aria-modal="true">
            <div class="modal-container">
                <div class="modal-header">
                    <h3 class="modal-title" id="purchaseDetailsTitle">
                        <i class="fas fa-list-alt mr-2" aria-hidden="true"></i>
                        Detalle de la Compra
                    </h3>
                    <button type="button" class="modal-close" onclick="closePurchaseModal()" aria-label="Cerrar modal">
                        <i class="fas fa-times" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-wrapper">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Producto</th>
                                    <th>Categoría</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unit.</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="purchaseDetailsTableBody">
                                <!-- Los detalles se cargarán aquí dinámicamente -->
                            </tbody>
                            <tfoot>
                                <tr class="total-row">
                                    <td colspan="5" class="total-label">
                                        <div class="total-content">
                                            <i class="fas fa-calculator"></i>
                                            <span>Total de la Compra</span>
                                        </div>
                                    </td>
                                    <td class="total-amount">
                                        <div class="amount-display">
                                            <span class="currency">{{ $currency->symbol }}</span>
                                            <span class="amount" id="modalTotal">0.00</span>
                                        </div>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modern btn-secondary" onclick="closePurchaseModal()"
                        aria-label="Cerrar modal de detalles">
                        <div class="btn-content">
                            <i class="fas fa-times" aria-hidden="true"></i>
                            <span>Cerrar</span>
                        </div>
                        <div class="btn-bg"></div>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para mostrar información de proveedor -->
    <div class="modal-overlay" id="supplierInfoModal" style="display: none;" role="dialog"
        aria-labelledby="supplierInfoTitle" aria-modal="true">
        <div class="modal-container">
            <div class="modal-header">
                <div class="modal-title">
                    <div class="title-icon">
                        <i class="fas fa-truck" aria-hidden="true"></i>
                    </div>
                    <div class="title-text">
                        <h3 id="supplierInfoTitle">Detalles del Proveedor</h3>
                        <p>Información completa del proveedor seleccionado</p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeSupplierModal()" aria-label="Cerrar modal">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="modal-content-grid">
                    {{-- Información de la empresa --}}
                    <div class="info-card">
                        <div class="info-card-header">
                            <div class="info-icon">
                                <i class="fas fa-building"></i>
                            </div>
                            <h4>Información de la Empresa</h4>
                        </div>
                        <div class="info-card-body">
                            <div class="info-item">
                                <span class="info-label">Nombre:</span>
                                <span class="info-value" id="modalCompanyName"></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Email:</span>
                                <span class="info-value" id="modalCompanyEmail"></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Teléfono:</span>
                                <span class="info-value" id="modalCompanyPhone"></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Dirección:</span>
                                <span class="info-value" id="modalCompanyAddress"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Información del contacto --}}
                    <div class="info-card">
                        <div class="info-card-header">
                            <div class="info-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <h4>Información del Contacto</h4>
                        </div>
                        <div class="info-card-body">
                            <div class="info-item">
                                <span class="info-label">Nombre:</span>
                                <span class="info-value" id="modalContactName"></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Teléfono:</span>
                                <span class="info-value" id="modalContactPhone"></span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Estadísticas del Proveedor --}}
                <div class="stats-section" id="productsDistributedSection" style="display: none;">
                    <div class="stats-card">
                        <div class="stats-card-header">
                            <div class="stats-icon">
                                <i class="fas fa-box"></i>
                            </div>
                            <h4>Resumen de Productos Distribuidos</h4>
                        </div>
                        <div class="stats-card-body">
                            <div class="table-responsive">
                                <table class="stats-table">
                                    <thead>
                                        <tr>
                                            <th>Producto</th>
                                            <th class="text-center">Cantidad</th>
                                            <th class="text-right">Precio Unitario</th>
                                            <th class="text-right">Sub Total</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modalProductsTableBody">
                                        <!-- Los detalles se cargarán dinámicamente -->
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-right"><strong>Total General:</strong></td>
                                            <td class="text-right"><strong id="modalTotalAmount">0.00</strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-modern btn-secondary" onclick="closeSupplierModal()"
                    aria-label="Cerrar modal de proveedor">
                    <i class="fas fa-times" aria-hidden="true"></i>
                    <span>Cerrar</span>
                </button>
            </div>
        </div>
    </div>

    @push('css')
        <link rel="stylesheet" href="{{ asset('css/admin/purchases/index.css') }}">
    @endpush


    @push('js')
        <script src="{{ asset('js/admin/purchases/index.js') }}" defer></script>
    @endpush
@endsection
