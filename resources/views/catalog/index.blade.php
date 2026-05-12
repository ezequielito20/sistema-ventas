@extends('layouts.catalog')

@section('title', $company->name . ' — ' . __('Catálogo'))

@push('meta')
    <meta name="description" content="{{ __('Catálogo de productos de') }} {{ $company->name }}. {{ __('Consultá disponibilidad por WhatsApp.') }}">
    <meta property="og:title" content="{{ $company->name }} — {{ __('Catálogo') }}">
    <meta property="og:description" content="{{ __('Catálogo de productos de') }} {{ $company->name }}">
    <meta property="og:image" content="{{ $company->logo_url }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ request()->url() }}">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="canonical" href="{{ request()->url() }}">
@endpush

@section('content')
<script>
window.__CATALOG_PRODUCTS__ = {{ Js::from($products->map(function ($p) {
    return [
        'id' => $p->id,
        'name' => $p->name,
        'description' => $p->description,
        'sale_price' => (float) $p->sale_price,
        'category_name' => $p->category_name ?? 'Sin categoría',
        'image_url' => $p->cover_image_url,
        'code' => $p->code,
        'final_price' => (float) $p->final_price,
        'has_discount' => $p->has_discount,
        'discount_percent' => $p->discount_percent,
    ];
})) }};
window.__CATALOG_PRODUCT_BASE__ = {{ Js::from(rtrim(url('/'.$company->slug.'/producto'), '/')) }};
</script>

