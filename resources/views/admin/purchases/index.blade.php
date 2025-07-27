@extends('adminlte::page')

@section('title', 'Gestión de Compras')

@section('content_header')
    <div class="modern-header">
        <div class="header-content">
            <div class="header-left">
                <div class="title-section">
                    <div class="icon-wrapper">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="title-text">
                        <h1 class="main-title">Gestión de Compras</h1>
                        <p class="subtitle">Administra y controla todas las compras del sistema</p>
                    </div>
                </div>
            </div>
            <div class="header-actions">
                @can('purchases.report')
                    <a href="{{ route('admin.purchases.report') }}" class="action-btn report-btn" target="_blank">
                        <div class="btn-content">
                            <i class="fas fa-file-pdf"></i>
                            <span class="btn-text">Reporte</span>
                        </div>
                        <div class="btn-glow"></div>
                    </a>
                @endcan
                @if ($cashCount)
                    @can('purchases.create')
                        <a href="{{ route('admin.purchases.create') }}" class="action-btn primary-btn">
                            <div class="btn-content">
                                <i class="fas fa-plus-circle"></i>
                                <span class="btn-text">Nueva Compra</span>
                            </div>
                            <div class="btn-glow"></div>
                        </a>
                    @endcan
                @else
                    @can('cash-counts.create')
                        <a href="{{ route('admin.cash-counts.create') }}" class="action-btn danger-btn">
                            <div class="btn-content">
                                <i class="fas fa-cash-register"></i>
                                <span class="btn-text">Abrir Caja</span>
                            </div>
                            <div class="btn-glow"></div>
                        </a>
                    @endcan
                @endif
            </div>
        </div>
        <div class="header-decoration">
            <div class="decoration-line"></div>
            <div class="decoration-dots">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </div>
@stop

