@extends('layouts.catalog')

@section('title', $company->name . ' — ' . __('Catálogo'))

@push('meta')
    <meta name="description" content="{{ __('Catálogo de productos de') }} {{ $company->name }}. {{ __('Consultá disponibilidad por WhatsApp.') }}">
    <meta property="og:title" content="{{ $company->name }} — {{ __('Catálogo') }}">
    <meta property="og:description" content="{{ __('Catálogo de productos de') }} {{ $company->name }}">
    <meta property="og:image" content="{{ $company->catalog_og_image_url_absolute }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ request()->url() }}">
    {{-- summary = miniatura pequeña a la izquierda; WhatsApp usa sobre todo el ancho real de og:image (<300px) --}}
    <meta name="twitter:card" content="summary">
    <meta name="twitter:image" content="{{ $company->catalog_og_image_url_absolute }}">
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
window.__CATALOG_CART_URLS__ = @json($catalogCartUrls ?? []);
</script>

<div class="font-dv-body min-w-0 max-w-full overflow-x-hidden" x-data="catalog">
    @include('catalog.partials.top-nav', ['company' => $company, 'searchable' => true])

    <div class="flex min-w-0 pt-16 sm:pt-20">
        {{-- Sidebar — visible desde lg (1024px+); un poco más ancho en xl --}}
        <aside class="fixed left-0 top-16 z-40 hidden h-[calc(100vh-4rem)] w-56 flex-col gap-5 overflow-y-auto border-r border-dv-outline-variant/20 bg-dv-surface-container-lowest p-4 sm:top-20 sm:h-[calc(100vh-5rem)] sm:p-5 lg:flex xl:w-64">
            <div>
                <h2 class="font-dv-display text-dv-headline-md text-dv-on-surface">{{ __('Inventario') }}</h2>
                <p class="font-dv-label text-dv-label-md uppercase tracking-wider text-dv-outline/70">{{ __('Catálogo en vivo') }}</p>
            </div>

            <nav class="flex flex-col gap-0.5">
                <button type="button" @click="selectCat('all')"
                        class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 font-dv-label text-dv-label-md transition"
                        :class="selectedCategory === 'all' ? 'bg-dv-primary/10 text-dv-primary' : 'text-dv-on-surface-variant hover:bg-dv-surface-container hover:text-dv-on-surface'">
                    <span class="flex items-center gap-2.5">
                        <i class="fas fa-grip-vertical w-3.5 text-center text-[10px]" :class="selectedCategory === 'all' ? 'text-dv-primary' : 'text-dv-outline'"></i>
                        {{ __('Todos los productos') }}
                    </span>
                    <span class="rounded-md px-2 py-0.5 text-[10px] font-semibold"
                          :class="selectedCategory === 'all' ? 'bg-dv-primary/20 text-dv-primary' : 'bg-dv-surface-container-high text-dv-outline'"
                          x-text="filteredCount"></span>
                </button>

                @foreach($categories as $cat)
                    <button type="button" @click="selectCat(@js($cat->name))"
                            class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 font-dv-label text-dv-label-md transition"
                            :class="selectedCategory === @js($cat->name) ? 'bg-dv-primary/10 text-dv-primary' : 'text-dv-on-surface-variant hover:bg-dv-surface-container hover:text-dv-on-surface'">
                        <span class="flex min-w-0 items-center gap-2.5">
                            <i class="fas fa-layer-group w-3.5 shrink-0 text-center text-[10px]" :class="selectedCategory === @js($cat->name) ? 'text-dv-primary' : 'text-dv-outline'"></i>
                            <span class="truncate">{{ $cat->name }}</span>
                        </span>
                        <span class="shrink-0 rounded-md px-2 py-0.5 text-[10px] font-semibold"
                              :class="selectedCategory === @js($cat->name) ? 'bg-dv-primary/20 text-dv-primary' : 'bg-dv-surface-container-high text-dv-outline'">{{ $cat->product_count }}</span>
                    </button>
                @endforeach
            </nav>

            <div class="border-t border-dv-outline-variant/20 pt-4">
                <p class="mb-3 font-dv-label text-[11px] font-semibold uppercase tracking-widest text-dv-outline/70">{{ __('Precio') }}</p>
                <div class="flex items-center gap-2">
                    <div class="relative flex-1">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-[10px] text-dv-outline">$</span>
                        <input type="number" x-model.number="priceMin" min="0"
                               class="w-full rounded-lg border border-dv-outline-variant/30 bg-dv-surface-container-high py-1.5 pl-5 pr-2 text-xs text-dv-on-surface outline-none transition placeholder:text-dv-outline/50 focus:border-dv-primary/50 focus:ring-1 focus:ring-dv-primary/20"
                               placeholder="{{ __('Mín') }}">
                    </div>
                    <span class="text-[10px] text-dv-outline/40">—</span>
                    <div class="relative flex-1">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-[10px] text-dv-outline">$</span>
                        <input type="number" x-model.number="priceMax" min="0"
                               class="w-full rounded-lg border border-dv-outline-variant/30 bg-dv-surface-container-high py-1.5 pl-5 pr-2 text-xs text-dv-on-surface outline-none transition placeholder:text-dv-outline/50 focus:border-dv-primary/50 focus:ring-1 focus:ring-dv-primary/20"
                               placeholder="{{ __('Máx') }}">
                    </div>
                </div>
            </div>

            <div>
                <button type="button" @click="toggleDiscounted()"
                        class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2.5 text-xs font-semibold transition"
                        :class="onlyDiscounted ? 'bg-amber-500/10 text-amber-400' : 'text-dv-on-surface-variant hover:bg-dv-surface-container hover:text-dv-on-surface'">
                    <i class="fas fa-tag w-3.5 text-center text-[10px]" :class="onlyDiscounted ? 'text-amber-400' : 'text-dv-outline'"></i>
                    <span>{{ __('Ofertas') }}</span>
                    <span x-show="onlyDiscounted" class="ml-auto flex h-4 w-4 items-center justify-center rounded-full bg-amber-500/20">
                        <i class="fas fa-check text-[6px] text-amber-400"></i>
                    </span>
                </button>
            </div>

            <div>
                <p class="mb-2 font-dv-label text-[11px] font-semibold uppercase tracking-widest text-dv-outline/70">{{ __('Ordenar') }}</p>
                <select x-model="sortBy"
                        class="w-full rounded-lg border border-dv-outline-variant/30 bg-dv-surface-container-high px-3 py-2 text-xs text-dv-on-surface outline-none transition focus:border-dv-primary/50 focus:ring-1 focus:ring-dv-primary/20">
                    <option value="name_asc">A–Z</option>
                    <option value="name_desc">Z–A</option>
                    <option value="price_asc">{{ __('Menor precio') }}</option>
                    <option value="price_desc">{{ __('Mayor precio') }}</option>
                </select>
            </div>

            <button type="button" @click="resetFilters()"
                    class="flex w-full items-center justify-center gap-2 rounded-lg border border-dv-outline-variant/20 bg-dv-surface-container-high px-3 py-2 text-xs font-semibold text-dv-on-surface-variant transition hover:bg-dv-surface-container hover:text-dv-on-surface">
                <i class="fas fa-undo-alt text-[10px]"></i>
                {{ __('Reiniciar') }}
            </button>

            @if($company->phone)
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company->phone) }}?text={{ urlencode('Hola! Vi tu catálogo y quiero hacer una consulta.') }}"
                   target="_blank" rel="noopener"
                   class="mt-auto flex w-full items-center justify-center gap-2 rounded-lg bg-dv-primary px-3 py-2.5 text-xs font-bold text-dv-on-primary transition hover:opacity-90 active:scale-[0.98]">
                    <i class="fab fa-whatsapp"></i>
                    {{ __('Consultar') }}
                </a>
            @endif
        </aside>

        <main class="min-h-screen min-w-0 flex-1 overflow-x-hidden lg:ml-56 xl:ml-64">
            <div class="mx-auto min-w-0 w-full max-w-[1440px]">
            {{-- Header --}}
            <header class="border-b border-dv-outline-variant/20 px-margin-mobile py-4 sm:px-6 sm:py-5 md:py-6 lg:px-8">
                <div class="flex flex-col gap-1 md:max-w-3xl lg:max-w-none">
                    <h1 class="font-dv-display text-xl font-bold leading-tight text-dv-on-surface sm:text-2xl lg:text-3xl xl:text-4xl">{{ __('Catálogo') }}</h1>
                    <p class="max-w-2xl text-sm leading-relaxed text-dv-on-surface-variant/80 sm:text-base lg:text-dv-body-md">
                        {{ __('Explorá nuestros productos. Filtralos por categoría y precio, y consultanos por WhatsApp.') }}
                    </p>
                </div>

                <div class="relative mt-4 lg:hidden">
                    <i class="fas fa-search pointer-events-none absolute left-3.5 top-1/2 z-10 -translate-y-1/2 text-xs text-dv-outline"></i>
                    <label class="sr-only" for="catalog-mobile-search">{{ __('Buscar') }}</label>
                    <input id="catalog-mobile-search" type="search" x-model="search"
                           autocomplete="off"
                           placeholder="{{ __('Buscar productos…') }}"
                           class="w-full rounded-xl border border-dv-outline-variant/30 bg-dv-surface-container-low py-2.5 pl-9 pr-9 text-sm text-dv-on-surface outline-none transition placeholder:text-dv-outline/50 focus:border-dv-primary/50 focus:ring-1 focus:ring-dv-primary/20">
                    <button type="button" x-show="search.length" x-cloak @click="search = ''"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-dv-outline transition hover:text-dv-on-surface-variant">
                        <i class="fas fa-times text-[10px]"></i>
                    </button>
                </div>
            </header>

            {{-- Filtros móvil — categorías dentro del drawer (lg: sidebar) --}}
            <div class="flex items-center gap-2 px-margin-mobile pb-3 pt-3 lg:hidden sm:px-6">
                <button type="button" @click="showFilters = true"
                        class="relative flex flex-1 items-center justify-center gap-2 rounded-xl border border-dv-outline-variant/25 bg-dv-surface-container px-4 py-2.5 text-xs font-semibold text-dv-on-surface-variant transition hover:bg-dv-surface-container-high hover:text-dv-on-surface active:scale-[0.98]">
                    <i class="fas fa-sliders-h text-[10px]"></i>
                    {{ __('Filtros') }}
                    <span x-show="hasActiveFilters" x-cloak
                          class="absolute right-3 top-1/2 h-2 w-2 -translate-y-1/2 rounded-full bg-dv-primary ring-2 ring-dv-surface-container"
                          aria-hidden="true"></span>
                </button>
                <button type="button" @click="resetFilters()"
                        class="flex items-center justify-center gap-2 rounded-xl border border-dv-outline-variant/25 bg-dv-surface-container px-4 py-2.5 text-xs font-semibold text-dv-on-surface-variant transition hover:bg-dv-surface-container-high hover:text-dv-on-surface active:scale-[0.98]">
                    <i class="fas fa-undo-alt text-[10px]"></i>
                </button>
            </div>

            {{-- Drawer de filtros en móvil --}}
            <div x-show="showFilters" x-cloak
                 class="fixed inset-0 z-50 lg:hidden"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                <div class="absolute inset-0 bg-black/50" @click="showFilters = false"></div>
                <div class="absolute bottom-0 left-0 right-0 max-h-[80vh] overflow-y-auto rounded-t-2xl border-t border-dv-outline-variant/20 bg-dv-surface-container-lowest px-margin-mobile pb-10 pt-5 shadow-2xl sm:px-6"
                     @click.stop>
                    <div class="mb-5 flex items-center justify-between">
                        <h3 class="font-dv-display text-lg font-semibold text-dv-on-surface">{{ __('Filtros') }}</h3>
                        <button type="button" @click="showFilters = false"
                                class="flex h-8 w-8 items-center justify-center rounded-full bg-dv-surface-container-high text-dv-on-surface-variant transition hover:bg-dv-surface-container hover:text-dv-on-surface">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>

                    <div class="mb-5 min-w-0">
                        <p class="mb-2.5 text-xs font-semibold uppercase tracking-widest text-dv-outline/70">{{ __('Categoría') }}</p>
                        <div class="catalog-category-chips flex max-w-full flex-wrap gap-2">
                            <button type="button" @click="selectCat('all')"
                                    class="max-w-full rounded-full border px-3 py-2 text-left text-xs font-semibold leading-snug transition sm:px-3.5 sm:py-2 sm:text-sm"
                                    :class="selectedCategory === 'all' ? 'border-transparent bg-dv-primary text-dv-on-primary' : 'border-dv-outline-variant/25 bg-dv-surface-container text-dv-on-surface-variant hover:border-dv-outline-variant/50'">
                                {{ __('Todos') }}
                            </button>
                            @foreach($categories as $cat)
                                <button type="button" @click="selectCat(@js($cat->name))"
                                        class="max-w-full rounded-full border px-3 py-2 text-left text-xs font-semibold leading-snug transition sm:px-3.5 sm:py-2 sm:text-sm"
                                        :class="selectedCategory === @js($cat->name) ? 'border-transparent bg-dv-primary text-dv-on-primary' : 'border-dv-outline-variant/25 bg-dv-surface-container text-dv-on-surface-variant hover:border-dv-outline-variant/50'">
                                    <span class="line-clamp-2 break-words">{{ $cat->name }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-5">
                        <p class="mb-2.5 text-xs font-semibold uppercase tracking-widest text-dv-outline/70">{{ __('Precio') }}</p>
                        <div class="flex items-center gap-2">
                            <div class="relative flex-1">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-[10px] text-dv-outline">$</span>
                                <input type="number" x-model.number="priceMin" min="0"
                                       class="w-full rounded-lg border border-dv-outline-variant/30 bg-dv-surface-container-high py-2 pl-5 pr-2 text-sm text-dv-on-surface outline-none transition placeholder:text-dv-outline/50 focus:border-dv-primary/50 focus:ring-1 focus:ring-dv-primary/20"
                                       placeholder="{{ __('Mín') }}">
                            </div>
                            <span class="text-xs text-dv-outline/40">—</span>
                            <div class="relative flex-1">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-[10px] text-dv-outline">$</span>
                                <input type="number" x-model.number="priceMax" min="0"
                                       class="w-full rounded-lg border border-dv-outline-variant/30 bg-dv-surface-container-high py-2 pl-5 pr-2 text-sm text-dv-on-surface outline-none transition placeholder:text-dv-outline/50 focus:border-dv-primary/50 focus:ring-1 focus:ring-dv-primary/20"
                                       placeholder="{{ __('Máx') }}">
                            </div>
                        </div>
                    </div>

                    <div class="mb-5">
                        <button type="button" @click="toggleDiscounted()"
                                class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2.5 text-xs font-semibold transition"
                                :class="onlyDiscounted ? 'bg-amber-500/10 text-amber-400' : 'text-dv-on-surface-variant border border-dv-outline-variant/25'">
                            <i class="fas fa-tag w-3.5 text-center text-[10px]" :class="onlyDiscounted ? 'text-amber-400' : 'text-dv-outline'"></i>
                            <span>{{ __('En ofertas y descuentos') }}</span>
                            <span x-show="onlyDiscounted" class="ml-auto flex h-4 w-4 items-center justify-center rounded-full bg-amber-500/20">
                                <i class="fas fa-check text-[6px] text-amber-400"></i>
                            </span>
                        </button>
                    </div>

                    <div class="mb-5">
                        <p class="mb-2.5 text-xs font-semibold uppercase tracking-widest text-dv-outline/70">{{ __('Ordenar') }}</p>
                        <select x-model="sortBy"
                                class="w-full rounded-lg border border-dv-outline-variant/30 bg-dv-surface-container-high px-3 py-2.5 text-sm text-dv-on-surface outline-none transition focus:border-dv-primary/50 focus:ring-1 focus:ring-dv-primary/20">
                            <option value="name_asc">{{ __('Nombre A-Z') }}</option>
                            <option value="name_desc">{{ __('Nombre Z-A') }}</option>
                            <option value="price_asc">{{ __('Menor precio') }}</option>
                            <option value="price_desc">{{ __('Mayor precio') }}</option>
                        </select>
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap">
                        <button type="button" @click="resetFilters()"
                                class="flex w-full flex-1 items-center justify-center gap-2 rounded-xl border border-dv-outline-variant/25 bg-dv-surface-container px-4 py-2.5 text-xs font-semibold text-dv-on-surface-variant transition hover:bg-dv-surface-container-high hover:text-dv-on-surface sm:w-auto sm:min-w-0">
                            <i class="fas fa-undo-alt text-[10px]"></i>
                            {{ __('Reiniciar filtros') }}
                        </button>
                        <button type="button" @click="showFilters = false"
                                class="flex w-full flex-1 items-center justify-center gap-2 rounded-xl bg-dv-primary px-4 py-2.5 text-xs font-bold text-dv-on-primary transition hover:opacity-90 active:scale-[0.98] sm:w-auto sm:min-w-[40%]">
                            {{ __('Ver resultados') }}
                        </button>
                    </div>
                </div>
            </div>

            {{-- Productos --}}
            <div class="px-margin-mobile py-5 sm:px-6 sm:py-6 lg:px-8 lg:py-8">
                <template x-if="filtered.length === 0">
                    <div class="flex flex-col items-center justify-center py-20 text-center">
                        <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-dv-surface-container">
                            <i class="fas fa-cube text-xl text-dv-outline/60"></i>
                        </div>
                        <h3 class="font-dv-display text-base font-semibold text-dv-on-surface">{{ __('Sin resultados') }}</h3>
                        <p class="mt-1 max-w-xs text-sm text-dv-on-surface-variant/70">{{ __('Probá con otros filtros o términos de búsqueda.') }}</p>
                    </div>
                </template>

                <div class="grid grid-cols-1 gap-3 xs:grid-cols-2 sm:gap-4 md:grid-cols-3 md:gap-4 lg:grid-cols-3 xl:grid-cols-4 xl:gap-5 2xl:gap-6">
                    <template x-for="(product, idx) in filtered" :key="product.id">
                        <article class="group flex min-w-0 flex-col overflow-hidden rounded-xl border border-dv-outline-variant/15 bg-dv-surface-container-low transition-all duration-200 hover:border-dv-outline-variant/35 hover:shadow-[0_4px_20px_-4px_rgba(0,0,0,0.3)] animate-catalog-fade-up"
                                 :style="'animation-delay:' + (idx * 60) + 'ms'">

                            <a :href="productUrl(product.id)" class="relative aspect-video overflow-hidden bg-dv-surface-container-high">
                                <img :src="product.image_url" :alt="product.name"
                                     class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                     loading="lazy"
                                     x-on:error="
                                        $el.classList.add('hidden');
                                        $el.nextElementSibling.classList.remove('hidden');
                                        $el.nextElementSibling.classList.add('flex');
                                     ">
                                <div class="absolute inset-0 hidden flex-col items-center justify-center gap-1.5 bg-dv-surface-container-high">
                                    <i class="fas fa-box-open text-2xl text-dv-outline/30"></i>
                                    <span class="text-[10px] font-medium text-dv-outline/60">{{ __('Sin imagen') }}</span>
                                </div>

                                <span class="absolute left-2.5 top-2.5 rounded-md border border-dv-secondary/25 bg-dv-secondary/10 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider text-dv-secondary backdrop-blur-sm xs:left-3 xs:top-3 xs:px-2.5 xs:py-1 xs:text-[10px]"
                                      x-text="product.category_name"></span>

                                <template x-if="product.has_discount">
                                    <span class="absolute right-2.5 top-2.5 z-10 inline-flex items-center gap-1 rounded-md bg-amber-500 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wide text-white shadow-lg xs:right-3 xs:top-3 xs:px-2.5 xs:py-1 xs:text-[10px]"
                                          x-text="'-' + product.discount_percent + '%'"></span>
                                </template>
                            </a>

                            <div class="flex flex-1 flex-col p-3 xs:p-4 sm:p-4">
                                <div class="mb-1.5 flex flex-col gap-1.5 xs:flex-row xs:items-start xs:justify-between xs:gap-2">
                                    <h3 class="min-w-0 break-words font-dv-display text-sm font-semibold leading-snug text-dv-on-surface line-clamp-2 xs:text-base"
                                        x-text="product.name"></h3>
                                    <div class="shrink-0 text-left xs:text-right">
                                        <template x-if="product.has_discount">
                                            <p class="text-[10px] font-medium text-amber-400/70 line-through"
                                               x-text="'$' + formatPrice(product.sale_price)"></p>
                                        </template>
                                        <p class="font-dv-display text-sm font-semibold xs:text-base"
                                           :class="product.has_discount ? 'text-amber-400' : 'text-dv-primary'"
                                           x-text="'$' + formatPrice(product.final_price)"></p>
                                    </div>
                                </div>

                                <p class="mb-3 line-clamp-2 break-words text-xs leading-relaxed text-dv-on-surface-variant/70"
                                   x-text="product.description || @js(__('Sin descripción breve.'))"></p>

                                <p class="mb-3 font-mono text-[10px] text-dv-outline/50" x-show="product.code" x-text="'#' + product.code"></p>

                                <a :href="productUrl(product.id)"
                                   class="mt-auto w-full rounded-lg border border-dv-primary/20 bg-dv-primary/8 py-2 text-center text-[10px] font-bold uppercase tracking-wider text-dv-primary transition duration-200 hover:bg-dv-primary hover:text-dv-on-primary group-hover:border-dv-primary/40 xs:py-2.5 xs:text-[11px]">
                                    {{ __('Ver detalle') }}
                                </a>
                            </div>
                        </article>
                    </template>
                </div>
            </div>
            </div>
        </main>
    </div>

    @if($company->phone)
        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company->phone) }}?text={{ urlencode('Hola! Vi tu catálogo y me interesa información.') }}"
           target="_blank" rel="noopener"
           class="group fixed z-50 flex h-12 w-12 items-center justify-center rounded-xl bg-dv-primary text-dv-on-primary shadow-lg transition duration-200 hover:scale-110 active:scale-95 [bottom:max(1rem,env(safe-area-inset-bottom,0px))] [right:max(1rem,env(safe-area-inset-right,0px))] xs:h-14 xs:w-14 xs:rounded-2xl sm:bottom-6 sm:right-6"
           aria-label="{{ __('WhatsApp') }}">
            <i class="fab fa-whatsapp text-lg xs:text-2xl"></i>
            <span class="pointer-events-none absolute right-full mr-3 hidden whitespace-nowrap rounded-lg border border-dv-outline-variant/20 bg-dv-surface-container-high px-3 py-1.5 text-xs text-dv-on-surface opacity-0 shadow-lg transition-opacity group-hover:opacity-100 md:block">{{ __('¿Consultas?') }}</span>
        </a>
    @endif
</div>
@endsection
