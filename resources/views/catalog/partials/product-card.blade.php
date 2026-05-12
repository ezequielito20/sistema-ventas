{{--
    Tarjeta de producto — Digital Vault · catálogo público
    Requiere $product con relación category y relación images eager cargada.
--}}
@php
    $categoryLabel = $product->category_name ?? optional($product->category)->name ?? __('Sin categoría');

    $primaryUrl = $product->cover_image_url;
    $fallbackUrl = $product->image_url;
    $hasFallback = $fallbackUrl !== $primaryUrl;
    $hasDiscount = $product->has_discount;
    $finalPrice = $product->final_price;
@endphp
<a href="{{ route('catalog.product', ['company' => $company->slug, 'product' => $product]) }}"
   class="catalog-glass-card catalog-glow-hover group flex flex-col overflow-hidden">

    <div class="catalog-glass-card--media relative aspect-video overflow-hidden">
        <img src="{{ $primaryUrl }}"
             alt="{{ $product->name }}"
             class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
             loading="lazy"
             @if($hasFallback)
             onerror="if (!this.dataset.tried) { this.dataset.tried = '1'; this.src = '{{ $fallbackUrl }}'; } else { this.onerror = null; this.style.display = 'none'; var ph = this.parentElement.querySelector('.catalog-img-placeholder'); if (ph) ph.style.display = ''; }"
             @else
             onerror="this.onerror = null; this.style.display = 'none'; var ph = this.parentElement.querySelector('.catalog-img-placeholder'); if (ph) ph.style.display = '';"
             @endif>
        <div class="catalog-img-placeholder absolute inset-0 flex flex-col items-center justify-center gap-2 bg-dv-surface-container-high" style="display: none;">
            <i class="fas fa-box-open text-3xl text-dv-outline/40"></i>
            <span class="font-dv-label text-dv-label-md text-dv-outline">{{ __('Sin imagen') }}</span>
        </div>

        <span class="absolute left-3 top-3 rounded-full border border-dv-secondary bg-dv-secondary/10 px-2.5 py-1 font-dv-label text-[10px] font-bold uppercase tracking-wide text-dv-secondary backdrop-blur-md sm:left-4 sm:top-4">
            {{ $categoryLabel }}
        </span>

        @if($hasDiscount)
            <span class="absolute right-3 top-3 z-10 inline-flex items-center gap-1 rounded-full bg-amber-500 px-2.5 py-1 font-dv-label text-[10px] font-bold uppercase tracking-wide text-white shadow-lg">
                -{{ $product->discount_percent }}%
            </span>
        @endif
    </div>

    <div class="flex flex-1 flex-col p-4 sm:p-5">
        <h3 class="font-dv-display text-dv-body-md leading-snug text-dv-on-surface line-clamp-2 transition group-hover:text-dv-primary sm:text-dv-headline-md">
            {{ $product->name }}</h3>

        @if($product->code)
            <p class="mt-1 font-mono text-[11px] text-dv-outline">#{{ $product->code }}</p>
        @endif

        @if($product->description ?? false)
            <p class="mt-2 line-clamp-2 font-dv-body text-dv-body-sm text-dv-on-surface-variant">{{ \Illuminate\Support\Str::limit(strip_tags($product->description), 120) }}</p>
        @endif

        <div class="mt-auto flex flex-1 flex-col justify-end pt-4">
            @if($hasDiscount)
                <span class="font-dv-display text-sm text-amber-400/70 line-through">
                    ${{ number_format((float) $product->sale_price, 2) }}
                </span>
            @endif
            <span class="font-dv-display text-xl sm:text-dv-headline-md {{ $hasDiscount ? 'text-amber-400' : 'text-dv-primary' }}">
                ${{ number_format($finalPrice, 2) }}</span>

            <span class="mt-4 block w-full rounded-lg border border-dv-primary/30 bg-dv-primary/10 py-2.5 text-center font-dv-label text-[11px] font-bold uppercase text-dv-primary transition group-hover:bg-dv-primary group-hover:text-dv-on-primary">
                {{ __('Ver detalle') }}
            </span>
        </div>
    </div>
</a>