@section('content')
    {{-- Dashboard de Estadísticas Moderno --}}
    <div class="stats-dashboard">
        <div class="stats-grid">
            <div class="stat-card products-card">
                <div class="stat-icon">
                    <i class="fas fa-boxes"></i>
                    <div class="icon-glow"></div>
                </div>
                <div class="stat-content">
                    <div class="stat-number">{{ $totalPurchases }}</div>
                    <div class="stat-label">Productos Únicos</div>
                    <div class="stat-description">Comprados en total</div>
                </div>
                <div class="stat-decoration">
                    <div class="decoration-circle"></div>
                </div>
            </div>

            <div class="stat-card investment-card">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                    <div class="icon-glow"></div>
                </div>
                <div class="stat-content">
                    <div class="stat-number">{{ $currency->symbol }} {{ number_format($totalAmount, 2) }}</div>
                    <div class="stat-label">Total Invertido</div>
                    <div class="stat-description">Capital comprometido</div>
                </div>
                <div class="stat-decoration">
                    <div class="decoration-circle"></div>
                </div>
            </div>

            <div class="stat-card monthly-card">
                <div class="stat-icon">
                    <i class="fas fa-calendar-check"></i>
                    <div class="icon-glow"></div>
                </div>
                <div class="stat-content">
                    <div class="stat-number">{{ $monthlyPurchases }}</div>
                    <div class="stat-label">Compras del Mes</div>
                    <div class="stat-description">Actividad reciente</div>
                </div>
                <div class="stat-decoration">
                    <div class="decoration-circle"></div>
                </div>
            </div>

            <div class="stat-card pending-card">
                <div class="stat-icon">
                    <i class="fas fa-hourglass-half"></i>
                    <div class="icon-glow"></div>
                </div>
                <div class="stat-content">
                    <div class="stat-number">{{ $pendingDeliveries }}</div>
                    <div class="stat-label">Pendientes</div>
                    <div class="stat-description">Por entregar</div>
                </div>
                <div class="stat-decoration">
                    <div class="decoration-circle"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Panel Principal de Compras --}}
    <div class="main-panel">
        <div class="panel-header">
            <div class="header-content">
                <div class="title-section">
                    <div class="title-icon">
                        <i class="fas fa-list-alt"></i>
                    </div>
                    <div class="title-text">
                        <h3 class="panel-title">Lista de Compras</h3>
                        <p class="panel-subtitle">Gestiona todas las transacciones de compra</p>
                    </div>
                </div>
                
                <div class="header-controls">
                    <div class="search-container">
                        <div class="search-wrapper">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" id="purchasesSearch" class="search-input" placeholder="Buscar compra por recibo o fecha...">
                            <button type="button" class="search-clear" id="clearSearch">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <button type="button" class="panel-toggle" data-card-widget="collapse">
                        <i class="fas fa-chevron-up"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="panel-body">
            {{-- Vista de tabla para pantallas grandes --}}
            <div class="table-container d-none d-lg-block">
                <table id="purchasesTable" class="modern-table">
                    <thead>
                        <tr>
                            <th class="th-number">#</th>
                            <th class="th-receipt">Recibo de Pago</th>
                            <th class="th-date">Fecha</th>
                            <th class="th-products">Productos</th>
                            <th class="th-amount">Monto Total</th>
                            <th class="th-details">Detalles</th>
                            <th class="th-status">Estado</th>
                            <th class="th-actions">Acciones</th>
                        </tr>
                    </thead>
                <tbody>
                    @foreach ($purchases as $purchase)
                        <tr class="table-row" data-purchase-id="{{ $purchase->id }}">
                            <td class="td-number">
                                <div class="number-badge">{{ $loop->iteration }}</div>
                            </td>
                            <td class="td-receipt">
                                <div class="receipt-info">
                                    <div class="receipt-icon">
                                        <i class="fas fa-receipt"></i>
                                    </div>
                                    <div class="receipt-text">
                                        <strong>{{ $purchase->payment_receipt ?: 'Sin recibo' }}</strong>
                                    </div>
                                </div>
                            </td>
                            <td class="td-date">
                                <div class="date-info">
                                    <div class="date-icon">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <div class="date-text">
                                        <div class="date-main">{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d/m/Y') }}</div>
                                        <div class="time-sub">{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('H:i') }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="td-products">
                                <div class="products-info">
                                    <div class="product-badge unique">
                                        <i class="fas fa-boxes"></i>
                                        <span>{{ $purchase->details->count() }} únicos</span>
                                    </div>
                                    <div class="product-badge total">
                                        <i class="fas fa-cubes"></i>
                                        <span>{{ $purchase->details->sum('quantity') }} totales</span>
                                    </div>
                                </div>
                            </td>
                            <td class="td-amount">
                                <div class="amount-info">
                                    <div class="amount-icon">
                                        <i class="fas fa-dollar-sign"></i>
                                    </div>
                                    <div class="amount-text">
                                        {{ $currency->symbol }} {{ number_format($purchase->total_price, 2) }}
                                    </div>
                                </div>
                            </td>
                            <td class="td-details">
                                <button type="button" class="action-btn details-btn view-details"
                                    data-id="{{ $purchase->id }}" data-toggle="modal" data-target="#purchaseDetailsModal">
                                    <i class="fas fa-list"></i>
                                    <span>Ver Detalle</span>
                                </button>
                            </td>
                            <td class="td-status">
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
                            <td class="td-actions">
                                <div class="actions-group">
                                    @can('purchases.edit')
                                        <a href="{{ route('admin.purchases.edit', $purchase->id) }}"
                                            class="action-btn edit-btn" data-toggle="tooltip" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan
                                    @can('purchases.destroy')
                                        <button type="button" class="action-btn delete-btn delete-purchase"
                                            data-id="{{ $purchase->id }}" data-toggle="tooltip" title="Eliminar">
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

            {{-- Vista de tarjetas para móviles --}}
            <div class="mobile-container d-lg-none">
                <div class="mobile-grid" id="mobilePurchasesContainer">
                    @foreach ($purchases as $purchase)
                        <div class="mobile-card purchase-card" data-purchase-id="{{ $purchase->id }}">
                            <div class="card-header">
                                <div class="card-number">
                                    <span class="number-circle">{{ $loop->iteration }}</span>
                                </div>
                                <div class="card-status">
                                    @if ($purchase->payment_receipt)
                                        <div class="status-indicator completed">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                    @else
                                        <div class="status-indicator pending">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="card-content">
                                <div class="receipt-section">
                                    <div class="receipt-header">
                                        <div class="receipt-icon">
                                            <i class="fas fa-receipt"></i>
                                        </div>
                                        <div class="receipt-info">
                                            <h6 class="receipt-title">{{ $purchase->payment_receipt ?: 'Sin recibo' }}</h6>
                                            <div class="receipt-date">
                                                <i class="fas fa-calendar-alt"></i>
                                                <div class="date-time-info">
                                                    <div class="date-main">{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d/m/Y') }}</div>
                                                    <div class="time-sub">{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('H:i') }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="stats-section">
                                    <div class="stat-item">
                                        <div class="stat-icon">
                                            <i class="fas fa-boxes"></i>
                                        </div>
                                        <div class="stat-content">
                                            <div class="stat-value">{{ $purchase->details->count() }}</div>
                                            <div class="stat-label">Productos Únicos</div>
                                        </div>
                                    </div>
                                    
                                    <div class="stat-item">
                                        <div class="stat-icon">
                                            <i class="fas fa-cubes"></i>
                                        </div>
                                        <div class="stat-content">
                                            <div class="stat-value">{{ $purchase->details->sum('quantity') }}</div>
                                            <div class="stat-label">Total Unidades</div>
                                        </div>
                                    </div>
                                    
                                    <div class="stat-item amount">
                                        <div class="stat-icon">
                                            <i class="fas fa-dollar-sign"></i>
                                        </div>
                                        <div class="stat-content">
                                            <div class="stat-value">{{ $currency->symbol }} {{ number_format($purchase->total_price, 2) }}</div>
                                            <div class="stat-label">Monto Total</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card-actions">
                                <div class="action-buttons">
                                    <button type="button" class="mobile-btn details-btn view-details"
                                        data-id="{{ $purchase->id }}" data-toggle="modal" data-target="#purchaseDetailsModal">
                                        <i class="fas fa-list"></i>
                                    </button>
                                    
                                    @can('purchases.edit')
                                        <a href="{{ route('admin.purchases.edit', $purchase->id) }}" class="mobile-btn edit-btn">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan
                                    
                                    @can('purchases.destroy')
                                        <button type="button" class="mobile-btn delete-btn delete-purchase" data-id="{{ $purchase->id }}">
                                            <i class="fas fa-trash"></i>
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

    {{-- Modal Moderno para Detalles --}}
    <div class="modal fade" id="purchaseDetailsModal" tabindex="-1" role="dialog"
        aria-labelledby="purchaseDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content modern-modal">
                <div class="modal-header">
                    <div class="modal-title-section">
                        <div class="modal-icon">
                            <i class="fas fa-list-alt"></i>
                        </div>
                        <div class="modal-title-content">
                            <h5 class="modal-title" id="purchaseDetailsModalLabel">Detalle de la Compra</h5>
                            <p class="modal-subtitle">Información completa de productos y precios</p>
                        </div>
                    </div>
                    <button type="button" class="modal-close" data-dismiss="modal" aria-label="Close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-container">
                        <table class="modern-table details-table">
                            <thead>
                                <tr>
                                    <th class="th-code">Código</th>
                                    <th class="th-product">Producto</th>
                                    <th class="th-category">Categoría</th>
                                    <th class="th-quantity">Cantidad</th>
                                    <th class="th-price">Precio Unit.</th>
                                    <th class="th-subtotal">Subtotal</th>
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
                    <button type="button" class="modal-btn close-btn" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                        <span>Cerrar</span>
                    </button>
                </div>
            </div>
        </div>
    </div>


@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">
    <style>
        /* Variables CSS */
        :root {
            --primary-color: #4f46e5;
            --primary-light: #6366f1;
            --primary-dark: #3730a3;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #06b6d4;
            --dark-color: #1f2937;
            --light-color: #f8fafc;
            --border-radius: 12px;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Header Moderno */
        .modern-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
        }

        .modern-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            pointer-events: none;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .title-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .icon-wrapper {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .icon-wrapper i {
            font-size: 1.5rem;
            color: white;
        }

        .title-text {
            color: white;
        }

        .main-title {
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .subtitle {
            font-size: 1rem;
            opacity: 0.9;
            margin: 0.25rem 0 0 0;
        }

        .header-actions {
            display: flex;
            gap: 1rem;
        }

        .action-btn {
            position: relative;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--border-radius);
            text-decoration: none;
            color: white;
            font-weight: 600;
            transition: var(--transition);
            overflow: hidden;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            color: white;
            text-decoration: none;
        }

        .primary-btn {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .report-btn {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        }

        .danger-btn {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .btn-content {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            position: relative;
            z-index: 1;
        }

        .btn-glow {
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: var(--transition);
        }

        .action-btn:hover .btn-glow {
            left: 100%;
        }

        .header-decoration {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        }

        .decoration-dots {
            position: absolute;
            top: 1rem;
            right: 1rem;
            display: flex;
            gap: 0.5rem;
        }

        .decoration-dots span {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            animation: pulse 2s infinite;
        }

        .decoration-dots span:nth-child(2) {
            animation-delay: 0.5s;
        }

        .decoration-dots span:nth-child(3) {
            animation-delay: 1s;
        }

        @keyframes pulse {
            0%, 100% { opacity: 0.3; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.2); }
        }

        /* Dashboard de Estadísticas */
        .stats-dashboard {
            margin-bottom: 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .stat-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--primary-light));
        }

        .products-card::before { background: linear-gradient(90deg, var(--info-color), #0891b2); }
        .investment-card::before { background: linear-gradient(90deg, var(--success-color), #059669); }
        .monthly-card::before { background: linear-gradient(90deg, var(--warning-color), #d97706); }
        .pending-card::before { background: linear-gradient(90deg, var(--danger-color), #dc2626); }

        .stat-icon {
            position: relative;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
            font-size: 1.5rem;
        }

        .products-card .stat-icon { background: linear-gradient(135deg, var(--info-color), #0891b2); }
        .investment-card .stat-icon { background: linear-gradient(135deg, var(--success-color), #059669); }
        .monthly-card .stat-icon { background: linear-gradient(135deg, var(--warning-color), #d97706); }
        .pending-card .stat-icon { background: linear-gradient(135deg, var(--danger-color), #dc2626); }

        .icon-glow {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: inherit;
            opacity: 0.2;
            filter: blur(10px);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.25rem;
        }

        .stat-description {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .stat-decoration {
            position: absolute;
            top: 1rem;
            right: 1rem;
        }

        .decoration-circle {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            opacity: 0.1;
        }

        /* Panel Principal */
        .main-panel {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }

        .panel-header {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .title-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .title-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
        }

        .panel-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-color);
            margin: 0;
        }

        .panel-subtitle {
            font-size: 0.875rem;
            color: #6b7280;
            margin: 0.25rem 0 0 0;
        }

        .header-controls {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .search-container {
            position: relative;
        }

        .search-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-input {
            padding: 0.75rem 1rem 0.75rem 3rem;
            border: 2px solid #e5e7eb;
            border-radius: var(--border-radius);
            font-size: 0.875rem;
            transition: var(--transition);
            background: white;
            width: 300px;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            color: #9ca3af;
            z-index: 1;
        }

        .search-clear {
            position: absolute;
            right: 0.75rem;
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 50%;
            transition: var(--transition);
        }

        .search-clear:hover {
            background: #f3f4f6;
            color: #6b7280;
        }

        .panel-toggle {
            background: none;
            border: none;
            color: #6b7280;
            padding: 0.5rem;
            border-radius: 50%;
            transition: var(--transition);
            cursor: pointer;
        }

        .panel-toggle:hover {
            background: #f3f4f6;
            color: var(--dark-color);
        }

        .panel-body {
            padding: 1.5rem;
        }

        /* Tabla Moderna */
        .table-container {
            overflow: hidden;
            border-radius: var(--border-radius);
        }

        .modern-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
        }

        .modern-table thead {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
        }

        .modern-table th {
            padding: 1rem;
            color: white;
            font-weight: 600;
            text-align: left;
            border: none;
            position: relative;
        }

        .modern-table th:first-child {
            border-top-left-radius: var(--border-radius);
        }

        .modern-table th:last-child {
            border-top-right-radius: var(--border-radius);
        }

        .modern-table tbody tr {
            transition: var(--transition);
            border-bottom: 1px solid #f3f4f6;
        }

        .modern-table tbody tr:hover {
            background: #f8fafc;
            transform: scale(1.01);
        }

        .modern-table td {
            padding: 1rem;
            vertical-align: middle;
            border: none;
        }

        /* Estilos específicos de columnas */
        .number-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
            border-radius: 50%;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .receipt-info, .date-info, .amount-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        /* Estilos para fecha y hora */
        .date-text {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        
        .date-main {
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--dark-color);
        }
        
        .time-sub {
            font-size: 0.75rem;
            color: #6b7280;
            font-weight: 500;
        }
        
        .date-time-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .receipt-icon, .date-icon, .amount-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f3f4f6;
            color: var(--primary-color);
        }

        .products-info {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .product-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .product-badge.unique {
            background: linear-gradient(135deg, var(--info-color), #0891b2);
            color: white;
        }

        .product-badge.total {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            cursor: pointer;
        }

        .details-btn {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
        }

        .details-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
            color: white;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .status-badge.completed {
            background: linear-gradient(135deg, var(--success-color), #059669);
            color: white;
        }

        .status-badge.pending {
            background: linear-gradient(135deg, var(--warning-color), #d97706);
            color: white;
        }

        .actions-group {
            display: flex;
            gap: 0.5rem;
        }

        .edit-btn {
            background: linear-gradient(135deg, var(--warning-color), #d97706);
            color: white;
            padding: 0.5rem;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .delete-btn {
            background: linear-gradient(135deg, var(--danger-color), #dc2626);
            color: white;
            padding: 0.5rem;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .edit-btn:hover, .delete-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
            color: white;
        }

        /* Vista Móvil */
        .mobile-container {
            padding: 1rem 0;
        }

        .mobile-grid {
            display: grid;
            gap: 1rem;
        }

        .mobile-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: var(--transition);
            border: 1px solid #e5e7eb;
        }

        .mobile-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .mobile-card .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-bottom: 1px solid #e5e7eb;
        }

        .number-circle {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .status-indicator {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .status-indicator.completed {
            background: linear-gradient(135deg, var(--success-color), #059669);
            color: white;
        }

        .status-indicator.pending {
            background: linear-gradient(135deg, var(--warning-color), #d97706);
            color: white;
        }

        .card-content {
            padding: 1rem;
        }

        @media (max-width: 768px) {
            .card-content {
                padding: 0.75rem;
            }
        }

        @media (max-width: 576px) {
            .card-content {
                padding: 0.5rem;
            }
        }

        .receipt-section {
            margin-bottom: 1rem;
        }

        .receipt-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .receipt-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
        }

        .receipt-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--dark-color);
            margin: 0 0 0.25rem 0;
        }

        .receipt-date {
            font-size: 0.875rem;
            color: #6b7280;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        @media (max-width: 768px) {
            .receipt-section {
                margin-bottom: 0.75rem;
            }

            .receipt-header {
                gap: 0.5rem;
            }

            .receipt-icon {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }

            .receipt-title {
                font-size: 1rem;
                margin: 0 0 0.125rem 0;
            }

            .receipt-date {
                font-size: 0.8rem;
            }
        }

        @media (max-width: 576px) {
            .receipt-section {
                margin-bottom: 0.5rem;
            }

            .receipt-header {
                gap: 0.375rem;
            }

            .receipt-icon {
                width: 35px;
                height: 35px;
                font-size: 0.875rem;
            }

            .receipt-title {
                font-size: 0.9rem;
                margin: 0 0 0.125rem 0;
            }

            .receipt-date {
                font-size: 0.75rem;
            }
        }

        .stats-section {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .stat-item {
            text-align: center;
            padding: 1rem;
            background: #f8fafc;
            border-radius: var(--border-radius);
            border: 1px solid #e5e7eb;
        }

        .stat-item.amount {
            background: linear-gradient(135deg, var(--success-color), #059669);
            color: white;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            margin: 0 auto 0.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-color);
            color: white;
            font-size: 1rem;
        }

        .stat-item.amount .stat-icon {
            background: rgba(255, 255, 255, 0.2);
        }

        .stat-value {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .stat-label {
            font-size: 0.75rem;
            font-weight: 600;
            opacity: 0.8;
        }

        /* Estilos compactos para pantallas pequeñas */
        @media (max-width: 768px) {
            .stats-section {
                gap: 0.75rem;
                margin-bottom: 0.75rem;
            }

            .stat-item {
                padding: 0.75rem 0.5rem;
            }

            .stat-icon {
                width: 32px;
                height: 32px;
                margin: 0 auto 0.375rem;
                font-size: 0.875rem;
            }

            .stat-value {
                font-size: 1rem;
                margin-bottom: 0.125rem;
            }

            .stat-label {
                font-size: 0.7rem;
            }
        }

        .card-actions {
            padding: 1rem;
            background: #f8fafc;
            border-top: 1px solid #e5e7eb;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            align-items: center;
        }

        .mobile-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            cursor: pointer;
            font-size: 1.25rem;
            width: 60px;
            height: 60px;
            position: relative;
            overflow: hidden;
        }

        .mobile-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: var(--transition);
        }

        .mobile-btn:hover::before {
            left: 100%;
        }

        .mobile-btn i {
            position: relative;
            z-index: 1;
        }

        .details-btn {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
        }

        .edit-btn {
            background: linear-gradient(135deg, var(--warning-color), #d97706);
            color: white;
        }

        .delete-btn {
            background: linear-gradient(135deg, var(--danger-color), #dc2626);
            color: white;
        }

        .mobile-btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
            color: white;
            text-decoration: none;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }

            .header-actions {
                justify-content: center;
            }

            .search-input {
                width: 100%;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .stats-section {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                gap: 0.75rem;
            }

            .mobile-btn {
                width: 55px;
                height: 55px;
                font-size: 1.125rem;
            }
        }

        @media (max-width: 576px) {
            .modern-header {
                padding: 1.5rem;
            }

            .main-title {
                font-size: 1.5rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .title-section {
                flex-direction: column;
                text-align: center;
            }

            .action-buttons {
                gap: 0.5rem;
            }

            .mobile-btn {
                width: 50px;
                height: 50px;
                font-size: 1rem;
                padding: 0.75rem;
            }

            .stats-section {
                grid-template-columns: repeat(3, 1fr);
                gap: 0.5rem;
                margin-bottom: 0.5rem;
            }

            .stat-item {
                padding: 0.5rem 0.25rem;
            }

            .stat-icon {
                width: 28px;
                height: 28px;
                margin: 0 auto 0.25rem;
                font-size: 0.75rem;
            }

            .stat-value {
                font-size: 0.875rem;
                margin-bottom: 0.125rem;
            }

            .stat-label {
                font-size: 0.65rem;
                line-height: 1.2;
            }
        }

        /* Animaciones */
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

        .stat-card, .mobile-card {
            animation: fadeInUp 0.6s ease-out;
        }

        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }

        /* Modal Moderno */
        .modern-modal {
            border-radius: var(--border-radius);
            border: none;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        .modern-modal .modal-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
            border-bottom: none;
            padding: 1.5rem;
        }

        .modal-title-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .modal-icon {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .modal-icon i {
            font-size: 1.25rem;
            color: white;
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }

        .modal-subtitle {
            font-size: 0.875rem;
            opacity: 0.9;
            margin: 0.25rem 0 0 0;
        }

        .modal-close {
            background: none;
            border: none;
            color: white;
            font-size: 1.25rem;
            padding: 0.5rem;
            border-radius: 50%;
            transition: var(--transition);
            cursor: pointer;
        }

        .modal-close:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: rotate(90deg);
        }

        .modern-modal .modal-body {
            padding: 1.5rem;
        }

        .details-table {
            border-radius: var(--border-radius);
            overflow: hidden;
        }

        .details-table thead {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        }

        .details-table th {
            color: var(--dark-color);
            font-weight: 600;
            padding: 1rem;
            border-bottom: 2px solid #e5e7eb;
        }

        .details-table tbody tr {
            border-bottom: 1px solid #f3f4f6;
        }

        .details-table tbody tr:hover {
            background: #f8fafc;
        }

        .details-table td {
            padding: 1rem;
            vertical-align: middle;
        }

        .total-row {
            background: linear-gradient(135deg, var(--success-color), #059669);
            color: white;
        }

        .total-label {
            text-align: right;
        }

        .total-content {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 600;
            font-size: 1.125rem;
        }

        .total-amount {
            text-align: right;
        }

        .amount-display {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 700;
            font-size: 1.5rem;
        }

        .currency {
            font-size: 1rem;
            opacity: 0.8;
        }

        .modern-modal .modal-footer {
            background: #f8fafc;
            border-top: 1px solid #e5e7eb;
            padding: 1rem 1.5rem;
        }

        .modal-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            cursor: pointer;
        }

        .close-btn {
            background: linear-gradient(135deg, #6b7280, #4b5563);
            color: white;
        }

        .close-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
            color: white;
        }

        /* Responsive para modal */
        @media (max-width: 768px) {
            .modal-title-section {
                flex-direction: column;
                text-align: center;
            }

            .modal-title {
                font-size: 1.25rem;
            }

            .details-table {
                font-size: 0.875rem;
            }

            .details-table th,
            .details-table td {
                padding: 0.75rem 0.5rem;
            }

            .amount-display {
                font-size: 1.25rem;
            }
        }

        /* Ocultar campo de búsqueda nativo de DataTables pero mantener funcionalidad */
        .dataTables_filter {
            display: none !important;
        }

        .dataTables_filter label {
            display: none !important;
        }

        .dataTables_filter input {
            display: none !important;
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
            // Inicializar DataTable
            const table = $('#purchasesTable').DataTable({
                responsive: true,
                searching: true, // Mantener búsqueda habilitada para filtros personalizados
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

            // Conectar búsqueda del header con DataTable y vista móvil
            $('#purchasesSearch').on('keyup', function() {
                const searchTerm = $(this).val().toLowerCase();
                
                // Búsqueda en DataTable (vista desktop)
                table.search(this.value).draw();
                
                // Búsqueda en tarjetas móviles
                $('.mobile-card').each(function() {
                    const receiptText = $(this).find('.receipt-title').text().toLowerCase();
                    const dateText = $(this).find('.receipt-date').text().toLowerCase();
                    
                    if (receiptText.includes(searchTerm) || dateText.includes(searchTerm)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // Limpiar búsqueda
            $('#clearSearch').on('click', function() {
                $('#purchasesSearch').val('');
                table.search('').draw();
                $('.mobile-card').show();
            });

            // Inicializar tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Mostrar modal de detalles de la compra
            $('.show-purchase').click(function() {
                const id = $(this).data('id');

                // Limpiar datos anteriores
                $('#purchaseId, #purchaseDate, #purchaseTotal, #purchaseStatus, #productName, #productQuantity, #supplierName')
                    .text('');
                $('#receiptImage').attr('src', '');
                $('#receiptSection').hide();

                $.ajax({
                    url: `/purchases/${id}`,
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            const purchase = response.purchase;

                            // Llenar datos generales
                            $('#purchaseId').text(`#${String(purchase.id).padStart(6, '0')}`);
                            $('#purchaseDate').text(purchase.formatted_date);
                            $('#purchaseTotal').text(`$${purchase.total_price}`);
                            $('#purchaseStatus').html(purchase.payment_receipt ?
                                '<span class="badge badge-success">Completado</span>' :
                                '<span class="badge badge-warning">Pendiente</span>'
                            );

                            // Llenar datos del producto
                            $('#productName').text(purchase.product.name);
                            $('#productQuantity').text(`${purchase.quantity} unidades`);
                            $('#supplierName').text(purchase.supplier.name);

                            // Mostrar recibo si existe
                            if (purchase.payment_receipt) {
                                $('#receiptImage').attr('src', purchase.payment_receipt);
                                $('#receiptSection').show();
                            }

                            // Mostrar el modal
                            $('#showPurchaseModal').modal('show');
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'No se pudieron cargar los datos de la compra',
                            'error');
                    }
                });
            });

            // Eliminar compra
            $('.delete-purchase').click(function() {
                const id = $(this).data('id');
                console.log('🔄 Intentando eliminar compra ID:', id);

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
                        console.log('✅ Usuario confirmó eliminación');
                        
                        $.ajax({
                            url: `/purchases/delete/${id}`,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            beforeSend: function() {
                                console.log('📡 Enviando petición AJAX...');
                                console.log('🔗 URL:', `/purchases/delete/${id}`);
                                console.log('🔑 CSRF Token:', $('meta[name="csrf-token"]').attr('content'));
                            },
                            success: function(response) {
                                console.log('✅ Respuesta exitosa:', response);
                                
                                if (response.success) {
                                    Swal.fire({
                                        title: '¡Eliminado!',
                                        text: response.message,
                                        icon: 'success'
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    console.log('❌ Respuesta con error:', response);
                                    Swal.fire('Error', response.message, 'error');
                                }
                            },
                            error: function(xhr, status, error) {
                                console.log('❌ Error en petición AJAX:');
                                console.log('📊 Status:', status);
                                console.log('🔍 Error:', error);
                                console.log('📄 XHR:', xhr);
                                console.log('📋 Response Text:', xhr.responseText);
                                
                                let errorMessage = 'No se pudo eliminar la compra';
                                let errorDetails = '';
                                
                                try {
                                    const response = JSON.parse(xhr.responseText);
                                    if (response.message) {
                                        errorMessage = response.message;
                                    }
                                    if (response.details) {
                                        errorDetails = response.details;
                                    }
                                } catch (e) {
                                    console.log('⚠️ No se pudo parsear la respuesta JSON');
                                }
                                
                                if (xhr.status === 403) {
                                    errorMessage = 'No tienes permisos para eliminar esta compra';
                                } else if (xhr.status === 404) {
                                    errorMessage = 'La compra no fue encontrada';
                                } else if (xhr.status === 422) {
                                    errorMessage = 'No se puede eliminar esta compra (tiene movimientos de caja)';
                                } else if (xhr.status === 500) {
                                    errorMessage = 'Error interno del servidor';
                                }
                                
                                Swal.fire({
                                    title: 'Error',
                                    html: `<div>${errorMessage}</div>${errorDetails ? `<div style="margin-top: 10px; font-size: 0.9em; color: #666;">${errorDetails}</div>` : ''}`,
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        });
                    } else {
                        console.log('❌ Usuario canceló la eliminación');
                    }
                });
            });

            // Ver detalles de la compra
            $('.view-details').click(function() {
                const purchaseId = $(this).data('id');
                $('#purchaseDetailsTableBody').empty();

                $.ajax({
                    url: `/purchases/${purchaseId}/details`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            let total = 0;

                            response.details.forEach(function(detail) {
                                const quantity = parseFloat(detail.quantity);
                                const price = parseFloat(detail.product_price);
                                const subtotal = quantity * price;
                                total += subtotal;

                                $('#purchaseDetailsTableBody').append(`
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

                            // Formatear el total con separador de miles
                            const formattedTotal = total.toFixed(2).replace(
                                /\B(?=(\d{3})+(?!\d))/g, ",");
                            $('#modalTotal').text(formattedTotal);
                            $('#purchaseDetailsModal').modal('show');
                        } else {
                            Swal.fire('Error', response.message ||
                                'Error al cargar los detalles', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'No se pudieron cargar los detalles', 'error');
                    }
                });
            });
        });
    </script>
@stop
