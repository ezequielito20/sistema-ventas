<div class="group relative bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-500 overflow-hidden border border-gray-100 hover:border-blue-200 transform hover:-translate-y-1" data-product-id="{{ $product->id }}">
    
    <!-- Imagen del Producto con Efectos -->
    <div class="relative overflow-hidden aspect-square">
        <!-- Imagen Principal -->
        <img src="{{ $product->image_url ?? asset('img/no-image.svg') }}" 
             alt="{{ $product->name }}"
             class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
             loading="lazy">
        
        <!-- Overlay de Hover -->
        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        
        <!-- Badge de Stock -->
        <div class="absolute top-3 left-3">
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $this->getStockStatusClass() }} border shadow-sm">
                <i class="{{ $this->getStockStatusIcon() }} mr-1"></i>
                {{ $this->getStockStatusText() }}
            </span>
        </div>
        
        <!-- Badge de Categoría -->
        @if($product->category)
        <div class="absolute top-3 right-3">
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-sm">
                <i class="fas fa-tag mr-1"></i>
                {{ $product->category->name }}
            </span>
        </div>
        @endif
        
        <!-- Botón de Vista Rápida -->
        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            <button class="bg-white/90 backdrop-blur-sm text-gray-800 px-4 py-2 rounded-full font-semibold hover:bg-white transition-all duration-300 transform hover:scale-105 shadow-lg">
                <i class="fas fa-eye mr-2"></i>
                Vista Rápida
            </button>
        </div>
    </div>

    <!-- Contenido de la Tarjeta -->
    <div class="p-5">
        <!-- Nombre del Producto -->
        <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors duration-300">
            {{ $product->name }}
        </h3>
        
        <!-- Descripción -->
        @if($product->description)
        <p class="text-sm text-gray-600 mb-4 line-clamp-2">
            {{ Str::limit($product->description, 80) }}
        </p>
        @endif
        
        <!-- Precio y Stock -->
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-baseline space-x-2">
                <span class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                    {{ $this->getFormattedPrice() }}
                </span>
                @if($product->cost_price && $product->cost_price < $product->sale_price)
                <span class="text-sm text-gray-500 line-through">
                    ${{ number_format($product->cost_price, 2) }}
                </span>
                @endif
            </div>
            
            <div class="text-sm text-gray-500 flex items-center">
                <i class="fas fa-box mr-1"></i>
                {{ $this->getFormattedStock() }}
            </div>
        </div>

        <!-- Controles de Cantidad -->
        @if($product->stock > 0)
            <div class="space-y-3">
                <!-- Controles + y - -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <!-- Botón Menos -->
                        <button wire:click="decrementQuantity"
                                wire:loading.attr="disabled"
                                class="w-10 h-10 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-700 flex items-center justify-center transition-all duration-200 hover:scale-110 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
                                {{ $quantity <= 0 ? 'disabled' : '' }}>
                            <i class="fas fa-minus text-sm"></i>
                        </button>
                        
                        <!-- Cantidad -->
                        <div class="w-16 text-center">
                            <span class="text-lg font-bold text-gray-900">{{ $quantity }}</span>
                        </div>
                        
                        <!-- Botón Más -->
                        <button wire:click="incrementQuantity"
                                wire:loading.attr="disabled"
                                class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white flex items-center justify-center transition-all duration-200 hover:scale-110 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
                                {{ $quantity >= $product->stock ? 'disabled' : '' }}>
                            <i class="fas fa-plus text-sm"></i>
                        </button>
                    </div>
                    
                    <!-- Total del Producto -->
                    @if($quantity > 0)
                    <div class="text-right">
                        <div class="text-sm text-gray-500">Total</div>
                        <div class="text-lg font-bold text-green-600">
                            ${{ number_format($product->sale_price * $quantity, 2) }}
                        </div>
                    </div>
                    @endif
                </div>
                
                <!-- Botón Agregar al Carrito -->
                <button wire:click="addToCart"
                        wire:loading.attr="disabled"
                        class="w-full py-3 px-4 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold rounded-xl transition-all duration-300 transform hover:scale-105 hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none shadow-md"
                        {{ $quantity <= 0 ? 'disabled' : '' }}>
                    <div wire:loading.remove wire:target="addToCart" class="flex items-center justify-center">
                        <i class="fas fa-shopping-cart mr-2"></i>
                        {{ $isInCart ? 'Actualizar Carrito' : 'Agregar al Carrito' }}
                    </div>
                    <div wire:loading wire:target="addToCart" class="flex items-center justify-center">
                        <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></div>
                        Actualizando...
                    </div>
                </button>
            </div>
        @else
            <!-- Producto Sin Stock -->
            <div class="text-center py-6">
                <div class="text-red-500 text-lg font-semibold mb-2">
                    <i class="fas fa-times-circle mr-2"></i>
                    Sin Stock
                </div>
                <p class="text-sm text-gray-500">
                    Este producto no está disponible actualmente
                </p>
            </div>
        @endif
    </div>

    <!-- Indicador de Carga -->
    <div wire:loading wire:target="incrementQuantity,decrementQuantity,addToCart" 
         class="absolute inset-0 bg-white/80 backdrop-blur-sm flex items-center justify-center rounded-2xl">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
    </div>

    <!-- Animación de Éxito -->
    <div wire:loading.remove wire:target="addToCart"
         class="success-notification absolute top-4 right-4 bg-green-500 text-white px-3 py-1 rounded-full text-sm font-semibold z-10 shadow-lg opacity-0 transition-opacity duration-300"
         style="display: none;">
        <i class="fas fa-check mr-1"></i>
        ¡Agregado!
    </div>
</div>
