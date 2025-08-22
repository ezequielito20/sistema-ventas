@extends('layouts.app')

@section('title', 'Nueva Compra')

@push('head')
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
@endpush

@section('content')
    <!-- Background Pattern -->
    <div class="page-background"></div>

    <!-- Main Container -->
    <div class="main-container" x-data="purchaseForm()"
        data-products-count="{{ $products->count() }}"
        data-first-product="{{ json_encode($products->first() ? $products->first()->append('image_url') : null) }}">
        
        <!-- Floating Header -->
        <div class="floating-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon-wrapper">
                        <div class="header-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="icon-glow"></div>
                    </div>
                    <div class="header-text">
                        <h1 class="header-title">Nueva Compra</h1>
                        <p class="header-subtitle">Registre una nueva compra de productos al inventario</p>
                    </div>
                </div>
                <div class="header-actions">
                    <button onclick="goBack()" class="btn-glass btn-secondary-glass" title="Volver a la página anterior">
                        <i class="fas fa-arrow-left"></i>
                        <span>Volver</span>
                        <div class="btn-ripple"></div>
                    </button>
                </div>
            </div>
        </div>

        <!-- Form Container -->
        <div class="form-container">
            <form id="purchaseForm" action="{{ route('admin.purchases.store') }}" method="POST"
                enctype="multipart/form-data">
                @csrf

                <!-- Compact Layout -->
                <div class="compact-layout">
                    <!-- Main Content Panel -->
                    <div class="main-content-panel">
                        <!-- Combined Information & Products Panel -->
                        <div class="form-card combined-panel">
                            <div class="card-header">
                                <div class="header-content">
                                    <div class="title-section">
                                        <div class="title-icon">
                                            <i class="fas fa-shopping-basket"></i>
                                        </div>
                                        <div class="title-text">
                                            <h3 class="panel-title">Compra de Productos</h3>
                                            <p class="panel-subtitle">Agregue productos y configure la compra</p>
                                        </div>
                                    </div>
                                    <div class="header-controls">
                                        <div class="product-counter">
                                            <span class="counter-badge" id="productCount">0 productos</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                <!-- Compact Form Section -->
                                <div class="compact-form-section">
                                    <div class="form-row">
                                        <!-- Código de Producto -->
                                        <div class="form-field product-code-field">
                                            <label for="product_code" class="field-label">
                                                <div class="label-content">
                                                    <div class="label-icon">
                                                        <i class="fas fa-barcode"></i>
                                                    </div>
                                                    <span>Código de Producto</span>
                                                    <span class="required-indicator">*</span>
                                                </div>
                                            </label>

                                            <div class="input-container">
                                                <div class="input-wrapper">
                                                    <div class="input-icon">
                                                        <i class="fas fa-barcode"></i>
                                                    </div>
                                                    <input type="text" name="product_code" id="product_code"
                                                        x-model="productCode"
                                                        @keyup.enter="searchProductByCode(productCode)"
                                                        class="modern-input @error('product_code') error @enderror"
                                                        placeholder="Escanee o ingrese el código del producto"
                                                        value="{{ old('product_code') }}" autocomplete="off">
                                                    <div class="input-border"></div>
                                                    <div class="input-focus-effect"></div>
                                                </div>

                                                <div class="input-actions">
                                                    <button type="button" class="btn-modern btn-primary"
                                                        @click="openSearchModal()">
                                                        <div class="btn-content">
                                                            <i class="fas fa-search"></i>
                                                        </div>
                                                        <div class="btn-bg"></div>
                                                    </button>
                                                    <a href="/products/create" class="btn-modern btn-success">
                                                        <div class="btn-content">
                                                            <i class="fas fa-plus"></i>
                                                        </div>
                                                        <div class="btn-bg"></div>
                                                    </a>
                                                </div>

                                                @error('product_code')
                                                    <div class="error-message">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                        <span>{{ $message }}</span>
                                                    </div>
                                                @enderror

                                                
                                            </div>
                                        </div>

                                        <!-- Fecha y Hora de Compra -->
                                        <div class="form-field date-time-fields">
                                            <div class="date-time-row">
                                                <div class="date-field">
                                                    <label for="purchase_date" class="field-label">
                                                        <div class="label-content">
                                                            <div class="label-icon">
                                                                <i class="fas fa-calendar"></i>
                                                            </div>
                                                            <span>Fecha</span>
                                                            <span class="required-indicator">*</span>
                                                        </div>
                                                    </label>

                                                    <div class="input-container">
                                                        <div class="input-wrapper">
                                                            <div class="input-icon">
                                                                <i class="fas fa-calendar"></i>
                                                            </div>
                                                            <input type="date" name="purchase_date" id="purchase_date"
                                                                class="modern-input @error('purchase_date') error @enderror"
                                                                value="{{ old('purchase_date', date('Y-m-d')) }}" required>
                                                            <div class="input-border"></div>
                                                            <div class="input-focus-effect"></div>
                                                        </div>

                                                        @error('purchase_date')
                                                            <div class="error-message">
                                                                <i class="fas fa-exclamation-triangle"></i>
                                                                <span>{{ $message }}</span>
                                                            </div>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="time-field">
                                                    <label for="purchase_time" class="field-label">
                                                        <div class="label-content">
                                                            <div class="label-icon">
                                                                <i class="fas fa-clock"></i>
                                                            </div>
                                                            <span>Hora</span>
                                                            <span class="required-indicator">*</span>
                                                        </div>
                                                    </label>

                                                    <div class="input-container">
                                                        <div class="input-wrapper">
                                                            <div class="input-icon">
                                                                <i class="fas fa-clock"></i>
                                                            </div>
                                                            <input type="time" name="purchase_time" id="purchase_time"
                                                                class="modern-input @error('purchase_time') error @enderror"
                                                                value="{{ old('purchase_time', date('H:i')) }}" required>
                                                            <div class="input-border"></div>
                                                            <div class="input-focus-effect"></div>
                                                        </div>

                                                        @error('purchase_time')
                                                            <div class="error-message">
                                                                <i class="fas fa-exclamation-triangle"></i>
                                                                <span>{{ $message }}</span>
                                                            </div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Products Table Section -->
                                <div class="products-table-section">
                                    <div class="table-container">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-2 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Producto</th>
                                                    <th class="px-2 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">
                                                        Stock</th>
                                                    <th class="px-2 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Cantidad</th>
                                                    <th class="px-2 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">
                                                        Precio Unit.</th>
                                                    <th class="px-2 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">
                                                        Subtotal</th>
                                                    <th class="px-2 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Acción</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                <template x-for="(product, index) in products" :key="product.id">
                                                    <tr class="hover:bg-gray-50 transition-colors">
                                                        <td class="px-2 sm:px-6 py-2 sm:py-4 whitespace-nowrap">
                                                            <div class="flex items-center">
                                                                <div class="flex-shrink-0 h-8 w-8 sm:h-10 sm:w-10">
                                                                    <img class="h-8 w-8 sm:h-10 sm:w-10 rounded-lg object-cover"
                                                                        :src="product.image_url"
                                                                        :alt="product.name">
                                                                </div>
                                                                <div class="ml-2 sm:ml-4">
                                                                    <div class="text-xs sm:text-sm font-medium text-gray-900"
                                                                        x-text="product.name"></div>
                                                                    <div class="text-xs sm:text-sm text-gray-500 hidden sm:block" x-text="product.code">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="px-2 sm:px-6 py-2 sm:py-4 whitespace-nowrap hidden sm:table-cell">
                                                            <span class="inline-flex px-1 sm:px-2 py-1 text-xs font-semibold rounded-full"
                                                                :class="{
                                                                    'bg-red-100 text-red-800': product.stock < 10,
                                                                    'bg-yellow-100 text-yellow-800': product.stock >= 10 &&
                                                                        product.stock < 50,
                                                                    'bg-green-100 text-green-800': product.stock >= 50
                                                                }"
                                                                x-text="product.stock"></span>
                                                        </td>
                                                        <td class="px-2 sm:px-6 py-2 sm:py-4 whitespace-nowrap">
                                                            <input type="number" :value="product.quantity"
                                                                @input="updateProduct(index, 'quantity', $event.target.value)"
                                                                class="w-16 sm:w-20 px-2 sm:px-3 py-1 sm:py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-center text-xs sm:text-sm"
                                                                min="1" step="1">
                                                        </td>
                                                        <td class="px-2 sm:px-6 py-2 sm:py-4 whitespace-nowrap hidden md:table-cell">
                                                            <div class="flex items-center">
                                                                <span class="text-gray-500 mr-1 sm:mr-2 text-xs sm:text-sm">{{ $currency->symbol }}</span>
                                                                <input type="number" :value="product.price"
                                                                    @input="updateProduct(index, 'price', $event.target.value)"
                                                                    class="w-20 sm:w-24 px-2 sm:px-3 py-1 sm:py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm"
                                                                    step="0.01">
                                                            </div>
                                                        </td>
                                                        <td class="px-2 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-xs sm:text-sm font-medium text-green-600 hidden lg:table-cell">
                                                            <span x-text="'{{ $currency->symbol }} ' + (parseFloat(product.subtotal) || 0).toFixed(2)"></span>
                                                        </td>
                                                        <td class="px-2 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-xs sm:text-sm font-medium">
                                                            <button @click="removeProduct(index)"
                                                                class="inline-flex items-center px-2 sm:px-3 py-1 sm:py-2 border border-transparent text-xs sm:text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                                                                <i class="fas fa-trash mr-1 sm:mr-2 text-xs sm:text-sm"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Estado vacío -->
                                    <div x-show="products.length === 0" class="text-center py-12">
                                        <i class="fas fa-shopping-cart text-4xl text-gray-300 mb-4"></i>
                                        <h5 class="text-lg font-medium text-gray-900 mb-2">No hay productos agregados</h5>
                                        <p class="text-gray-500 mb-3">Escanee un producto o use el botón "Buscar" para agregar
                                            productos a la compra</p>
                                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 max-w-md mx-auto">
                                            
                                        </div>
                                    </div>
                                </div>

                                <!-- Summary and Actions Section -->
                                <div class="summary-actions-section">
                                    <div class="summary-stats">
                                        <div class="summary-item">
                                            <div class="summary-icon">
                                                <i class="fas fa-boxes"></i>
                                            </div>
                                            <div class="summary-content">
                                                <div class="summary-value" x-text="totalProducts">0</div>
                                                <div class="summary-label">Productos Únicos</div>
                                            </div>
                                        </div>

                                        <div class="summary-item">
                                            <div class="summary-icon">
                                                <i class="fas fa-cubes"></i>
                                            </div>
                                            <div class="summary-content">
                                                <div class="summary-value" x-text="totalQuantity">0</div>
                                                <div class="summary-label">Cantidad Total</div>
                                            </div>
                                        </div>

                                        <div class="summary-divider"></div>

                                        <div class="summary-item total">
                                            <div class="summary-icon">
                                                <i class="fas fa-dollar-sign"></i>
                                            </div>
                                            <div class="summary-content">
                                                <div class="summary-value total-amount"
                                                    x-text="'{{ $currency->symbol }} ' + (parseFloat(totalAmount) || 0).toFixed(2)">
                                                    {{ $currency->symbol }} 0.00</div>
                                                <div class="summary-label">Total a Pagar</div>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="total_price" x-model="totalAmount">

                                    <!-- Actions Section -->
                                    <div class="action-buttons">
                                        <button type="submit" class="btn-modern btn-primary" @click="submitForm()"
                                            title="Guardar esta compra y volver al listado">
                                            <div class="btn-content">
                                                <i class="fas fa-save"></i>
                                            </div>
                                            <div class="btn-bg"></div>
                                            <div class="btn-shine"></div>
                                        </button>

                                        <button type="submit" class="btn-modern btn-success" name="action"
                                            value="save_and_new" @click="submitForm()"
                                            title="Guardar esta compra y crear una nueva">
                                            <div class="btn-content">
                                                <i class="fas fa-plus-circle"></i>
                                            </div>
                                            <div class="btn-bg"></div>
                                        </button>

                                        <button type="button" class="btn-modern btn-danger" @click="cancelPurchase()">
                                            <div class="btn-content">
                                                <i class="fas fa-times-circle"></i>
                                            </div>
                                            <div class="btn-bg"></div>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de Búsqueda de Productos con Alpine.js -->
    <div id="searchProductModal" x-data="searchModal()" x-show="isOpen"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>

        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" x-show="isOpen"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="closeModal()"></div>

        <!-- Modal Content -->
        <div class="flex min-h-full items-center justify-center p-4 sm:p-6">
            <div class="relative w-full max-w-7xl transform overflow-hidden rounded-2xl bg-white shadow-2xl transition-all mx-4"
                x-show="isOpen" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-4 sm:px-6 py-3 sm:py-4 text-white">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2 sm:space-x-4">
                            <div
                                class="flex h-8 w-8 sm:h-12 sm:w-12 items-center justify-center rounded-full bg-white bg-opacity-20 backdrop-blur-sm">
                                <i class="fas fa-search text-sm sm:text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg sm:text-xl font-bold">Búsqueda de Productos</h3>
                                <p class="text-blue-100 text-sm sm:text-base">Seleccione los productos para agregar a la compra</p>
                            </div>
                        </div>
                        <button @click="closeModal()"
                            class="rounded-full p-1 sm:p-2 text-white hover:bg-white hover:bg-opacity-20 transition-colors">
                            <i class="fas fa-times text-lg sm:text-xl"></i>
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="p-4 sm:p-6">
                    <!-- Search Bar -->
                    <div class="mb-4 sm:mb-6">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400 text-sm sm:text-base"></i>
                            </div>
                            <input type="text" x-model="searchTerm" @input="filterProductsInModal()"
                                class="block w-full pl-10 pr-3 py-2 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm sm:text-base"
                                placeholder="Buscar productos por nombre o código...">
                        </div>
                    </div>

                    <!-- Products Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-2 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Código</th>
                                    <th
                                        class="px-2 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Acción</th>
                                    <th
                                        class="px-2 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Producto</th>
                                    <th
                                        class="px-2 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">
                                        Categoría</th>
                                    <th
                                        class="px-2 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Stock</th>
                                    <th
                                        class="px-2 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">
                                        Precio Compra</th>
                                    <th
                                        class="px-2 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">
                                        Estado</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($products as $product)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-2 sm:px-6 py-2 sm:py-4 whitespace-nowrap">
                                            <span class="text-xs sm:text-sm font-mono text-gray-900">{{ $product->code }}</span>
                                        </td>
                                        <td class="px-2 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-xs sm:text-sm font-medium">
                                            <button
                                                @click="addProductFromModal({{ $product->id }}, '{{ $product->code }}', '{{ $product->name }}', '{{ $product->image_url }}', {{ $product->stock }}, {{ $product->purchase_price }}, '{{ $product->category->name }}')"
                                                data-product-id="{{ $product->id }}"
                                                class="inline-flex items-center px-2 sm:px-3 py-1 sm:py-2 border border-transparent text-xs sm:text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                                <i class="fas fa-plus-circle mr-1 sm:mr-2 text-xs sm:text-sm"></i>
                                            </button>
                                        </td>
                                        <td class="px-2 sm:px-6 py-2 sm:py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-8 w-8 sm:h-10 sm:w-10">
                                                    <img class="h-8 w-8 sm:h-10 sm:w-10 rounded-lg object-cover"
                                                        src="{{ $product->image_url }}" alt="{{ $product->name }}">
                                                </div>
                                                <div class="ml-2 sm:ml-4">
                                                    <div class="text-xs sm:text-sm font-medium text-gray-900">{{ $product->name }}
                                                    </div>
                                                    <div class="text-xs sm:text-sm text-gray-500 hidden sm:block">{{ $product->code }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-2 sm:px-6 py-2 sm:py-4 whitespace-nowrap hidden sm:table-cell">
                                            <span
                                                class="inline-flex px-1 sm:px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                                {{ $product->category->name }}
                                            </span>
                                        </td>
                                        <td class="px-2 sm:px-6 py-2 sm:py-4 whitespace-nowrap">
                                            <span
                                                class="inline-flex px-1 sm:px-2 py-1 text-xs font-semibold rounded-full
                                            @if ($product->stock < 10) bg-red-100 text-red-800
                                            @elseif($product->stock < 50) bg-yellow-100 text-yellow-800
                                            @else bg-green-100 text-green-800 @endif">
                                                {{ $product->stock }}
                                            </span>
                                        </td>
                                        <td class="px-2 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900 hidden md:table-cell">
                                            <span>{{ $currency->symbol }}
                                                {{ number_format($product->purchase_price, 2) }}</span>
                                        </td>
                                        <td class="px-2 sm:px-6 py-2 sm:py-4 whitespace-nowrap hidden lg:table-cell">
                                            <span
                                                class="inline-flex px-1 sm:px-2 py-1 text-xs font-semibold rounded-full
                                            @if ($product->stock_status_label === 'Bajo')
                                                bg-red-100 text-red-800
                                            @elseif($product->stock_status_label === 'Normal')
                                                bg-yellow-100 text-yellow-800
                                            @else
                                                bg-green-100 text-green-800
                                            @endif">
                                                {{ $product->stock_status_label }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    @if ($products->count() === 0)
                        <div class="text-center py-12">
                            <i class="fas fa-search text-4xl text-gray-300 mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No hay productos disponibles</h3>
                            <p class="text-gray-500">No hay productos en el inventario para realizar compras</p>
                        </div>
                    @endif
                </div>

                <!-- Modal Footer -->
                <div class="bg-gray-50 px-4 sm:px-6 py-3 sm:py-4 flex justify-end">
                    <button @click="closeModal()"
                        class="inline-flex items-center px-3 sm:px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <i class="fas fa-times mr-1 sm:mr-2"></i>
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/purchases/create.css') }}">
@endpush

@push('js')
    <script src="{{ asset('js/admin/purchases/create.js') }}" defer></script>
@endpush

