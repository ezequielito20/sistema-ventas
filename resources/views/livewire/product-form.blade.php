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

                        <div class="flex w-full max-w-[13rem] shrink-0 flex-col gap-2 self-start sm:w-[13rem]">
                            <p class="text-[0.65rem] font-medium uppercase tracking-wide text-slate-500">
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

                {{-- Galería de imágenes adicionales --}}
                <div class="mt-8 border-t border-slate-700/60 pt-8" x-data="productGallery()" x-init="init()">
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-200">Galería de imágenes</h3>
                            <p class="mt-1 text-xs text-slate-500">
                                Podés agregar hasta {{ $galleryMax }} imágenes adicionales para mostrar el producto desde diferentes ángulos.
                            </p>
                        </div>
                        @if(count($existingImages) > 0 || count($newImages) > 0)
                            <span class="rounded-full bg-slate-800 px-2.5 py-1 text-[11px] font-medium text-slate-400">
                                {{ count($existingImages) + count($newImages) }} de 5
                            </span>
                        @endif
                    </div>

                    {{-- Dropzone --}}
                    @if(count($existingImages) + count($newImages) < 5)
                    <div
                        x-ref="dropzone"
                        class="relative flex flex-col items-center justify-center gap-3 rounded-xl border-2 border-dashed border-slate-600 bg-slate-950/40 px-6 py-10 transition hover:border-cyan-500/60 hover:bg-slate-900/60"
                        :class="{ 'border-cyan-400 bg-cyan-500/5': dragging }"
                        @dragover.prevent="dragging = true"
                        @dragleave.prevent="dragging = false"
                        @drop.prevent="handleDrop($event)"
                    >
                        <div class="rounded-full bg-slate-800/80 p-3 text-cyan-500/90">
                            <i class="fas fa-images text-2xl"></i>
                        </div>
                        <div class="text-center">
                            <p class="text-sm font-medium text-slate-300">
                                Arrastrá tus imágenes acá
                            </p>
                            <p class="mt-1 text-xs text-slate-500">o hacé clic para seleccionar</p>
                        </div>
                        <p class="text-[0.65rem] text-slate-600">JPG, PNG, GIF o WebP · hasta 2 MB cada una</p>
                        <input
                            type="file"
                            accept="image/jpeg,image/png,image/gif,image/webp"
                            wire:model="newImages"
                            multiple
                            class="absolute inset-0 cursor-pointer opacity-0"
                            x-ref="fileInput"
                        >
                        <div wire:loading wire:target="newImages" class="absolute inset-0 flex items-center justify-center rounded-xl bg-slate-950/80 backdrop-blur-sm">
                            <div class="flex items-center gap-3 rounded-lg bg-slate-800 px-4 py-2.5 text-sm text-cyan-300">
                                <i class="fas fa-circle-notch fa-spin"></i>
                                Subiendo imágenes…
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="flex flex-col items-center justify-center gap-2 rounded-xl border border-slate-700 bg-slate-950/30 px-6 py-6 text-center">
                        <i class="fas fa-check-circle text-2xl text-emerald-500/70"></i>
                        <p class="text-sm font-medium text-slate-400">Límite de imágenes alcanzado</p>
                        <p class="text-xs text-slate-500">Eliminá alguna imagen para agregar más.</p>
                    </div>
                    @endif

                    {{-- Existing images --}}
                    @if(count($existingImages) > 0)
                    <div class="mt-4">
                        <p class="mb-2 text-[0.65rem] font-medium uppercase tracking-wide text-slate-500">Imágenes guardadas</p>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                            @foreach($existingImages as $img)
                            <div class="group relative aspect-video overflow-hidden rounded-lg border @if($coverImageId === $img['id']) border-dv-primary ring-2 ring-dv-primary/40 @else border-slate-700 @endif bg-slate-900/80">
                                <img src="{{ $img['url'] }}" alt="" class="h-full w-full object-cover" loading="lazy">
                                <div class="absolute inset-0 flex items-start justify-end p-1.5 opacity-0 transition group-hover:opacity-100">
                                    <div class="flex gap-1">
                                        <button type="button"
                                                wire:click="setCoverImage({{ $img['id'] }})"
                                                class="flex h-7 w-7 items-center justify-center rounded-full bg-slate-900/90 text-xs backdrop-blur-sm transition hover:bg-dv-primary hover:text-white {{ $coverImageId === $img['id'] ? 'text-dv-primary' : 'text-slate-400' }}"
                                                title="{{ __('Marcar como portada') }}">
                                            <i class="fas fa-star"></i>
                                        </button>
                                        <button type="button"
                                                wire:click="removeExistingImage({{ $img['id'] }})"
                                                x-on:click.prevent="
                                                    if (typeof Swal === 'undefined') { $wire.removeExistingImage({{ $img['id'] }}); return; }
                                                    Swal.fire({
                                                        title: '{{ __('¿Eliminar imagen?') }}',
                                                        text: '{{ __('Esta imagen se quitará de la galería al guardar.') }}',
                                                        icon: 'warning',
                                                        showCancelButton: true,
                                                        confirmButtonColor: '#d33',
                                                        cancelButtonColor: '#475569',
                                                        confirmButtonText: '{{ __('Sí, eliminar') }}',
                                                        cancelButtonText: '{{ __('Cancelar') }}'
                                                    }).then((result) => {
                                                        if (result.isConfirmed) {
                                                            $wire.removeExistingImage({{ $img['id'] }});
                                                        }
                                                    });
                                                "
                                                class="flex h-7 w-7 items-center justify-center rounded-full bg-slate-900/90 text-xs text-rose-400 backdrop-blur-sm transition hover:bg-rose-600/80 hover:text-white"
                                                title="{{ __('Eliminar imagen') }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                @if($coverImageId === $img['id'])
                                    <span class="absolute bottom-1.5 left-1.5 rounded-full bg-dv-primary px-2 py-0.5 font-dv-label text-[9px] font-bold uppercase text-white shadow-sm">
                                        {{ __('Portada') }}
                                    </span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- New images preview --}}
                    @if(count($newImages) > 0)
                    <div class="mt-4">
                        <p class="mb-2 text-[0.65rem] font-medium uppercase tracking-wide text-slate-500">Nuevas imágenes</p>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                            @foreach($newImages as $index => $img)
                            <div class="group relative aspect-video overflow-hidden rounded-lg border border-cyan-500/50 bg-slate-900/80">
                                <img src="{{ $img->temporaryUrl() }}" alt="" class="h-full w-full object-cover" loading="lazy">
                                <div class="absolute inset-0 flex items-start justify-end p-1.5 opacity-0 transition group-hover:opacity-100">
                                     <button type="button"
                                            x-on:click.prevent="
                                                if (typeof Swal === 'undefined') { $wire.removeNewImage({{ $index }}); return; }
                                                Swal.fire({
                                                    title: '{{ __('¿Quitar imagen?') }}',
                                                    text: '{{ __('Esta imagen no se guardará en la galería.') }}',
                                                    icon: 'question',
                                                    showCancelButton: true,
                                                    confirmButtonColor: '#d33',
                                                    cancelButtonColor: '#475569',
                                                    confirmButtonText: '{{ __('Sí, quitar') }}',
                                                    cancelButtonText: '{{ __('Cancelar') }}'
                                                }).then((result) => {
                                                    if (result.isConfirmed) {
                                                        $wire.removeNewImage({{ $index }});
                                                    }
                                                });
                                            "
                                            class="flex h-7 w-7 items-center justify-center rounded-full bg-slate-900/90 text-xs text-rose-400 backdrop-blur-sm transition hover:bg-rose-600/80 hover:text-white"
                                            title="{{ __('Quitar imagen') }}">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <span class="absolute bottom-1.5 left-1.5 rounded-full bg-cyan-600/80 px-2 py-0.5 font-dv-label text-[9px] font-bold uppercase text-white shadow-sm">
                                    {{ __('Nueva') }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                <script>
                document.addEventListener('alpine:init', () => {
                    Alpine.data('productGallery', () => ({
                        dragging: false,
                        init() {
                            if (typeof Livewire !== 'undefined') {
                                Livewire.on('cover-image-changed', (event) => {
                                    const data = event?.[0];
                                    const root = document.getElementById('product-form-preview-root');
                                    const img = document.getElementById('product-form-preview-img');
                                    const empty = document.getElementById('product-form-preview-empty');
                                    if (root && img && data?.url) {
                                        root.setAttribute('data-existing-url', data.url);
                                        img.src = data.url;
                                        img.classList.remove('hidden');
                                        if (empty) empty.classList.add('hidden');
                                    }
                                });
                            }
                        },
                        handleDrop(e) {
                            this.dragging = false;
                            const files = e.dataTransfer.files;
                            if (!files.length) return;
                            const input = this.$refs.fileInput;
                            const dt = new DataTransfer();
                            for (const f of files) {
                                dt.items.add(f);
                            }
                            input.files = dt.files;
                            input.dispatchEvent(new Event('change', { bubbles: true }));
                        },
                    }));
                });
                </script>

                <div class="mt-8 border-t border-slate-700/60 pt-8">
                    <h3 class="mb-4 text-sm font-semibold text-slate-200">Inventario</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <label for="stock" class="{{ $labelBase }}">Stock actual <span class="text-rose-400">*</span></label>
                            <input
                                id="stock"
                                type="number"
                                inputmode="numeric"
                                min="0"
                                step="1"
                                wire:model.blur="stock"
                                class="{{ $inputBase }} tabular-nums @error('stock') border-rose-500/80 @enderror"
                                onkeydown="return event.key !== '.' && event.key !== 'e' && event.key !== 'E' && event.key !== '-' && event.key !== '+'"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
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
                                inputmode="numeric"
                                min="0"
                                step="1"
                                wire:model.blur="min_stock"
                                class="{{ $inputBase }} tabular-nums @error('min_stock') border-rose-500/80 @enderror"
                                onkeydown="return event.key !== '.' && event.key !== 'e' && event.key !== 'E' && event.key !== '-' && event.key !== '+'"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
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
                                inputmode="numeric"
                                min="0"
                                step="1"
                                wire:model.blur="max_stock"
                                class="{{ $inputBase }} tabular-nums @error('max_stock') border-rose-500/80 @enderror"
                                onkeydown="return event.key !== '.' && event.key !== 'e' && event.key !== 'E' && event.key !== '-' && event.key !== '+'"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
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
                                    wire:model.live.debounce.300ms="purchase_price"
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
                                    wire:model.live.debounce.300ms="sale_price"
                                    class="{{ $inputBase }} pl-9 tabular-nums @error('sale_price') border-rose-500/80 @enderror"
                                >
                            </div>
                            @error('sale_price')
                                <p class="mt-1.5 text-sm text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    @php
                        $profitMargin = $this->profitMarginPercent();
                    @endphp
                    <div class="mt-4 flex flex-col gap-1 rounded-lg border border-slate-600/70 bg-slate-950/50 px-4 py-3 ring-1 ring-white/[0.03] sm:flex-row sm:items-baseline sm:justify-between sm:gap-4">
                        <span class="text-xs font-medium uppercase tracking-wide text-slate-500">Ganancia sobre compra</span>
                        @if ($profitMargin === null)
                            <p class="text-sm text-slate-500">
                                Indica un <span class="text-slate-400">precio de compra</span> mayor que 0 para ver el porcentaje.
                            </p>
                        @else
                            <p class="flex flex-wrap items-baseline gap-2 text-sm text-slate-200">
                                <span
                                    class="text-lg font-semibold tabular-nums {{ $profitMargin >= 0 ? 'text-emerald-400' : 'text-amber-400' }}"
                                >
                                    {{ number_format($profitMargin, 2, ',', '.') }}&nbsp;%
                                </span>
                                <span class="text-xs text-slate-500">
                                    ({{ $currency->symbol }}&nbsp;{{ number_format($this->profitAbsoluteAmount(), 2, ',', '.') }} de diferencia)
                                </span>
                            </p>
                        @endif
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
