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
                    <h3 class="mb-1 text-sm font-semibold text-slate-200">Imagen</h3>
                    <p class="mb-4 text-xs text-slate-500">
                        Opcional en edición si ya hay imagen. Tras elegir archivo, se sube un temporal y aquí verás la vista previa.
                    </p>
                    {{--
                        Vista previa: resources/js/app.js (delegación en document). wire:ignore evita que Livewire pise el <img>.
                    --}}
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-start sm:gap-6 md:gap-7">
                        <div class="min-w-0 w-full max-w-md space-y-3 sm:w-auto sm:max-w-[17rem]">
                            <span class="{{ $labelBase }}">Archivo</span>
                            <div class="flex flex-col gap-2">
                                <div class="flex flex-wrap items-center gap-3">
                                    <input
                                        id="product-form-image-input"
                                        type="file"
                                        accept="image/jpeg,image/png,image/gif,image/webp"
                                        wire:model="image"
                                        class="sr-only"
                                    >
                                    <label
                                        for="product-form-image-input"
                                        class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-dashed border-slate-500 bg-slate-950/60 px-4 py-2.5 text-sm font-medium text-slate-100 transition hover:border-cyan-500/60 hover:bg-slate-900/80 focus-within:ring-2 focus-within:ring-cyan-500/40 @error('image') border-rose-500/70 @enderror"
                                    >
                                        <i class="fas fa-folder-open text-slate-400"></i>
                                        <span>Seleccionar imagen</span>
                                    </label>
                                    <div wire:loading wire:target="image" class="inline-flex items-center gap-2 text-xs text-cyan-300/90">
                                        <i class="fas fa-circle-notch fa-spin"></i>
                                        Subiendo temporal…
                                    </div>
                                </div>
                                @if ($image)
                                    <p class="truncate text-xs text-slate-400" title="{{ $image->getClientOriginalName() }}">
                                        <i class="fas fa-paperclip mr-1 text-slate-500"></i>{{ $image->getClientOriginalName() }}
                                    </p>
                                @endif
                                <p class="text-xs text-slate-500">Formatos: JPG, PNG, GIF o WebP · máximo 2&nbsp;MB.</p>
                            </div>
                            @error('image')
                                <p class="text-sm text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mx-auto flex w-full max-w-[13rem] shrink-0 flex-col items-center gap-2 sm:mx-0">
                            <p class="w-full text-left text-[0.65rem] font-medium uppercase tracking-wide text-slate-500 sm:text-center">
                                Vista previa
                            </p>
                            <div
                                wire:ignore
                                id="product-form-preview-root"
                                data-existing-url="{{ $existingImageUrl ?? '' }}"
                                class="relative h-48 w-full max-w-[13rem] overflow-hidden rounded-xl border border-slate-600 bg-slate-900/80 shadow-inner ring-1 ring-white/5"
                            >
                                <img
                                    id="product-form-preview-img"
                                    @if ($existingImageUrl) src="{{ $existingImageUrl }}" @endif
                                    alt=""
                                    class="absolute inset-0 h-full w-full object-cover {{ $existingImageUrl ? '' : 'hidden' }}"
                                >
                                <div
                                    id="product-form-preview-empty"
                                    class="{{ $existingImageUrl ? 'hidden' : '' }} absolute inset-0 flex flex-col items-center justify-center gap-2 p-4 text-center text-slate-500"
                                >
                                    <div class="rounded-full bg-slate-800/80 p-3 text-cyan-500/90">
                                        <i class="fas fa-cloud-upload-alt text-2xl"></i>
                                    </div>
                                    <span class="text-xs font-medium text-slate-400">Selecciona una imagen</span>
                                    <span class="text-[0.65rem] leading-snug text-slate-600">JPG, PNG, GIF o WebP · hasta 2&nbsp;MB</span>
                                </div>
                            </div>
                        </div>
                    </div>
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
