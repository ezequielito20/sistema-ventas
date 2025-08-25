@extends('layouts.app')

@section('title', 'Gestión de Compras')

@section('content')
    <!-- Background Pattern -->
    <div class="page-background"></div>

    <!-- Main Container -->
    <div class="main-container" id="purchasesRoot" data-currency-symbol="{{ $currency->symbol }}">
        <!-- Page Header with System Gradient -->
        <div class="system-gradient-header">
            <div class="page-header">
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
        </div>
        <!-- Stats Dashboard -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-4 gap-2 sm:gap-3 mb-6">
            <!-- Productos Únicos -->
            <x-dashboard-widget 
                title="Productos Únicos"
                value="{{ $totalPurchases }}"
                valueType="number"
                icon="fas fa-boxes"
                trend="Comprados"
                trendIcon="fas fa-shopping-cart"
                trendColor="text-green-300"
                subtitle="Productos únicos comprados"
                subtitleIcon="fas fa-box"
                gradientFrom="from-blue-500"
                gradientTo="to-blue-600"
                progressWidth="100%"
                progressGradientFrom="from-blue-400"
                progressGradientTo="to-blue-500"
            />

            <!-- Total Invertido -->
            <x-dashboard-widget 
                title="Total Invertido"
                value="{{ $totalAmount }}"
                valueType="currency"
                icon="fas fa-chart-line"
                trend="Capital"
                trendIcon="fas fa-dollar-sign"
                trendColor="text-green-300"
                subtitle="Capital comprometido"
                subtitleIcon="fas fa-chart-bar"
                gradientFrom="from-green-500"
                gradientTo="to-emerald-600"
                progressWidth="100%"
                progressGradientFrom="from-green-400"
                progressGradientTo="to-emerald-500"
            />

            <!-- Compras del Mes -->
            <x-dashboard-widget 
                title="Compras del Mes"
                value="{{ $monthlyPurchases }}"
                valueType="number"
                icon="fas fa-calendar-check"
                trend="Recientes"
                trendIcon="fas fa-calendar-month"
                trendColor="text-yellow-300"
                subtitle="Actividad reciente"
                subtitleIcon="fas fa-clock"
                gradientFrom="from-yellow-500"
                gradientTo="to-orange-500"
                progressWidth="100%"
                progressGradientFrom="from-yellow-400"
                progressGradientTo="to-orange-400"
            />

            <!-- Entregas Pendientes -->
            <x-dashboard-widget 
                title="Entregas Pendientes"
                value="{{ $pendingDeliveries }}"
                valueType="number"
                icon="fas fa-hourglass-half"
                trend="Pendientes"
                trendIcon="fas fa-clock"
                trendColor="text-red-300"
                subtitle="Por entregar"
                subtitleIcon="fas fa-truck"
                gradientFrom="from-red-500"
                gradientTo="to-pink-600"
                progressWidth="100%"
                progressGradientFrom="from-red-400"
                progressGradientTo="to-pink-500"
            />
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
                            aria-label="Buscar compras" autocomplete="off" value="{{ request('search') }}">
                        <button type="button" id="clearSearch" class="clear-btn" aria-label="Limpiar búsqueda">
                            <i class="fas fa-times" aria-hidden="true"></i>
                        </button>
                    </div>
                    
                    <!-- Product Filter Select -->
                    <div class="product-filter-container">
                       
                        <div class="relative" 
                             x-data="{ 
                                 isOpen: false, 
                                 searchTerm: '', 
                                 filteredProducts: @js($products),
                                 selectedProductName: 'Todos los productos',
                                 selectedProductId: '',
                                 filterProducts() {
                                     if (!this.searchTerm) {
                                         this.filteredProducts = @js($products);
                                         return;
                                     }
                                     const term = this.searchTerm.toLowerCase();
                                     this.filteredProducts = @js($products).filter(product => 
                                         product.name.toLowerCase().includes(term) || 
                                         product.code.toLowerCase().includes(term) ||
                                         (product.category && product.category.name.toLowerCase().includes(term))
                                     );
                                 },
                                 selectProduct(product) {
                                     if (product) {
                                         this.selectedProductName = product.name;
                                         this.selectedProductId = product.id;
                                     } else {
                                         this.selectedProductName = 'Todos los productos';
                                         this.selectedProductId = '';
                                     }
                                     this.isOpen = false;
                                     this.searchTerm = '';
                                     this.filteredProducts = @js($products);
                                     // Trigger filter event
                                     window.purchasesIndex.filterByProduct(this.selectedProductId);
                                 }
                             }" 
                             @click.away="isOpen = false">
                            
                            <div class="filter-input-wrapper">
                                <div class="filter-input-icon">
                                    <i class="fas fa-box"></i>
                                </div>
                                
                                <!-- Select Button -->
                                <button type="button" 
                                        @click="isOpen = !isOpen; if (isOpen) { $nextTick(() => $refs.productSearch.focus()) }"
                                        class="filter-input w-full text-left flex items-center justify-between">
                                    <span class="block truncate" x-text="selectedProductName"></span>
                                    <svg class="h-4 w-4 text-gray-400 transition-transform duration-200 ml-2" 
                                         :class="{ 'rotate-180': isOpen }" 
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <div class="filter-input-border"></div>
                            </div>

                            <!-- Dropdown -->
                            <div x-show="isOpen" 
                                 x-cloak
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute z-[9999] mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg max-h-60 overflow-auto"
                                 style="z-index: 9999 !important;">
                                
                                <!-- Search Input -->
                                <div class="p-2 border-b border-gray-100">
                                    <input type="text" 
                                           x-ref="productSearch"
                                           x-model="searchTerm" 
                                           @input="filterProducts()"
                                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           placeholder="Buscar producto...">
                                </div>
                                
                                <!-- Options -->
                                <div class="py-1">
                                    <!-- All products option -->
                                    <button type="button" 
                                            @click="selectProduct(null)"
                                            class="w-full px-4 py-2.5 text-left text-sm hover:bg-gray-50 flex items-center gap-3 transition-colors duration-150"
                                            :class="{ 'bg-blue-50 text-blue-700 font-medium': selectedProductId === '' }">
                                        <i class="fas fa-list text-gray-400"></i>
                                        <span>Todos los productos</span>
                                    </button>
                                    
                                    <!-- Product options -->
                                    <template x-for="product in filteredProducts" :key="product.id">
                                        <button type="button" 
                                                @click="selectProduct(product)"
                                                class="w-full px-4 py-2.5 text-left text-sm hover:bg-gray-50 flex items-center gap-3 transition-colors duration-150"
                                                :class="{ 'bg-blue-50 text-blue-700 font-medium': selectedProductId == product.id }">
                                            <i class="fas fa-box text-gray-400"></i>
                                            <div class="flex flex-col">
                                                <span x-text="product.name" class="font-medium"></span>
                                                <span x-text="product.code + ' • ' + (product.category ? product.category.name : 'Sin categoría')" class="text-xs text-gray-500"></span>
                                            </div>
                                        </button>
                                    </template>
                                    
                                    <!-- No results -->
                                    <div x-show="filteredProducts.length === 0" class="px-4 py-2 text-sm text-gray-500 text-center">
                                        No se encontraron productos
                                    </div>
                                </div>
                            </div>
                        </div>
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
                                    <tr data-purchase-id="{{ $purchase->id }}">
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
                                                @foreach($purchase->details as $detail)
                                                    <div class="product-badge" data-product-id="{{ $detail->product_id }}">
                                                        <i class="fas fa-boxes"></i>
                                                        <span>{{ $detail->product->name ?? 'Producto' }}</span>
                                                    </div>
                                                @endforeach
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
                            <div class="purchase-card-modern" data-purchase-id="{{ $purchase->id }}">
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
                                            @foreach($purchase->details as $detail)
                                                <div class="product-data" data-product-id="{{ $detail->product_id }}" style="display: none;"></div>
                                            @endforeach
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
        <div class="modal-overlay" id="purchaseDetailsModal" role="dialog"
            aria-labelledby="purchaseDetailsTitle" aria-modal="true">
            <div class="modal-container">
                <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; color: white !important;">
                    <h3 class="modal-title" id="purchaseDetailsTitle" style="color: white !important;">
                        <i class="fas fa-list-alt mr-2" aria-hidden="true"></i>
                        Detalle de la Compra
                    </h3>
                    <button type="button" class="modal-close" onclick="closePurchaseModal()" aria-label="Cerrar modal" 
                            style="color: white !important; background: rgba(255, 255, 255, 0.1) !important; border: 1px solid rgba(255, 255, 255, 0.2) !important; border-radius: 8px !important; padding: 8px 12px !important; transition: all 0.3s ease !important;"
                            onmouseover="this.style.background='rgba(255, 255, 255, 0.2)'; this.style.transform='scale(1.05)'"
                            onmouseout="this.style.background='rgba(255, 255, 255, 0.1)'; this.style.transform='scale(1)'">
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
                        aria-label="Cerrar modal de detalles"
                        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; 
                               color: white !important; 
                               border: none !important;
                               box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3) !important;
                               transition: all 0.3s ease !important;
                               text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1) !important;"
                        onmouseover="this.style.background='linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%)'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(102, 126, 234, 0.4)'"
                        onmouseout="this.style.background='linear-gradient(135deg, #667eea 0%, #764ba2 100%)'; this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(102, 126, 234, 0.3)'"
                        onmousedown="this.style.transform='translateY(0) scale(0.98)'"
                        onmouseup="this.style.transform='translateY(-2px) scale(1)'">
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

    <!-- Paginación personalizada -->
    @if($purchases->hasPages())
        <div class="mt-8 px-6">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="custom-pagination">
                    <div class="pagination-info">
                        <span id="paginationInfo">Mostrando {{ $purchases->firstItem() ?? 0 }}-{{ $purchases->lastItem() ?? 0 }} de {{ $purchases->total() }} compras</span>
                    </div>
                    <div class="pagination-controls">
                        @if($purchases->onFirstPage())
                            <button class="pagination-btn" disabled>
                                <i class="fas fa-chevron-left"></i>
                                Anterior
                            </button>
                        @else
                            <a href="{{ $purchases->previousPageUrl() }}" class="pagination-btn">
                                <i class="fas fa-chevron-left"></i>
                                Anterior
                            </a>
                        @endif
                        
                        <div class="page-numbers">
                            @foreach($purchases->getUrlRange(1, $purchases->lastPage()) as $page => $url)
                                @if($page == $purchases->currentPage())
                                    <span class="page-number active">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" class="page-number">{{ $page }}</a>
                                @endif
                            @endforeach
                        </div>
                        
                        @if($purchases->hasMorePages())
                            <a href="{{ $purchases->nextPageUrl() }}" class="pagination-btn">
                                Siguiente
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        @else
                            <button class="pagination-btn" disabled>
                                Siguiente
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif



    @push('css')
        <link rel="stylesheet" href="{{ asset('css/admin/purchases/index.css') }}">
    @endpush


    @push('js')
        <script src="{{ asset('js/admin/purchases/index.js') }}" defer></script>
    @endpush
@endsection
