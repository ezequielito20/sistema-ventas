<!DOCTYPE html>
<html lang="es" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="$watch('darkMode', val => { localStorage.setItem('darkMode', val); document.documentElement.classList.toggle('dark', val); }); darkMode = localStorage.getItem('darkMode') === 'true'; document.documentElement.classList.toggle('dark', darkMode);"
      :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- SEO Meta Tags --}}
    <title>{{ $company->name }} - Catálogo de Productos</title>
    <meta name="description" content="Catálogo de productos de {{ $company->name }}. Descubrí nuestros productos y contactanos por WhatsApp.">
    <meta property="og:title" content="{{ $company->name }} - Catálogo">
    <meta property="og:description" content="Catálogo de productos de {{ $company->name }}">
    <meta property="og:image" content="{{ $company->logo_url }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ request()->url() }}">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="canonical" href="{{ request()->url() }}">

    {{-- Tailwind CSS CDN (with dark mode) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'sans-serif'],
                    },
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
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        /* Smooth scroll for category pills */
        .category-scroll::-webkit-scrollbar { height: 4px; }
        .category-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .dark .category-scroll::-webkit-scrollbar-thumb { background: #475569; }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col transition-colors duration-200">

    {{-- Header --}}
    @include('catalog.partials.header')

    {{-- Dark Mode Toggle & Controls Bar --}}
    <div class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 py-2 sm:px-6 lg:px-8 flex items-center justify-between">
            {{-- Dark Mode Toggle --}}
            <button @click="darkMode = !darkMode" type="button"
                    class="inline-flex items-center gap-2 px-3 py-1.5 text-sm rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                <i class="fas" :class="darkMode ? 'fa-sun text-amber-400' : 'fa-moon text-indigo-500'"></i>
                <span x-text="darkMode ? 'Modo claro' : 'Modo oscuro'"></span>
            </button>

            {{-- Product count --}}
            <span class="text-sm text-gray-500 dark:text-gray-400">
                <span x-text="filteredProducts.length"></span> producto(s)
            </span>
        </div>
    </div>

    {{-- Search & Filters (Alpine-driven) --}}
    <div x-data="{
        search: '',
        selectedCategory: 'all',
        filterProducts() {
            return {{ Js::from($products->map(function($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'description' => $p->description,
                    'sale_price' => (float)$p->sale_price,
                    'category_name' => $p->category_name,
                    'category_id' => $p->category_id,
                    'image_url' => $p->images->isNotEmpty() ? $p->images->first()->image_url : null,
                ];
            })) }}.filter(p => {
                const matchesCategory = selectedCategory === 'all' || p.category_name === selectedCategory;
                const query = search.toLowerCase();
                const matchesSearch = !query ||
                    p.name.toLowerCase().includes(query) ||
                    (p.description && p.description.toLowerCase().includes(query));
                return matchesCategory && matchesSearch;
            });
        }
    }" x-init="filteredProducts = filterProducts()">
        {{-- Search Bar --}}
        <div class="max-w-7xl mx-auto px-4 pt-4 sm:px-6 lg:px-8">
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500"></i>
                <input type="text" x-model="search" placeholder="Buscar productos..."
                       @input="filteredProducts = filterProducts()"
                       class="w-full pl-10 pr-4 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400 transition-colors">
                <button x-show="search" @click="search = ''; filteredProducts = filterProducts()" type="button"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        {{-- Category Filter Pills --}}
        <div class="max-w-7xl mx-auto px-4 pt-3 pb-4 sm:px-6 lg:px-8">
            <div class="category-scroll flex gap-2 overflow-x-auto pb-1">
                <button @click="selectedCategory = 'all'; filteredProducts = filterProducts()" type="button"
                        class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium transition-all duration-200"
                        :class="selectedCategory === 'all'
                            ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                            : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'">
                    Todos
                </button>
                @foreach($categories as $category)
                    <button @click="selectedCategory = '{{ $category->name }}'; filteredProducts = filterProducts()" type="button"
                            class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium transition-all duration-200"
                            :class="selectedCategory === '{{ $category->name }}'
                                ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'">
                        {{ $category->name }}
                        <span class="ml-1 text-xs opacity-75">({{ $category->products->count() }})</span>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Product Grid --}}
        <div class="max-w-7xl mx-auto px-4 pb-8 sm:px-6 lg:px-8">
            <template x-if="filteredProducts.length === 0">
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <i class="fas fa-search text-5xl text-gray-300 dark:text-gray-600 mb-4"></i>
                    <h3 class="text-lg font-semibold text-gray-600 dark:text-gray-400 mb-2">No se encontraron productos</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-500 mb-6">Intentá con otros términos de búsqueda o categoría.</p>
                    @if($company->phone)
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company->phone) }}?text=Hola!+Vi+tu+catálogo+y+me+interesa+info"
                           target="_blank" rel="noopener"
                           class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 text-white rounded-full font-medium hover:bg-green-700 transition-colors shadow-lg shadow-green-600/30">
                            <i class="fab fa-whatsapp text-lg"></i>
                            Consultar por WhatsApp
                        </a>
                    @endif
                </div>
            </template>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                <template x-for="product in filteredProducts" :key="product.id">
                    <a :href="'{{ route('catalog.index', $company->slug) }}/producto/' + product.id"
                       class="group block bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-lg hover:border-blue-300 dark:hover:border-blue-600 transition-all duration-200">
                        {{-- Image --}}
                        <div class="aspect-[4/3] bg-gray-100 dark:bg-gray-700 relative overflow-hidden">
                            <template x-if="product.image_url">
                                <img :src="product.image_url" :alt="product.name"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                     loading="lazy">
                            </template>
                            <template x-if="!product.image_url">
                                <div class="w-full h-full flex items-center justify-center">
                                    <i class="fas fa-box-open text-4xl text-gray-400 dark:text-gray-500"></i>
                                </div>
                            </template>

                            {{-- Category badge --}}
                            <span class="absolute top-2 left-2 bg-blue-600 dark:bg-blue-500 text-white text-xs font-medium px-2 py-1 rounded-full"
                                  x-text="product.category_name"></span>
                        </div>

                        {{-- Info --}}
                        <div class="p-3 sm:p-4">
                            <h3 class="text-sm sm:text-base font-semibold text-gray-900 dark:text-white line-clamp-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors"
                                x-text="product.name"></h3>

                            <div class="mt-2">
                                <span class="text-lg sm:text-xl font-bold text-green-600 dark:text-green-400"
                                      x-text="'$' + product.sale_price.toFixed(2)"></span>
                            </div>
                        </div>
                    </a>
                </template>
            </div>
        </div>
    </div>

    {{-- WhatsApp Floating Button --}}
    @if($company->phone)
        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company->phone) }}?text=Hola!+Vi+tu+catálogo+y+me+interesa+info"
           target="_blank" rel="noopener"
           class="fixed bottom-6 right-6 z-50 flex items-center justify-center w-14 h-14 bg-green-500 hover:bg-green-600 text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-200 hover:scale-110 group"
           title="Contactar por WhatsApp">
            <i class="fab fa-whatsapp text-3xl"></i>
            <span class="absolute right-full mr-3 bg-gray-900 dark:bg-gray-700 text-white text-sm px-3 py-1.5 rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                ¡Consultame!
            </span>
        </a>
    @endif

    {{-- Footer --}}
    <footer class="mt-auto py-6 border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-sm text-gray-500 dark:text-gray-400">
            <p>&copy; {{ date('Y') }} {{ $company->name }}. Todos los derechos reservados.</p>
        </div>
    </footer>

</body>
</html>
