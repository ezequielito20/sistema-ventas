@extends('layouts.catalog')

@section('title', $product->name.' — '.$company->name)

@php
    $productUrl = url($company->slug.'/producto/'.$product->id);
    $whatsappMsg = 'Hola! Me interesa '.$product->name.' - $'.number_format($product->final_price, 2)."\n".$productUrl;
@endphp

@push('meta')
    <meta name="description" content="{{ Str::limit($product->description, 160) }}">
    <meta property="og:title" content="{{ $product->name }} — {{ $company->name }}">
    <meta property="og:description" content="{{ Str::limit($product->description, 200) }}">
    <meta property="og:image" content="{{ $product->cover_image_url }}">
    <meta property="og:type" content="product">
    <meta property="og:url" content="{{ request()->url() }}">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="canonical" href="{{ request()->url() }}">
@endpush

@push('scripts')
<script>
window.__CATALOG_GALLERY_IMAGES__ = {{ Js::from($product->images->isNotEmpty()
    ? $product->images
        ->sortBy(fn ($img) => $img->is_cover ? -1 : $img->sort_order)
        ->map(fn ($img) => ['url' => $img->image_url, 'id' => $img->id, 'is_cover' => (bool) $img->is_cover])
        ->values()
    : collect([['url' => $product->image_url, 'id' => 0, 'is_cover' => true]])) }};

window.shareCatalogProduct = async function (title, url) {
    if (navigator.share) {
        try {
            await navigator.share({ title, url });
        } catch (e) {}
        return;
    }
    try {
        await navigator.clipboard.writeText(url);
    } catch (e) {
        prompt({{ Js::from(__('Copia este enlace:')) }}, url);
    }
};
</script>
@endpush

@push('scripts')
<script>
window.__CATALOG_GALLERY_IMAGES__ = {{ Js::from($product->images->isNotEmpty()
    ? $product->images
        ->sortBy(fn ($img) => $img->is_cover ? -1 : $img->sort_order)
        ->map(fn ($img) => ['url' => $img->image_url, 'id' => $img->id, 'is_cover' => (bool) $img->is_cover])
        ->values()
    : collect([['url' => $product->image_url, 'id' => 0, 'is_cover' => true]])) }};

window.shareCatalogProduct = async function (title, url) {
    if (navigator.share) {
        try {
            await navigator.share({ title, url });
        } catch (e) {}
        return;
    }
    try {
        await navigator.clipboard.writeText(url);
        alert({{ Js::from(__('Enlace copiado al portapapeles.')) }});
    } catch (e) {
        prompt({{ Js::from(__('Copiá este enlace:')) }}, url);
    }
};
</script>
@endpush

