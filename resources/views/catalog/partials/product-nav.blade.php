@props([
    'company',
    'productName' => '',
])

<nav class="fixed top-0 z-50 w-full overflow-x-hidden border-b border-white/5 bg-dv-surface/70 backdrop-blur-xl">
    <div class="mx-auto flex h-16 min-h-[3.5rem] w-full max-w-dv min-w-0 items-center justify-between gap-3 overflow-x-hidden px-margin-mobile sm:h-20 sm:min-h-0 sm:gap-4 md:px-margin-desktop">
        <a href="{{ route('catalog.index', $company->slug) }}"
           class="flex min-w-0 items-center gap-2 font-dv-body text-dv-body-sm text-dv-on-surface-variant transition hover:text-dv-primary">
            <i class="fas fa-arrow-left shrink-0 text-xs"></i>
            <span class="truncate">{{ $company->name }}</span>
        </a>

        <div class="flex shrink-0 items-center gap-2 sm:gap-3">
            <a x-show="cartQtyTotal > 0" x-cloak :href="cartUrls.checkout"
               class="relative flex items-center gap-2 rounded-xl border border-dv-primary/40 bg-dv-primary/10 px-3 py-2 text-xs font-semibold text-dv-primary">
                <i class="fas fa-shopping-bag"></i>
                <span class="absolute -right-1 -top-1 flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-dv-secondary px-0.5 text-[9px] font-bold text-white" x-text="cartQtyTotal"></span>
            </a>
            <button type="button"
                    onclick="window.shareCatalogProduct(@js($productName), @js(url()->current()))"
                    class="flex items-center gap-2 rounded-xl border border-dv-outline-variant/50 px-3 py-2 font-dv-label text-dv-label-md font-semibold uppercase text-dv-on-surface-variant transition hover:border-dv-primary/40 hover:text-dv-on-surface">
                <i class="fas fa-share-alt text-xs"></i>
                <span class="hidden sm:inline">{{ __('Compartir') }}</span>
            </button>
            @if($company->phone)
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company->phone) }}?text={{ urlencode('Hola! Me interesa: '.$productName) }}"
                   target="_blank" rel="noopener"
                   class="flex items-center gap-2 rounded-xl border border-dv-secondary/30 bg-dv-secondary-container/20 px-3 py-2 font-dv-label text-dv-label-md font-bold text-dv-secondary transition hover:bg-dv-secondary/10">
                    <i class="fab fa-whatsapp"></i>
                    <span class="hidden sm:inline">WA</span>
                </a>
            @endif
        </div>
    </div>
</nav>
