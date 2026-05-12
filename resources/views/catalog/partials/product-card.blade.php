{{--
    Product Card — Digital Vault
--}}
<a href="{{ route('catalog.product', ['company' => $company->slug, 'product' => $product]) }}"
   class="group block rounded-2xl overflow-hidden transition-all duration-300 hover:-translate-y-1"
   style="background: rgba(33, 30, 39, 0.7); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 0.5px solid rgba(255,255,255,0.05);"
   onmouseenter="this.style.boxShadow='0 0 40px rgba(208,188,255,0.12)';this.style.borderColor='rgba(208,188,255,0.2)';"
   onmouseleave="this.style.boxShadow='none';this.style.borderColor='rgba(255,255,255,0.05)';">

    <div class="aspect-[4/3] relative overflow-hidden" style="background: #2c2832;">
        @if($product->images->isNotEmpty())
            <img src="{{ $product->images->first()->image_url }}" alt="{{ $product->name }}"
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                 loading="lazy">
        @else
            <div class="w-full h-full flex items-center justify-center">
                <svg class="w-12 h-12" style="color: #494454;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
        @endif

        <span class="absolute top-3 left-3 text-[11px] font-medium px-2.5 py-1 rounded-full backdrop-blur-md"
              style="background: rgba(3, 181, 211, 0.15); border: 1px solid rgba(3, 181, 211, 0.2); color: #4cd7f6;">
            {{ $product->category_name ?? ($product->category?->name ?? '') }}
        </span>

        <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center"
             style="background: rgba(21,18,27,0.5);">
            <span class="text-sm font-semibold px-5 py-2.5 rounded-full backdrop-blur-md"
                  style="color: #d0bcff; background: rgba(208,188,255,0.1); border: 1px solid rgba(208,188,255,0.35);">
                Ver detalle
            </span>
        </div>
    </div>

    <div class="p-5">
        <h3 class="text-sm font-bold leading-snug line-clamp-2 mb-1 group-hover:opacity-80 transition-opacity"
            style="color: #e7e0ed;">{{ $product->name }}</h3>
        @if($product->code)
            <p class="text-xs font-mono mt-1 mb-3" style="color: #958ea0;">#{{ $product->code }}</p>
        @endif
        <div class="flex items-center justify-between">
            <span class="text-lg font-black" style="color: #d0bcff;">${{ number_format((float)$product->sale_price, 2) }}</span>
            <span class="w-8 h-8 flex items-center justify-center rounded-full group-hover:scale-110 transition-transform"
                  style="background: rgba(208,188,255,0.1); color: #d0bcff;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </span>
        </div>
    </div>
</a>
