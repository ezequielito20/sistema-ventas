@props([
    'company',
    'productName' => '',
])

<nav class="fixed top-0 z-50 w-full border-b border-white/5 bg-dv-surface/70 backdrop-blur-xl">
    <div class="mx-auto flex h-20 w-full max-w-dv items-center justify-between gap-4 px-margin-mobile md:px-margin-desktop">
        <a href="{{ route('catalog.index', $company->slug) }}"
           class="flex min-w-0 items-center gap-2 font-dv-body text-dv-body-sm text-dv-on-surface-variant transition hover:text-dv-primary">
            <i class="fas fa-arrow-left shrink-0 text-xs"></i>
            <span class="truncate">{{ $company->name }}</span>
        </a>

        <div class="flex shrink-0 items-center gap-2 sm:gap-3">
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
