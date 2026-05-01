{{-- Contenedor Dinámico para Lista de Ventas (Tabla/Tarjetas) y Paginación --}}
{{-- Vista de tabla moderna - mismas clases CSS que renderTableFromData() en index.js --}}
<div class="table-view">
    <div class="modern-table-container">
        <table class="modern-table">
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
            <tbody id="salesTableBody">
                @forelse ($sales as $index => $sale)
                    <tr class="table-row">
                        <td>
                            <div class="row-number">{{ $sales->firstItem() + $index }}</div>
                        </td>
                        <td>
                            <div class="customer-info">
                                <div class="customer-avatar">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                                <div class="customer-details">
                                    <span
                                        class="customer-name">{{ $sale->customer->name ?? 'Cliente no especificado' }}</span>
                                    <span class="customer-email">{{ $sale->customer->email ?? 'Sin email' }}</span>
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
                                    <span>{{ $sale->products_count }} únicos</span>
                                </div>
                                <div class="product-badge total">
                                    <i class="fas fa-cubes"></i>
                                    <span>{{ $sale->total_quantity }} totales</span>
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
                            <div style="display: flex; justify-content: center; align-items: center;">
                                @if ($permissions['can_show'])
                                    <button type="button" class="btn-modern btn-primary view-details"
                                        onclick="window.salesSPAInstance && window.salesSPAInstance.showSaleDetails({{ $sale->id }})"
                                        title="Ver detalles">
                                        <i class="fas fa-list"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="action-buttons">
                                @if ($permissions['can_edit'])
                                    <button type="button" class="btn-action btn-edit"
                                        onclick="window.location.href='{{ route('admin.sales.edit', $sale->id) }}'"
                                        title="Editar venta">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                @endif
                                @if ($permissions['can_destroy'])
                                    <button type="button" class="btn-action btn-delete"
                                        onclick="window.salesSPAInstance && window.salesSPAInstance.deleteSale({{ $sale->id }})"
                                        title="Eliminar venta">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                                @if ($permissions['can_print'])
                                    <a href="{{ route('admin.sales.print', $sale->id) }}" target="_blank" class="btn-action print" title="Imprimir">
                                        <i class="fas fa-print"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-shopping-bag text-gray-300 text-4xl mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No hay ventas registradas</h3>
                                <p class="text-gray-500">Comienza registrando tu primera venta</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Vista de tarjetas moderna - mismas clases CSS que renderCardsFromData() en index.js --}}
<div class="cards-view" style="display:none;">
    <div class="modern-cards-grid" id="salesCardsGrid">
        @forelse ($sales as $index => $sale)
            <div class="modern-sale-card">
                <div class="sale-card-header">
                    <div class="sale-number">#{{ str_pad($sales->firstItem() + $index, 3, '0', STR_PAD_LEFT) }}</div>
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
                            <h4 class="customer-name">{{ $sale->customer->name ?? 'Cliente no especificado' }}</h4>
                            <p class="customer-phone">{{ $sale->customer->phone ?? 'Sin teléfono' }}</p>
                        </div>
                    </div>

                    <div class="sale-details">
                        <div class="detail-row">
                            <div class="detail-label">
                                <i class="fas fa-calendar-alt"></i>
                                <span>Fecha</span>
                            </div>
                            <div class="detail-value">
                                {{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y H:i') }}</div>
                        </div>

                        <div class="detail-row">
                            <div class="detail-label">
                                <i class="fas fa-boxes"></i>
                                <span>Productos</span>
                            </div>
                            <div class="detail-value">
                                <div class="product-badges">
                                    <span class="mini-badge unique">{{ $sale->products_count }} únicos</span>
                                    <span class="mini-badge total">{{ $sale->total_quantity }} totales</span>
                                </div>
                            </div>
                        </div>

                        <div class="detail-row total-row">
                            <div class="detail-label">
                                <i class="fas fa-dollar-sign"></i>
                                <span>Total</span>
                            </div>
                            <div class="detail-value total-amount">{{ $currency->symbol }}
                                {{ number_format($sale->total_price, 2) }}</div>
                        </div>
                    </div>
                </div>

                <div class="sale-card-footer">
                    @if ($permissions['can_show'])
                        <button type="button" class="btn-card-primary"
                            onclick="window.salesSPAInstance && window.salesSPAInstance.showSaleDetails({{ $sale->id }})">
                            <i class="fas fa-list"></i>
                        </button>
                    @endif

                    <div class="card-actions">
                        @if ($permissions['can_edit'])
                            <button type="button" class="btn-card-action btn-edit"
                                onclick="window.location.href='{{ route('admin.sales.edit', $sale->id) }}'"
                                title="Editar venta">
                                <i class="fas fa-edit"></i>
                            </button>
                        @endif
                        @if ($permissions['can_destroy'])
                            <button type="button" class="btn-card-action btn-delete"
                                onclick="window.salesSPAInstance && window.salesSPAInstance.deleteSale({{ $sale->id }})"
                                title="Eliminar venta">
                                <i class="fas fa-trash"></i>
                            </button>
                        @endif
                        @if ($permissions['can_print'])
                            <a href="{{ route('admin.sales.print', $sale->id) }}" target="_blank" class="btn-card-action print" title="Imprimir venta">
                                <i class="fas fa-print"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="text-center py-12">
                    <i class="fas fa-shopping-bag text-gray-300 text-6xl mb-4"></i>
                    <h3 class="text-xl font-medium text-gray-900 mb-2">No hay ventas registradas</h3>
                    <p class="text-gray-500">Comienza registrando tu primera venta</p>
                </div>
            </div>
        @endforelse
    </div>
</div>

{{-- Paginación Inteligente --}}
@include('partials.smart-pagination', ['items' => $sales, 'label' => 'ventas'])
