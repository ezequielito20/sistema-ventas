<!DOCTYPE html>
<html lang="es" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="$watch('darkMode', val => { localStorage.setItem('darkMode', val); document.documentElement.classList.toggle('dark', val); }); darkMode = localStorage.getItem('darkMode') === 'true'; document.documentElement.classList.toggle('dark', darkMode);"
      :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- SEO Meta Tags --}}
    <title>{{ $product->name }} - {{ $company->name }}</title>
    <meta name="description" content="{{ Str::limit($product->description, 160) }}">
    <meta property="og:title" content="{{ $product->name }} - {{ $company->name }}">
    <meta property="og:description" content="{{ Str::limit($product->description, 200) }}">
    <meta property="og:image" content="{{ $product->images->isNotEmpty() ? $product->images->first()->image_url : $company->logo_url }}">
    <meta property="og:type" content="product">
    <meta property="og:url" content="{{ request()->url() }}">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="canonical" href="{{ request()->url() }}">

    {{-- Tailwind CSS CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'sans-serif'],
                    },
                    animation: {
                        'pulse-soft': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    }
                }
            }
        }
    </script>

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.15.11/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        .gallery-scroll::-webkit-scrollbar { height: 4px; }
        .gallery-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .dark .gallery-scroll::-webkit-scrollbar-thumb { background: #475569; }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col transition-colors duration-200">

    {{-- Top Navigation Bar --}}
    <nav class="sticky top-0 z-50 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md border-b border-gray-200 dark:border-gray-800">
        <div class="max-w-7xl mx-auto px-4 py-3 sm:px-6 lg:px-8 flex items-center justify-between">
            <a href="{{ route('catalog.index', $company->slug) }}"
               class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                <i class="fas fa-arrow-left"></i>
                <span class="hidden sm:inline">{{ $company->name }}</span>
            </a>

            <div class="flex items-center gap-3">
                <button onclick="shareProduct('{{ $product->name }}', '{{ request()->url() }}')" type="button"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                    <i class="fas fa-share-alt text-xs"></i>
                    <span class="hidden sm:inline">Compartir</span>
                </button>

                <button @click="darkMode = !darkMode" type="button"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                    <i class="fas text-sm" :class="darkMode ? 'fa-sun text-amber-400' : 'fa-moon text-indigo-400'"></i>
                </button>
            </div>
        </div>
    </nav>

    {{-- Main Content --}}
    <main class="flex-1">
        <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8 lg:py-10">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">

                {{-- LEFT: Image Gallery --}}
                <div class="lg:sticky lg:top-20 self-start">
                    @include('catalog.partials.gallery', ['images' => $product->images])
                </div>

                {{-- RIGHT: Product Info --}}
                <div class="flex flex-col">
                    {{-- Category Breadcrumb --}}
                    @if($product->category)
                        <span class="inline-flex self-start items-center gap-1.5 text-xs font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 px-3 py-1 rounded-full mb-4">
                            <i class="fas fa-tag text-[10px]"></i>
                            {{ $product->category->name }}
                        </span>
                    @endif

                    {{-- Product Name --}}
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 dark:text-white leading-tight">
                        {{ $product->name }}
                    </h1>

                    {{-- Product Code --}}
                    @if($product->code)
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 font-mono">
                            CÓD: {{ $product->code }}
                        </p>
                    @endif

                    {{-- Price --}}
                    <div class="mt-6 p-5 rounded-2xl bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border border-green-200 dark:border-green-800/40">
                        <div class="flex items-baseline gap-2">
                            <span class="text-4xl sm:text-5xl font-extrabold text-green-700 dark:text-green-400">
                                ${{ number_format((float)$product->sale_price, 2) }}
                            </span>
                        </div>
                        @if($product->stock > 0)
                            <div class="mt-2 flex items-center gap-2">
                                <span class="flex items-center gap-1.5 text-sm font-medium text-green-700 dark:text-green-400">
                                    <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse-soft"></span>
                                    {{ $product->stock }} disponibles
                                </span>
                            </div>
                        @endif
                    </div>

                    {{-- WhatsApp CTA — Primary --}}
                    @if($company->phone)
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company->phone) }}?text={{ urlencode('Hola! Me interesa ' . $product->name . ' - $' . number_format((float)$product->sale_price, 2)) }}"
                           target="_blank" rel="noopener"
                           class="mt-6 group relative inline-flex items-center justify-center gap-3 px-8 py-4 bg-green-600 hover:bg-green-700 text-white font-bold text-lg rounded-2xl transition-all duration-200 shadow-lg shadow-green-600/30 hover:shadow-green-600/40 hover:scale-[1.02] active:scale-[0.98] overflow-hidden">
                            <span class="absolute inset-0 bg-gradient-to-r from-green-500 via-green-600 to-green-500 opacity-0 group-hover:opacity-100 transition-opacity bg-[length:200%_100%] animate-[shimmer_2s_linear_infinite]"></span>
                            <i class="fab fa-whatsapp text-2xl relative z-10"></i>
                            <span class="relative z-10">Consultar por WhatsApp</span>
                        </a>
                    @endif

                    {{-- Trust badges --}}
                    <div class="mt-4 flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                        <span class="inline-flex items-center gap-1">
                            <i class="fas fa-shield-alt text-green-500"></i> Empresa verificada
                        </span>
                        <span class="inline-flex items-center gap-1">
                            <i class="fas fa-truck-fast text-blue-500"></i> Entrega inmediata
                        </span>
                    </div>

                    {{-- Description --}}
                    @if($product->description)
                        <div class="mt-8">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                                <span class="w-1 h-5 bg-blue-500 rounded-full"></span>
                                Descripción
                            </h3>
                            <div class="text-gray-600 dark:text-gray-400 leading-relaxed text-[15px] space-y-3">
                                @foreach(explode("\n", $product->description) as $paragraph)
                                    @if(trim($paragraph) !== '')
                                        <p>{{ $paragraph }}</p>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Specifications / Details --}}
                    <div class="mt-8 p-5 rounded-2xl bg-gray-100 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-3">Detalles del producto</h3>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @if($product->category)
                                <div class="flex items-center gap-3">
                                    <dt class="text-sm text-gray-500 dark:text-gray-400">Categoría:</dt>
                                    <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $product->category->name }}</dd>
                                </div>
                            @endif
                            @if($product->code)
                                <div class="flex items-center gap-3">
                                    <dt class="text-sm text-gray-500 dark:text-gray-400">Código:</dt>
                                    <dd class="text-sm font-medium text-gray-900 dark:text-white font-mono">{{ $product->code }}</dd>
                                </div>
                            @endif
                            <div class="flex items-center gap-3">
                                <dt class="text-sm text-gray-500 dark:text-gray-400">Stock:</dt>
                                <dd class="text-sm font-medium">
                                    @if($product->stock > 10)
                                        <span class="text-green-600 dark:text-green-400">Disponible</span>
                                    @elseif($product->stock > 0)
                                        <span class="text-amber-600 dark:text-amber-400">Pocas unidades ({{ $product->stock }})</span>
                                    @else
                                        <span class="text-red-600 dark:text-red-400">Sin stock</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Mobile WhatsApp Fixed Button (visible only on small screens) --}}
                    @if($company->phone)
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company->phone) }}?text={{ urlencode('Hola! Me interesa ' . $product->name . ' - $' . number_format((float)$product->sale_price, 2)) }}"
                           target="_blank" rel="noopener"
                           class="lg:hidden fixed bottom-6 left-4 right-4 z-50 flex items-center justify-center gap-3 px-6 py-4 bg-green-600 hover:bg-green-700 text-white font-bold text-lg rounded-2xl shadow-2xl shadow-green-600/40 transition-all active:scale-[0.98]">
                            <i class="fab fa-whatsapp text-2xl"></i>
                            Consultar por WhatsApp
                        </a>
                    @endif
                </div>
            </div>

            {{-- Related Products --}}
            @if($relatedProducts->isNotEmpty())
                <div class="mt-16">
                    <div class="flex items-center gap-3 mb-6">
                        <span class="w-1 h-6 bg-blue-500 rounded-full"></span>
                        <h2 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">
                            Productos similares
                        </h2>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
                        @foreach($relatedProducts as $related)
                            @include('catalog.partials.product-card', ['product' => $related, 'company' => $company])
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </main>

    {{-- Footer --}}
    <footer class="mt-auto py-8 border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p class="text-sm text-gray-500 dark:text-gray-400">&copy; {{ date('Y') }} {{ $company->name }}. Todos los derechos reservados.</p>
        </div>
    </footer>

    {{-- Share Script --}}
    <script>
        async function shareProduct(name, url) {
            if (navigator.share) {
                try {
                    await navigator.share({ title: name, url: url });
                } catch (err) { /* User cancelled */ }
            } else {
                try {
                    await navigator.clipboard.writeText(url);
                    alert('¡Enlace copiado al portapapeles!');
                } catch (err) {
                    prompt('Copiá este enlace:', url);
                }
            }
        }
    </script>
</body>
</html>
