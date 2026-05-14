@php
    $inputBase = 'w-full rounded-lg border border-slate-600 bg-slate-950/60 px-3 py-2.5 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500';
    $labelBase = 'mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400';
@endphp

<div class="space-y-6" wire:key="purchase-form-{{ $purchaseId ?? 'create' }}">
    {{-- ================================================================ --}}
    {{-- HEADER                                                        --}}
    {{-- ================================================================ --}}
    <div class="ui-panel">
        <div class="ui-panel__header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="ui-panel__title">{{ $headingTitle }}</h1>
                <p class="ui-panel__subtitle">{{ $headingSubtitle }}</p>
            </div>
            <a
                href="{{ route('admin.purchases.index') }}"
                wire:navigate
            >
                <i class="fas fa-arrow-left"></i> Volver al listado
            </a>
        </div>
    </div>


    {{-- ================================================================ --}}
    {{-- FORMULARIO PRINCIPAL                                          --}}
    {{-- ================================================================ --}}
    <div class="ui-panel">
        <div class="ui-panel__body">
            <div class="space-y-6">

                {{-- Fecha y Hora --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="purchase_date" class="{{ $labelBase }}">
                            Fecha <span class="text-rose-400">*</span>
                        </label>
                        <input
                            id="purchase_date"
                            type="date"
                            wire:model="purchase_date"
                            class="{{ $inputBase }} @error('purchase_date') border-rose-500/80 @enderror"
                        >
                        @error('purchase_date')
                            <p class="mt-1.5 text-sm text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="purchase_time" class="{{ $labelBase }}">
                            Hora <span class="text-rose-400">*</span>
                        </label>
                        <input
                            id="purchase_time"
                            type="time"
                            wire:model="purchase_time"
                            class="{{ $inputBase }} @error('purchase_time') border-rose-500/80 @enderror"
                        >
                        @error('purchase_time')
                            <p class="mt-1.5 text-sm text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

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
                                    const res = await fetch('/purchases/product-by-code/' + encodeURIComponent(code));
                                    const data = await res.json();
                                    if (data.success) {
                                        const p = data.product;
                                        const alreadyAdded = {{ Js::from(collect($items)->pluck('product_id')->all()) }};
                                        if (alreadyAdded.includes(p.id)) {
                                            window.uiNotifications?.showToast?.('Este producto ya está en la lista de compra', {type:'warning', title:'Atención', timeout:4800, theme:'futuristic'});
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
                                <i class="fas fa-shopping-cart text-2xl text-slate-500"></i>
                            </div>
                            <h4 class="text-lg font-medium text-slate-300">No hay productos agregados</h4>
                            <p class="mt-1 text-sm text-slate-500">
                                Escanee un producto o use el botón "Buscar productos" para agregar items a la compra.
                            </p>
                        </div>
                    @else
                        {{-- Mobile cards (hidden sm and up) --}}
                        <div class="space-y-3 sm:hidden">
                            @foreach ($items as $index => $item)
                                <div
                                    class="rounded-xl border border-slate-700/70 bg-slate-950/60 p-4"
                                    wire:key="purchase-item-mobile-{{ $index }}"
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
                                                'bg-rose-900/60 text-rose-300' => ($item['stock'] ?? 0) < 10,
                                                'bg-amber-900/60 text-amber-300' => ($item['stock'] ?? 0) >= 10 && ($item['stock'] ?? 0) < 50,
                                                'bg-emerald-900/60 text-emerald-300' => ($item['stock'] ?? 0) >= 50,
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
                                                min="1"
                                                step="1"
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
                                        <tr class="transition-colors hover:bg-slate-900/60" wire:key="purchase-item-{{ $index }}">
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
                                                    'bg-rose-900/60 text-rose-300' => ($item['stock'] ?? 0) < 10,
                                                    'bg-amber-900/60 text-amber-300' => ($item['stock'] ?? 0) >= 10 && ($item['stock'] ?? 0) < 50,
                                                    'bg-emerald-900/60 text-emerald-300' => ($item['stock'] ?? 0) >= 50,
                                                ])>
                                                    {{ $item['stock'] ?? 0 }}
                                                </span>
                                            </td>
                                            {{-- Cantidad --}}
                                            <td class="whitespace-nowrap px-4 py-3">
                                                <input
                                                    type="number"
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

                {{-- Botones de acción --}}
                <div class="grid grid-cols-2 gap-2 pt-2 sm:flex sm:flex-wrap sm:items-center sm:gap-3">
                    <button
                        type="button"
                        wire:click="saveAndBack"
                        class="ui-btn ui-btn-primary col-span-2 justify-center text-sm"
                    >
                        <i class="fas fa-save mr-2 sm:mr-2"></i>
                        <span class="sm:hidden">{{ $isEdit ? 'Guardar' : 'Guardar' }}</span>
                        <span class="hidden sm:inline">{{ $isEdit ? 'Guardar cambios' : 'Guardar compra' }}</span>
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
                        href="{{ route('admin.purchases.index') }}"
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
        <template x-teleport="body">
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
                                <p class="text-sm text-slate-400">Selecciona los productos para agregar a la compra</p>
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

                        {{-- Productos: cards (móvil) + tabla (desktop) --}}
                        <div class="max-h-[55vh] overflow-y-auto">
                            @if ($modalProducts->isEmpty())
                                <div class="py-12 text-center">
                                    <i class="fas fa-search text-3xl text-slate-600 mb-3"></i>
                                    <p class="text-slate-400">No se encontraron productos</p>
                                </div>
                            @else
                                {{-- Vista móvil: tarjetas --}}
                                <div class="md:hidden space-y-2 p-2">
                                    @foreach ($modalProducts as $product)
                                        <div class="flex items-center gap-3 rounded-xl border border-slate-700/50 bg-slate-800/40 px-3 py-2.5">
                                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-10 w-10 shrink-0 rounded-lg object-cover">
                                            <div class="flex-1 min-w-0">
                                                <div class="text-sm font-medium text-slate-200 truncate">{{ $product->name }}</div>
                                                <div class="flex items-center gap-2 mt-0.5">
                                                    <span class="text-[11px] text-slate-500">{{ $product->code }}</span>
                                                    <span class="text-[11px] text-slate-500">Stock: {{ $product->stock }}</span>
                                                </div>
                                            </div>
                                            <div class="text-right flex-shrink-0">
                                                <div class="text-sm font-semibold text-cyan-400">{{ $currency->symbol }} {{ number_format($product->purchase_price, 2) }}</div>
                                                @if (in_array($product->id, $existingProductIds))
                                                    <span class="text-[11px] text-emerald-400"><i class="fas fa-check"></i> Agregado</span>
                                                @else
                                                    <button type="button" wire:click="addProductFromModal({{ $product->id }})"
                                                        class="text-[11px] font-medium text-cyan-400 hover:text-cyan-300"><i class="fas fa-plus"></i> Agregar</button>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Vista desktop: tabla --}}
                                <div class="hidden md:block overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-700/70">
                                    <thead class="sticky top-0 z-10 bg-slate-900">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Código</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Producto</th>
                                            <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-400 sm:table-cell">Categoría</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Stock</th>
                                            <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-400 md:table-cell">Precio compra</th>
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
                                                        'bg-rose-900/60 text-rose-300' => $product->stock < 10,
                                                        'bg-amber-900/60 text-amber-300' => $product->stock >= 10 && $product->stock < 50,
                                                        'bg-emerald-900/60 text-emerald-300' => $product->stock >= 50,
                                                    ])>
                                                        {{ $product->stock }}
                                                    </span>
                                                </td>
                                                <td class="hidden whitespace-nowrap px-4 py-3 text-sm text-slate-300 md:table-cell">
                                                    {{ $currency->symbol }} {{ number_format($product->purchase_price, 2) }}
                                                </td>
                                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                                    @if (in_array($product->id, $existingProductIds))
                                                        <span class="inline-flex items-center rounded-md bg-emerald-950/40 px-3 py-1.5 text-xs font-medium text-emerald-400">
                                                            <i class="fas fa-check mr-1.5"></i> Agregado
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
                                </div>
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
        </template>
    @endif
</div>
