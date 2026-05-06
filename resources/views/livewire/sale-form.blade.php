@php
    $inputBase = 'w-full rounded-lg border border-slate-600 bg-slate-950/60 px-3 py-2.5 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500';
    $labelBase = 'mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400';
@endphp

<div class="space-y-6" wire:key="sale-form-{{ $saleId ?? 'create' }}">
    {{-- ================================================================ --}}
    {{-- HEADER                                                        --}}
    {{-- ================================================================ --}}
    <div class="ui-panel">
        <div class="ui-panel__header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="ui-panel__title">{{ $headingTitle }}</h1>
                <p class="ui-panel__subtitle">{{ $headingSubtitle }}</p>
            </div>
            <div class="flex items-center gap-3">
                @if ($permissions['can_create'] ?? true)
                    <button type="button" wire:click="openBulkModal" class="ui-btn ui-btn-ghost text-sm">
                        <i class="fas fa-layer-group mr-2"></i> Ventas masivas
                    </button>
                @endif
                <a
                    href="{{ route('admin.sales.index') }}"
                    wire:navigate
                >
                    <i class="fas fa-arrow-left"></i> Volver al listado
                </a>
            </div>
        </div>
    </div>

    {{-- Cash count warning --}}
    @if (! $hasCashOpen && ! $isEdit)
        <div class="rounded-lg border border-rose-500/40 bg-rose-950/30 px-4 py-3">
            <p class="flex items-center gap-2 text-sm text-rose-300">
                <i class="fas fa-exclamation-triangle"></i>
                No hay una caja abierta. Debe abrir una caja antes de registrar ventas.
            </p>
        </div>
