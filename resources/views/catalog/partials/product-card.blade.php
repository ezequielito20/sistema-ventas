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
   class="group flex flex-col overflow-hidden rounded-xl border border-dv-outline-variant/15 bg-dv-surface-container-low transition-all duration-200 hover:border-dv-outline-variant/35 hover:shadow-[0_4px_20px_-4px_rgba(0,0,0,0.3)]">

    <div class="relative aspect-video overflow-hidden bg-dv-surface-container-high">
        <img src="{{ $primaryUrl }}"
             alt="{{ $product->name }}"
             class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
             loading="lazy"
             @if($hasFallback)
             onerror="if (!this.dataset.tried) { this.dataset.tried = '1'; this.src = '{{ $fallbackUrl }}'; } else { this.onerror = null; this.style.display = 'none'; var ph = this.parentElement.querySelector('.catalog-img-placeholder'); if (ph) ph.style.display = ''; }"
             @else
             onerror="this.onerror = null; this.style.display = 'none'; var ph = this.parentElement.querySelector('.catalog-img-placeholder'); if (ph) ph.style.display = '';"
             @endif>
        <div class="catalog-img-placeholder absolute inset-0 hidden flex-col items-center justify-center gap-1.5 bg-dv-surface-container-high">
            <i class="fas fa-box-open text-2xl text-dv-outline/30"></i>
            <span class="text-[10px] font-medium text-dv-outline/60">{{ __('Sin imagen') }}</span>
        </div>

        <span class="absolute left-2.5 top-2.5 rounded-md border border-dv-secondary/25 bg-dv-secondary/10 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider text-dv-secondary backdrop-blur-sm xs:left-3 xs:top-3 xs:px-2.5 xs:py-1 xs:text-[10px]">
            {{ $categoryLabel }}
        </span>

        @if($hasDiscount)
            <span class="absolute right-2.5 top-2.5 z-10 inline-flex items-center gap-1 rounded-md bg-amber-500 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wide text-white shadow-lg xs:right-3 xs:top-3 xs:px-2.5 xs:py-1 xs:text-[10px]">
                -{{ $product->discount_percent }}%
            </span>
        @endif
    </div>

    <div class="flex flex-1 flex-col p-3 xs:p-4">
        <h3 class="font-dv-display text-sm font-semibold leading-snug text-dv-on-surface line-clamp-2 transition group-hover:text-dv-primary xs:text-base">
            {{ $product->name }}</h3>

        @if($product->code)
            <p class="mt-1 font-mono text-[10px] text-dv-outline/50">#{{ $product->code }}</p>
        @endif

        @if($product->description ?? false)
            <p class="mt-1.5 line-clamp-2 text-xs leading-relaxed text-dv-on-surface-variant/70">{{ \Illuminate\Support\Str::limit(strip_tags($product->description), 120) }}</p>
        @endif

        <div class="mt-auto flex flex-1 flex-col justify-end pt-3">
            @if($hasDiscount)
                <span class="font-dv-display text-xs text-amber-400/70 line-through xs:text-sm">
                    ${{ number_format((float) $product->sale_price, 2) }}
                </span>
            @endif
            <span class="font-dv-display text-sm font-semibold xs:text-base {{ $hasDiscount ? 'text-amber-400' : 'text-dv-primary' }}">
                ${{ number_format($finalPrice, 2) }}</span>

            <span class="mt-3 block w-full rounded-lg border border-dv-primary/20 bg-dv-primary/8 py-2 text-center text-[10px] font-bold uppercase tracking-wider text-dv-primary transition duration-200 hover:bg-dv-primary hover:text-dv-on-primary group-hover:border-dv-primary/40 xs:py-2.5 xs:text-[11px]">
                {{ __('Ver detalle') }}
            </span>
        </div>
    </div>
</a>
