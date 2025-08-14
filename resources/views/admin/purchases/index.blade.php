@extends('layouts.app')

@section('title', 'Gestión de Compras')

@section('content')
    <!-- Background Pattern -->
    <div class="page-background"></div>

    <!-- Main Container -->
    <div class="main-container">
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
                    @can('purchases.report')
                        <a href="{{ route('admin.purchases.report') }}" class="btn-glass btn-secondary-glass" target="_blank">
                            <i class="fas fa-file-pdf"></i>
                            <span>Reporte</span>
                            <div class="btn-ripple"></div>
                        </a>
                    @endcan
                    @if ($cashCount)
                        @can('purchases.create')
                            <a href="{{ route('admin.purchases.create') }}" class="btn-glass btn-primary-glass">
                                <i class="fas fa-plus-circle"></i>
                                <span>Nueva Compra</span>
                                <div class="btn-ripple"></div>
                            </a>
                        @endcan
                    @else
                        @can('cash-counts.create')
                            <a href="{{ route('admin.cash-counts.create') }}" class="btn-glass btn-danger-glass">
                                <i class="fas fa-cash-register"></i>
                                <span>Abrir Caja</span>
                                <div class="btn-ripple"></div>
                            </a>
                        @endcan
                    @endif
                </div>
            </div>
        </div>
        <!-- Stats Dashboard -->
        <div class="stats-dashboard">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-boxes"></i>
                        <div class="stat-glow"></div>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">{{ $totalPurchases }}</div>
                        <div class="stat-label">Productos Únicos</div>
                        <div class="stat-trend">
                            <i class="fas fa-arrow-up"></i>
                            <span>Comprados en total</span>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                        <div class="stat-glow"></div>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">{{ $currency->symbol }} {{ number_format($totalAmount, 2) }}</div>
                        <div class="stat-label">Total Invertido</div>
                        <div class="stat-trend">
                            <i class="fas fa-dollar-sign"></i>
                            <span>Capital comprometido</span>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                        <div class="stat-glow"></div>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">{{ $monthlyPurchases }}</div>
                        <div class="stat-label">Compras del Mes</div>
                        <div class="stat-trend">
                            <i class="fas fa-arrow-up"></i>
                            <span>Actividad reciente</span>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-hourglass-half"></i>
                        <div class="stat-glow"></div>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">{{ $pendingDeliveries }}</div>
                        <div class="stat-label">Pendientes</div>
                        <div class="stat-trend">
                            <i class="fas fa-clock"></i>
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
                    <div class="header-icon">
                        <i class="fas fa-list-alt"></i>
                    </div>
                    <div class="header-text">
                        <h3>Lista de Compras</h3>
                        <p>Gestiona todas las transacciones de compra</p>
                    </div>
                </div>
                <div class="header-controls">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="purchasesSearch" placeholder="Buscar compra por recibo o fecha...">
                        <button type="button" id="clearSearch" class="clear-btn">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="view-toggle">
                        <button type="button" class="view-btn active" data-view="table">
                            <i class="fas fa-table"></i>
                        </button>
                        <button type="button" class="view-btn" data-view="cards">
                            <i class="fas fa-th-large"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="data-content">
                <!-- Table View -->
                <div class="table-view" id="tableView">
                    <div class="table-wrapper">
                        <table id="purchasesTable" class="modern-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Recibo de Pago</th>
                                    <th>Fecha</th>
                                    <th>Productos</th>
                                    <th>Monto Total</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
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
                                            <div class="actions-group">
                                                <button type="button" class="action-btn details-btn view-details"
                                                    data-id="{{ $purchase->id }}">
                                                    <i class="fas fa-list"></i>
                                                </button>
                                                <button type="button" class="action-btn supplier-btn" 
                                                    onclick="showSupplierInfo({{ $purchase->supplier_id ?? 1 }})" 
                                                    title="Ver Proveedor">
                                                    <i class="fas fa-truck"></i>
                                                </button>
                                                @can('purchases.edit')
                                                    <a href="{{ route('admin.purchases.edit', $purchase->id) }}"
                                                        class="action-btn edit-btn">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endcan
                                                @can('purchases.destroy')
                                                    <button type="button" class="action-btn delete-btn delete-purchase"
                                                        data-id="{{ $purchase->id }}">
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

                <!-- Cards View -->
                <div class="cards-view" id="cardsView" style="display: none;">
                    <div class="cards-grid">
                        @foreach ($purchases as $purchase)
                            <div class="purchase-card">
                                <div class="card-header">
                                    <div class="card-number">
                                        <span class="number-badge">{{ $loop->iteration }}</span>
                                    </div>
                                    <div class="card-status">
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
                                    </div>
                                </div>

                                <div class="card-content">
                                    <div class="purchase-info">
                                        <div class="info-icon">
                                            <i class="fas fa-receipt"></i>
                                        </div>
                                        <div class="info-text">
                                            <h6>{{ $purchase->payment_receipt ?: 'Sin recibo' }}</h6>
                                            <div class="date-info">
                                                <i class="fas fa-calendar-alt"></i>
                                                <span>{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d/m/Y H:i') }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="stats-grid">
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
                                                <div class="stat-value">{{ $currency->symbol }}
                                                    {{ number_format($purchase->total_price, 2) }}</div>
                                                <div class="stat-label">Monto Total</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-actions">
                                    <div class="actions-group">
                                        <button type="button" class="action-btn details-btn view-details"
                                            data-id="{{ $purchase->id }}">
                                            <i class="fas fa-list"></i>
                                        </button>
                                        <button type="button" class="action-btn supplier-btn" 
                                            onclick="showSupplierInfo({{ $purchase->supplier_id ?? 1 }})" 
                                            title="Ver Proveedor">
                                            <i class="fas fa-truck"></i>
                                        </button>
                                        @can('purchases.edit')
                                            <a href="{{ route('admin.purchases.edit', $purchase->id) }}"
                                                class="action-btn edit-btn">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan
                                        @can('purchases.destroy')
                                            <button type="button" class="action-btn delete-btn delete-purchase"
                                                data-id="{{ $purchase->id }}">
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

        <!-- Modal para Detalles -->
        <div class="modal fade" id="purchaseDetailsModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="modal-title-section">
                            <div class="modal-icon">
                                <i class="fas fa-list-alt"></i>
                            </div>
                            <div class="modal-title-content">
                                <h5 class="modal-title">Detalle de la Compra</h5>
                                <p class="modal-subtitle">Información completa de productos y precios</p>
                            </div>
                        </div>
                        <button type="button" class="modal-close" data-dismiss="modal" aria-label="Close">
                            <i class="fas fa-times"></i>
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
                        <button type="button" class="btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i>
                            <span>Cerrar</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para mostrar información de proveedor -->
    <div class="modal-overlay" id="supplierInfoModal" style="display: none;">
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title">
                    <i class="fas fa-truck mr-2"></i>
                    Información del Proveedor
                </h3>
                <button type="button" class="modal-close" onclick="closeSupplierModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="modal-content-grid">
                    <div class="modal-content-column">
                        <div class="modal-detail-item">
                            <div class="detail-label">
                                <i class="fas fa-building"></i>
                                Información de la Empresa
                            </div>
                            <div class="detail-content">
                                <div class="detail-row">
                                    <span class="detail-field">Nombre:</span>
                                    <span class="detail-value" id="modalCompanyName">-</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-field">Email:</span>
                                    <span class="detail-value" id="modalCompanyEmail">-</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-field">Teléfono:</span>
                                    <span class="detail-value" id="modalCompanyPhone">-</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-field">Dirección:</span>
                                    <span class="detail-value" id="modalCompanyAddress">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-content-column">
                        <div class="modal-detail-item">
                            <div class="detail-label">
                                <i class="fas fa-user"></i>
                                Información del Contacto
                            </div>
                            <div class="detail-content">
                                <div class="detail-row">
                                    <span class="detail-field">Nombre:</span>
                                    <span class="detail-value" id="modalContactName">-</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-field">Teléfono:</span>
                                    <span class="detail-value" id="modalContactPhone">-</span>
                                </div>
                            </div>
                        </div>
                        <div class="modal-detail-item" id="productsDistributedSection" style="display: none;">
                            <div class="detail-label">
                                <i class="fas fa-boxes"></i>
                                Resumen de Productos Distribuidos
                            </div>
                            <div class="detail-content">
                                <div class="products-summary-table">
                                    <table class="summary-table">
                                        <thead>
                                            <tr>
                                                <th>Producto</th>
                                                <th>Cantidad</th>
                                                <th>Precio Unit.</th>
                                                <th>Sub Total</th>
                                            </tr>
                                        </thead>
                                        <tbody id="modalProductsTableBody">
                                            <tr>
                                                <td colspan="4" class="text-center">No hay productos registrados</td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr class="total-row">
                                                <td colspan="3" class="total-label">Total General:</td>
                                                <td class="total-amount" id="modalTotalAmount">$ 0.00</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modern btn-secondary" onclick="closeSupplierModal()">
                    <div class="btn-content">
                        <i class="fas fa-times"></i>
                        <span>Cerrar</span>
                    </div>
                    <div class="btn-bg"></div>
                </button>
            </div>
        </div>
    </div>

    @push('css')
        <style>
            /* ===== VARIABLES CSS ===== */
            :root {
                --primary-50: #f0f9ff;
                --primary-100: #e0f2fe;
                --primary-200: #bae6fd;
                --primary-300: #7dd3fc;
                --primary-400: #38bdf8;
                --primary-500: #0ea5e9;
                --primary-600: #0284c7;
                --primary-700: #0369a1;
                --primary-800: #075985;
                --primary-900: #0c4a6e;

                --secondary-50: #f8fafc;
                --secondary-100: #f1f5f9;
                --secondary-200: #e2e8f0;
                --secondary-300: #cbd5e1;
                --secondary-400: #94a3b8;
                --secondary-500: #64748b;
                --secondary-600: #475569;
                --secondary-700: #334155;
                --secondary-800: #1e293b;
                --secondary-900: #0f172a;

                --success-50: #f0fdf4;
                --success-100: #dcfce7;
                --success-500: #22c55e;
                --success-600: #16a34a;
                --success-700: #15803d;

                --warning-50: #fffbeb;
                --warning-100: #fef3c7;
                --warning-500: #f59e0b;
                --warning-600: #d97706;
                --warning-700: #b45309;

                --danger-50: #fef2f2;
                --danger-100: #fee2e2;
                --danger-500: #ef4444;
                --danger-600: #dc2626;
                --danger-700: #b91c1c;

                --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                --gradient-success: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
                --gradient-secondary: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
                --gradient-info: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);

                --shadow-sm: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
                --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
                --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
                --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);

                --border-radius-sm: 0.375rem;
                --border-radius-md: 0.5rem;
                --border-radius-lg: 0.75rem;
                --border-radius-xl: 1rem;
                --border-radius-2xl: 1.5rem;

                --transition-fast: 150ms cubic-bezier(0.4, 0, 0.2, 1);
                --transition-normal: 300ms cubic-bezier(0.4, 0, 0.2, 1);

                --spacing-xs: 0.25rem;
                --spacing-sm: 0.5rem;
                --spacing-md: 1rem;
                --spacing-lg: 1.5rem;
                --spacing-xl: 2rem;
                --spacing-2xl: 3rem;
            }

            /* ===== RESET Y BASE ===== */
            * {
                box-sizing: border-box;
            }

            body {
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                background: var(--secondary-50);
                color: var(--secondary-900);
                line-height: 1.6;
                margin: 0;
                padding: 0;
            }

            /* ===== FONDO Y CONTENEDOR PRINCIPAL ===== */
            .page-background {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background:
                    radial-gradient(circle at 20% 30%, rgba(102, 126, 234, 0.08) 0%, transparent 50%),
                    radial-gradient(circle at 80% 70%, rgba(59, 130, 246, 0.06) 0%, transparent 50%),
                    radial-gradient(circle at 40% 80%, rgba(34, 197, 94, 0.05) 0%, transparent 50%),
                    linear-gradient(135deg, var(--secondary-50) 0%, var(--secondary-100) 50%, var(--secondary-200) 100%);
                z-index: -1;
            }

            .main-container {
                max-width: 1400px;
                margin: 0 auto;
                padding: var(--spacing-lg);
                position: relative;
                z-index: 1;
            }

            /* ===== HEADER FLOTANTE ===== */
            .floating-header {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(20px);
                border: 1px solid rgba(255, 255, 255, 0.3);
                border-radius: var(--border-radius-2xl);
                box-shadow: var(--shadow-xl);
                padding: var(--spacing-xl);
                margin-bottom: var(--spacing-xl);
                position: sticky;
                top: var(--spacing-md);
                z-index: 100;
            }

            .header-content {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: var(--spacing-lg);
            }

            .header-left {
                display: flex;
                align-items: center;
                gap: var(--spacing-lg);
            }

            .header-icon-wrapper {
                position: relative;
                width: 64px;
                height: 64px;
            }

            .header-icon {
                width: 100%;
                height: 100%;
                background: var(--gradient-primary);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 1.75rem;
                box-shadow: var(--shadow-lg);
                position: relative;
                z-index: 2;
            }

            .icon-glow {
                position: absolute;
                inset: -8px;
                background: var(--gradient-primary);
                border-radius: 50%;
                opacity: 0.3;
                filter: blur(12px);
                z-index: 1;
            }

            .header-text {
                flex: 1;
            }

            .header-title {
                font-size: 2rem;
                font-weight: 800;
                color: var(--secondary-900);
                margin: 0;
                line-height: 1.2;
                background: var(--gradient-primary);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }

            .header-subtitle {
                font-size: 1rem;
                color: var(--secondary-600);
                margin-top: var(--spacing-xs);
                font-weight: 500;
            }

            .header-actions {
                display: flex;
                gap: var(--spacing-md);
            }

            /* ===== BOTONES ===== */
            .btn-glass {
                position: relative;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: var(--spacing-sm);
                padding: var(--spacing-md) var(--spacing-lg);
                border: 1px solid transparent;
                border-radius: var(--border-radius-lg);
                font-size: 0.875rem;
                font-weight: 600;
                text-decoration: none;
                cursor: pointer;
                transition: all var(--transition-normal);
                overflow: hidden;
            }

            .btn-glass:hover {
                transform: translateY(-2px);
                box-shadow: var(--shadow-lg);
            }

            .btn-secondary-glass {
                background: rgba(255, 255, 255, 0.9);
                border-color: var(--secondary-200);
                color: var(--secondary-700);
                box-shadow: var(--shadow-sm);
            }

            .btn-secondary-glass:hover {
                background: rgba(255, 255, 255, 1);
                border-color: var(--secondary-300);
                color: var(--secondary-800);
            }

            .btn-primary-glass {
                background: var(--gradient-primary);
                color: white;
                box-shadow: var(--shadow-sm);
            }

            .btn-primary-glass:hover {
                color: white;
                box-shadow: var(--shadow-md);
            }

            .btn-danger-glass {
                background: var(--gradient-danger);
                color: white;
                box-shadow: var(--shadow-sm);
            }

            .btn-danger-glass:hover {
                color: white;
                box-shadow: var(--shadow-md);
            }

            /* ===== ESTADÍSTICAS ===== */
            .stats-dashboard {
                margin-bottom: var(--spacing-xl);
            }

            .stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                gap: var(--spacing-lg);
            }

            .stat-card {
                background: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(20px);
                border: 1px solid rgba(255, 255, 255, 0.3);
                border-radius: var(--border-radius-xl);
                padding: var(--spacing-xl);
                box-shadow: var(--shadow-lg);
                transition: all var(--transition-normal);
                position: relative;
                overflow: hidden;
            }

            .stat-card:hover {
                transform: translateY(-4px);
                box-shadow: var(--shadow-xl);
            }

            .stat-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 4px;
                background: var(--gradient-primary);
            }

            .stat-icon {
                position: relative;
                width: 64px;
                height: 64px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: var(--spacing-lg);
                background: var(--gradient-primary);
                color: white;
                font-size: 1.5rem;
                box-shadow: var(--shadow-md);
            }

            .stat-glow {
                position: absolute;
                inset: -8px;
                background: var(--gradient-primary);
                border-radius: 50%;
                opacity: 0.3;
                filter: blur(12px);
            }

            .stat-content {
                flex: 1;
            }

            .stat-value {
                font-size: 2.5rem;
                font-weight: 800;
                color: var(--secondary-900);
                margin-bottom: var(--spacing-sm);
                line-height: 1;
            }

            .stat-label {
                font-size: 1.125rem;
                font-weight: 700;
                color: var(--secondary-700);
                margin-bottom: var(--spacing-xs);
            }

            .stat-trend {
                display: flex;
                align-items: center;
                gap: var(--spacing-xs);
                font-size: 0.875rem;
                color: var(--secondary-600);
                font-weight: 500;
            }

            .stat-trend i {
                color: var(--success-500);
            }

            /* ===== CONTENEDOR DE DATOS ===== */
            .data-container {
                background: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(20px);
                border: 1px solid rgba(255, 255, 255, 0.3);
                border-radius: var(--border-radius-2xl);
                box-shadow: var(--shadow-lg);
                overflow: hidden;
            }

            .data-header {
                background: var(--secondary-50);
                padding: var(--spacing-xl);
                border-bottom: 1px solid var(--secondary-200);
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: var(--spacing-lg);
            }

            .header-left {
                display: flex;
                align-items: center;
                gap: var(--spacing-lg);
            }

            .header-icon {
                width: 56px;
                height: 56px;
                background: var(--gradient-primary);
                border-radius: var(--border-radius-lg);
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 1.5rem;
            }

            .header-text h3 {
                font-size: 1.5rem;
                font-weight: 700;
                color: var(--secondary-900);
                margin: 0;
            }

            .header-text p {
                color: var(--secondary-600);
                margin: var(--spacing-xs) 0 0 0;
                font-size: 0.875rem;
            }

            .header-controls {
                display: flex;
                align-items: center;
                gap: var(--spacing-lg);
            }

            .search-box {
                position: relative;
                display: flex;
                align-items: center;
            }

            .search-box i {
                position: absolute;
                left: var(--spacing-md);
                color: var(--secondary-400);
                z-index: 1;
            }

            .search-box input {
                padding: var(--spacing-md) var(--spacing-lg);
                padding-left: 3rem;
                border: 2px solid var(--secondary-200);
                border-radius: var(--border-radius-lg);
                font-size: 0.875rem;
                background: white;
                transition: all var(--transition-fast);
                width: 300px;
            }

            .search-box input:focus {
                outline: none;
                border-color: var(--primary-500);
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            }

            .clear-btn {
                position: absolute;
                right: var(--spacing-md);
                background: none;
                border: none;
                color: var(--secondary-400);
                cursor: pointer;
                padding: var(--spacing-xs);
                border-radius: 50%;
                transition: var(--transition-fast);
            }

            .clear-btn:hover {
                background: var(--secondary-100);
                color: var(--secondary-600);
            }

            .view-toggle {
                display: flex;
                gap: var(--spacing-xs);
            }

            .view-btn {
                padding: var(--spacing-sm) var(--spacing-md);
                border: 1px solid var(--secondary-200);
                background: white;
                color: var(--secondary-600);
                border-radius: var(--border-radius-md);
                cursor: pointer;
                transition: all var(--transition-fast);
            }

            .view-btn.active {
                background: var(--primary-500);
                color: white;
                border-color: var(--primary-500);
            }

            .view-btn:hover:not(.active) {
                background: var(--secondary-50);
                color: var(--secondary-700);
            }

            .data-content {
                padding: var(--spacing-xl);
            }

            /* ===== TABLA ===== */
            .table-view {
                display: block;
            }

            .table-wrapper {
                overflow: hidden;
                border-radius: var(--border-radius-lg);
                border: 1px solid var(--secondary-200);
            }

            .modern-table {
                width: 100%;
                border-collapse: separate;
                border-spacing: 0;
                background: white;
            }

            .modern-table thead {
                background: var(--gradient-primary);
            }

            .modern-table th {
                padding: var(--spacing-lg);
                color: white;
                font-weight: 600;
                text-align: left;
                border: none;
                font-size: 0.875rem;
            }

            .modern-table tbody tr {
                transition: all var(--transition-fast);
                border-bottom: 1px solid var(--secondary-100);
            }

            .modern-table tbody tr:hover {
                background: var(--secondary-50);
                transform: scale(1.01);
            }

            .modern-table td {
                padding: var(--spacing-lg);
                vertical-align: middle;
                border: none;
            }

            .number-badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 40px;
                height: 40px;
                background: var(--gradient-primary);
                color: white;
                border-radius: 50%;
                font-weight: 700;
                font-size: 0.875rem;
            }

            .purchase-info {
                display: flex;
                align-items: center;
                gap: var(--spacing-md);
            }

            .info-icon {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                background: var(--secondary-100);
                color: var(--primary-500);
                font-size: 1rem;
            }

            .info-text {
                display: flex;
                flex-direction: column;
                gap: var(--spacing-xs);
            }

            .info-text strong {
                font-weight: 600;
                color: var(--secondary-900);
            }

            .date-main {
                font-weight: 600;
                font-size: 0.875rem;
                color: var(--secondary-900);
            }

            .time-sub {
                font-size: 0.75rem;
                color: var(--secondary-600);
                font-weight: 500;
            }

            .products-info {
                display: flex;
                flex-direction: column;
                gap: var(--spacing-sm);
            }

            .product-badge {
                display: inline-flex;
                align-items: center;
                gap: var(--spacing-sm);
                padding: var(--spacing-sm) var(--spacing-md);
                border-radius: 20px;
                font-size: 0.75rem;
                font-weight: 600;
                background: var(--gradient-primary);
                color: white;
            }

            .status-badge {
                display: inline-flex;
                align-items: center;
                gap: var(--spacing-sm);
                padding: var(--spacing-sm) var(--spacing-md);
                border-radius: 20px;
                font-weight: 600;
                font-size: 0.875rem;
            }

            .status-badge.completed {
                background: var(--gradient-success);
                color: white;
            }

            .status-badge.pending {
                background: var(--gradient-warning);
                color: white;
            }

            .actions-group {
                display: flex;
                gap: var(--spacing-sm);
            }

            .action-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: var(--spacing-sm);
                border: none;
                border-radius: 50%;
                font-weight: 600;
                text-decoration: none;
                transition: all var(--transition-fast);
                cursor: pointer;
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }

            .details-btn {
                background: var(--gradient-primary);
                color: white;
            }

            .edit-btn {
                background: var(--gradient-warning);
                color: white;
            }

            .delete-btn {
                background: var(--gradient-danger);
                color: white;
            }

            .supplier-btn {
                background: var(--gradient-info);
                color: white;
            }

            .action-btn:hover {
                transform: translateY(-2px);
                box-shadow: var(--shadow-md);
                color: white;
            }

            /* ===== TARJETAS ===== */
            .cards-view {
                display: none;
            }

            .cards-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
                gap: var(--spacing-lg);
            }

            .purchase-card {
                background: white;
                border-radius: var(--border-radius-xl);
                box-shadow: var(--shadow-lg);
                overflow: hidden;
                transition: all var(--transition-normal);
                border: 1px solid var(--secondary-200);
            }

            .purchase-card:hover {
                transform: translateY(-4px);
                box-shadow: var(--shadow-xl);
            }

            .card-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: var(--spacing-lg);
                background: var(--secondary-50);
                border-bottom: 1px solid var(--secondary-200);
            }

            .card-content {
                padding: var(--spacing-lg);
            }

            .stats-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: var(--spacing-md);
                margin-top: var(--spacing-lg);
            }

            .stat-item {
                text-align: center;
                padding: var(--spacing-md);
                background: var(--secondary-50);
                border-radius: var(--border-radius-lg);
                border: 1px solid var(--secondary-200);
            }

            .stat-item.amount {
                background: var(--gradient-success);
                color: white;
            }

            .stat-item .stat-icon {
                width: 40px;
                height: 40px;
                margin: 0 auto var(--spacing-sm);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                background: var(--primary-500);
                color: white;
                font-size: 1rem;
            }

            .stat-item.amount .stat-icon {
                background: rgba(255, 255, 255, 0.2);
            }

            .stat-item .stat-value {
                font-size: 1.25rem;
                font-weight: 700;
                margin-bottom: var(--spacing-xs);
            }

            .stat-item .stat-label {
                font-size: 0.75rem;
                font-weight: 600;
                opacity: 0.8;
            }

            .card-actions {
                padding: var(--spacing-lg);
                background: var(--secondary-50);
                border-top: 1px solid var(--secondary-200);
            }

            .card-actions .actions-group {
                justify-content: center;
            }

            /* ===== MODAL ===== */
            .modal-content {
                border-radius: var(--border-radius-xl);
                border: none;
                box-shadow: var(--shadow-xl);
                overflow: hidden;
            }

            .modal-header {
                background: var(--gradient-primary);
                color: white;
                border-bottom: none;
                padding: var(--spacing-xl);
            }

            .modal-title-section {
                display: flex;
                align-items: center;
                gap: var(--spacing-lg);
            }

            .modal-icon {
                width: 56px;
                height: 56px;
                background: rgba(255, 255, 255, 0.2);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.3);
            }

            .modal-icon i {
                font-size: 1.5rem;
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
                margin: var(--spacing-xs) 0 0 0;
            }

            .modal-close {
                background: none;
                border: none;
                color: white;
                font-size: 1.25rem;
                padding: var(--spacing-sm);
                border-radius: 50%;
                transition: var(--transition-fast);
                cursor: pointer;
            }

            .modal-close:hover {
                background: rgba(255, 255, 255, 0.2);
                transform: rotate(90deg);
            }

            .modal-body {
                padding: var(--spacing-xl);
            }

            .total-row {
                background: var(--gradient-success);
                color: white;
            }

            .total-label {
                text-align: right;
            }

            .total-content {
                display: flex;
                align-items: center;
                gap: var(--spacing-md);
                font-weight: 600;
                font-size: 1.125rem;
            }

            .total-amount {
                text-align: right;
            }

            .amount-display {
                display: flex;
                align-items: center;
                gap: var(--spacing-sm);
                font-weight: 700;
                font-size: 1.5rem;
            }

            .currency {
                font-size: 1rem;
                opacity: 0.8;
            }

            .modal-footer {
                background: var(--secondary-50);
                border-top: 1px solid var(--secondary-200);
                padding: var(--spacing-lg) var(--spacing-xl);
            }

            .btn-secondary {
                display: inline-flex;
                align-items: center;
                gap: var(--spacing-sm);
                padding: var(--spacing-md) var(--spacing-lg);
                border: none;
                border-radius: var(--border-radius-lg);
                font-weight: 600;
                text-decoration: none;
                transition: all var(--transition-normal);
                cursor: pointer;
                background: var(--secondary-500);
                color: white;
            }

            .btn-secondary:hover {
                transform: translateY(-2px);
                box-shadow: var(--shadow-md);
                color: white;
            }

            /* Estilos adicionales para el modal */
            .modal-backdrop {
                background-color: rgba(0, 0, 0, 0.5);
            }

            .modal-dialog.modal-xl {
                max-width: 90%;
            }

            .text-center {
                text-align: center;
            }

            .text-danger {
                color: var(--danger-500);
            }

            .text-right {
                text-align: right;
            }

            /* ===== MODAL DE PROVEEDOR ===== */
            .modal-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.6);
                backdrop-filter: blur(8px);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 9999;
                padding: 1rem;
            }

            .modal-container {
                background: white;
                border-radius: 24px;
                box-shadow: 0 25px 80px rgba(0, 0, 0, 0.25);
                width: 90%;
                max-width: 1000px;
                max-height: 90%;
                display: flex;
                flex-direction: column;
                overflow: hidden;
                border: 1px solid rgba(255, 255, 255, 0.1);
                animation: modalSlideIn 0.3s ease-out;
            }

            @keyframes modalSlideIn {
                from {
                    opacity: 0;
                    transform: translateY(-20px) scale(0.95);
                }
                to {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }

            .modal-header {
                background: var(--gradient-primary);
                color: white;
                padding: 2rem 2.5rem;
                position: relative;
                overflow: hidden;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .modal-header::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
                opacity: 0.3;
            }

            .modal-title {
                display: flex;
                align-items: center;
                gap: 1rem;
                font-size: 1.5rem;
                font-weight: 700;
                position: relative;
                z-index: 1;
                margin: 0;
            }

            .modal-title i {
                background: rgba(255, 255, 255, 0.2);
                padding: 0.75rem;
                border-radius: 12px;
                font-size: 1.2rem;
            }

            .modal-close {
                background: rgba(255, 255, 255, 0.15);
                border: 1px solid rgba(255, 255, 255, 0.2);
                color: white;
                padding: 0.75rem;
                border-radius: 12px;
                cursor: pointer;
                transition: all 0.3s ease;
                position: relative;
                z-index: 1;
                backdrop-filter: blur(10px);
                border: none;
                font-size: 1.2rem;
            }

            .modal-close:hover {
                background: rgba(255, 255, 255, 0.25);
                transform: scale(1.05);
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            }

            .modal-body {
                padding: 2.5rem;
                background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
                overflow-y: auto;
                flex-grow: 1;
            }

            .modal-content-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 2.5rem;
            }

            .modal-content-column {
                display: flex;
                flex-direction: column;
                gap: 1.5rem;
            }

            .modal-detail-item {
                background: white;
                border-radius: 16px;
                padding: 1.5rem;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                border: 1px solid rgba(102, 126, 234, 0.1);
                position: relative;
                overflow: hidden;
                transition: all 0.3s ease;
            }

            .modal-detail-item::before {
                content: '';
                position: absolute;
                left: 0;
                top: 0;
                bottom: 0;
                width: 4px;
                background: var(--gradient-primary);
                border-radius: 0 2px 2px 0;
            }

            .modal-detail-item:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            }

            .detail-label {
                color: #64748b;
                font-size: 0.875rem;
                font-weight: 600;
                margin-bottom: 1rem;
                display: flex;
                align-items: center;
                gap: 0.75rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .detail-label i {
                color: var(--primary-500);
                font-size: 1rem;
                background: rgba(102, 126, 234, 0.1);
                padding: 0.5rem;
                border-radius: 8px;
            }

            .detail-content {
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
            }

            .detail-row {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.5rem 0;
                border-bottom: 1px solid #f1f5f9;
            }

            .detail-row:last-child {
                border-bottom: none;
            }

            .detail-field {
                font-weight: 600;
                color: #64748b;
                font-size: 0.875rem;
            }

            .detail-value {
                font-weight: 600;
                color: var(--secondary-900);
                font-size: 0.875rem;
                text-align: right;
            }

            .products-summary-table {
                overflow-x: auto;
            }

            .summary-table {
                width: 100%;
                border-collapse: collapse;
                background: white;
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            }

            .summary-table thead {
                background: var(--gradient-primary);
                color: white;
            }

            .summary-table th {
                padding: 0.75rem;
                text-align: left;
                font-weight: 600;
                font-size: 0.875rem;
            }

            .summary-table td {
                padding: 0.75rem;
                border-bottom: 1px solid #f1f5f9;
                font-size: 0.875rem;
            }

            .summary-table tbody tr:hover {
                background: #f8fafc;
            }

            .total-row {
                background: var(--gradient-success);
                color: white;
                font-weight: 600;
            }

            .total-label {
                text-align: right;
                padding: 1rem 0.75rem;
            }

            .total-amount {
                text-align: right;
                padding: 1rem 0.75rem;
                font-size: 1.1rem;
            }

            .modal-footer {
                padding: 2rem 2.5rem;
                background: white;
                border-top: 1px solid #e2e8f0;
                display: flex;
                justify-content: flex-end;
                gap: 1rem;
            }

            .btn-modern {
                position: relative;
                overflow: hidden;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.75rem 1.5rem;
                border: 1px solid transparent;
                border-radius: 12px;
                font-size: 0.9rem;
                font-weight: 600;
                color: white;
                transition: all 0.3s ease;
                cursor: pointer;
                box-shadow: var(--shadow-medium);
                text-decoration: none;
            }

            .btn-modern:hover {
                transform: translateY(-2px);
                box-shadow: var(--shadow-heavy);
            }

            .btn-secondary {
                background: var(--secondary-500);
                border-color: transparent;
            }

            .btn-secondary:hover {
                background: var(--secondary-600);
            }

            .btn-content {
                position: relative;
                z-index: 1;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .btn-bg {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
                border-radius: 12px;
                opacity: 0;
                transition: opacity 0.3s ease;
                z-index: -1;
            }

            .btn-modern:hover .btn-bg {
                opacity: 1;
            }

            /* Responsive para el modal */
            @media (max-width: 768px) {
                .modal-container {
                    max-width: 95%;
                    border-radius: 16px;
                }

                .modal-header {
                    padding: 1rem 1.5rem;
                }

                .modal-title {
                    font-size: 1.1rem;
                }

                .modal-body {
                    padding: 1rem;
                }

                .modal-content-grid {
                    grid-template-columns: 1fr;
                    gap: 1rem;
                }

                .modal-detail-item {
                    padding: 1rem;
                }

                .detail-label {
                    font-size: 0.8rem;
                }

                .detail-value {
                    font-size: 0.8rem;
                }

                .modal-footer {
                    padding: 1rem 1.5rem;
                }
            }

            /* ===== RESPONSIVE ===== */
            @media (max-width: 1024px) {
                .main-container {
                    padding: var(--spacing-md);
                }

                .floating-header {
                    padding: var(--spacing-lg);
                }

                .header-content {
                    flex-direction: column;
                    gap: var(--spacing-lg);
                    align-items: stretch;
                }

                .header-actions {
                    justify-content: center;
                }

                .stats-grid {
                    grid-template-columns: repeat(2, 1fr);
                }

                .data-header {
                    flex-direction: column;
                    gap: var(--spacing-lg);
                    align-items: stretch;
                }

                .header-controls {
                    flex-direction: column;
                    gap: var(--spacing-md);
                }

                .search-box input {
                    width: 100%;
                }

                .cards-grid {
                    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                }
            }

            @media (max-width: 768px) {
                .header-title {
                    font-size: 1.5rem;
                }

                .stats-grid {
                    grid-template-columns: 1fr;
                }

                .cards-grid {
                    grid-template-columns: 1fr;
                }

                .stats-grid .stat-item {
                    grid-template-columns: 1fr;
                }

                .modal-title-section {
                    flex-direction: column;
                    text-align: center;
                }

                .modal-title {
                    font-size: 1.25rem;
                }

                .modern-table {
                    font-size: 0.875rem;
                }

                .modern-table th,
                .modern-table td {
                    padding: var(--spacing-md) var(--spacing-sm);
                }

                .amount-display {
                    font-size: 1.25rem;
                }
            }

            @media (max-width: 576px) {
                .main-container {
                    padding: var(--spacing-sm);
                }

                .floating-header {
                    padding: var(--spacing-md);
                }

                .header-left {
                    flex-direction: column;
                    text-align: center;
                }

                .header-actions {
                    flex-direction: column;
                }

                .btn-glass {
                    width: 100%;
                    justify-content: center;
                }

                .data-content {
                    padding: var(--spacing-md);
                }

                .purchase-info {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: var(--spacing-sm);
                }

                .actions-group {
                    flex-wrap: wrap;
                    justify-content: center;
                }
            }

            /* ===== ANIMACIONES ===== */
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

            .stat-card,
            .purchase-card {
                animation: fadeInUp 0.6s ease-out;
            }

            .stat-card:nth-child(1) {
                animation-delay: 0.1s;
            }

            .stat-card:nth-child(2) {
                animation-delay: 0.2s;
            }

            .stat-card:nth-child(3) {
                animation-delay: 0.3s;
            }

            .stat-card:nth-child(4) {
                animation-delay: 0.4s;
            }

            /* ===== GRADIENTES ADICIONALES ===== */
            .gradient-danger {
                background: linear-gradient(135deg, var(--danger-500) 0%, var(--danger-600) 100%);
            }

            .gradient-warning {
                background: linear-gradient(135deg, var(--warning-500) 0%, var(--warning-600) 100%);
            }
        </style>
    @endpush


    @push('js')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Variables globales
                let currentView = 'table';
                let searchTerm = '';

                // Elementos del DOM
                const tableView = document.getElementById('tableView');
                const cardsView = document.getElementById('cardsView');
                const viewButtons = document.querySelectorAll('.view-btn');
                const searchInput = document.getElementById('purchasesSearch');
                const clearSearchBtn = document.getElementById('clearSearch');
                const purchaseCards = document.querySelectorAll('.purchase-card');
                const tableRows = document.querySelectorAll('.modern-table tbody tr');

                // Cambiar vista (tabla/tarjetas)
                viewButtons.forEach(btn => {
                    btn.addEventListener('click', function() {
                        const view = this.dataset.view;

                        // Actualizar botones
                        viewButtons.forEach(b => b.classList.remove('active'));
                        this.classList.add('active');

                        // Cambiar vista
                        if (view === 'table') {
                            tableView.style.display = 'block';
                            cardsView.style.display = 'none';
                            currentView = 'table';
                        } else {
                            tableView.style.display = 'none';
                            cardsView.style.display = 'block';
                            currentView = 'cards';
                        }

                        // Aplicar filtro actual
                        applySearch();
                    });
                });

                // Búsqueda en tiempo real
                searchInput.addEventListener('input', function() {
                    searchTerm = this.value.toLowerCase();
                    applySearch();
                });

                // Limpiar búsqueda
                clearSearchBtn.addEventListener('click', function() {
                    searchInput.value = '';
                    searchTerm = '';
                    applySearch();
                });

                // Función para aplicar búsqueda
                function applySearch() {
                    if (currentView === 'table') {
                        // Búsqueda en tabla
                        tableRows.forEach(row => {
                            const text = row.textContent.toLowerCase();
                            if (text.includes(searchTerm)) {
                                row.style.display = '';
                            } else {
                                row.style.display = 'none';
                            }
                        });
                    } else {
                        // Búsqueda en tarjetas
                        purchaseCards.forEach(card => {
                            const text = card.textContent.toLowerCase();
                            if (text.includes(searchTerm)) {
                                card.style.display = '';
                            } else {
                                card.style.display = 'none';
                            }
                        });
                    }
                }

                // Ver detalles de compra
                document.querySelectorAll('.view-details').forEach(btn => {
                    btn.addEventListener('click', function() {
                        console.log('Botón de detalles clickeado');
                        const purchaseId = this.dataset.id;
                        console.log('ID de compra:', purchaseId);
                        loadPurchaseDetails(purchaseId);
                    });
                });

                // Cargar detalles de la compra
                function loadPurchaseDetails(purchaseId) {
                    console.log('Iniciando carga de detalles para compra:', purchaseId);
                    const tableBody = document.getElementById('purchaseDetailsTableBody');
                    tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Cargando...</td></tr>';

                    // Mostrar el modal primero
                    $('#purchaseDetailsModal').modal('show');
                    
                    // Mostrar la sección de productos distribuidos en el modal de detalles
                    const productsSection = document.getElementById('productsDistributedSection');
                    if (productsSection) {
                        productsSection.style.display = 'block';
                    }

                    fetch(`/purchases/${purchaseId}/details`, {
                        method: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => {
                        console.log('Respuesta del servidor:', response);
                        return response.json();
                    })
                    .then(data => {
                        console.log('Datos recibidos:', data);
                        if (data.success) {
                            let total = 0;
                            tableBody.innerHTML = '';

                            data.details.forEach(detail => {
                                const quantity = parseFloat(detail.quantity);
                                const price = parseFloat(detail.product_price);
                                const subtotal = quantity * price;
                                total += subtotal;

                                const row = document.createElement('tr');
                                row.innerHTML = `
                                    <td>${detail.product.code || ''}</td>
                                    <td>${detail.product.name || ''}</td>
                                    <td>${detail.product.category || 'Sin categoría'}</td>
                                    <td class="text-center">${quantity}</td>
                                    <td class="text-right">{{ $currency->symbol }} ${price.toFixed(2)}</td>
                                    <td class="text-right">{{ $currency->symbol }} ${subtotal.toFixed(2)}</td>
                                `;
                                tableBody.appendChild(row);
                            });

                            document.getElementById('modalTotal').textContent = total.toFixed(2);
                        } else {
                            tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error al cargar los detalles</td></tr>';
                            Swal.fire('Error', data.message || 'Error al cargar los detalles', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error en fetch:', error);
                        console.error('URL intentada:', `/purchases/${purchaseId}/details`);
                        tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error de conexión</td></tr>';
                        Swal.fire('Error', 'No se pudieron cargar los detalles: ' + error.message, 'error');
                    });
                }

                // Eliminar compra
                document.querySelectorAll('.delete-purchase').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const purchaseId = this.dataset.id;

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
                                deletePurchase(purchaseId);
                            }
                        });
                    });
                });

                // Función para eliminar compra
                function deletePurchase(purchaseId) {
                    fetch(`/purchases/delete/${purchaseId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content'),
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    title: '¡Eliminado!',
                                    text: data.message,
                                    icon: 'success'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error', data.message, 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire('Error', 'No se pudo eliminar la compra', 'error');
                        });
                }

                // Efectos de hover para botones
                document.querySelectorAll('.btn-glass').forEach(btn => {
                    btn.addEventListener('mouseenter', function() {
                        this.style.transform = 'translateY(-2px)';
                    });

                    btn.addEventListener('mouseleave', function() {
                        this.style.transform = 'translateY(0)';
                    });
                });

                // Animaciones de entrada
                const observerOptions = {
                    threshold: 0.1,
                    rootMargin: '0px 0px -50px 0px'
                };

                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.style.opacity = '1';
                            entry.target.style.transform = 'translateY(0)';
                        }
                    });
                }, observerOptions);

                                 // Observar elementos para animaciones
                 document.querySelectorAll('.stat-card, .purchase-card').forEach(el => {
                     el.style.opacity = '0';
                     el.style.transform = 'translateY(20px)';
                     el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                     observer.observe(el);
                 });
             });

             // Función para cerrar el modal
             function closeModal() {
                 $('#purchaseDetailsModal').modal('hide');
             }

                         // Eventos del modal
            $('#purchaseDetailsModal').on('hidden.bs.modal', function () {
                // Limpiar contenido cuando se cierra el modal
                document.getElementById('purchaseDetailsTableBody').innerHTML = '';
                document.getElementById('modalTotal').textContent = '0.00';
                
                // Ocultar la sección de productos distribuidos
                const productsSection = document.getElementById('productsDistributedSection');
                if (productsSection) {
                    productsSection.style.display = 'none';
                }
            });

             // Función para mostrar información del proveedor
             function showSupplierInfo(supplierId) {
                 console.log('Mostrando información del proveedor:', supplierId);
                 
                 // Mostrar el modal
                 document.getElementById('supplierInfoModal').style.display = 'flex';
                 
                 // Aquí puedes hacer una petición AJAX para obtener los datos del proveedor
                 // Por ahora, mostraremos datos de ejemplo
                 loadSupplierData(supplierId);
             }

             // Función para cargar datos del proveedor
             async function loadSupplierData(supplierId) {
                 try {
                     const response = await fetch(`/suppliers/${supplierId}`);
                     const data = await response.json();
                     
                     if (data.status === 'success') {
                         // Llenar datos de la empresa
                         document.getElementById('modalCompanyName').textContent = data.supplier.company_name || 'N/A';
                         document.getElementById('modalCompanyEmail').textContent = data.supplier.company_email || 'N/A';
                         document.getElementById('modalCompanyPhone').textContent = data.supplier.company_phone || 'N/A';
                         document.getElementById('modalCompanyAddress').textContent = data.supplier.company_address || 'N/A';
                         
                         // Llenar datos del contacto
                         document.getElementById('modalContactName').textContent = data.supplier.supplier_name || 'N/A';
                         document.getElementById('modalContactPhone').textContent = data.supplier.supplier_phone || 'N/A';
                         
                         // Cargar productos del proveedor
                         loadSupplierProducts(supplierId);
                     } else {
                         console.error('Error al cargar datos del proveedor:', data.message);
                     }
                 } catch (error) {
                     console.error('Error en la petición:', error);
                     // Mostrar datos de ejemplo si hay error
                     showExampleData();
                 }
             }

             // Función para cargar productos del proveedor
             async function loadSupplierProducts(supplierId) {
                 try {
                     const response = await fetch(`/suppliers/${supplierId}/products`);
                     const data = await response.json();
                     
                     const tableBody = document.getElementById('modalProductsTableBody');
                     let total = 0;
                     
                     if (data.status === 'success' && data.products.length > 0) {
                         tableBody.innerHTML = '';
                         
                         data.products.forEach(product => {
                             const row = document.createElement('tr');
                             row.innerHTML = `
                                 <td>${product.name}</td>
                                 <td>${product.quantity || 0}</td>
                                 <td>{{ $currency->symbol }} ${(product.purchase_price || 0).toFixed(2)}</td>
                                 <td>{{ $currency->symbol }} ${((product.quantity || 0) * (product.purchase_price || 0)).toFixed(2)}</td>
                             `;
                             tableBody.appendChild(row);
                             
                             total += (product.quantity || 0) * (product.purchase_price || 0);
                         });
                         
                         document.getElementById('modalTotalAmount').textContent = `{{ $currency->symbol }} ${total.toFixed(2)}`;
                     } else {
                         tableBody.innerHTML = '<tr><td colspan="4" class="text-center">No hay productos registrados para este proveedor</td></tr>';
                         document.getElementById('modalTotalAmount').textContent = '{{ $currency->symbol }} 0.00';
                     }
                 } catch (error) {
                     console.error('Error al cargar productos:', error);
                     showExampleProducts();
                 }
             }

             // Función para mostrar datos de ejemplo
             function showExampleData() {
                 document.getElementById('modalCompanyName').textContent = 'Empresa Ejemplo S.A.';
                 document.getElementById('modalCompanyEmail').textContent = 'contacto@empresa.com';
                 document.getElementById('modalCompanyPhone').textContent = '+1 234 567 8900';
                 document.getElementById('modalCompanyAddress').textContent = '123 Calle Principal, Ciudad';
                 document.getElementById('modalContactName').textContent = 'Juan Pérez';
                 document.getElementById('modalContactPhone').textContent = '+1 234 567 8901';
                 
                 showExampleProducts();
             }

             // Función para mostrar productos de ejemplo
             function showExampleProducts() {
                 const tableBody = document.getElementById('modalProductsTableBody');
                 tableBody.innerHTML = `
                     <tr>
                         <td>Producto A</td>
                         <td>10</td>
                         <td>{{ $currency->symbol }} 25.00</td>
                         <td>{{ $currency->symbol }} 250.00</td>
                     </tr>
                     <tr>
                         <td>Producto B</td>
                         <td>5</td>
                         <td>{{ $currency->symbol }} 15.50</td>
                         <td>{{ $currency->symbol }} 77.50</td>
                     </tr>
                 `;
                 
                 document.getElementById('modalTotalAmount').textContent = '{{ $currency->symbol }} 327.50';
             }

             // Función para cerrar el modal de proveedor
             function closeSupplierModal() {
                 document.getElementById('supplierInfoModal').style.display = 'none';
             }

             // Cerrar modal al hacer clic fuera
             document.getElementById('supplierInfoModal').addEventListener('click', function(e) {
                 if (e.target === this) {
                     closeSupplierModal();
                 }
             });
        </script>
    @endpush
@endsection