@endif

    {{-- ================================================================ --}}
    {{-- FORMULARIO PRINCIPAL                                          --}}
    {{-- ================================================================ --}}
    <div class="ui-panel">
        <div class="ui-panel__body">
            <div class="space-y-6">

                {{-- Fecha, Hora y Cliente --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label for="sale_date" class="{{ $labelBase }}">
                            Fecha <span class="text-rose-400">*</span>
                        </label>
                        <input
                            id="sale_date"
                            type="date"
                            wire:model="sale_date"
                            class="{{ $inputBase }} @error('sale_date') border-rose-500/80 @enderror"
                        >
                        @error('sale_date')
                            <p class="mt-1.5 text-sm text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="sale_time" class="{{ $labelBase }}">
                            Hora <span class="text-rose-400">*</span>
                        </label>
                        <input
                            id="sale_time"
                            type="time"
                            wire:model="sale_time"
                            class="{{ $inputBase }} @error('sale_time') border-rose-500/80 @enderror"
                        >
                        @error('sale_time')
                            <p class="mt-1.5 text-sm text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>
                    {{-- Cliente — selector personalizado con deuda --}}
                    <div>
                        <label for="customer_id" class="{{ $labelBase }}">
                            Cliente <span class="text-rose-400">*</span>
                        </label>

                        <div class="relative" x-data="{
                            isOpen: false,
                            searchTerm: '',
                            customers: @js($customers->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'phone' => $c->phone, 'total_debt' => (float) $c->total_debt])->values()->toArray()),
                            filtered: @js($customers->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'phone' => $c->phone, 'total_debt' => (float) $c->total_debt])->values()->toArray()),
                            selectedId: @entangle('customer_id'),
                            get selected() {
                                return this.customers.find(c => c.id == this.selectedId) || null;
                            },
                            get selectedName() {
                                return this.selected ? this.selected.name : 'Seleccionar cliente...';
                            },
                            get selectedDebt() {
                                return this.selected ? this.selected.total_debt : 0;
                            },
                            filter() {
                                const t = this.searchTerm.toLowerCase();
                                this.filtered = t ? this.customers.filter(c => c.name.toLowerCase().includes(t)) : this.customers;
                            },
                            select(customer) {
                                this.selectedId = customer.id;
                                this.isOpen = false;
                                this.searchTerm = '';
                            },
                            init() {
                                // Preseleccionar si customer_id viene de la URL
                                const urlParams = new URLSearchParams(window.location.search);
                                const cid = urlParams.get('customer_id');
                                if (cid && !this.selectedId) {
                                    this.selectedId = parseInt(cid);
                                }
                            }
                        }" @click.away="isOpen = false">
                            {{-- Button --}}
                            <button type="button" @click="isOpen = !isOpen; if(isOpen) $nextTick(() => $refs.csSearch?.focus())"
                                class="{{ $inputBase }} flex items-center justify-between gap-2 text-left h-[42px] pr-10 relative
                                    @error('customer_id') border-rose-500/80 @enderror">
                                <span class="truncate text-sm flex-1" :class="selected ? 'text-slate-100' : 'text-slate-500'"
                                    x-text="selectedName"></span>
                                <span class="flex-shrink-0 flex items-center gap-1.5">
                                    <span x-show="selectedDebt > 0"
                                        x-text="'{{ $currency->symbol }} ' + selectedDebt.toLocaleString('es-PE', {minimumFractionDigits: 2})"
                                        class="inline-flex items-center rounded-md bg-rose-500/15 px-2 py-0.5 text-[11px] font-semibold text-rose-300 border border-rose-500/30">
                                    </span>
                                    <span x-show="selectedDebt === 0 && selected !== null"
                                        class="inline-flex items-center rounded-md bg-emerald-500/15 px-2 py-0.5 text-[11px] font-semibold text-emerald-300 border border-emerald-500/30">
                                        Sin deuda
                                    </span>
                                </span>
                                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                                    <i class="fas fa-chevron-down text-xs text-slate-500 transition-transform" :class="isOpen ? 'rotate-180' : ''"></i>
                                </span>
                            </button>

                            {{-- Dropdown --}}
                            <div x-show="isOpen" x-cloak x-transition
                                class="absolute z-50 mt-1 w-full rounded-xl border border-slate-600/50 bg-slate-900/95 shadow-2xl backdrop-blur-xl overflow-hidden"
                                style="display: none;">
                                {{-- Search --}}
                                <div class="border-b border-slate-700/50 p-3">
                                    <input type="text" x-ref="csSearch" x-model="searchTerm" @input="filter()"
                                        @keydown.escape="isOpen = false"
                                        placeholder="Buscar cliente..."
                                        class="w-full rounded-lg border border-slate-600 bg-slate-950/60 px-3 py-2 text-xs text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500">
                                </div>
                                {{-- Options --}}
                                <div class="max-h-56 overflow-y-auto py-1">
                                    <template x-for="c in filtered" :key="c.id">
                                        <button type="button" @click="select(c)"
                                            class="w-full flex items-center justify-between gap-3 px-5 py-3 text-left text-sm text-slate-200 hover:bg-slate-700/60 hover:text-white transition-all duration-150"
                                            :class="selectedId == c.id ? 'bg-cyan-500/10 text-cyan-300 font-medium' : ''">
                                            <span class="truncate flex-1" x-text="c.name"></span>
                                            <span class="flex-shrink-0 flex items-center gap-1">
                                                <span x-show="parseFloat(c.total_debt) > 0"
                                                    x-text="'{{ $currency->symbol }} ' + parseFloat(c.total_debt).toLocaleString('es-PE', {minimumFractionDigits: 2})"
                                                    class="inline-flex items-center rounded-md bg-rose-500/15 px-2 py-0.5 text-[10px] font-semibold text-rose-300 border border-rose-500/30">
                                                </span>
                                                <span x-show="parseFloat(c.total_debt || 0) === 0"
                                                    class="inline-flex items-center rounded-md bg-emerald-500/15 px-2 py-0.5 text-[10px] font-semibold text-emerald-300 border border-emerald-500/30">
                                                    Sin deuda
                                                </span>
                                            </span>
                                        </button>
                                    </template>
                                    <div x-show="filtered.length === 0" class="px-5 py-10 text-center text-sm text-slate-500">
                                        No se encontraron clientes.
                                    </div>
                                </div>
                            </div>
                        </div>
                        @error('customer_id')
                            <p class="mt-1.5 text-sm text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Ya pagó? --}}
                @if (! $isEdit)
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <label class="{{ $labelBase }}">
                                <i class="fas fa-credit-card mr-1 text-slate-400"></i> ¿Ya pagó?
                            </label>
                        <div class="relative" x-data="{
                            isOpen: false,
                            selectedText: 'No',
                            selectOption(value) {
                                if (value === true) {
                                    Swal.fire({
                                        title: '¿Confirmar pago automático?',
                                        text: 'Al seleccionar Sí, se registrará automáticamente el pago de esta venta. ¿Está seguro?',
                                        icon: 'question',
                                        showCancelButton: true,
                                        confirmButtonColor: '#10b981',
                                        cancelButtonColor: '#6b7280',
                                        confirmButtonText: 'Sí, confirmar',
                                        cancelButtonText: 'Cancelar',
                                        background: '#0f172a',
                                        color: '#e2e8f0',
                                        customClass: { popup: 'border border-slate-700 rounded-xl' }
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            $wire.set('already_paid', true);
                                            this.selectedText = 'Sí';
                                            this.isOpen = false;
                                            window.uiNotifications?.showToast?.('¡Pago automático activado!', {type:'success', title:'Atención', timeout:4800, theme:'futuristic'});
                                        }
                                    });
                                } else {
                                    $wire.set('already_paid', false);
                                    this.selectedText = 'No';
                                    this.isOpen = false;
                                }
                            }
                        }" @click.away="isOpen = false">
                            {{-- Select button --}}
                            <button type="button" @click="isOpen = !isOpen"
                                class="relative w-full rounded-lg border border-slate-600 bg-slate-950/60 px-3 py-2.5 pr-10 text-left text-sm text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500 transition-colors">
                                <span class="flex items-center gap-2">
                                    <i class="fas" :class="selectedText === 'Sí' ? 'fa-check-circle text-emerald-400' : 'fa-times-circle text-rose-400'"></i>
                                    <span x-text="selectedText"></span>
                                </span>
                                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                                    <i class="fas fa-chevron-down text-xs text-slate-500 transition-transform" :class="{ 'rotate-180': isOpen }"></i>
                                </span>
                            </button>
                            {{-- Dropdown --}}
                            <div x-show="isOpen" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="absolute z-50 mt-1 w-full overflow-hidden rounded-lg border border-slate-700 bg-slate-900 shadow-xl">
                                <div @click="selectOption(false)" class="flex cursor-pointer items-center gap-2 px-3 py-2.5 text-sm text-slate-200 hover:bg-slate-800 transition-colors">
                                    <i class="fas fa-times-circle text-rose-400"></i> No
                                </div>
                                <div @click="selectOption(true)" class="flex cursor-pointer items-center gap-2 border-t border-slate-700/50 px-3 py-2.5 text-sm text-slate-200 hover:bg-slate-800 transition-colors">
                                    <i class="fas fa-check-circle text-emerald-400"></i> Sí
                                </div>
                            </div>
                        </div>
                        <p class="mt-1.5 text-xs text-slate-500"><i class="fas fa-info-circle mr-1"></i>Si selecciona "Sí", se registrará automáticamente el pago</p>
                    </div>
                </div>
                @endif

                {{-- Escaneo por código + búsqueda --}}
                <div class="rounded-lg border border-slate-700/70 bg-slate-900/60 p-4">
                    <h3 class="mb-3 text-sm font-semibold text-slate-200">
                        <i class="fas fa-barcode mr-2 text-cyan-400"></i>Agregar productos
                    </h3>
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <div class="relative flex-1" x-data="{
                            productCode: @entangle('product_code'),
                            async scanAndAdd() {
                                const code = this.productCode;
                                if (!code) return;
                                try {
                                    const res = await fetch('/sales/product-by-code/' + encodeURIComponent(code));
                                    const data = await res.json();
                                    if (data.success) {
                                        const p = data.product;
                                        const alreadyAdded = {{ Js::from(collect($items)->pluck('product_id')->all()) }};
                                        if (alreadyAdded.includes(p.id)) {
                                            window.uiNotifications?.showToast?.('Este producto ya está en la lista de venta', {type:'warning', title:'Atención', timeout:4800, theme:'futuristic'});
                                        } else {
                                            $wire.addProductFromScan(p.id, p.code, p.name, p.image_url ?? '', p.stock ?? 0, p.price ?? 0);
                                        }
                                    } else {
                                        window.uiNotifications?.showToast?.(data.message || 'No se encontró el producto', {type:'error', title:'Atención', timeout:7200, theme:'futuristic'});
                                    }
                                } catch {
                                    window.uiNotifications?.showToast?.('Error al buscar el producto', {type:'error', title:'Atención', timeout:7200, theme:'futuristic'});
                                }
                                this.productCode = '';
                            }
                        }">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                                <i class="fas fa-barcode text-[0.8rem]"></i>
                            </span>
                            <input
                                type="text"
                                x-model="productCode"
                                @keyup.enter="scanAndAdd()"
                                placeholder="Escanee o ingrese el código del producto"
                                class="{{ $inputBase }} pl-9 pr-12"
                                autocomplete="off"
                            >
                            <button
                                type="button"
                                @click="scanAndAdd()"
                                class="absolute inset-y-0 right-0 flex items-center px-3 text-cyan-400 hover:text-cyan-300 transition-colors"
                                title="Buscar por código"
                            >
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <button
                            type="button"
                            wire:click="openProductModal"
                            class="ui-btn ui-btn-ghost shrink-0 text-sm"
                        >
                            <i class="fas fa-boxes mr-2"></i>
                            <span class="hidden xs:inline">Buscar productos</span>
                            <span class="xs:hidden">Buscar</span>
                        </button>
                    </div>
                </div>

                {{-- Tabla de productos --}}
                <div>
                    @error('items')
                        <div class="mb-4 rounded-lg border border-rose-500/40 bg-rose-950/30 px-4 py-3">
                            <p class="flex items-center gap-2 text-sm text-rose-300">
                                <i class="fas fa-exclamation-triangle"></i>
                                {{ $message }}
                            </p>
                        </div>
                    @enderror

                    {{-- Estado vacío --}}
                    @if (empty($items))
                        <div class="rounded-lg border border-dashed border-slate-700/70 bg-slate-900/30 py-16 text-center">
                            <div class="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-full bg-slate-800/80">
                                <i class="fas fa-shopping-bag text-2xl text-slate-500"></i>
                            </div>
                            <h4 class="text-lg font-medium text-slate-300">No hay productos agregados</h4>
                            <p class="mt-1 text-sm text-slate-500">
                                Escanee un producto o use el botón "Buscar productos" para agregar items a la venta.
                            </p>
                        </div>
                    @else
                        {{-- Mobile cards (hidden sm and up) --}}
                        <div class="space-y-3 sm:hidden">
                            @foreach ($items as $index => $item)
                                <div
                                    class="rounded-xl border border-slate-700/70 bg-slate-950/60 p-4"
                                    wire:key="sale-item-mobile-{{ $index }}"
                                >
                                    {{-- Top row: image + name/code + stock badge + delete --}}
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex min-w-0 items-center gap-3">
                                            <img
                                                src="{{ $item['image_url'] ?? asset('img/no-image.svg') }}"
                                                alt="{{ $item['name'] }}"
                                                class="h-10 w-10 shrink-0 rounded-lg object-cover"
                                            >
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-medium text-slate-200">{{ $item['name'] }}</p>
                                                <p class="text-xs text-slate-500">{{ $item['code'] }}</p>
                                            </div>
                                        </div>
                                        <div class="flex shrink-0 items-center gap-2">
                                            <span @class([
                                                'inline-flex rounded-full px-2 py-0.5 text-xs font-semibold',
                                                'bg-rose-900/60 text-rose-300' => ($item['stock'] ?? 0) <= 0,
                                                'bg-amber-900/60 text-amber-300' => ($item['stock'] ?? 0) > 0 && ($item['stock'] ?? 0) < 10,
                                                'bg-emerald-900/60 text-emerald-300' => ($item['stock'] ?? 0) >= 10,
                                            ])>
                                                {{ $item['stock'] ?? 0 }}
                                            </span>
                                            <button
                                                type="button"
                                                wire:click="removeProduct({{ $index }})"
                                                class="inline-flex items-center rounded-md border border-rose-500/30 bg-rose-950/30 px-2 py-1.5 text-xs font-medium text-rose-400 transition-colors hover:bg-rose-950/60"
                                            >
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Inputs grid --}}
                                    <div class="mt-3 grid grid-cols-2 gap-3">
                                        {{-- Cantidad --}}
                                        <div>
                                            <label class="mb-1 block text-xs font-semibold text-slate-400">
                                                Cantidad
                                            </label>
                                            <input
                                                type="number"
                                                inputmode="numeric"
                                                wire:model.blur="items.{{ $index }}.quantity"
                                                wire:change="updateItemQuantity({{ $index }}, $event.target.value)"
                                                class="h-11 w-full rounded-md border border-slate-600 bg-slate-900/80 px-2 py-1.5 text-center text-sm text-slate-200 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                                                min="1" step="1"
                                                onkeydown="return event.key !== '.' && event.key !== 'e' && event.key !== 'E' && event.key !== '-' && event.key !== '+'"
                                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                            >
                                        </div>
                                        {{-- Precio --}}
                                        <div>
                                            <label class="mb-1 block text-xs font-semibold text-slate-400">
                                                Precio
                                            </label>
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-xs text-slate-500">{{ $currency->symbol }}</span>
                                                <input
                                                    type="number"
                                                    wire:model.blur="items.{{ $index }}.price"
                                                    wire:change="updateItemPrice({{ $index }}, $event.target.value)"
                                                    class="h-11 w-full rounded-md border border-slate-600 bg-slate-900/80 px-2 py-1.5 text-right text-sm text-slate-200 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                                                    min="0"
                                                    step="0.01"
                                                >
                                            </div>
                                        </div>
                                        {{-- Descuento --}}
                                        <div class="col-span-2">
                                            <label class="mb-1 block text-xs font-semibold text-slate-400">
                                                Descuento
                                            </label>
                                            <div class="flex items-center gap-2">
                                                <input
                                                    type="number"
                                                    wire:model.blur="items.{{ $index }}.discount_value"
                                                    wire:change="updateItemDiscount({{ $index }}, $event.target.value)"
                                                    class="h-11 w-full rounded-md border border-slate-600 bg-slate-900/80 px-2 py-1.5 text-right text-sm text-slate-200 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                                                    min="0"
                                                    step="0.01"
                                                >
                                                <button
                                                    type="button"
                                                    wire:click="toggleItemDiscountType({{ $index }})"
                                                    @class([
                                                        'h-11 shrink-0 rounded-md border px-3 text-xs font-semibold transition-colors',
                                                        'border-cyan-500/50 bg-cyan-950/50 text-cyan-300' => ($item['discount_type'] ?? 'fixed') === 'percentage',
                                                        'border-slate-600 bg-slate-800 text-slate-400' => ($item['discount_type'] ?? 'fixed') === 'fixed',
                                                    ])
                                                >
                                                    {{ ($item['discount_type'] ?? 'fixed') === 'percentage' ? '%' : $currency->symbol }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Bottom: Subtotal --}}
                                    <div class="mt-3 flex items-center justify-between border-t border-slate-700/50 pt-3">
                                        <span class="text-xs font-semibold uppercase text-slate-400">Subtotal</span>
                                        <span class="text-base font-bold text-emerald-400">
                                            {{ $currency->symbol }} {{ number_format($this->getItemSubtotal($index), 2) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Desktop table (hidden below sm) --}}
                        <div class="hidden sm:block overflow-x-auto rounded-lg border border-slate-700/70">
                            <table class="min-w-full divide-y divide-slate-700/70">
                                <thead class="bg-slate-900/80">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Producto</th>
                                        <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-400 sm:table-cell">Stock</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Cant.</th>
                                        <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-400 md:table-cell">Precio</th>
                                        <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-400 lg:table-cell">Desc.</th>
                                        <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-400 lg:table-cell">Subtotal</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-400">Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-700/70 bg-slate-950/40">
                                    @foreach ($items as $index => $item)
                                        <tr class="transition-colors hover:bg-slate-900/60" wire:key="sale-item-{{ $index }}">
                                            {{-- Producto --}}
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-3">
                                                    <img
                                                        src="{{ $item['image_url'] ?? asset('img/no-image.svg') }}"
                                                        alt="{{ $item['name'] }}"
                                                        class="h-9 w-9 shrink-0 rounded-lg object-cover"
                                                    >
                                                    <div class="min-w-0">
                                                        <p class="truncate text-sm font-medium text-slate-200">{{ $item['name'] }}</p>
                                                        <p class="text-xs text-slate-500">{{ $item['code'] }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            {{-- Stock --}}
                                            <td class="hidden whitespace-nowrap px-4 py-3 sm:table-cell">
                                                <span @class([
                                                    'inline-flex rounded-full px-2 py-0.5 text-xs font-semibold',
                                                    'bg-rose-900/60 text-rose-300' => ($item['stock'] ?? 0) <= 0,
                                                    'bg-amber-900/60 text-amber-300' => ($item['stock'] ?? 0) > 0 && ($item['stock'] ?? 0) < 10,
                                                    'bg-emerald-900/60 text-emerald-300' => ($item['stock'] ?? 0) >= 10,
                                                ])>
                                                    {{ $item['stock'] ?? 0 }}
                                                </span>
                                            </td>
                                            {{-- Cantidad --}}
                                            <td class="whitespace-nowrap px-4 py-3">
                                                <input
                                                    type="number"
                                                    inputmode="numeric"
                                                    wire:model.blur="items.{{ $index }}.quantity"
                                                    wire:change="updateItemQuantity({{ $index }}, $event.target.value)"
                                                    class="w-20 rounded-md border border-slate-600 bg-slate-900/80 px-2 py-1.5 text-center text-sm text-slate-200 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                                                    min="1" step="1"
                                                    onkeydown="return event.key !== '.' && event.key !== 'e' && event.key !== 'E' && event.key !== '-' && event.key !== '+'"
                                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                                >
                                            </td>
                                            {{-- Precio Unit. --}}
                                            <td class="hidden whitespace-nowrap px-4 py-3 md:table-cell">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-xs text-slate-500">{{ $currency->symbol }}</span>
                                                    <input
                                                        type="number"
                                                        wire:model.blur="items.{{ $index }}.price"
                                                        wire:change="updateItemPrice({{ $index }}, $event.target.value)"
                                                        class="w-24 rounded-md border border-slate-600 bg-slate-900/80 px-2 py-1.5 text-right text-sm text-slate-200 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                                                        min="0" step="0.01"
                                                    >
                                                </div>
                                            </td>
                                            {{-- Descuento por ítem --}}
                                            <td class="hidden whitespace-nowrap px-4 py-3 lg:table-cell">
                                                <div class="flex items-center gap-1">
                                                    <input
                                                        type="number"
                                                        wire:model.blur="items.{{ $index }}.discount_value"
                                                        wire:change="updateItemDiscount({{ $index }}, $event.target.value)"
                                                        class="w-20 rounded-md border border-slate-600 bg-slate-900/80 px-2 py-1.5 text-right text-sm text-slate-200 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                                                        min="0" step="0.01"
                                                    >
                                                    <button
                                                        type="button"
                                                        wire:click="toggleItemDiscountType({{ $index }})"
                                                        @class([
                                                            'rounded-md border px-2 py-1 text-xs font-semibold transition-colors',
                                                            'border-cyan-500/50 bg-cyan-950/50 text-cyan-300' => ($item['discount_type'] ?? 'fixed') === 'percentage',
                                                            'border-slate-600 bg-slate-800 text-slate-400' => ($item['discount_type'] ?? 'fixed') === 'fixed',
                                                        ])
                                                    >
                                                        {{ ($item['discount_type'] ?? 'fixed') === 'percentage' ? '%' : $currency->symbol }}
                                                    </button>
                                                </div>
                                            </td>
                                            {{-- Subtotal --}}
                                            <td class="hidden whitespace-nowrap px-4 py-3 text-sm font-medium text-emerald-400 lg:table-cell">
                                                {{ $currency->symbol }} {{ number_format($this->getItemSubtotal($index), 2) }}
                                            </td>
                                            {{-- Acción --}}
                                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                                <button
                                                    type="button"
                                                    wire:click="removeProduct({{ $index }})"
                                                    class="inline-flex items-center rounded-md border border-rose-500/30 bg-rose-950/30 px-2 py-1.5 text-xs font-medium text-rose-400 transition-colors hover:bg-rose-950/60"
                                                >
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- Resumen y Totales --}}
                @if (! empty($items))
                    <div class="rounded-lg border border-slate-700/70 bg-slate-900/60 p-3 sm:p-5">
                        <div class="grid grid-cols-3 gap-2 xs:gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                            {{-- Productos únicos --}}
                            <div class="flex min-w-0 items-center gap-2 sm:gap-3">
                                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-slate-800 text-cyan-400 sm:h-9 sm:w-9">
                                    <i class="fas fa-boxes text-xs sm:text-sm"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-base font-bold text-slate-200 sm:text-lg">{{ $this->totalProducts }}</p>
                                    <p class="text-[10px] text-slate-500 sm:text-xs">Productos</p>
                                </div>
                            </div>
                            {{-- Cantidad total --}}
                            <div class="flex min-w-0 items-center gap-2 sm:gap-3">
                                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-slate-800 text-cyan-400 sm:h-9 sm:w-9">
                                    <i class="fas fa-cubes text-xs sm:text-sm"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-base font-bold text-slate-200 sm:text-lg">{{ number_format($this->totalQuantity) }}</p>
                                    <p class="text-[10px] text-slate-500 sm:text-xs">Cantidad</p>
                                </div>
                            </div>
                            {{-- Subtotal --}}
                            <div class="flex min-w-0 items-center gap-2 sm:gap-3">
                                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-slate-800 text-cyan-400 sm:h-9 sm:w-9">
                                    <i class="fas fa-calculator text-xs sm:text-sm"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-base font-bold text-slate-200 sm:text-lg">{{ $currency->symbol }} {{ number_format($this->subtotal, 2) }}</p>
                                    <p class="text-[10px] text-slate-500 sm:text-xs">Subtotal</p>
                                </div>
                            </div>
                            {{-- Descuento general --}}
                            <div class="flex min-w-0 items-center gap-2 sm:gap-3">
                                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-slate-800 text-amber-400 sm:h-9 sm:w-9">
                                    <i class="fas fa-tag text-xs sm:text-sm"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="flex min-w-0 items-center gap-1">
                                        <input
                                            type="number"
                                            wire:model.blur="general_discount_value"
                                            class="w-full min-w-0 rounded-md border border-slate-600 bg-slate-900/80 px-1.5 py-1 text-right text-xs font-bold text-slate-200 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 sm:px-2 sm:text-sm"
                                            min="0" step="0.01"
                                        >
                                        <button
                                            type="button"
                                            wire:click="toggleGeneralDiscountType"
                                            class="shrink-0 rounded-md border px-1.5 py-1 text-[10px] font-semibold transition-colors sm:px-2 sm:text-xs {{ $general_discount_type === 'percentage' ? 'border-amber-500/50 bg-amber-950/50 text-amber-300' : 'border-slate-600 bg-slate-800 text-slate-400' }}"
                                        >
                                            {{ $general_discount_type === 'percentage' ? '%' : $currency->symbol }}
                                        </button>
                                    </div>
                                    <p class="mt-0.5 truncate text-[10px] text-slate-500 sm:text-xs">Descuento</p>
                                </div>
                            </div>
                            {{-- Total --}}
                            <div class="col-span-1 flex min-w-0 items-center gap-2 sm:gap-3">
                                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-emerald-900/60 text-emerald-400 sm:h-9 sm:w-9">
                                    <i class="fas fa-dollar-sign text-xs sm:text-sm"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-base font-bold text-emerald-400 sm:text-lg">{{ $currency->symbol }} {{ number_format($this->totalAmount, 2) }}</p>
                                    <p class="text-[10px] text-slate-500 sm:text-xs">Total</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Nota --}}
                <div>
                    <label for="note" class="{{ $labelBase }}">
                        Nota
                    </label>
                    <textarea
                        id="note"
                        wire:model="note"
                        rows="2"
                        class="{{ $inputBase }} @error('note') border-rose-500/80 @enderror"
                        placeholder="Observaciones opcionales..."
                    ></textarea>
                    @error('note')
                        <p class="mt-1.5 text-sm text-rose-300">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Botones de acción --}}
                <div class="grid grid-cols-2 gap-2 pt-2 sm:flex sm:flex-wrap sm:items-center sm:gap-3">
                    <button
                        type="button"
                        wire:click="saveAndBack"
                        class="ui-btn ui-btn-primary col-span-2 justify-center text-sm"
                    >
                        <i class="fas fa-save mr-2 sm:mr-2"></i>
                        <span class="sm:hidden">{{ $isEdit ? 'Guardar' : 'Guardar' }}</span>
                        <span class="hidden sm:inline">{{ $isEdit ? 'Guardar cambios' : 'Guardar venta' }}</span>
                    </button>

                    @if (! $isEdit)
                        <button
                            type="button"
                            wire:click="saveAndCreateAnother"
                            class="ui-btn ui-btn-success justify-center text-xs sm:text-sm"
                        >
                            <i class="fas fa-plus-circle mr-1.5 sm:mr-2"></i>
                            <span class="sm:hidden">Guardar +</span>
                            <span class="hidden sm:inline">Guardar y crear otra</span>
                        </button>
                    @endif

                    <a
                        href="{{ route('admin.sales.index') }}"
                        class="ui-btn ui-btn-ghost justify-center text-xs sm:text-sm"
                        wire:navigate
                    >
                        <i class="fas fa-times mr-1.5 sm:mr-2"></i>
                        <span class="sm:hidden">Volver</span>
                        <span class="hidden sm:inline">Cancelar</span>
                    </a>
                </div>

            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- MODAL DE BÚSQUEDA DE PRODUCTOS                                --}}
    {{-- ================================================================ --}}
    @if ($show_product_modal)
        <div
            class="fixed inset-0 z-50 overflow-y-auto"
            x-data
            x-show="true"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
        >
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeProductModal"></div>

            <div class="flex min-h-full items-center justify-center p-2 sm:p-4">
                <div class="relative w-full max-w-full rounded-2xl border border-slate-700/70 bg-slate-950 shadow-2xl sm:max-w-5xl">

                    {{-- Header --}}
                    <div class="flex flex-col gap-3 border-b border-slate-700/70 px-4 py-3 xs:flex-row xs:items-center xs:justify-between sm:px-6 sm:py-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-cyan-950/60 text-cyan-400">
                                <i class="fas fa-boxes"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-100">Búsqueda de productos</h3>
                                <p class="text-sm text-slate-400">Selecciona los productos para agregar a la venta</p>
                            </div>
                        </div>
                        <button
                            type="button"
                            wire:click="closeProductModal"
                            class="rounded-full p-2 text-slate-400 hover:bg-slate-800 hover:text-slate-200 transition-colors"
                        >
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    {{-- Body --}}
                    <div class="px-3 py-3 sm:px-6 sm:py-4">
                        {{-- Búsqueda --}}
                        <div class="relative mb-4">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                                <i class="fas fa-search"></i>
                            </span>
                            <input
                                type="text"
                                wire:model.live.debounce.200ms="modal_search"
                                placeholder="Buscar por nombre, código o categoría..."
                                class="{{ $inputBase }} pl-10"
                                autocomplete="off"
                            >
                        </div>

                        {{-- Tabla de productos --}}
                        <div class="max-h-[55vh] overflow-y-auto overflow-x-auto">
                            @if ($modalProducts->isEmpty())
                                <div class="py-12 text-center">
                                    <i class="fas fa-search text-3xl text-slate-600 mb-3"></i>
                                    <p class="text-slate-400">No se encontraron productos</p>
                                </div>
                            @else
                                <table class="min-w-full divide-y divide-slate-700/70">
                                    <thead class="sticky top-0 z-10 bg-slate-900">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Código</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Producto</th>
                                            <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-400 sm:table-cell">Categoría</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Stock</th>
                                            <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-400 md:table-cell">Precio venta</th>
                                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-400"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-700/70">
                                        @foreach ($modalProducts as $product)
                                            <tr class="transition-colors hover:bg-slate-900/60">
                                                <td class="whitespace-nowrap px-4 py-3 font-mono text-sm text-slate-300">
                                                    {{ $product->code }}
                                                </td>
                                                <td class="px-4 py-3">
                                                    <div class="flex items-center gap-3">
                                                        <img
                                                            src="{{ $product->image_url }}"
                                                            alt="{{ $product->name }}"
                                                            class="h-9 w-9 shrink-0 rounded-lg object-cover"
                                                        >
                                                        <span class="text-sm font-medium text-slate-200">{{ $product->name }}</span>
                                                    </div>
                                                </td>
                                                <td class="hidden whitespace-nowrap px-4 py-3 sm:table-cell">
                                                    <span class="inline-flex rounded-full bg-slate-800 px-2 py-0.5 text-xs text-slate-400">
                                                        {{ $product->category->name ?? 'Sin categoría' }}
                                                    </span>
                                                </td>
                                                <td class="whitespace-nowrap px-4 py-3">
                                                    <span @class([
                                                        'inline-flex rounded-full px-2 py-0.5 text-xs font-semibold',
                                                        'bg-rose-900/60 text-rose-300' => $product->stock <= 0,
                                                        'bg-amber-900/60 text-amber-300' => $product->stock > 0 && $product->stock < 10,
                                                        'bg-emerald-900/60 text-emerald-300' => $product->stock >= 10,
                                                    ])>
                                                        {{ $product->stock }}
                                                    </span>
                                                </td>
                                                <td class="hidden whitespace-nowrap px-4 py-3 text-sm text-slate-300 md:table-cell">
                                                    {{ $currency->symbol }} {{ number_format($product->sale_price, 2) }}
                                                </td>
                                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                                    @if (in_array($product->id, $existingProductIds))
                                                        <span class="inline-flex items-center rounded-md bg-emerald-950/40 px-3 py-1.5 text-xs font-medium text-emerald-400">
                                                            <i class="fas fa-check mr-1.5"></i> Agregado
                                                        </span>
                                                    @elseif($product->stock <= 0)
                                                        <span class="inline-flex items-center rounded-md bg-rose-950/40 px-3 py-1.5 text-xs font-medium text-rose-400">
                                                            <i class="fas fa-ban mr-1.5"></i> Sin stock
                                                        </span>
                                                    @else
                                                        <button
                                                            type="button"
                                                            wire:click="addProductFromModal({{ $product->id }})"
                                                            class="inline-flex items-center rounded-md border border-cyan-500/30 bg-cyan-950/30 px-3 py-1.5 text-xs font-medium text-cyan-400 transition-colors hover:bg-cyan-950/60"
                                                        >
                                                            <i class="fas fa-plus mr-1.5"></i> Agregar
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>

                        {{-- Paginación --}}
                        @if ($modalProducts->hasPages())
                            <div class="mt-4 border-t border-slate-700/70 pt-3">
                                {{ $modalProducts->links(data: ['scrollTo' => false]) }}
                            </div>
                        @endif
                    </div>

                    {{-- Footer --}}
                    <div class="flex justify-end border-t border-slate-700/70 px-4 py-3 sm:px-6 sm:py-4">
                        <button
                            type="button"
                            wire:click="closeProductModal"
                            class="ui-btn ui-btn-ghost text-sm"
                        >
                            <i class="fas fa-times mr-2"></i>Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ================================================================ --}}
    {{-- MODAL DE VENTAS MASIVAS — estilo consistente con otros módulos  --}}
    {{-- ================================================================ --}}
    @if ($showBulkModal)
        <template x-teleport="body">
            <div class="fixed inset-0 z-[60] flex items-center justify-center bg-[#020617]/90 p-4 backdrop-blur-md"
                wire:click.self="closeBulkModal"
                x-data="{
                    bulkProducts: {{ Js::from($bulkProducts ?? []) }},
                    allCustomers: {{ Js::from($allCustomers ?? []) }},
                    productOpen: false,
                    productSearch: '',
                    selectedProductName: '{{ $bulkProductId ? ($bulkProducts->firstWhere('id', $bulkProductId)?->name ?? 'Seleccione el producto...') : 'Seleccione el producto...' }}',
                    getResolvedCount() { return ($wire.bulkResults || []).filter(r => r.status === 'resolved').length; },
                    getTotalCount() { return ($wire.bulkResults || []).length; },
                    hasResolved() { return this.getResolvedCount() > 0; },
                    selectProduct(p) {
                        $wire.set('bulkProductId', p.id);
                        this.selectedProductName = p.code + ' - ' + p.name;
                        this.productOpen = false;
                        this.productSearch = '';
                    }
                }"
                x-show="true"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-on:keydown.escape.window="$wire.closeBulkModal()"
            >
                <div class="relative w-full max-w-xl max-h-[90vh] overflow-hidden rounded-2xl border border-slate-600 bg-slate-900 text-slate-100 shadow-[0_25px_80px_rgba(0,0,0,0.75),inset_0_1px_0_rgba(255,255,255,0.06)] flex flex-col">

                    {{-- ═══════════════════════════════════════════════════════ --}}
                    {{-- HEADER                                                 --}}
                    {{-- ═══════════════════════════════════════════════════════ --}}
                    <div class="border-b border-slate-700 bg-slate-900 px-5 pb-4 pt-5 shrink-0">
                        <div class="flex items-start gap-3">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-indigo-500/40 bg-indigo-950 text-indigo-200">
                                <i class="fas fa-layer-group text-lg"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-base font-semibold text-white">Cargar Ventas Masivas</h3>
                                    <button type="button" wire:click="closeBulkModal" class="rounded-full p-1.5 text-slate-400 hover:bg-slate-800 hover:text-slate-200 transition-colors -mt-1 -mr-1">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <p class="mt-1 text-xs leading-relaxed text-slate-400">
                                    <span x-show="$wire.bulkResults.length === 0">Ingresá los datos y analizá para ver los resultados.</span>
                                    <span x-show="$wire.bulkResults.length > 0" x-text="getResolvedCount() + ' de ' + getTotalCount() + ' listos para procesar'"></span>
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- ═══════════════════════════════════════════════════════ --}}
                    {{-- BODY  — inputs SIEMPRE visibles + resultados abajo     --}}
                    {{-- ═══════════════════════════════════════════════════════ --}}
                    <div class="flex-1 overflow-y-auto">
                        <div class="p-5 space-y-5">

                            {{-- Inputs config — siempre presentes --}}
                            <div class="space-y-4">
                                {{-- Product selector --}}
                                <div @click.away="productOpen = false">
                                    <label class="{{ $labelBase }}">Producto Base *</label>
                                    <div class="relative">
                                        <button type="button" @click="productOpen = !productOpen" class="{{ $inputBase }} flex items-center justify-between">
                                            <span class="truncate" :class="selectedProductName === 'Seleccione el producto...' ? 'text-slate-500' : 'text-slate-100'" x-text="selectedProductName"></span>
                                            <i class="fas fa-chevron-down text-xs text-slate-500" :class="{ 'rotate-180': productOpen }"></i>
                                        </button>
                                        <div x-show="productOpen" x-cloak class="absolute z-50 mt-1 w-full rounded-lg border border-slate-700 bg-slate-900 shadow-xl">
                                            <div class="p-2 border-b border-slate-700/50">
                                                <input type="text" x-model="productSearch" class="{{ $inputBase }} text-xs py-2" placeholder="Buscar producto...">
                                            </div>
                                            <div class="max-h-48 overflow-y-auto">
                                                <template x-for="p in bulkProducts.filter(p => !productSearch || p.name.toLowerCase().includes(productSearch.toLowerCase()) || p.code.toLowerCase().includes(productSearch.toLowerCase()))" :key="p.id">
                                                    <button type="button" @click="selectProduct(p)"
                                                        class="w-full px-3 py-2 text-left text-sm text-slate-200 hover:bg-slate-800 transition-colors border-b border-slate-700/30 last:border-0">
                                                        <p class="font-medium truncate" x-text="p.name"></p>
                                                        <div class="flex items-center gap-2 text-xs text-slate-400">
                                                            <span x-text="p.code"></span>
                                                            <span>·</span>
                                                            <span :class="p.stock <= 5 ? 'text-rose-400' : 'text-slate-500'" x-text="'Stock: ' + p.stock"></span>
                                                            <span>·</span>
                                                            <span class="text-emerald-400" x-text="'$ ' + parseFloat(p.sale_price).toFixed(2)"></span>
                                                        </div>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Date + Time --}}
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="{{ $labelBase }}">Fecha *</label>
                                        <input type="date" wire:model="bulkSaleDate" class="{{ $inputBase }}" style="color-scheme: dark;">
                                    </div>
                                    <div>
                                        <label class="{{ $labelBase }}">Hora *</label>
                                        <input type="time" wire:model="bulkSaleTime" class="{{ $inputBase }}" style="color-scheme: dark;">
                                    </div>
                                </div>

                                {{-- Textarea --}}
                                <div>
                                    <label class="{{ $labelBase }}">Datos de Transacciones *</label>
                                    <textarea wire:model="bulkRawData" rows="6" class="{{ $inputBase }} font-mono text-xs min-h-[120px]"
                                        placeholder="Ingrese los datos de venta (un cliente por línea)&#10;&#10;Formato: nombre cantidad[-deuda]&#10;Ejemplos:&#10;  juan 1-0&#10;  pepe 2&#10;  maria 3-1"
                                        style="color-scheme: dark;"></textarea>
                                    <p class="mt-1 text-xs text-slate-500">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        <code class="text-slate-400">nombre cantidad</code> o <code class="text-slate-400">nombre cantidad-deuda_restante</code>
                                    </p>
                                </div>
                            </div>

                            {{-- Divider cuando hay resultados --}}
                            <div x-show="$wire.bulkResults.length > 0" class="border-t border-slate-700/50 pt-4">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="text-sm font-semibold text-slate-200">
                                        <i class="fas fa-list-check mr-1.5 text-indigo-400"></i>Resultados del análisis
                                    </h4>
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-slate-800 text-slate-400" x-text="getResolvedCount() + '/' + getTotalCount()"></span>
                                </div>

                                {{-- Analyzing state --}}
                                <div x-show="$wire.bulkIsAnalyzing" class="py-8 text-center">
                                    <div class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-indigo-950 text-indigo-400 mb-2">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </div>
                                    <p class="text-sm text-slate-400">Analizando datos...</p>
                                </div>

                                {{-- Results list --}}
                                <div class="space-y-2 max-h-[280px] overflow-y-auto pr-1">
                                    <template x-for="(result, index) in $wire.bulkResults" :key="index">
                                        <div :class="{
                                            'border-l-4 border-l-emerald-500 bg-emerald-950/20': result.status === 'resolved',
                                            'border-l-4 border-l-amber-500 bg-amber-950/20': result.status === 'ambiguous',
                                            'border-l-4 border-l-rose-500 bg-rose-950/20': result.status === 'not_found' || result.status === 'error',
                                            'border-l-4 border-l-slate-600 bg-slate-800/30 opacity-50': result.status === 'ignored',
                                        }" class="rounded-r-lg border border-slate-700/50 p-3">

                                            {{-- Ignored --}}
                                            <div x-show="result.status === 'ignored'" class="flex items-center justify-between">
                                                <span class="text-sm text-slate-500 line-through" x-text="result.originalText"></span>
                                                <button type="button" @click="$wire.restoreBulkLine(index)" class="text-xs text-cyan-400 hover:text-cyan-300">
                                                    <i class="fas fa-undo mr-1"></i>Restaurar
                                                </button>
                                            </div>

                                            {{-- Error --}}
                                            <div x-show="result.status === 'error'" class="flex items-center justify-between">
                                                <div class="min-w-0">
                                                    <p class="text-sm text-rose-300 line-through truncate" x-text="result.originalText"></p>
                                                    <p class="text-xs text-rose-400 mt-0.5" x-text="result.error"></p>
                                                </div>
                                                <button type="button" @click="$wire.ignoreBulkLine(index)" class="text-xs text-slate-400 hover:text-slate-300 ml-2 shrink-0">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>

                                            {{-- Not found --}}
                                            <div x-show="result.status === 'not_found'" class="flex items-center justify-between">
                                                <div class="min-w-0">
                                                    <p class="text-sm text-rose-200 font-medium truncate" x-text="result.clientName"></p>
                                                    <p class="text-xs text-rose-400 mt-0.5">
                                                        <i class="fas fa-user-slash mr-1"></i>No encontrado
                                                        <span class="text-slate-500" x-text="'(cantidad: ' + result.quantity + ')'"></span>
                                                    </p>
                                                </div>
                                                <button type="button" @click="$wire.ignoreBulkLine(index)" class="text-xs text-slate-400 hover:text-slate-300 ml-2 shrink-0">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>

                                            {{-- Resolved --}}
                                            <div x-show="result.status === 'resolved'" class="flex items-center justify-between">
                                                <div class="min-w-0">
                                                    <p class="text-sm text-emerald-200 font-medium truncate" x-text="result.clientName"></p>
                                                    <p class="text-xs text-emerald-400 mt-0.5">
                                                        <i class="fas fa-check-circle mr-1"></i>
                                                        <span x-text="'Cantidad: ' + result.quantity"></span>
                                                        <template x-if="result.isPaid">
                                                            <span class="ml-1 text-emerald-300">· Pagado</span>
                                                        </template>
                                                        <template x-if="result.isPartialPayment">
                                                            <span class="ml-1 text-amber-300" x-text="'· Parcial (resta: ' + result.remainingQuantity + ')'"></span>
                                                        </template>
                                                    </p>
                                                    <p class="text-xs text-slate-500 mt-0.5 truncate" x-show="result.selectedCustomer" x-text="'→ ' + result.selectedCustomer.name"></p>
                                                </div>
                                                <button type="button" @click="$wire.ignoreBulkLine(index)" class="text-xs text-slate-400 hover:text-slate-300 ml-2 shrink-0">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>

                                            {{-- Ambiguous --}}
                                            <div x-show="result.status === 'ambiguous'" class="space-y-2">
                                                <div class="flex items-center justify-between">
                                                    <div class="min-w-0">
                                                        <p class="text-sm text-amber-200 font-medium truncate" x-text="result.clientName"></p>
                                                        <p class="text-xs text-amber-400 mt-0.5">
                                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                                            <span x-text="result.matches.length + ' coincidencias'"></span>
                                                            <span class="text-slate-500" x-text="'(cantidad: ' + result.quantity + ')'"></span>
                                                        </p>
                                                    </div>
                                                    <button type="button" @click="$wire.ignoreBulkLine(index)" class="text-xs text-slate-400 hover:text-slate-300 ml-2 shrink-0">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <select @change="$wire.resolveBulkMatch(index, parseInt($event.target.value))"
                                                        class="flex-1 rounded-md border border-slate-600 bg-slate-900/80 px-2 py-1.5 text-xs text-slate-200 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                                                        <option value="">Seleccionar cliente...</option>
                                                        <template x-for="match in result.matches" :key="match.id">
                                                            <option :value="match.id" x-text="match.name + (match.phone ? ' — ' + match.phone : '')"></option>
                                                        </template>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ═══════════════════════════════════════════════════════ --}}
                    {{-- FOOTER                                                 --}}
                    {{-- ═══════════════════════════════════════════════════════ --}}
                    <div class="flex flex-wrap justify-end gap-2 border-t border-slate-700 bg-slate-950 px-4 py-3 shrink-0">
                        <button type="button" wire:click="closeBulkModal" class="ui-btn ui-btn-ghost text-sm">Cancelar</button>

                        {{-- Analyze button — siempre visible --}}
                        <button type="button"
                            wire:click="analyzeBulkData"
                            wire:loading.attr="disabled"
                            class="ui-btn ui-btn-primary text-sm"
                        >
                            <i class="fas fa-search mr-1.5"></i>
                            <span wire:loading.remove wire:target="analyzeBulkData">Analizar Datos</span>
                            <span wire:loading wire:target="analyzeBulkData">Analizando...</span>
                        </button>

                        {{-- Process button — solo visible cuando hay resultados --}}
                        <button type="button"
                            x-show="$wire.bulkResults.length > 0 && !$wire.bulkIsAnalyzing"
                            x-bind:disabled="!hasResolved()"
                            :class="hasResolved() ? 'ui-btn ui-btn-success text-sm' : 'ui-btn ui-btn-ghost text-sm opacity-50 cursor-not-allowed'"
                            @click="
                                const resolved = ($wire.bulkResults || []).filter(r => r.status === 'resolved');
                                const pending = ($wire.bulkResults || []).filter(r => ['ambiguous', 'not_found', 'error'].includes(r.status));

                                if (pending.length > 0) {
                                    window.uiNotifications?.showToast?.('Hay ' + pending.length + ' transacciones por resolver', {type:'error', title:'Pendientes', timeout:4800, theme:'futuristic'});
                                    return;
                                }
                                if (resolved.length === 0) {
                                    window.uiNotifications?.showToast?.('No hay ventas válidas para procesar', {type:'warning', title:'Sin Ventas', timeout:4800, theme:'futuristic'});
                                    return;
                                }

                                const product = bulkProducts.find(p => p.id == $wire.bulkProductId);
                                if (!product) {
                                    window.uiNotifications?.showToast?.('Producto no encontrado', {type:'error', title:'Error', timeout:4800, theme:'futuristic'});
                                    return;
                                }

                                const price = parseFloat(product.sale_price) || 0;
                                const paidCount = resolved.filter(r => r.isPaid || r.isPartialPayment).length;
                                const paidMsg = paidCount > 0 ? ' Se registrarán ' + paidCount + ' pagos automáticos.' : '';

                                Swal.fire({
                                    title: '¿Confirmar Venta Masiva?',
                                    html: 'Se generarán <b>' + resolved.length + '</b> ventas para <b>' + product.name + '</b>.' + paidMsg + '<br>¿Desea continuar?',
                                    icon: 'question',
                                    showCancelButton: true,
                                    confirmButtonColor: '#10b981',
                                    cancelButtonColor: '#6b7280',
                                    confirmButtonText: 'Sí, procesar',
                                    cancelButtonText: 'Cancelar',
                                    background: '#0f172a',
                                    color: '#e2e8f0',
                                    customClass: { popup: 'border border-slate-700 rounded-xl' }
                                }).then((swalResult) => {
                                    if (!swalResult.isConfirmed) return;

                                    const sales = resolved.map(r => {
                                        let paymentAmount = 0;
                                        let isPaid = false;

                                        if (r.isPaid) {
                                            paymentAmount = r.quantity * price;
                                            isPaid = true;
                                        } else if (r.isPartialPayment && r.remainingQuantity !== null) {
                                            const paidQty = r.quantity - r.remainingQuantity;
                                            paymentAmount = paidQty * price;
                                            isPaid = paymentAmount > 0;
                                        }

                                        return {
                                            customer_id: r.selectedCustomer.id,
                                            quantity: r.quantity,
                                            price: price,
                                            is_paid: isPaid,
                                            payment_amount: paymentAmount
                                        };
                                    });

                                    window.uiNotifications?.showToast?.('Procesando ' + resolved.length + ' ventas...', {type:'info', title:'Enviando', timeout:4800, theme:'futuristic'});

                                    fetch('/sales/bulk-store', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                            'Accept': 'application/json',
                                            'X-Requested-With': 'XMLHttpRequest'
                                        },
                                        body: JSON.stringify({
                                            product_id: $wire.bulkProductId,
                                            sale_date: $wire.bulkSaleDate,
                                            sale_time: $wire.bulkSaleTime,
                                            sales: sales
                                        })
                                    })
                                    .then(r => r.json())
                                    .then(data => {
                                        if (data.success) {
                                            window.uiNotifications?.showToast?.(data.message || '¡Ventas masivas procesadas exitosamente!', {type:'success', title:'Éxito', timeout:4800, theme:'futuristic'});
                                            $wire.closeBulkModal();
                                        } else {
                                            window.uiNotifications?.showToast?.(data.message || 'Error al procesar ventas masivas', {type:'error', title:'Error', timeout:7200, theme:'futuristic'});
                                        }
                                    })
                                    .catch(err => {
                                        window.uiNotifications?.showToast?.('Error de red: ' + err.message, {type:'error', title:'Error', timeout:7200, theme:'futuristic'});
                                    });
                                });
                            "
                        >
                            <i class="fas fa-paper-plane mr-1.5"></i> Procesar ventas
                        </button>
                    </div>
                </div>
            </div>
        </template>
    @endif
</div>