@section('content')
    @include('catalog.partials.product-nav', ['company' => $company, 'productName' => $product->name])

    <div class="mx-auto max-w-dv px-margin-mobile pb-24 pt-24 sm:px-6 lg:px-margin-desktop lg:pb-12">
        <div class="grid grid-cols-1 gap-stack-lg lg:grid-cols-2 lg:gap-12 xl:gap-16">

            <div class="lg:sticky lg:top-24 lg:self-start">
                <div x-data="gallery" class="w-full" @keydown.window="keyNav">
                    <div class="catalog-glass-card relative overflow-hidden rounded-2xl border border-dv-outline-variant/30"
                         @touchstart="touchStart" @touchmove="touchMove" @touchend="touchEnd"
                         style="aspect-ratio:4/3">

                        @if($product->has_discount)
                            <span class="absolute right-3 top-3 z-20 inline-flex items-center gap-1 rounded-full bg-amber-500 px-3 py-1.5 font-dv-label text-xs font-bold uppercase tracking-wide text-white shadow-lg">
                                -{{ $product->discount_percent }}%
                            </span>
                        @endif

                        <template x-if="images.length === 0">
                            <div class="absolute inset-0 flex items-center justify-center bg-dv-surface-container-high">
                                <i class="fas fa-image text-6xl text-dv-outline/25"></i>
                            </div>
                        </template>

                        <template x-for="(img, i) in images" :key="img.id">
                            <div x-show="active === i"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-200"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute inset-0 flex cursor-pointer items-center justify-center bg-dv-surface-container-low"
                                 @click="window.open(img.url, '_blank')">
                                <img :src="img.url" alt="" class="max-h-full w-full object-contain" loading="lazy">
                            </div>
                        </template>

                        <template x-if="images.length > 1">
                            <div>
                                <button type="button" @click="prev()"
                                        class="absolute left-3 top-1/2 z-10 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-white/10 bg-dv-surface/85 text-dv-on-surface backdrop-blur-sm transition hover:bg-dv-surface-container-high">
                                    <i class="fas fa-chevron-left text-xs"></i>
                                </button>
                                <button type="button" @click="next()"
                                        class="absolute right-3 top-1/2 z-10 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-white/10 bg-dv-surface/85 text-dv-on-surface backdrop-blur-sm transition hover:bg-dv-surface-container-high">
                                    <i class="fas fa-chevron-right text-xs"></i>
                                </button>
                                <div class="absolute right-4 top-4 z-10 rounded-full border border-white/10 bg-dv-surface/80 px-3 py-1 font-dv-label text-[11px] font-semibold uppercase text-dv-on-surface-variant backdrop-blur-sm">
                                    <span x-text="active + 1"></span>/<span x-text="images.length"></span>
                                </div>
                            </div>
                        </template>
                    </div>

                    <template x-if="images.length > 1">
                        <div class="mt-stack-md space-y-3">
                            <div class="flex justify-center gap-2">
                                <template x-for="(img, i) in images" :key="'d'+img.id">
                                    <button type="button" @click="goTo(i)"
                                            class="h-2 rounded-full transition-all duration-300"
                                            :class="active === i ? 'w-6 bg-dv-primary' : 'w-2 bg-dv-outline-variant/70 hover:bg-dv-outline'"></button>
                                </template>
                            </div>
                            <div class="catalog-category-scroll flex justify-center gap-2 overflow-x-auto pb-1">
                                <template x-for="(img, i) in images" :key="'t'+img.id">
                                    <button type="button" @click="goTo(i)"
                                            class="h-16 w-16 shrink-0 overflow-hidden rounded-xl border-2 transition-all duration-200"
                                            :class="active === i ? 'scale-105 border-dv-primary opacity-100 ring-2 ring-dv-primary/30' : 'border-transparent opacity-50 hover:border-dv-outline-variant/50 hover:opacity-80'">
                                        <img :src="img.url" alt="" class="h-full w-full object-cover" loading="lazy">
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="pb-8">
                @if($product->category)
                    <span class="mb-4 inline-flex items-center gap-1.5 rounded-full border border-dv-secondary/25 bg-dv-secondary/10 px-3 py-1 font-dv-label text-dv-label-md font-semibold uppercase text-dv-secondary backdrop-blur-md">
                        <i class="fas fa-tag text-[10px]"></i>{{ $product->category->name }}
                    </span>
                @endif

                <h1 class="font-dv-display text-dv-headline-lg-mobile leading-tight text-dv-on-surface md:text-dv-headline-lg">
                    {{ $product->name }}</h1>
                @if($product->code)
                    <p class="mt-stack-sm font-mono text-dv-body-sm tracking-wider text-dv-outline">
                        {{ __('Cód.') }} {{ $product->code }}</p>
                @endif

                <div class="catalog-glass-card mt-6 rounded-2xl p-6">
                    <div class="flex items-baseline gap-3">
                        @if($product->has_discount)
                            <span class="font-dv-display text-2xl text-amber-400/60 line-through sm:text-3xl">
                                ${{ number_format((float) $product->sale_price, 2) }}
                            </span>
                        @endif
                        <span class="font-dv-display text-4xl sm:text-5xl {{ $product->has_discount ? 'text-amber-400' : 'text-dv-primary' }}">
                            ${{ number_format($product->final_price, 2) }}
                        </span>
                    </div>
                    @if($product->stock > 0)
                        <div class="mt-stack-sm flex items-center gap-2 font-dv-body text-dv-body-sm text-dv-secondary">
                            <span class="h-2 w-2 animate-pulse rounded-full bg-dv-secondary"></span>
                            {{ $product->stock }} {{ __('disponibles') }}
                        </div>
                    @endif
                </div>

                <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                    @if($company->phone)
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company->phone) }}?text={{ urlencode($whatsappMsg) }}"
                           target="_blank" rel="noopener"
                           class="flex flex-1 items-center justify-center gap-3 rounded-xl bg-dv-primary px-6 py-3.5 font-dv-label text-dv-label-md font-bold text-dv-on-primary shadow-[0_0_30px_-4px_rgb(208_188_255/0.4)] transition hover:opacity-95 active:scale-[0.99]">
                            <i class="fab fa-whatsapp text-lg"></i>{{ __('Consultar por WhatsApp') }}
                        </a>
                    @endif
                    <button type="button"
                            onclick="window.shareCatalogProduct(@js($product->name), @js(url()->current()))"
                            class="flex items-center justify-center gap-2 rounded-xl border border-dv-outline-variant/50 bg-dv-surface-container px-6 py-3.5 font-dv-body text-dv-body-sm font-semibold text-dv-on-surface-variant transition hover:border-dv-primary/40 hover:bg-dv-surface-container-high hover:text-dv-on-surface">
                        <i class="fas fa-share-alt"></i>{{ __('Compartir') }}
                    </button>
                </div>

                @if($product->description)
                    <div class="mt-stack-lg">
                        <h3 class="mb-3 flex items-center gap-2 font-dv-label text-dv-label-md font-bold uppercase tracking-wider text-dv-on-surface">
                            <span class="h-4 w-1 rounded-full bg-dv-primary"></span>{{ __('Descripción') }}
                        </h3>
                        <div class="space-y-3 font-dv-body text-dv-body-sm leading-relaxed text-dv-on-surface-variant">
                            @foreach(explode("\n", $product->description) as $para)
                                @if(trim($para) !== '')
                                    <p>{{ $para }}</p>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="catalog-glass-card mt-stack-lg rounded-2xl border border-dv-outline-variant/30 p-5">
                    <h3 class="mb-4 font-dv-label text-dv-label-md font-semibold uppercase tracking-wider text-dv-outline">{{ __('Detalles') }}</h3>
                    <dl class="grid grid-cols-1 gap-3 font-dv-body text-dv-body-sm sm:grid-cols-2 sm:gap-4">
                        @if($product->category)
                            <div>
                                <dt class="font-dv-label text-[11px] uppercase tracking-wide text-dv-outline">{{ __('Categoría') }}</dt>
                                <dd class="mt-1 font-medium text-dv-on-surface">{{ $product->category->name }}</dd>
                            </div>
                        @endif
                        @if($product->code)
                            <div>
                                <dt class="font-dv-label text-[11px] uppercase tracking-wide text-dv-outline">{{ __('Código') }}</dt>
                                <dd class="mt-1 font-mono font-medium text-dv-on-surface">{{ $product->code }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="font-dv-label text-[11px] uppercase tracking-wide text-dv-outline">{{ __('Stock') }}</dt>
                            <dd class="mt-1 font-medium">
                                @if($product->stock > 10)
                                    <span class="text-dv-secondary">{{ __('Disponible') }}</span>
                                @elseif($product->stock > 0)
                                    <span class="text-dv-tertiary">{{ __('Pocas unidades') }} ({{ $product->stock }})</span>
                                @else
                                    <span class="text-dv-error">{{ __('Sin stock') }}</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        @if($relatedProducts->isNotEmpty())
            <div class="mt-16 lg:mt-24">
                <h2 class="mb-gutter flex items-center gap-3 font-dv-display text-dv-headline-md text-dv-on-surface">
                    <span class="h-5 w-1 rounded-full bg-dv-primary"></span>{{ __('Productos similares') }}
                </h2>
                <div class="grid grid-cols-2 gap-gutter sm:grid-cols-3 lg:grid-cols-4">
                    @foreach($relatedProducts as $related)
                        @include('catalog.partials.product-card', ['product' => $related, 'company' => $company])
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    @if($company->phone)
        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company->phone) }}?text={{ urlencode($whatsappMsg) }}"
           target="_blank" rel="noopener"
           class="fixed inset-x-4 bottom-6 z-50 flex items-center justify-center gap-3 rounded-2xl bg-dv-primary px-6 py-4 font-dv-display text-base font-bold text-dv-on-primary shadow-[0_0_40px_-8px_rgb(208_188_255/0.45)] transition active:scale-[0.98] lg:hidden">
            <i class="fab fa-whatsapp text-xl"></i>{{ __('Consultar por WhatsApp') }}
        </a>
    @endif
@endsection
