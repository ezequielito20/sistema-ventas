{{--
    Product card partial.
    Expects: $product (with images eager loaded, first image at index 0)
--}}
<a href="{{ route('catalog.product', ['company' => $company->slug, 'product' => $product]) }}"
   class="group block bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-lg hover:border-blue-300 dark:hover:border-blue-600 transition-all duration-200">

    {{-- Product Image --}}
    <div class="aspect-[4/3] bg-gray-100 dark:bg-gray-700 relative overflow-hidden">
        @if($product->images->isNotEmpty())
            <img src="{{ $product->images->first()->image_url }}"
                 alt="{{ $product->name }}"
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                 loading="lazy">
        @else
            <div class="w-full h-full flex items-center justify-center">
                <i class="fas fa-box-open text-4xl text-gray-400 dark:text-gray-500"></i>
            </div>
        @endif

        {{-- Category badge --}}
        <span class="absolute top-2 left-2 bg-blue-600 dark:bg-blue-500 text-white text-xs font-medium px-2 py-1 rounded-full">
            {{ $product->category_name ?? ($product->category->name ?? '') }}
        </span>
    </div>

    {{-- Product Info --}}
    <div class="p-3 sm:p-4">
        <h3 class="text-sm sm:text-base font-semibold text-gray-900 dark:text-white line-clamp-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
            {{ $product->name }}
        </h3>

        <div class="mt-2 flex items-center justify-between">
            <span class="text-lg sm:text-xl font-bold text-green-600 dark:text-green-400">
                ${{ number_format((float)$product->sale_price, 2) }}
            </span>
        </div>
    </div>
</a>
