@props([
    'company',
    /** @var bool Cuando está dentro de Alpine `catalog`, el buscador enlaza x-model.search */
    'searchable' => false,
])

<nav class="fixed top-0 z-50 w-full border-b border-white/5 bg-dv-surface/70 shadow-sm backdrop-blur-xl">
    <div class="mx-auto flex h-20 w-full max-w-dv items-center gap-4 px-margin-mobile md:gap-6 md:px-margin-desktop">
        <a href="{{ route('catalog.index', $company->slug) }}" class="flex shrink-0 items-center gap-3">
            @if($company->logo)
                <img src="{{ $company->logo_url }}" alt="{{ $company->name }}"
                     class="h-9 w-9 rounded-lg border border-dv-outline-variant/30 bg-dv-surface-container object-contain">
            @else
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-dv-primary-container/20">
                    <i class="fas fa-store text-sm text-dv-primary"></i>
                </div>
            @endif
            <span class="font-dv-display text-dv-headline-md tracking-tight text-dv-on-surface max-[420px]:hidden sm:inline">
                {{ \Illuminate\Support\Str::upper($company->name) }}
            </span>
        </a>

        <div class="hidden flex-col items-start gap-0.5 lg:flex xl:absolute xl:left-1/2 xl:-translate-x-1/2">
            <span class="font-dv-body text-dv-body-sm font-semibold text-dv-primary">
                Catálogo
            </span>
            <span class="h-0.5 w-full rounded-full bg-gradient-to-r from-dv-primary to-dv-secondary"></span>
        </div>

        <div class="min-w-0 flex-1"></div>

        @if($searchable)
            <div class="relative hidden min-w-0 max-w-md flex-1 md:block lg:max-w-lg">
                <i class="fas fa-search pointer-events-none absolute left-4 top-1/2 z-10 -translate-y-1/2 text-dv-outline text-xs"></i>
                <label class="sr-only" for="catalog-nav-search">{{ __('Buscar') }}</label>
                <input id="catalog-nav-search" type="search" x-model="search"
                       autocomplete="off"
                       placeholder="{{ __('Buscar por nombre, código o detalle…') }}"
                       class="w-full rounded-xl border border-dv-outline-variant bg-dv-surface-container-low py-3 pe-10 ps-11 font-dv-body text-dv-body-md text-dv-on-surface outline-none transition-all placeholder:text-dv-outline focus:border-transparent focus:ring-2 focus:ring-dv-primary">
                <button type="button" x-show="search.length" x-cloak @click="search = ''"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-dv-outline transition hover:text-dv-on-surface-variant">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
        @endif

        <div class="flex shrink-0 items-center gap-2 sm:gap-4">
            @if($company->ig)
                <a href="https://instagram.com/{{ $company->ig }}" target="_blank" rel="noopener"
                   class="hidden text-dv-on-surface-variant transition hover:text-dv-secondary xs:flex md:inline-flex md:items-center md:gap-1.5">
                    <i class="fab fa-instagram"></i>
                    <span class="hidden font-dv-body text-dv-body-sm lg:inline">{{ '@' . $company->ig }}</span>
                </a>
            @endif
            @if($company->phone)
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company->phone) }}?text={{ urlencode('Hola! Vi tu catálogo y me interesa información.') }}"
                   target="_blank" rel="noopener"
                   class="flex items-center gap-2 rounded-xl border border-dv-secondary/30 bg-dv-secondary-container/20 px-3 py-2 font-dv-label text-dv-label-md font-bold uppercase text-dv-secondary transition hover:bg-dv-secondary/10 md:py-2.5">
                    <i class="fab fa-whatsapp"></i>
                    <span class="hidden sm:inline">WhatsApp</span>
                </a>
            @endif
        </div>
    </div>
</nav>
