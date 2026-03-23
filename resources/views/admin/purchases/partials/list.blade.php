{{-- Contenedor Dinámico para Lista de Compras (Tabla/Tarjetas) y Paginación --}}
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
                <div class="relative" x-data="{
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
                        if (window.purchasesIndex) window.purchasesIndex.filterByProduct(this.selectedProductId);
                    }
                }" @click.away="isOpen = false">

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
                                :class="{ 'rotate-180': isOpen }" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7">
                                </path>
                            </svg>
                        </button>
                        <div class="filter-input-border"></div>
                    </div>

                    <!-- Dropdown -->
                    <div x-show="isOpen" x-cloak x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute z-[9999] mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg max-h-60 overflow-auto"
                        style="z-index: 9999 !important;">

                        <!-- Search Input -->
                        <div class="p-2 border-b border-gray-100">
                            <input type="text" x-ref="productSearch" x-model="searchTerm" @input="filterProducts()"
                                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Buscar producto...">
                        </div>

                        <!-- Options -->
                        <div class="py-1">
                            <!-- All products option -->
                            <button type="button" @click="selectProduct(null)"
                                class="w-full px-4 py-2.5 text-left text-sm hover:bg-gray-50 flex items-center gap-3 transition-colors duration-150"
                                :class="{ 'bg-blue-50 text-blue-700 font-medium': selectedProductId === '' }">
                                <i class="fas fa-list text-gray-400"></i>
                                <span>Todos los productos</span>
                            </button>

                            <!-- Product options -->
                            <template x-for="product in filteredProducts" :key="product.id">
                                <button type="button" @click="selectProduct(product)"
                                    class="w-full px-4 py-2.5 text-left text-sm hover:bg-gray-50 flex items-center gap-3 transition-colors duration-150"
                                    :class="{
                                        'bg-blue-50 text-blue-700 font-medium': selectedProductId == product
                                            .id
                                    }">
                                    <i class="fas fa-box text-gray-400"></i>
                                    <div class="flex flex-col">
                                        <span x-text="product.name" class="font-medium"></span>
                                        <span
                                            x-text="product.code + ' • ' + (product.category ? product.category.name : 'Sin categoría')"
                                            class="text-xs text-gray-500"></span>
                                    </div>
                                </button>
                            </template>
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
                            <th scope="col" role="columnheader">
                                <div class="th-content">#</div>
                            </th>
                            <th scope="col" role="columnheader">
                                <div class="th-content"><i class="fas fa-receipt"></i> Recibo de Pago</div>
                            </th>
                            <th scope="col" role="columnheader">
                                <div class="th-content"><i class="fas fa-calendar-alt"></i> Fecha</div>
                            </th>
                            <th scope="col" role="columnheader">
                                <div class="th-content"><i class="fas fa-boxes"></i> Productos</div>
                            </th>
                            <th scope="col" role="columnheader">
                                <div class="th-content"><i class="fas fa-dollar-sign"></i> Monto Total</div>
                            </th>
                            <th scope="col" role="columnheader">
                                <div class="th-content"><i class="fas fa-check-circle"></i> Estado</div>
                            </th>
                            <th scope="col" role="columnheader">
                                <div class="th-content"><i class="fas fa-cogs"></i> Acciones</div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($purchases as $purchase)
                            <tr class="table-row" data-purchase-id="{{ $purchase->id }}">
                                <td>
                                    <div class="number-badge">{{ $purchases->firstItem() + $loop->index }}</div>
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
                                        @foreach ($purchase->details as $detail)
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
                                            <strong>{{ $currency->symbol }}
                                                {{ number_format($purchase->total_price, 2) }}</strong>
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
                                    <div class="actions-group" role="group">
                                        @if ($permissions['can_show'])
                                            <button type="button" class="action-btn details-btn view-details"
                                                data-id="{{ $purchase->id }}" title="Ver Detalles">
                                                <i class="fas fa-list"></i>
                                            </button>
                                        @endif
                                        @if ($permissions['can_edit'])
                                            <a href="{{ route('admin.purchases.edit', $purchase->id) }}"
                                                class="action-btn edit-btn" title="Editar Compra">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        @if ($permissions['can_destroy'])
                                            <button type="button" class="action-btn delete-btn delete-purchase"
                                                data-id="{{ $purchase->id }}" title="Eliminar Compra">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <i class="fas fa-shopping-basket"></i>
                                        <p>No se encontraron compras registradas</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Cards View -->
        <div class="cards-view" id="cardsView" style="display: none;">
            <div class="cards-grid">
                @forelse ($purchases as $purchase)
                    <div class="purchase-card-modern" data-purchase-id="{{ $purchase->id }}">
                        <div class="card-header-modern">
                            <div class="purchase-number">
                                <div class="number-circle">
                                    <span>{{ $purchases->firstItem() + $loop->index }}</span>
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

                        <div class="card-body-modern">
                            <div class="purchase-header-info">
                                <div class="receipt-info">
                                    <div class="receipt-icon">
                                        <i class="fas fa-receipt"></i>
                                    </div>
                                    <div class="receipt-details">
                                        <h3 class="receipt-number">{{ $purchase->payment_receipt ?: 'Sin recibo' }}
                                        </h3>
                                        <div class="purchase-date">
                                            <i class="fas fa-calendar-alt"></i>
                                            <span>{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d/m/Y H:i') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="purchase-stats-grid">
                                <div class="stat-box products">
                                    <div class="stat-icon-wrapper">
                                        <i class="fas fa-boxes"></i>
                                    </div>
                                    <div class="stat-info">
                                        <div class="stat-number">{{ $purchase->details->count() }}</div>
                                        <div class="stat-text">Productos<br>Únicos</div>
                                    </div>
                                    @foreach ($purchase->details as $detail)
                                        <div class="product-data" data-product-id="{{ $detail->product_id }}"
                                            style="display: none;"></div>
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
                                            {{ $currency->symbol }}{{ number_format($purchase->total_price, 2) }}
                                        </div>
                                        <div class="stat-text">Monto<br>Total</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-actions-modern" role="group">
                            @if ($permissions['can_show'])
                                <button type="button" class="modern-action-btn primary view-details"
                                    data-id="{{ $purchase->id }}" title="Ver Detalles">
                                    <i class="fas fa-eye"></i>
                                </button>
                            @endif
                            @if ($permissions['can_edit'])
                                <a href="{{ route('admin.purchases.edit', $purchase->id) }}"
                                    class="modern-action-btn secondary" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endif
                            @if ($permissions['can_destroy'])
                                <button type="button" class="modern-action-btn danger delete-purchase"
                                    data-id="{{ $purchase->id }}" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-12 text-center text-gray-500">
                        <div class="empty-state">
                            <i class="fas fa-shopping-basket text-5xl mb-4 text-gray-200"></i>
                            <p class="text-lg">No se encontraron compras registradas</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
    {{-- Paginación inteligente --}}
    @include('partials.smart-pagination', ['items' => $purchases, 'label' => 'compras'])
</div>