<div class="font-dv-body" x-data="catalog">
    @include('catalog.partials.top-nav', ['company' => $company, 'searchable' => true])

    <div class="flex pt-20">
        {{-- Sidebar — Inventario / categorías --}}
        <aside class="fixed left-0 top-20 hidden h-[calc(100vh-5rem)] w-64 flex-col gap-stack-md overflow-y-auto border-r border-dv-outline-variant bg-dv-surface-container-low p-gutter md:flex">
            <div class="mb-2">
                <h2 class="font-dv-display text-dv-headline-md text-dv-on-surface">{{ __('Inventario') }}</h2>
                <p class="font-dv-label text-dv-label-md uppercase tracking-wider text-dv-outline">{{ __('Catálogo en vivo') }}</p>
            </div>

            <nav class="flex flex-col gap-1">
                <button type="button" @click="selectCat('all')"
                        class="flex w-full items-center justify-between rounded-lg px-4 py-3 font-dv-label text-dv-label-md transition hover:translate-x-0.5"
                        :class="selectedCategory === 'all' ? 'bg-dv-secondary-container text-dv-on-secondary-container' : 'text-dv-on-surface-variant hover:bg-dv-surface-container-high'">
                    <span class="flex items-center gap-3">
                        <i class="fas fa-grip-vertical w-4 text-center text-xs opacity-80"></i>
                        {{ __('Todos los productos') }}
                    </span>
                    <span class="rounded px-2 py-0.5 text-[10px] font-bold"
                          :class="selectedCategory === 'all' ? 'bg-black/20' : 'text-dv-outline'"
                          x-text="filteredCount"></span>
                </button>

                @foreach($categories as $cat)
                    <button type="button" @click="selectCat(@js($cat->name))"
                            class="flex w-full items-center justify-between rounded-lg px-4 py-3 font-dv-label text-dv-label-md transition hover:translate-x-0.5"
                            :class="selectedCategory === @js($cat->name) ? 'bg-dv-secondary-container text-dv-on-secondary-container' : 'text-dv-on-surface-variant hover:bg-dv-surface-container-high'">
                        <span class="flex min-w-0 items-center gap-3">
                            <i class="fas fa-layer-group w-4 shrink-0 text-center text-xs text-dv-primary/90"></i>
                            <span class="truncate">{{ $cat->name }}</span>
                        </span>
                        <span class="shrink-0 rounded px-2 py-0.5 text-[10px] font-bold"
                              :class="selectedCategory === @js($cat->name) ? 'bg-black/20' : 'text-dv-outline'">{{ $cat->product_count }}</span>
                    </button>
                @endforeach
            </nav>

            <div class="mt-stack-lg border-t border-dv-outline-variant pt-stack-lg">
                <div class="mb-3 flex items-center justify-between font-dv-label text-dv-label-md uppercase tracking-wider text-dv-outline">
                    <span>{{ __('Precio') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="relative flex-1">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-[10px] text-dv-outline">$</span>
                        <input type="number" x-model.number="priceMin" :max="priceMax" min="0"
                               class="w-full rounded-lg border border-dv-outline-variant/50 bg-dv-surface-container-high py-1.5 pl-5 pr-2 font-dv-body text-dv-body-sm text-dv-on-surface outline-none transition focus:border-dv-primary focus:ring-1 focus:ring-dv-primary/40"
                               placeholder="Min">
                    </div>
                    <span class="text-dv-outline/50 text-xs">—</span>
                    <div class="relative flex-1">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-[10px] text-dv-outline">$</span>
                        <input type="number" x-model.number="priceMax" :min="priceMin" :max="priceSliderMax"
                               class="w-full rounded-lg border border-dv-outline-variant/50 bg-dv-surface-container-high py-1.5 pl-5 pr-2 font-dv-body text-dv-body-sm text-dv-on-surface outline-none transition focus:border-dv-primary focus:ring-1 focus:ring-dv-primary/40"
                               placeholder="Max">
                    </div>
                </div>
            </div>

            <div class="mt-stack-lg border-t border-dv-outline-variant pt-stack-lg">
                <button type="button" @click="toggleDiscounted()"
                        class="flex w-full items-center gap-3 rounded-lg px-4 py-3 font-dv-label text-dv-label-md transition"
                        :class="onlyDiscounted ? 'bg-amber-500/10 text-amber-400 border border-amber-500/30' : 'text-dv-on-surface-variant hover:bg-dv-surface-container-high border border-transparent'">
                    <i class="fas fa-tag" :class="onlyDiscounted ? 'text-amber-400' : 'text-dv-outline'"></i>
                    <span>{{ __('Ofertas y descuentos') }}</span>
                    <span x-show="onlyDiscounted" class="ml-auto flex h-5 w-5 items-center justify-center rounded-full bg-amber-500/20">
                        <i class="fas fa-check text-[8px] text-amber-400"></i>
                    </span>
                </button>
            </div>

            <div class="mt-stack-lg border-t border-dv-outline-variant pt-stack-lg">
                <div class="mb-3 flex items-center justify-between font-dv-label text-dv-label-md uppercase tracking-wider text-dv-outline">
                    <span>{{ __('Ordenar por') }}</span>
                </div>
                <select x-model="sortBy"
                        class="w-full rounded-lg border border-dv-outline-variant/50 bg-dv-surface-container-high px-3 py-2 font-dv-body text-dv-body-sm text-dv-on-surface outline-none transition focus:border-dv-primary focus:ring-1 focus:ring-dv-primary/40">
                    <option value="name_asc">{{ __('Nombre A-Z') }}</option>
                    <option value="name_desc">{{ __('Nombre Z-A') }}</option>
                    <option value="price_asc">{{ __('Menor precio') }}</option>
                    <option value="price_desc">{{ __('Mayor precio') }}</option>
                </select>
            </div>

            <div class="mt-stack-lg border-t border-dv-outline-variant pt-stack-lg">
                <button type="button" @click="resetFilters()"
                        class="flex w-full items-center justify-center gap-2 rounded-lg border border-dv-outline-variant/40 bg-dv-surface-container-high px-4 py-2.5 font-dv-label text-dv-label-md text-dv-on-surface-variant transition hover:bg-dv-surface-container hover:text-dv-on-surface">
                    <i class="fas fa-undo-alt text-xs"></i>
                    {{ __('Reiniciar filtros') }}
                </button>
            </div>

            @if($company->phone)
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company->phone) }}?text={{ urlencode('Hola! Vi tu catálogo y quiero hacer una consulta.') }}"
                   target="_blank" rel="noopener"
                   class="mt-auto flex w-full items-center justify-center gap-2 rounded-xl bg-dv-primary py-3 font-dv-label text-dv-label-md font-bold text-dv-on-primary shadow-[0_0_40px_-4px_rgb(208_188_255/0.35)] transition hover:opacity-95 active:scale-[0.98]">
                    <i class="fab fa-whatsapp"></i>
                    {{ __('Consultar') }}
                </a>
            @endif
        </aside>

        <main class="min-h-screen flex-1 md:ml-64">
            {{-- Explorer header + búsqueda móvil --}}
            <header class="border-b border-dv-outline-variant/40 px-margin-mobile py-stack-lg sm:px-6 lg:px-margin-desktop">
                <h1 class="font-dv-display text-dv-headline-lg-mobile text-dv-on-surface md:text-dv-display-lg">{{ __('Catálogo') }}</h1>
                <p class="mt-stack-sm max-w-2xl font-dv-body text-dv-body-lg text-dv-on-surface-variant">
                    {{ __('Explorá nuestros productos con precisión. Filtrá por categoría, precio máximo y contactanos cuando quieras cerrar.') }}
                </p>

                <div class="relative mt-5 md:hidden">
                    <i class="fas fa-search pointer-events-none absolute left-4 top-1/2 z-10 -translate-y-1/2 text-dv-outline text-sm"></i>
                    <label class="sr-only" for="catalog-mobile-search">{{ __('Buscar') }}</label>
                    <input id="catalog-mobile-search" type="search" x-model="search"
                           autocomplete="off"
                           placeholder="{{ __('Buscar productos…') }}"
                           class="w-full rounded-xl border border-dv-outline-variant bg-dv-surface-container-low py-3 pe-10 ps-11 font-dv-body text-dv-body-md text-dv-on-surface outline-none transition-all placeholder:text-dv-outline focus:border-transparent focus:ring-2 focus:ring-dv-primary">
                    <button type="button" x-show="search.length" x-cloak @click="search = ''"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-dv-outline transition hover:text-dv-on-surface-variant">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                </div>
            </header>

            {{-- Categorías móvil --}}
            <div class="catalog-category-scroll border-b border-dv-outline-variant/30 px-margin-mobile pb-3 pt-2 md:hidden sm:px-6">
                <div class="flex gap-2 overflow-x-auto pb-1">
                    <button type="button" @click="selectCat('all')"
                            class="flex-shrink-0 rounded-full px-4 py-2 font-dv-label text-dv-label-md font-semibold uppercase transition"
                            :class="selectedCategory === 'all' ? 'bg-dv-secondary-container text-dv-on-secondary-container' : 'border border-dv-outline-variant/50 bg-dv-surface-container text-dv-on-surface-variant'">
                        {{ __('Todos') }}
                    </button>
                    @foreach($categories as $cat)
                        <button type="button" @click="selectCat(@js($cat->name))"
                                class="flex-shrink-0 rounded-full px-4 py-2 font-dv-label text-dv-label-md font-semibold uppercase transition"
                                :class="selectedCategory === @js($cat->name) ? 'bg-dv-secondary-container text-dv-on-secondary-container' : 'border border-dv-outline-variant/50 bg-dv-surface-container text-dv-on-surface-variant'">
                            {{ $cat->name }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Filtros móvil: botón + drawer --}}
            <div class="flex items-center gap-2 px-margin-mobile pb-3 md:hidden sm:px-6">
                <button type="button" @click="showFilters = true"
                        class="flex flex-1 items-center justify-center gap-2 rounded-xl border border-dv-outline-variant/40 bg-dv-surface-container-high px-4 py-3 font-dv-label text-dv-label-md text-dv-on-surface-variant transition hover:bg-dv-surface-container hover:text-dv-on-surface active:scale-[0.98]">
                    <i class="fas fa-sliders-h text-xs"></i>
                    {{ __('Filtros') }}
                </button>
                <button type="button" @click="resetFilters()"
                        class="flex items-center justify-center gap-2 rounded-xl border border-dv-outline-variant/40 bg-dv-surface-container-high px-4 py-3 font-dv-label text-dv-label-md text-dv-on-surface-variant transition hover:bg-dv-surface-container hover:text-dv-on-surface active:scale-[0.98]">
                    <i class="fas fa-undo-alt text-xs"></i>
                </button>
            </div>

            {{-- Overlay para el drawer de filtros en móvil --}}
            <div x-show="showFilters" x-cloak
                 class="fixed inset-0 z-50 md:hidden"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showFilters = false"></div>
                <div class="absolute bottom-0 left-0 right-0 max-h-[85vh] overflow-y-auto rounded-t-2xl border-t border-dv-outline-variant/40 bg-dv-surface-container-low px-margin-mobile pb-8 pt-6 shadow-2xl sm:px-6"
                     @click.stop>
                    <div class="mb-5 flex items-center justify-between">
                        <h3 class="font-dv-display text-dv-headline-md text-dv-on-surface">{{ __('Filtros') }}</h3>
                            <button type="button" @click="showFilters = false"
                                    class="flex h-9 w-9 items-center justify-center rounded-full bg-dv-surface-container-high text-dv-on-surface-variant transition hover:bg-dv-surface-container hover:text-dv-on-surface">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        {{-- Categorías --}}
                        <div class="mb-5">
                            <p class="mb-3 font-dv-label text-dv-label-md font-semibold uppercase tracking-wider text-dv-outline">{{ __('Categoría') }}</p>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" @click="selectCat('all'); showFilters = false"
                                        class="rounded-full px-4 py-2 font-dv-label text-dv-label-md font-semibold uppercase transition"
                                        :class="selectedCategory === 'all' ? 'bg-dv-secondary-container text-dv-on-secondary-container' : 'border border-dv-outline-variant/50 bg-dv-surface-container text-dv-on-surface-variant'">
                                    {{ __('Todos') }}
                                </button>
                                @foreach($categories as $cat)
                                    <button type="button" @click="selectCat(@js($cat->name)); showFilters = false"
                                            class="rounded-full px-4 py-2 font-dv-label text-dv-label-md font-semibold uppercase transition"
                                            :class="selectedCategory === @js($cat->name) ? 'bg-dv-secondary-container text-dv-on-secondary-container' : 'border border-dv-outline-variant/50 bg-dv-surface-container text-dv-on-surface-variant'">
                                        {{ $cat->name }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Precio --}}
                        <div class="mb-5">
                            <p class="mb-3 font-dv-label text-dv-label-md font-semibold uppercase tracking-wider text-dv-outline">{{ __('Precio') }}</p>
                            <div class="flex items-center gap-2">
                                <div class="relative flex-1">
                                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-xs text-dv-outline">$</span>
                                    <input type="number" x-model.number="priceMin" :max="priceMax" min="0"
                                           class="w-full rounded-lg border border-dv-outline-variant/50 bg-dv-surface-container-high py-2.5 pl-6 pr-3 font-dv-body text-dv-body-sm text-dv-on-surface outline-none transition focus:border-dv-primary focus:ring-1 focus:ring-dv-primary/40"
                                           placeholder="Min">
                                </div>
                                <span class="text-xs text-dv-outline/50">—</span>
                                <div class="relative flex-1">
                                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-xs text-dv-outline">$</span>
                                    <input type="number" x-model.number="priceMax" :min="priceMin" :max="priceSliderMax"
                                           class="w-full rounded-lg border border-dv-outline-variant/50 bg-dv-surface-container-high py-2.5 pl-6 pr-3 font-dv-body text-dv-body-sm text-dv-on-surface outline-none transition focus:border-dv-primary focus:ring-1 focus:ring-dv-primary/40"
                                           placeholder="Max">
                                </div>
                            </div>
                        </div>

                        {{-- Ofertas --}}
                        <div class="mb-5">
                            <button type="button" @click="toggleDiscounted()"
                                    class="flex w-full items-center gap-3 rounded-lg px-4 py-3 font-dv-label text-dv-label-md transition"
                                    :class="onlyDiscounted ? 'bg-amber-500/10 text-amber-400 border border-amber-500/30' : 'text-dv-on-surface-variant hover:bg-dv-surface-container-high border border-transparent'">
                                <i class="fas fa-tag" :class="onlyDiscounted ? 'text-amber-400' : 'text-dv-outline'"></i>
                                <span>{{ __('Ofertas y descuentos') }}</span>
                                <span x-show="onlyDiscounted" class="ml-auto flex h-5 w-5 items-center justify-center rounded-full bg-amber-500/20">
                                    <i class="fas fa-check text-[8px] text-amber-400"></i>
                                </span>
                            </button>
                        </div>

                        {{-- Ordenar --}}
                        <div class="mb-6">
                            <p class="mb-3 font-dv-label text-dv-label-md font-semibold uppercase tracking-wider text-dv-outline">{{ __('Ordenar por') }}</p>
                            <select x-model="sortBy"
                                    class="w-full rounded-lg border border-dv-outline-variant/50 bg-dv-surface-container-high px-3 py-2.5 font-dv-body text-dv-body-sm text-dv-on-surface outline-none transition focus:border-dv-primary focus:ring-1 focus:ring-dv-primary/40">
                                <option value="name_asc">{{ __('Nombre A-Z') }}</option>
                                <option value="name_desc">{{ __('Nombre Z-A') }}</option>
                                <option value="price_asc">{{ __('Menor precio') }}</option>
                                <option value="price_desc">{{ __('Mayor precio') }}</option>
                            </select>
                        </div>

                        <button type="button" @click="resetFilters(); showFilters = false"
                                class="flex w-full items-center justify-center gap-2 rounded-xl border border-dv-outline-variant/40 bg-dv-surface-container-high px-4 py-3 font-dv-label text-dv-label-md text-dv-on-surface-variant transition hover:bg-dv-surface-container hover:text-dv-on-surface">
                            <i class="fas fa-undo-alt text-xs"></i>
                            {{ __('Reiniciar filtros') }}
                        </button>
                    </div>
                </div>
            </template>

            <div class="px-margin-mobile py-stack-lg sm:px-6 lg:px-margin-desktop">
                <template x-if="filtered.length === 0">
                    <div class="flex flex-col items-center justify-center py-24 text-center">
                        <div class="mb-5 flex h-20 w-20 items-center justify-center rounded-full bg-dv-surface-container">
                            <i class="fas fa-cube text-2xl text-dv-outline"></i>
                        </div>
                        <h3 class="font-dv-display text-dv-headline-md text-dv-on-surface">{{ __('Sin resultados') }}</h3>
                        <p class="mt-2 max-w-sm font-dv-body text-dv-body-sm text-dv-outline">{{ __('Probá otra búsqueda, otra categoría o un rango de precio más alto.') }}</p>
                    </div>
                </template>

                <div class="grid grid-cols-1 gap-gutter sm:grid-cols-2 xl:grid-cols-3">
                    <template x-for="(product, idx) in filtered" :key="product.id">
                        <article class="catalog-glass-card catalog-glow-hover flex flex-col overflow-hidden transition-all animate-catalog-fade-up"
                                 :style="'animation-delay:' + (idx * 70) + 'ms'">
                            <a :href="productUrl(product.id)" class="catalog-glass-card--media relative block aspect-video overflow-hidden">
                                <img :src="product.image_url" :alt="product.name"
                                     class="h-full w-full object-cover transition duration-500 hover:scale-105"
                                     loading="lazy"
                                     x-on:error="
                                        $el.classList.add('hidden');
                                        $el.nextElementSibling.classList.remove('hidden');
                                        $el.nextElementSibling.classList.add('flex');
                                     ">
                                <div class="absolute inset-0 hidden flex-col items-center justify-center gap-2 bg-dv-surface-container-high">
                                    <i class="fas fa-box-open text-3xl text-dv-outline/40"></i>
                                    <span class="font-dv-label text-dv-label-md text-dv-outline">{{ __('Sin imagen') }}</span>
                                </div>
                                <span class="absolute left-4 top-4 rounded-full border border-dv-secondary bg-dv-secondary/10 px-3 py-1 font-dv-label text-dv-label-md font-semibold uppercase text-dv-secondary backdrop-blur-md"
                                      x-text="product.category_name"></span>
                                <template x-if="product.has_discount">
                                    <span class="absolute right-3 top-3 z-10 inline-flex items-center gap-1 rounded-full bg-amber-500 px-2.5 py-1 font-dv-label text-[10px] font-bold uppercase tracking-wide text-white shadow-lg"
                                          x-text="'-' + product.discount_percent + '%'"></span>
                                </template>
                            </a>
                            <div class="flex flex-1 flex-col p-6">
                                <div class="mb-2 flex items-start justify-between gap-3">
                                    <h3 class="font-dv-display text-dv-headline-md text-dv-on-surface line-clamp-2" x-text="product.name"></h3>
                                    <div class="shrink-0 text-right">
                                        <template x-if="product.has_discount">
                                            <p class="text-[11px] font-medium text-amber-400 line-through"
                                               x-text="'$' + formatPrice(product.sale_price)"></p>
                                        </template>
                                        <p class="font-dv-display text-dv-headline-md"
                                           :class="product.has_discount ? 'text-amber-400' : 'text-dv-primary'"
                                           x-text="'$' + formatPrice(product.final_price)"></p>
                                    </div>
                                </div>
                                <p class="mb-4 line-clamp-2 font-dv-body text-dv-body-sm text-dv-on-surface-variant"
                                   x-text="product.description || @js(__('Sin descripción breve.'))"></p>
                                <p class="mb-4 font-mono text-[11px] text-dv-outline" x-show="product.code" x-text="'#' + product.code"></p>
                                <a :href="productUrl(product.id)"
                                   class="mt-auto w-full rounded-lg border border-dv-primary/30 bg-dv-primary/10 py-3 text-center font-dv-label text-dv-label-md font-bold uppercase text-dv-primary transition duration-300 hover:bg-dv-primary hover:text-dv-on-primary">
                                    {{ __('Ver detalle') }}
                                </a>
                            </div>
                        </article>
                    </template>
                </div>
            </div>
        </main>
    </div>

    @if($company->phone)
        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company->phone) }}?text={{ urlencode('Hola! Vi tu catálogo y me interesa información.') }}"
           target="_blank" rel="noopener"
           class="group fixed bottom-6 right-6 z-50 flex h-14 w-14 items-center justify-center rounded-2xl bg-dv-primary text-dv-on-primary shadow-[0_0_30px_rgb(208_188_255/0.35)] transition duration-300 hover:scale-110 active:scale-95"
           aria-label="{{ __('WhatsApp') }}">
            <i class="fab fa-whatsapp text-2xl"></i>
            <span class="pointer-events-none absolute right-full mr-3 whitespace-nowrap rounded-lg border border-dv-outline-variant/40 bg-dv-surface-container-high px-3 py-1.5 font-dv-body text-dv-body-sm text-dv-on-surface opacity-0 shadow-lg transition-opacity group-hover:opacity-100">{{ __('¿Consultanos?') }}</span>
        </a>
    @endif
</div>
@endsection
