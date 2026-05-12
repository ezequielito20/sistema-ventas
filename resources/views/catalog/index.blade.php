@extends('layouts.catalog')

@section('title', $company->name . ' - Catálogo de Productos')

@push('head')
<meta name="description" content="Catálogo de productos de {{ $company->name }}. Descubrí nuestros productos y contactanos por WhatsApp.">
<meta property="og:title" content="{{ $company->name }} - Catálogo">
<meta property="og:description" content="Catálogo de productos de {{ $company->name }}">
<meta property="og:image" content="{{ $company->logo_url }}">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ request()->url() }}">
<meta name="twitter:card" content="summary_large_image">
<link rel="canonical" href="{{ request()->url() }}">
@endpush

@section('content')
<div x-data="catalog" class="flex" x-init="init()">

    {{-- SIDEBAR — Categorías (Desktop) --}}
    <aside class="hidden lg:flex lg:flex-col w-64 fixed left-0 top-16 h-[calc(100vh-4rem)] z-40 transition-all duration-300"
           style="background: rgba(29, 26, 35, 0.95); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border-right: 1px solid rgba(255,255,255,0.06);">

        <div class="flex-1 overflow-y-auto p-5 space-y-6">
            <div>
                <h2 class="text-xs font-semibold uppercase tracking-[0.15em] mb-4" style="color: #958ea0;">Categorías</h2>
                <nav class="flex flex-col gap-1">
                    <button @click="selectCat('all')"
                            class="flex items-center justify-between w-full px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 hover:translate-x-1"
                            :class="selectedCategory === 'all'
                                ? 'bg-cyber/10 border border-cyber/20 text-cyber'
                                : 'text-dv-muted hover:bg-white/5'">
                        <span class="flex items-center gap-2.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                            Todos
                        </span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded" :class="selectedCategory === 'all' ? 'bg-cyber/20 text-cyber' : 'bg-white/5 text-dv-muted'">{{ $products->count() }}</span>
                    </button>
                    @foreach($categories as $cat)
                        <button @click="selectCat('{{ $cat->name }}')"
                                class="flex items-center justify-between w-full px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 hover:translate-x-1"
                                :class="selectedCategory === '{{ $cat->name }}'
                                    ? 'bg-cyber/10 border border-cyber/20 text-cyber'
                                    : 'text-dv-muted hover:bg-white/5'">
                            <span class="flex items-center gap-2.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M7 7l-3 3l3 3M17 7l3 3l-3 3"/><circle cx="12" cy="12" r="2"/></svg>
                                {{ $cat->name }}
                            </span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded"
                                  :class="selectedCategory === '{{ $cat->name }}' ? 'bg-cyber/20 text-cyber' : 'bg-white/5 text-dv-muted'">{{ $cat->product_count }}</span>
                        </button>
                    @endforeach
                </nav>
            </div>
        </div>

        {{-- Contacto --}}
        <div class="p-5 border-t" style="border-color: rgba(255,255,255,0.06);">
            @if($company->phone)
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company->phone) }}?text=Hola!+Vi+tu+catálogo+y+me+interesa+info"
                   target="_blank" rel="noopener"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors hover:bg-white/5"
                   style="color: #4cd7f6;">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    <span class="text-xs">WhatsApp</span>
                </a>
            @endif
            @if($company->address)
                <p class="flex items-start gap-3 px-3 mt-3 text-xs" style="color: #958ea0;">
                    <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>
                    <span>{{ $company->address }}</span>
                </p>
            @endif
        </div>
    </aside>

    {{-- MAIN CONTENT --}}
    <main class="flex-1 lg:ml-64 min-h-screen">

        {{-- Hero --}}
        <header class="px-6 sm:px-8 lg:px-10 pt-10 pb-8" style="background: linear-gradient(180deg, rgba(208,188,255,0.04) 0%, transparent 100%);">
            <h1 class="text-3xl sm:text-4xl font-black tracking-tight" style="color: #e7e0ed;">{{ $company->name }}</h1>
            <p class="mt-2 text-sm max-w-xl" style="color: #cbc3d7;">
                Descubrí todos nuestros productos. Filtrá por categoría y contactanos directamente.
            </p>

            {{-- Search --}}
            <div class="mt-6 relative max-w-md">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4" style="color: #958ea0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                <input type="text" x-model="search" placeholder="Buscar productos..."
                       class="w-full py-3 pl-11 pr-10 rounded-xl text-sm outline-none transition-all duration-200"
                       style="background: #211e27; border: 1px solid #494454; color: #e7e0ed;"
                       onfocus="this.style.borderColor='rgba(208,188,255,0.5)';this.style.boxShadow='0 0 0 3px rgba(208,188,255,0.1)';"
                       onblur="this.style.borderColor='#494454';this.style.boxShadow='none';">
                <button x-show="search" @click="search = ''" class="absolute right-3 top-1/2 -translate-y-1/2" style="color: #958ea0;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Result count --}}
            <p class="mt-3 text-xs" style="color: #958ea0;">
                <span x-text="filtered.length"></span> de {{ $products->count() }} productos
            </p>
        </header>

        {{-- Category Pills — Mobile Only --}}
        <div class="lg:hidden px-4 sm:px-6 pb-4">
            <div class="flex gap-2 overflow-x-auto pb-1 category-scroll">
                <button @click="selectCat('all')"
                        class="flex-shrink-0 px-4 py-2 rounded-full text-xs font-semibold transition-all duration-200"
                        :class="selectedCategory === 'all'
                            ? 'bg-cyber/15 border border-cyber/25 text-cyber'
                            : 'text-dv-muted border border-dv-border/30'"
                        style="background: transparent;">
                    Todos
                </button>
                @foreach($categories as $cat)
                    <button @click="selectCat('{{ $cat->name }}')"
                            class="flex-shrink-0 px-4 py-2 rounded-full text-xs font-semibold transition-all duration-200"
                            :class="selectedCategory === '{{ $cat->name }}'
                                ? 'bg-cyber/15 border border-cyber/25 text-cyber'
                                : 'text-dv-muted border border-dv-border/30'"
                            style="background: transparent;">
                        {{ $cat->name }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- PRODUCT GRID --}}
        <div class="px-4 sm:px-6 lg:px-8 pb-16">
            {{-- Empty --}}
            <template x-if="filtered.length === 0">
                <div class="flex flex-col items-center justify-center py-24 text-center">
                    <div class="w-24 h-24 rounded-full flex items-center justify-center mb-6"
                         style="background: rgba(255,255,255,0.03);">
                        <svg class="w-10 h-10" style="color: #494454;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                    </div>
                    <h3 class="text-lg font-semibold mb-2" style="color: #e7e0ed;">No se encontraron productos</h3>
                    <p class="text-sm max-w-sm" style="color: #958ea0;">Probá con otros términos de búsqueda o seleccioná una categoría diferente.</p>
                </div>
            </template>

            {{-- Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
                <template x-for="(product, idx) in filtered" :key="product.id"
                          :style="'animation: fadeUp 0.5s ease-out ' + (idx * 80) + 'ms both;'">
                    <a :href="'{{ route('catalog.index', $company->slug) }}/producto/' + product.id"
                         class="group block rounded-2xl overflow-hidden transition-all duration-300 hover:-translate-y-1"
                         style="background: rgba(33, 30, 39, 0.7); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 0.5px solid rgba(255,255,255,0.05);"
                         onmouseenter="this.style.boxShadow='0 0 40px rgba(208,188,255,0.12)';this.style.borderColor='rgba(208,188,255,0.2)';"
                         onmouseleave="this.style.boxShadow='none';this.style.borderColor='rgba(255,255,255,0.05)';">

                        {{-- Image --}}
                        <div class="aspect-[4/3] relative overflow-hidden" style="background: #2c2832;">
                            <template x-if="product.image_url">
                                <img :src="product.image_url" :alt="product.name"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                     loading="lazy">
                            </template>
                            <template x-if="!product.image_url">
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-12 h-12" style="color: #494454;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                </div>
                            </template>

                            {{-- Badge --}}
                            <span class="absolute top-3 left-3 text-[11px] font-medium px-2.5 py-1 rounded-full backdrop-blur-md"
                                  style="background: rgba(3, 181, 211, 0.15); border: 1px solid rgba(3, 181, 211, 0.2); color: #4cd7f6;"
                                  x-text="product.category_name"></span>

                            {{-- Hover overlay --}}
                            <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center"
                                 style="background: rgba(21,18,27,0.5);">
                                <span class="text-sm font-semibold px-5 py-2.5 rounded-full backdrop-blur-md"
                                      style="color: #d0bcff; background: rgba(208,188,255,0.1); border: 1px solid rgba(208,188,255,0.35);">
                                    Ver detalle
                                </span>
                            </div>
                        </div>

                        {{-- Info --}}
                        <div class="p-5">
                            <h3 class="text-sm font-bold leading-snug line-clamp-2 mb-1 group-hover:opacity-80 transition-opacity"
                                style="color: #e7e0ed;" x-text="product.name"></h3>
                            <p class="text-xs font-mono mt-1 mb-3" style="color: #958ea0;" x-show="product.code" x-text="'#' + product.code"></p>
                            <div class="flex items-center justify-between">
                                <span class="text-lg font-black" style="color: #d0bcff;"
                                      x-text="'$' + product.sale_price.toLocaleString('es-VE', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                                <span class="w-8 h-8 flex items-center justify-center rounded-full group-hover:scale-110 transition-transform"
                                      style="background: rgba(208,188,255,0.1); color: #d0bcff;">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                </span>
                            </div>
                        </div>
                    </a>
                </template>
            </div>
        </div>
    </main>
</div>

{{-- WhatsApp FAB --}}
@if($company->phone)
    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company->phone) }}?text=Hola!+Vi+tu+catálogo+y+me+interesa+info"
       target="_blank" rel="noopener"
       class="fixed bottom-6 right-6 z-50 flex items-center justify-center w-14 h-14 rounded-2xl shadow-lg transition-all duration-300 hover:scale-110 active:scale-95"
       style="background: #d0bcff; color: #3c0091; box-shadow: 0 0 30px rgba(208,188,255,0.3);">
        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
    </a>
@endif

{{-- Alpine Component --}}
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('catalog', () => ({
            search: '',
            selectedCategory: 'all',
            allProducts: {{ Js::from($products->map(function($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'description' => $p->description,
                    'sale_price' => (float)$p->sale_price,
                    'category_name' => $p->category_name,
                    'image_url' => $p->images->isNotEmpty() ? $p->images->first()->image_url : null,
                    'code' => $p->code,
                ];
            })) }},
            init() {
                // No-op, just ensures Alpine is ready
            },
            get filtered() {
                return this.allProducts.filter(p => {
                    const matchCat = this.selectedCategory === 'all' || p.category_name === this.selectedCategory;
                    const q = this.search.toLowerCase();
                    const matchSearch = !q
                        || p.name.toLowerCase().includes(q)
                        || (p.description && p.description.toLowerCase().includes(q))
                        || (p.code && p.code.toLowerCase().includes(q));
                    return matchCat && matchSearch;
                });
            },
            selectCat(cat) {
                this.selectedCategory = cat;
            }
        }));
    });
</script>

{{-- Animations --}}
<style>
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(24px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .category-scroll::-webkit-scrollbar { height: 0; }
    .category-scroll { scrollbar-width: none; }
</style>
@endsection
