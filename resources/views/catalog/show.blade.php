@extends('layouts.catalog')

@section('title', $product->name . ' - ' . $company->name)

@push('head')
<meta name="description" content="{{ Str::limit($product->description, 160) }}">
<meta property="og:title" content="{{ $product->name }} - {{ $company->name }}">
<meta property="og:description" content="{{ Str::limit($product->description, 200) }}">
<meta property="og:image" content="{{ $product->images->isNotEmpty() ? $product->images->first()->image_url : $company->logo_url }}">
<meta property="og:type" content="product">
<meta property="og:url" content="{{ request()->url() }}">
<meta name="twitter:card" content="summary_large_image">
<link rel="canonical" href="{{ request()->url() }}">
@endpush

@section('content')
<div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-10 py-8">

    {{-- Breadcrumb --}}
    <a href="{{ route('catalog.index', $company->slug) }}"
       class="inline-flex items-center gap-2 text-sm text-dv-on-surface-variant hover:text-dv-primary transition-colors mb-8">
        <i class="fas fa-arrow-left text-xs"></i>
        Volver a {{ $company->name }}
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">

        {{-- LEFT: Image Gallery --}}
        <div class="lg:sticky lg:top-24 self-start">
            @include('catalog.partials.gallery', ['images' => $product->images])
        </div>

        {{-- RIGHT: Product Info --}}
        <div>
            {{-- Category --}}
            @if($product->category)
                <span class="inline-flex items-center gap-1.5 bg-dv-secondary/10 border border-dv-secondary/20 text-dv-secondary text-xs font-medium px-3 py-1 rounded-full mb-4">
                    <i class="fas fa-tag text-[10px]"></i>
                    {{ $product->category->name }}
                </span>
            @endif

            {{-- Name --}}
            <h1 class="text-2xl sm:text-3xl font-bold text-dv-on-surface leading-tight">
                {{ $product->name }}
            </h1>

            @if($product->code)
                <p class="mt-2 text-xs text-dv-outline font-mono tracking-wider">CÓD: {{ $product->code }}</p>
            @endif

            {{-- Price Card --}}
            <div class="mt-6 p-5 rounded-2xl bg-dv-primary/5 border border-dv-primary/10">
                <span class="text-4xl sm:text-5xl font-black text-dv-primary">
                    ${{ number_format((float)$product->sale_price, 2) }}
                </span>
                @if($product->stock > 0)
                    <div class="mt-3 flex items-center gap-2">
                        <span class="w-2 h-2 bg-dv-secondary rounded-full animate-pulse"></span>
                        <span class="text-sm text-dv-secondary font-medium">{{ $product->stock }} disponibles</span>
                    </div>
                @endif
            </div>

            {{-- CTA Buttons --}}
            <div class="mt-6 flex flex-col sm:flex-row gap-3">
                @if($company->phone)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company->phone) }}?text={{ urlencode('Hola! Me interesa ' . $product->name . ' - $' . number_format((float)$product->sale_price, 2)) }}"
                       target="_blank" rel="noopener"
                       class="flex-1 flex items-center justify-center gap-2 px-6 py-3.5 bg-dv-primary border border-dv-primary/30 text-dv-on-primary font-bold text-sm rounded-xl hover:opacity-90 transition-all active:scale-[0.98] shadow-[0_0_30px_rgba(208,188,255,0.15)]">
                        <i class="fab fa-whatsapp text-lg"></i>
                        Consultar por WhatsApp
                    </a>
                @endif
                <button onclick="shareProduct('{{ $product->name }}', '{{ request()->url() }}')"
                        class="flex items-center justify-center gap-2 px-6 py-3.5 border border-dv-outline-variant/40 bg-dv-surface-container hover:bg-dv-surface-container-high text-dv-on-surface-variant font-medium text-sm rounded-xl transition-all active:scale-[0.98]">
                    <i class="fas fa-share-alt"></i>
                    Compartir
                </button>
            </div>

            {{-- Trust badges --}}
            <div class="mt-4 flex flex-wrap gap-3 text-[11px] text-dv-outline">
                <span class="inline-flex items-center gap-1"><i class="fas fa-shield text-dv-secondary text-[10px]"></i> Empresa verificada</span>
                <span class="inline-flex items-center gap-1"><i class="fas fa-truck-fast text-dv-primary text-[10px]"></i> Entrega inmediata</span>
            </div>

            {{-- Description --}}
            @if($product->description)
                <div class="mt-8">
                    <h3 class="text-sm font-semibold text-dv-on-surface uppercase tracking-wider mb-3 flex items-center gap-2">
                        <span class="w-1 h-4 rounded-full bg-dv-primary"></span>
                        Descripción
                    </h3>
                    <div class="text-sm text-dv-on-surface-variant leading-relaxed space-y-3">
                        @foreach(explode("\n", $product->description) as $p)
                            @if(trim($p) !== '')
                                <p>{{ $p }}</p>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Details --}}
            <div class="mt-8 p-5 rounded-2xl bg-dv-surface-container border border-dv-outline-variant/20">
                <h3 class="text-xs font-semibold text-dv-outline uppercase tracking-wider mb-3">Detalles</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    @if($product->category)
                        <div class="flex justify-between sm:block">
                            <dt class="text-dv-outline">Categoría</dt>
                            <dd class="text-dv-on-surface font-medium">{{ $product->category->name }}</dd>
                        </div>
                    @endif
                    @if($product->code)
                        <div class="flex justify-between sm:block">
                            <dt class="text-dv-outline">Código</dt>
                            <dd class="text-dv-on-surface font-mono font-medium">{{ $product->code }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between sm:block">
                        <dt class="text-dv-outline">Stock</dt>
                        <dd class="font-medium">
                            @if($product->stock > 10)
                                <span class="text-dv-secondary">Disponible</span>
                            @elseif($product->stock > 0)
                                <span class="text-dv-tertiary">Pocas unid. ({{ $product->stock }})</span>
                            @else
                                <span class="text-red-400">Sin stock</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    {{-- Related Products --}}
    @if($relatedProducts->isNotEmpty())
        <div class="mt-16">
            <div class="flex items-center gap-3 mb-6">
                <span class="w-1 h-5 rounded-full bg-dv-primary"></span>
                <h2 class="text-xl font-bold text-dv-on-surface">Productos similares</h2>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($relatedProducts as $related)
                    @include('catalog.partials.product-card', ['product' => $related, 'company' => $company])
                @endforeach
            </div>
        </div>
    @endif
</div>

{{-- Mobile WhatsApp FAB --}}
@if($company->phone)
    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company->phone) }}?text={{ urlencode('Hola! Me interesa ' . $product->name . ' - $' . number_format((float)$product->sale_price, 2)) }}"
       target="_blank" rel="noopener"
       class="lg:hidden fixed bottom-6 left-4 right-4 z-50 flex items-center justify-center gap-3 px-6 py-4 bg-dv-primary text-dv-on-primary font-bold text-base rounded-2xl shadow-[0_0_40px_rgba(208,188,255,0.3)] active:scale-[0.98] transition-all">
        <i class="fab fa-whatsapp text-xl"></i>
        Consultar por WhatsApp
    </a>
@endif
@endsection

@push('scripts')
<script>
    async function shareProduct(name, url) {
        if (navigator.share) {
            try { await navigator.share({ title: name, url }); } catch (e) {}
        } else {
            try {
                await navigator.clipboard.writeText(url);
                alert('Enlace copiado al portapapeles.');
            } catch (e) {
                prompt('Copiá este enlace:', url);
            }
        }
    }
</script>
@endpush
