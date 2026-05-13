@props([
    'company',
    /** @var bool Cuando está dentro de Alpine `catalog`, el buscador enlaza x-model.search */
    'searchable' => false,
])

<nav class="fixed top-0 z-50 w-full overflow-x-hidden border-b border-white/5 bg-dv-surface/70 shadow-sm backdrop-blur-xl">
    <div class="mx-auto flex h-16 min-h-[3.5rem] w-full max-w-dv min-w-0 items-center gap-2 overflow-x-hidden px-margin-mobile sm:h-20 sm:min-h-0 sm:gap-4 md:gap-6 md:px-margin-desktop">
        <a href="{{ route('catalog.index', $company->slug) }}" class="flex min-w-0 shrink-0 items-center gap-2 sm:gap-3">
            @if($company->logo)
                <img src="{{ $company->logo_url }}" alt="{{ $company->name }}"
                     class="h-8 w-8 shrink-0 rounded-lg border border-dv-outline-variant/30 bg-dv-surface-container object-contain sm:h-9 sm:w-9">
            @else
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-dv-primary-container/20 sm:h-9 sm:w-9">
                    <i class="fas fa-store text-xs text-dv-primary sm:text-sm"></i>
                </div>
            @endif
            <span class="max-w-[140px] truncate font-dv-display text-dv-headline-md tracking-tight text-dv-on-surface max-[420px]:hidden sm:max-w-[200px] md:max-w-none">
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
            <div class="relative hidden min-w-0 max-w-md flex-1 md:block lg:max-w-xl">
                <i class="fas fa-search pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-dv-outline text-xs sm:left-4"></i>
                <label class="sr-only" for="catalog-nav-search">{{ __('Buscar') }}</label>
                <input id="catalog-nav-search" type="search" x-model="search"
                       autocomplete="off"
                       placeholder="{{ __('Buscar por nombre, código o detalle…') }}"
                       class="w-full rounded-xl border border-dv-outline-variant bg-dv-surface-container-low py-2.5 pe-10 ps-10 font-dv-body text-dv-body-sm text-dv-on-surface outline-none transition-all placeholder:text-dv-outline focus:border-transparent focus:ring-2 focus:ring-dv-primary sm:py-3 sm:ps-11 sm:text-dv-body-md">
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
