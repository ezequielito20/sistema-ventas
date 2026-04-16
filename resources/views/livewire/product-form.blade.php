@php
    $inputBase = 'w-full rounded-lg border border-slate-600 bg-slate-950/60 px-3 py-2.5 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500';
    $labelBase = 'mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400';
@endphp

<div class="space-y-6" wire:key="product-form-{{ $productId ?? 'create' }}">
    <div class="ui-panel">
        <div class="ui-panel__header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="ui-panel__title">{{ $headingTitle }}</h1>
                <p class="ui-panel__subtitle">{{ $headingSubtitle }}</p>
            </div>
            <a
                href="{{ route('admin.products.index') }}"
                class="ui-btn ui-btn-ghost shrink-0 text-sm md:py-2.5 md:px-5 md:text-[0.95rem]"
                wire:navigate
            >
                <i class="fas fa-arrow-left"></i> Volver al listado
            </a>
        </div>
    </div>

    <div class="ui-panel">
        <div class="ui-panel__body">
            <form wire:submit="saveAndBack" class="space-y-0">
                {{-- Datos generales --}}
                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-slate-200">Datos generales</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="code" class="{{ $labelBase }}">Código <span class="text-rose-400">*</span></label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                                    <i class="fas fa-barcode text-[0.8rem]"></i>
                                </span>
                                <input
                                    id="code"
                                    type="text"
                                    wire:model.blur="code"
                                    placeholder="PROD001"
                                    class="{{ $inputBase }} pl-9 @error('code') border-rose-500/80 @enderror"
                                    autocomplete="off"
                                >
                            </div>
                            @error('code')
                                <p class="mt-1.5 text-sm text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="name" class="{{ $labelBase }}">Nombre <span class="text-rose-400">*</span></label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                                    <i class="fas fa-cube text-[0.8rem]"></i>
                                </span>
                                <input
                                    id="name"
                                    type="text"
                                    wire:model.blur="name"
                                    placeholder="Nombre del producto"
                                    class="{{ $inputBase }} pl-9 @error('name') border-rose-500/80 @enderror"
                                >
                            </div>
                            @error('name')
                                <p class="mt-1.5 text-sm text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="category_id" class="{{ $labelBase }}">Categoría <span class="text-rose-400">*</span></label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                                    <i class="fas fa-folder text-[0.8rem]"></i>
                                </span>
                                <select
                                    id="category_id"
                                    wire:model.live="category_id"
                                    class="{{ $inputBase }} appearance-none pl-9 pr-9 @error('category_id') border-rose-500/80 @enderror"
                                >
                                    <option value="">Selecciona una categoría</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-500">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </span>
                            </div>
                            @error('category_id')
                                <p class="mt-1.5 text-sm text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="entry_date" class="{{ $labelBase }}">Fecha de ingreso <span class="text-rose-400">*</span></label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                                    <i class="fas fa-calendar-alt text-[0.8rem]"></i>
                                </span>
                                <input
                                    id="entry_date"
                                    type="date"
                                    wire:model.live="entry_date"
                                    max="{{ date('Y-m-d') }}"
                                    class="{{ $inputBase }} pl-9 [color-scheme:dark] @error('entry_date') border-rose-500/80 @enderror"
                                >
                            </div>
                            @error('entry_date')
                                <p class="mt-1.5 text-sm text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="description" class="{{ $labelBase }}">Descripción</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-0 top-3 flex pl-3 text-slate-500">
                                <i class="fas fa-align-left"></i>
                            </span>
                            <textarea
                                id="description"
                                wire:model.blur="description"
                                rows="4"
                                class="{{ $inputBase }} py-2.5 pl-10 @error('description') border-rose-500/80 @enderror"
                                placeholder="Describe las características del producto"
                            ></textarea>
                        </div>
                        @error('description')
                            <p class="mt-1.5 text-sm text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-8 border-t border-slate-700/60 pt-8">
                    <h3 class="mb-4 text-sm font-semibold text-slate-200">Imagen</h3>
                    <label for="image" class="{{ $labelBase }}">Archivo (opcional en edición si ya hay imagen)</label>
                    <input
                        id="image"
                        type="file"
                        accept="image/*"
                        wire:model="image"
                        class="block w-full cursor-pointer rounded-lg border border-dashed border-slate-600 bg-slate-950/40 px-3 py-3 text-sm text-slate-300 file:mr-4 file:cursor-pointer file:rounded-lg file:border-0 file:bg-slate-700 file:px-4 file:py-2 file:text-sm file:font-medium file:text-slate-100 hover:file:bg-slate-600 @error('image') border-rose-500/80 @enderror"
                    >
                    <div wire:loading wire:target="image" class="mt-2 text-xs text-slate-400">
                        <i class="fas fa-spinner fa-spin"></i> Procesando imagen…
                    </div>
                    @if ($image)
                        <div class="mt-4 flex items-center gap-4 rounded-lg border border-slate-700/60 bg-slate-950/50 p-3">
                            <img src="{{ $image->temporaryUrl() }}" alt="" class="h-16 w-16 shrink-0 rounded-lg border border-slate-600 object-cover">
                            <p class="text-xs leading-relaxed text-slate-400">Vista previa del archivo seleccionado.</p>
                        </div>
                    @elseif ($existingImageUrl)
                        <div class="mt-4 flex items-center gap-4 rounded-lg border border-slate-700/60 bg-slate-950/50 p-3">
                            <img src="{{ $existingImageUrl }}" alt="" class="h-16 w-16 shrink-0 rounded-lg border border-slate-600 object-cover">
                            <p class="text-xs leading-relaxed text-slate-400">Si no subes otra imagen, se conserva la actual.</p>
                        </div>
                    @endif
                    @error('image')
                        <p class="mt-1.5 text-sm text-rose-300">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-8 border-t border-slate-700/60 pt-8">
                    <h3 class="mb-4 text-sm font-semibold text-slate-200">Inventario</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <label for="stock" class="{{ $labelBase }}">Stock actual <span class="text-rose-400">*</span></label>
                            <input
                                id="stock"
                                type="number"
                                min="0"
                                wire:model.blur="stock"
                                class="{{ $inputBase }} tabular-nums @error('stock') border-rose-500/80 @enderror"
                            >
                            @error('stock')
                                <p class="mt-1.5 text-sm text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="min_stock" class="{{ $labelBase }}">Stock mínimo <span class="text-rose-400">*</span></label>
                            <input
                                id="min_stock"
                                type="number"
                                min="0"
                                wire:model.blur="min_stock"
                                class="{{ $inputBase }} tabular-nums @error('min_stock') border-rose-500/80 @enderror"
                            >
                            @error('min_stock')
                                <p class="mt-1.5 text-sm text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="max_stock" class="{{ $labelBase }}">Stock máximo <span class="text-rose-400">*</span></label>
                            <input
                                id="max_stock"
                                type="number"
                                min="0"
                                wire:model.blur="max_stock"
                                class="{{ $inputBase }} tabular-nums @error('max_stock') border-rose-500/80 @enderror"
                            >
                            @error('max_stock')
                                <p class="mt-1.5 text-sm text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mt-8 border-t border-slate-700/60 pt-8">
                    <h3 class="mb-4 text-sm font-semibold text-slate-200">Precios</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="purchase_price" class="{{ $labelBase }}">Precio de compra <span class="text-rose-400">*</span></label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-slate-400">{{ $currency->symbol }}</span>
                                <input
                                    id="purchase_price"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    wire:model.blur="purchase_price"
                                    class="{{ $inputBase }} pl-9 tabular-nums @error('purchase_price') border-rose-500/80 @enderror"
                                >
                            </div>
                            @error('purchase_price')
                                <p class="mt-1.5 text-sm text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="sale_price" class="{{ $labelBase }}">Precio de venta <span class="text-rose-400">*</span></label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-slate-400">{{ $currency->symbol }}</span>
                                <input
                                    id="sale_price"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    wire:model.blur="sale_price"
                                    class="{{ $inputBase }} pl-9 tabular-nums @error('sale_price') border-rose-500/80 @enderror"
                                >
                            </div>
                            @error('sale_price')
                                <p class="mt-1.5 text-sm text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex flex-wrap items-center justify-end gap-2 border-t border-slate-700/60 pt-6">
                    <a
                        href="{{ route('admin.products.index') }}"
                        wire:navigate
                        class="ui-btn ui-btn-ghost text-sm md:py-2.5 md:px-5 md:text-[0.95rem]"
                    >
                        Cancelar
                    </a>

                    @unless ($isEdit)
                        <button
                            type="button"
                            wire:click="saveAndCreateAnother"
                            class="ui-btn ui-btn-ghost text-sm md:py-2.5 md:px-5 md:text-[0.95rem]"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove wire:target="saveAndCreateAnother">
                                <i class="fas fa-plus-circle"></i>
                                Crear y crear otro
                            </span>
                            <span wire:loading wire:target="saveAndCreateAnother" class="inline-flex items-center gap-2">
                                <i class="fas fa-spinner fa-spin"></i>
                                Guardando…
                            </span>
                        </button>
                    @endunless

                    <button type="submit" class="ui-btn ui-btn-primary text-sm md:py-2.5 md:px-5 md:text-[0.95rem]" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="saveAndBack">
                            <i class="fas fa-save"></i>
                            {{ $isEdit ? 'Actualizar producto' : 'Guardar producto' }}
                        </span>
                        <span wire:loading wire:target="saveAndBack" class="inline-flex items-center gap-2">
                            <i class="fas fa-spinner fa-spin"></i>
                            Guardando…
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
