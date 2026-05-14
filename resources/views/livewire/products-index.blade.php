@php($productFormReturnUrl = \App\Support\ProductListingReturnUrl::current(request()))
<div
    class="space-y-6"
    wire:key="products-index-root"
    x-data="{
            detailOpen: false,
            detailLoading: false,
            detailError: null,
            p: null,
            productShowBase: @js(url('/products')),
            productDestroyBase: @js(url('/products/delete')),
            csrf: document.querySelector('meta[name=csrf-token]')?.getAttribute('content'),
            async openDetail(id) {
                this.detailOpen = true;
                this.detailLoading = true;
                this.detailError = null;
                this.p = null;
                try {
                    const response = await fetch(`${this.productShowBase}/${id}`, {
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    const data = await response.json();
                    if (data.status !== 'success') {
                        throw new Error(data.message || 'No se pudo cargar el producto');
                    }
                    this.p = data.product;
                } catch (e) {
                    this.detailError = e.message || 'Error al cargar el producto';
                } finally {
                    this.detailLoading = false;
                }
            },
            closeDetail() {
                this.detailOpen = false;
            },
            async confirmDelete(id, name) {
                const confirmed = await window.uiNotifications.confirmDialog({
                    title: 'Eliminar producto',
                    text: `¿Eliminar el producto «${name}»? Esta acción no se puede deshacer.`,
                    type: 'warning',
                    confirmText: 'Eliminar',
                    cancelText: 'Cancelar',
                });
                if (!confirmed) return;

                try {
                    const response = await fetch(`${this.productDestroyBase}/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': this.csrf,
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    const data = await response.json();
                    if (data.status === 'success') {
                        window.uiNotifications.showToast(data.message, {
                            type: 'success',
                            title: 'Listo',
                            timeout: 4800,
                            theme: 'futuristic',
                        });
                        $wire.$refresh();
                        return;
                    }
                    window.uiNotifications.showToast(data.message || 'No fue posible eliminar el producto.', {
                        type: 'error',
                        title: 'Atención',
                        timeout: 7200,
                        theme: 'futuristic',
                    });
                } catch (e) {
                    window.uiNotifications.showToast('Error inesperado al eliminar el producto.', {
                        type: 'error',
                        title: 'Atención',
                        timeout: 7200,
                        theme: 'futuristic',
                    });
                }
            },
        }"
        @keydown.escape.window="closeDetail()"
    >
        <div class="ui-panel">
            <div class="ui-panel__header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="ui-panel__title">Productos</h1>
                    <p class="ui-panel__subtitle">Gestiona tu catálogo e inventario.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    @if ($permissions['products.report'])
                        <a
                            href="{{ route('admin.products.report') }}"
                            target="_blank"
                            rel="noopener"
                            class="ui-btn ui-btn-ghost text-sm md:py-2.5 md:px-5 md:text-[0.95rem]"
                        >
                            <i class="fas fa-file-pdf"></i> Reporte PDF
                        </a>
                    @endif
                    @if ($permissions['products.create'])
                        <a
                            href="{{ route('admin.products.create', ['return' => $productFormReturnUrl]) }}"
                            class="ui-btn ui-btn-primary text-sm md:py-2.5 md:px-5 md:text-[0.95rem]"
                            wire:navigate
                        >
                            <i class="fas fa-plus"></i> Nuevo producto
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-2 xs:gap-3 lg:grid-cols-4">
            <x-ui.stat-card
                variant="info"
                icon="fas fa-boxes"
                trend="Catálogo"
                label="Productos"
                :value="number_format($totalProducts)"
                meta="Registrados"
            />
            <x-ui.stat-card
                variant="success"
                icon="fas fa-shopping-cart"
                trend="Inventario"
                label="Valor de compra"
                :value="$currency->symbol . ' ' . number_format($totalPurchaseValue, 2)"
                meta="Costo estimado"
            />
            <x-ui.stat-card
                variant="warning"
                icon="fas fa-cash-register"
                trend="Inventario"
                label="Valor de venta"
                :value="$currency->symbol . ' ' . number_format($totalSaleValue, 2)"
                meta="Precio de venta × stock"
            />
            <x-ui.stat-card
                variant="danger"
                icon="fas fa-chart-line"
                trend="{{ number_format($profitPercentage, 1) }}%"
                label="Ganancia potencial"
                :value="$currency->symbol . ' ' . number_format($potentialProfit, 2)"
                meta="Sobre valor de compra"
            />
        </div>

        <div
            class="ui-panel"
            x-data="{ showFilters: @js($filtersOpen) }"
        >
            <div class="ui-panel__header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <h2 class="ui-panel__title">Filtros</h2>
                    <p class="ui-panel__subtitle">Búsqueda por texto, categoría y estado de stock.</p>
                </div>
                <button
                    type="button"
                    class="ui-btn ui-btn-ghost w-full shrink-0 text-sm sm:w-auto"
                    @click="showFilters = !showFilters"
                    :aria-expanded="showFilters"
                >
                    <i class="fas" :class="showFilters ? 'fa-sliders-h' : 'fa-filter'"></i>
                    <span x-text="showFilters ? 'Ocultar filtros' : 'Filtros avanzados'"></span>
                </button>
            </div>
            <div class="ui-panel__body space-y-4" x-show="showFilters" x-transition>
                <div
                    class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:items-end lg:grid-cols-[minmax(0,0.95fr)_minmax(0,0.95fr)_auto] 2xl:grid-cols-[minmax(0,1.05fr)_minmax(0,0.92fr)_auto]"
                    wire:loading.class="opacity-60"
                >
                    <div>
                        <label for="category_id_products" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Categoría</label>
                        <select
                            id="category_id_products"
                            wire:model.live="category_id"
                            class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                        >
                            <option value="">Todas</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="stock_status_products" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Estado de stock</label>
                        <select
                            id="stock_status_products"
                            wire:model.live="stock_status"
                            class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                        >
                            <option value="">Todos</option>
                            <option value="low">Bajo</option>
                            <option value="normal">Normal</option>
                            <option value="high">Alto</option>
                        </select>
                    </div>
                    <div>
                        <button
                            type="button"
                            wire:click="clearFilters"
                            class="ui-btn ui-btn-ghost w-full justify-center text-sm lg:w-auto"
                        >
                            <i class="fas fa-eraser"></i> Limpiar filtros
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="ui-panel overflow-hidden">
            <div class="ui-panel__header">
                <div class="flex w-full flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div class="shrink-0">
                        <h2 class="ui-panel__title">Listado</h2>
                        <p class="ui-panel__subtitle">
                            {{ $products->total() }} resultado(s) · Página {{ $products->currentPage() }} de {{ $products->lastPage() }}
                        </p>
                    </div>

                    <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center lg:w-auto">
                        <div class="relative min-w-[16rem] flex-1 lg:min-w-[18rem]">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                                <i class="fas fa-search text-xs"></i>
                            </span>
                            <input
                                id="search-products"
                                type="search"
                                wire:model.live.debounce.400ms="search"
                                placeholder="Buscar nombre, código o categoría..."
                                class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2 pl-9 pr-3 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                                autocomplete="off"
                            >
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="clearFilters" class="ui-btn ui-btn-ghost text-sm" title="Limpiar búsqueda y filtros">
                                <i class="fas fa-eraser"></i>
                            </button>
                            @if ($permissions['products.destroy'] && ! $products->isEmpty())
                                <button
                                    type="button"
                                    wire:click="toggleSelectionMode"
                                    class="ui-btn {{ $selectionMode ? 'ui-btn-warning' : 'ui-btn-ghost' }} text-sm"
                                >
                                    <i class="fas {{ $selectionMode ? 'fa-times-circle' : 'fa-check-square' }}"></i>
                                    {{ $selectionMode ? 'Cancelar' : 'Seleccionar' }}
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @if ($products->isEmpty())
                <div class="ui-panel__body">
                    <p class="py-10 text-center text-sm text-slate-400">No hay productos para los filtros seleccionados.</p>
                </div>
            @else
                @if ($selectionMode)
                    <div class="flex flex-col gap-3 border-b border-slate-700/50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-medium text-white">{{ count($selectedProductIds) }} producto(s) seleccionado(s)</p>
                            <p class="text-xs text-slate-400">
                                La selección aplica a la página actual. No se eliminan productos con ventas o compras asociadas.
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" wire:click="toggleSelectAllCurrentPage" class="ui-btn ui-btn-ghost text-sm">
                                <i class="fas {{ $allCurrentPageSelected ? 'fa-square-minus' : 'fa-square-check' }}"></i>
                                {{ $allCurrentPageSelected ? 'Limpiar página' : 'Seleccionar página' }}
                            </button>
                            <button
                                type="button"
                                wire:click="openBulkDeleteModal"
                                class="ui-btn ui-btn-danger text-sm"
                                @disabled(count($selectedProductIds) === 0)
                            >
                                <i class="fas fa-trash-alt"></i>
                                Eliminar seleccionados
                            </button>
                        </div>
                    </div>
                @endif

                <div class="ui-panel__body hidden p-0 md:block" wire:loading.class.delay="opacity-60">
                    <div class="ui-table-wrap border-0 rounded-none">
                        <table class="ui-table ui-table--nowrap-actions">
                            <thead>
                                <tr>
                                    @if ($selectionMode)
                                        <th class="w-12 text-center">
                                            <input
                                                type="checkbox"
                                                @checked($allCurrentPageSelected)
                                                wire:click="toggleSelectAllCurrentPage"
                                                class="rounded border-slate-500 bg-slate-900"
                                            />
                                        </th>
                                    @endif
                                    <th>Producto</th>
                                    <th>Categoría</th>
                                    <th class="text-right">Stock</th>
                                    <th class="text-right">Compra</th>
                                    <th class="text-right">Venta</th>
                                    <th class="text-left">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($products as $product)
                                    <tr wire:key="product-row-{{ $product->id }}">
                                        @if ($selectionMode)
                                            <td class="text-center">
                                                <input
                                                    type="checkbox"
                                                    value="{{ $product->id }}"
                                                    @checked(in_array($product->id, $selectedProductIds, true))
                                                    wire:click="toggleProductSelection({{ $product->id }})"
                                                    class="rounded border-slate-500 bg-slate-900"
                                                />
                                            </td>
                                        @endif
                                        <td>
                                            <div class="flex min-w-0 items-center gap-3">
                                                <img
                                                    src="{{ $product->image_url }}"
                                                    alt=""
                                                    class="h-10 w-10 shrink-0 rounded-lg border border-slate-600 object-cover"
                                                >
                                                <div class="min-w-0">
                                                    <p class="truncate font-medium text-white">{{ $product->name }}</p>
                                                    <p class="truncate text-xs text-slate-400">{{ $product->code }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-slate-300">{{ $product->category->name ?? 'Sin categoría' }}</td>
                                        <td class="text-right">
                                            <span
                                                @class([
                                                    'ui-badge',
                                                    'ui-badge-danger' => $product->stock_status === 'low',
                                                    'ui-badge-warning' => $product->stock_status === 'normal',
                                                    'ui-badge-success' => $product->stock_status === 'high',
                                                ])
                                            >
                                                {{ $product->stock }}
                                            </span>
                                        </td>
                                        <td class="text-right tabular-nums text-slate-200">{{ $currency->symbol }} {{ number_format($product->purchase_price, 2) }}</td>
                                        <td class="text-right tabular-nums text-slate-200">{{ $currency->symbol }} {{ number_format($product->sale_price, 2) }}</td>
                                        <td>
                                            <div class="ui-icon-action-row flex flex-nowrap items-center justify-start gap-1.5 md:gap-2">
                                                @if ($permissions['products.show'])
                                                    <button
                                                        type="button"
                                                        class="ui-icon-action ui-icon-action--info"
                                                        title="Ver"
                                                        @click="openDetail({{ $product->id }})"
                                                    >
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                @endif
                                                @if ($permissions['products.edit'])
                                                    <a
                                                        href="{{ route('admin.products.edit', ['id' => $product->id, 'return' => $productFormReturnUrl]) }}"
                                                        class="ui-icon-action ui-icon-action--primary"
                                                        title="Editar"
                                                        wire:navigate
                                                    >
                                                        <i class="fas fa-pen"></i>
                                                    </a>
                                                @endif
                                                @if ($permissions['products.destroy'])
                                                    <button
                                                        type="button"
                                                        class="ui-icon-action ui-icon-action--danger"
                                                        title="Eliminar"
                                                        @click="confirmDelete({{ $product->id }}, @js($product->name))"
                                                    >
                                                        <i class="fas fa-trash"></i>
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

                <div class="space-y-3 p-4 md:hidden">
                    @foreach ($products as $product)
                        <div class="rounded-xl border border-slate-700/60 bg-slate-950/40 p-4" wire:key="product-card-{{ $product->id }}">
                            <div class="flex items-start justify-between gap-3">
                                @if ($selectionMode)
                                    <input
                                        type="checkbox"
                                        @checked(in_array($product->id, $selectedProductIds, true))
                                        wire:click="toggleProductSelection({{ $product->id }})"
                                        class="mt-1 shrink-0 rounded border-slate-500 bg-slate-900"
                                    />
                                @endif
                            <div class="flex min-w-0 flex-1 gap-3">
                                <img
                                    src="{{ $product->image_url }}"
                                    alt=""
                                    class="h-14 w-14 shrink-0 rounded-lg border border-slate-600 object-cover"
                                >
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-white">{{ $product->name }}</p>
                                    <p class="text-xs text-slate-400">{{ $product->code }}</p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $product->category->name ?? 'Sin categoría' }}
                                    </p>
                                </div>
                                <span
                                    @class([
                                        'ui-badge h-fit shrink-0',
                                        'ui-badge-danger' => $product->stock_status === 'low',
                                        'ui-badge-warning' => $product->stock_status === 'normal',
                                        'ui-badge-success' => $product->stock_status === 'high',
                                    ])
                                >
                                    {{ $product->stock }}
                                </span>
                            </div>
                            </div>
                            <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                                <div class="rounded-lg bg-slate-900/60 p-2">
                                    <p class="text-slate-500">Compra</p>
                                    <p class="font-semibold tabular-nums text-slate-200">{{ $currency->symbol }} {{ number_format($product->purchase_price, 2) }}</p>
                                </div>
                                <div class="rounded-lg bg-slate-900/60 p-2">
                                    <p class="text-slate-500">Venta</p>
                                    <p class="font-semibold tabular-nums text-slate-200">{{ $currency->symbol }} {{ number_format($product->sale_price, 2) }}</p>
                                </div>
                            </div>
                            <div class="mt-3 flex flex-wrap items-center justify-end gap-2 border-t border-slate-700/50 pt-3">
                                @if ($permissions['products.show'])
                                    <button
                                        type="button"
                                        class="ui-btn ui-btn-ghost flex-1 text-xs sm:flex-none sm:text-sm"
                                        @click="openDetail({{ $product->id }})"
                                    >
                                        <i class="fas fa-eye"></i> Ver
                                    </button>
                                @endif
                                @if ($permissions['products.edit'])
                                    <a
                                        href="{{ route('admin.products.edit', ['id' => $product->id, 'return' => $productFormReturnUrl]) }}"
                                        class="ui-btn ui-btn-ghost flex-1 text-xs sm:flex-none sm:text-sm"
                                        wire:navigate
                                    >
                                        <i class="fas fa-pen"></i> Editar
                                    </a>
                                @endif
                                @if ($permissions['products.destroy'])
                                    <button
                                        type="button"
                                        class="ui-btn ui-btn-danger flex-1 text-xs sm:flex-none sm:text-sm"
                                        @click="confirmDelete({{ $product->id }}, @js($product->name))"
                                    >
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div>
            <x-ui.pagination :paginator="$products" scroll-into-view=".ui-panel.overflow-hidden" />
        </div>

        @if ($showBulkDeleteModal)
            <div
                class="fixed inset-0 z-[60] flex items-center justify-center bg-[#020617]/90 p-4 backdrop-blur-md"
                wire:click.self="closeBulkDeleteModal"
                x-data
                x-on:keydown.escape.window="$wire.closeBulkDeleteModal()"
                aria-modal="true"
                role="dialog"
            >
                <div class="relative w-full max-w-lg overflow-hidden rounded-2xl border border-slate-600 bg-slate-900 text-slate-100 shadow-[0_25px_80px_rgba(0,0,0,0.75)]">
                    <div class="border-b border-slate-700 bg-slate-900 px-5 pb-4 pt-5">
                        <div class="flex items-start gap-3">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-rose-500/40 bg-rose-950 text-rose-200">
                                <i class="fas fa-trash-alt text-lg"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="text-base font-semibold text-white">¿Eliminar productos seleccionados?</h3>
                                <p class="mt-1.5 text-sm leading-relaxed text-slate-300">
                                    Se intentará eliminar <span class="font-medium text-white">{{ count($selectedProductIds) }} producto(s)</span>.
                                    Los que tengan ventas o compras asociadas no se eliminarán y se indicará el motivo.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap justify-end gap-2 border-t border-slate-700 bg-slate-950 px-4 py-3">
                        <button type="button" wire:click="closeBulkDeleteModal" class="ui-btn ui-btn-ghost text-sm">Cancelar</button>
                        <button type="button" wire:click="confirmBulkDelete" class="ui-btn ui-btn-danger text-sm">
                            <i class="fas fa-trash-alt mr-1.5"></i>
                            Sí, eliminar seleccionados
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <div
            x-show="detailOpen"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4"
            @click.self="closeDetail()"
        >
            <div class="ui-panel max-h-[90vh] w-full max-w-lg overflow-y-auto" @click.stop>
                <div class="ui-panel__header flex items-start justify-between gap-3">
                    <div>
                        <h2 class="ui-panel__title">Detalle del producto</h2>
                        <p class="ui-panel__subtitle" x-text="p ? p.name : ''"></p>
                    </div>
                    <button type="button" class="ui-btn ui-btn-ghost !px-2 !py-1" @click="closeDetail()" aria-label="Cerrar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="ui-panel__body space-y-4 text-sm">
                    <template x-if="detailLoading">
                        <p class="text-slate-400">Cargando…</p>
                    </template>
                    <template x-if="detailError">
                        <p class="text-rose-400" x-text="detailError"></p>
                    </template>
                    <template x-if="p && !detailLoading">
                        <div class="space-y-3">
                            <div class="flex gap-3">
                                <img :src="p.image" alt="" class="h-20 w-20 rounded-lg border border-slate-600 object-cover">
                                <div class="min-w-0 space-y-1">
                                    <p class="text-xs text-slate-400">Código <span class="font-medium text-white" x-text="p.code"></span></p>
                                    <p class="text-xs text-slate-400">Categoría <span class="font-medium text-white" x-text="p.category"></span></p>
                                    <p class="text-xs text-slate-400">
                                        Stock <span class="font-medium text-white" x-text="p.stock"></span>
                                        (mín <span x-text="p.min_stock"></span>, máx <span x-text="p.max_stock"></span>)
                                    </p>
                                </div>
                            </div>
                            <p class="text-slate-300" x-text="p.description"></p>
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div class="rounded-lg border border-slate-700/60 bg-slate-950/50 p-2">
                                    <p class="text-slate-500">Compra</p>
                                    <p class="font-semibold text-white" x-text="p.purchase_price"></p>
                                </div>
                                <div class="rounded-lg border border-slate-700/60 bg-slate-950/50 p-2">
                                    <p class="text-slate-500">Venta</p>
                                    <p class="font-semibold text-white" x-text="p.sale_price"></p>
                                </div>
                            </div>
                            <p class="text-xs text-slate-500">Ingreso: <span x-text="p.entry_date"></span> · <span x-text="p.entry_days_ago"></span></p>
                        </div>
                    </template>
                </div>
                <div class="flex justify-end border-t border-slate-700/60 px-4 py-3">
                    <button type="button" class="ui-btn ui-btn-ghost" @click="closeDetail()">Cerrar</button>
                </div>
            </div>
        </div>
</div>
