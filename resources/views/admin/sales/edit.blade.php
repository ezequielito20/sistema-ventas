@extends('layouts.app')

@section('title', 'Editar Venta')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="bg-gradient-to-br from-indigo-600 via-purple-600 to-blue-600 mb-6 relative overflow-hidden rounded-2xl">
            <!-- Elementos decorativos de fondo -->
            <div class="absolute inset-0 bg-black bg-opacity-10"></div>

            <div class="relative z-10 px-6 py-8">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center space-x-4 mb-4 lg:mb-0">
                        <div
                            class="w-16 h-16 bg-indigo-600 bg-opacity-30 rounded-2xl flex items-center justify-center backdrop-blur-sm border border-white border-opacity-30">
                            <i class="fas fa-edit text-2xl text-white"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-white mb-1">Editar Venta #{{ $sale->id }}</h1>
                            <p class="text-white text-opacity-90 text-lg">Modifique los datos de la transacción de venta</p>
                        </div>
                    </div>
                    <div>
                        <button id="backButton"
                            class="bg-gray-800 bg-opacity-40 hover:bg-opacity-60 text-white px-6 py-3 rounded-xl font-medium transition-all duration-300 backdrop-blur-sm border border-white border-opacity-30 hover:scale-105 transform">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Volver
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div x-data="saleForm()" class="w-full space-y-8">
            <form action="{{ route('admin.sales.update', $sale->id) }}" method="POST" enctype="multipart/form-data" id="saleForm">
                @csrf
                @method('PUT')

                <!-- Sección de Información Básica -->
                <div class="mb-6 bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
                    <!-- Header de la sección -->
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-5">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-blue-500 bg-opacity-30 rounded-2xl flex items-center justify-center">
                                <i class="fas fa-info-circle text-white text-xl"></i>
                            </div>
                            <h3 class="text-2xl font-bold text-white">Información de la Venta</h3>
                        </div>
                    </div>

                    <div class="p-6">
                        <!-- Primera fila: Código, Cliente, Fecha, Hora -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                            <!-- Código de Producto -->
                            <div>
                                <label for="product_code" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-barcode text-indigo-500 mr-1"></i>
                                    Código <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="text" name="product_code" id="product_code"
                                        class="w-full pl-3 pr-20 py-2.5 bg-gray-50 border-2 border-gray-200 rounded-xl focus:border-indigo-500 focus:bg-white transition-all duration-300 text-gray-800 placeholder-gray-400 text-sm @error('product_code') border-red-300 @enderror"
                                        placeholder="Código del producto">
                                    <div class="absolute right-1 top-1 flex space-x-1">
                                        <button type="button"
                                            class="w-8 h-8 bg-blue-500 hover:bg-blue-600 text-white rounded-lg flex items-center justify-center transition-all duration-300"
                                            id="searchProduct" @click="searchModalOpen = true">
                                            <i class="fas fa-search text-xs"></i>
                                        </button>
                                        <a href="/products/create"
                                            class="w-8 h-8 bg-green-500 hover:bg-green-600 text-white rounded-lg flex items-center justify-center transition-all duration-300">
                                            <i class="fas fa-plus text-xs"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Cliente -->
                            <div>
                                <label for="customer_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-user text-indigo-500 mr-1"></i>
                                    Cliente <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <select name="customer_id" id="customer_id"
                                        class="w-full pl-3 pr-10 py-2.5 bg-gray-50 border-2 rounded-xl focus:border-gray-500 focus:bg-white transition-all duration-300 text-gray-800 text-sm h-11 @error('customer_id') border-gray-300 @enderror"
                                        required>
                                        <option value="">Seleccione un cliente</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}" {{ $sale->customer_id == $customer->id ? 'selected' : '' }} data-debt="{{ $customer->total_debt }}">
                                                {{ $customer->name }} - {{ $currency->symbol }} {{ number_format($customer->total_debt, 2) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="absolute right-1 top-1 z-10">
                                        <a href="{{ route('admin.customers.create') }}?return_to=sales.edit"
                                            class="w-8 h-8 bg-green-500 hover:bg-green-600 text-white rounded-lg flex items-center justify-center transition-all duration-300 add-customer-button">
                                            <i class="fas fa-plus text-xs"></i>
                                        </a>
                                    </div>
                                </div>
                                @error('customer_id')
                                    <div class="flex items-center mt-2 text-red-600 text-sm">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Fecha de Venta -->
                            <div>
                                <label for="sale_date" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-calendar text-indigo-500 mr-1"></i>
                                    Fecha <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="sale_date" id="sale_date"
                                    class="w-full px-3 py-2.5 bg-gray-50 border-2 border-gray-200 rounded-xl focus:border-indigo-500 focus:bg-white transition-all duration-300 text-gray-800 text-sm @error('sale_date') border-red-300 @enderror"
                                    value="{{ old('sale_date', $sale->sale_date->format('Y-m-d')) }}" required>
                                @error('sale_date')
                                    <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Hora de Venta -->
                            <div>
                                <label for="sale_time" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-clock text-indigo-500 mr-1"></i>
                                    Hora <span class="text-red-500">*</span>
                                </label>
                                <input type="time" name="sale_time" id="sale_time"
                                    class="w-full px-3 py-2.5 bg-gray-50 border-2 border-gray-200 rounded-xl focus:border-indigo-500 focus:bg-white transition-all duration-300 text-gray-800 text-sm @error('sale_time') border-red-300 @enderror"
                                    value="{{ old('sale_time', $sale->sale_date->format('H:i')) }}" required>
                                @error('sale_time')
                                    <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección de Productos -->
                <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
                    <!-- Header de la sección -->
                    <div class="bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-emerald-500 bg-opacity-30 rounded-2xl flex items-center justify-center">
                                    <i class="fas fa-shopping-bag text-white text-xl"></i>
                                </div>
                                <h3 class="text-2xl font-bold text-white">Productos en la Venta</h3>
                            </div>

                            <!-- Contadores en el header -->
                            <div class="flex items-center space-x-4">
                                <div class="bg-gray-800 bg-opacity-40 px-4 py-2 rounded-xl flex items-center space-x-2 backdrop-blur-sm border border-white border-opacity-20">
                                    <i class="fas fa-boxes text-white"></i>
                                    <span class="products-count text-white font-semibold">{{ count($saleDetails) }} productos</span>
                                </div>
                                <div class="bg-gray-800 bg-opacity-40 px-4 py-2 rounded-xl flex items-center space-x-2 backdrop-blur-sm border border-white border-opacity-20">
                                    <i class="fas fa-calculator text-white"></i>
                                    <span class="total-amount-display text-white font-bold">{{ $currency->symbol }} {{ number_format($sale->total_price, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <!-- Tabla de productos -->
                        <div class="relative">
                            <div class="overflow-x-auto bg-gray-50 rounded-2xl border-2 border-gray-100">
                                <table class="w-full modern-table">
                                    <thead class="bg-gradient-to-r from-gray-700 to-gray-800">
                                        <tr>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">
                                                <i class="fas fa-barcode mr-2"></i>Código
                                            </th>
                                            <th class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">
                                                <i class="fas fa-box mr-2"></i>Producto
                                            </th>
                                            <th class="px-6 py-4 text-center text-xs font-semibold text-white uppercase tracking-wider">
                                                <i class="fas fa-warehouse mr-2"></i>Stock
                                            </th>
                                            <th class="px-6 py-4 text-center text-xs font-semibold text-white uppercase tracking-wider">
                                                <i class="fas fa-sort-numeric-up mr-2"></i>Cantidad
                                            </th>
                                            <th class="px-6 py-4 text-right text-xs font-semibold text-white uppercase tracking-wider">
                                                <i class="fas fa-dollar-sign mr-2"></i>Precio Unit.
                                            </th>
                                            <th class="px-6 py-4 text-right text-xs font-semibold text-white uppercase tracking-wider">
                                                <i class="fas fa-calculator mr-2"></i>Subtotal
                                            </th>
                                            <th class="px-6 py-4 text-center text-xs font-semibold text-white uppercase tracking-wider">
                                                <i class="fas fa-cogs mr-2"></i>Acciones
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="saleItems" class="divide-y divide-gray-200">
                                        @foreach ($saleDetails as $detail)
                                            <tr data-product-id="{{ $detail['product_id'] }}" data-product-code="{{ $detail['code'] }}" class="hover:bg-gray-50 transition-colors duration-200">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $detail['code'] }}</td>
                                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $detail['name'] }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $detail['stock'] > 10 ? 'bg-green-100 text-green-800' : ($detail['stock'] > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                        {{ $detail['stock'] }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <input type="number" class="quantity-input" 
                                                           name="items[{{ $detail['product_id'] }}][quantity]"
                                                           value="{{ $detail['quantity'] }}" min="1" max="{{ $detail['stock'] }}" step="1">
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900">
                                                    {{ $currency->symbol }} {{ number_format($detail['sale_price'], 2) }}
                                                    <input type="hidden" class="price-input" value="{{ $detail['sale_price'] }}">
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900">
                                                    <span class="subtotal-display">{{ $currency->symbol }} {{ number_format($detail['quantity'] * $detail['sale_price'], 2) }}</span>
                                                    <span class="subtotal-value hidden">{{ $detail['quantity'] * $detail['sale_price'] }}</span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <button type="button" class="btn-action-remove remove-item">
                                                        <i class="fas fa-trash text-sm"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Estado vacío -->
                            <div class="empty-state" id="emptyState" style="display: none;">
                                <div class="text-center py-16">
                                    <div class="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center">
                                        <i class="fas fa-shopping-cart text-4xl text-gray-400"></i>
                                    </div>
                                    <h4 class="text-xl font-semibold text-gray-600 mb-2">No hay productos agregados</h4>
                                    <p class="text-gray-500">Agregue productos escaneando códigos o usando el buscador</p>
                                </div>
                            </div>
                        </div>

                        <!-- Total de la venta y Nota -->
                        <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Campo de Nota -->
                            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-3 border border-blue-100">
                                <div class="flex items-start space-x-3">
                                                                            <div class="w-8 h-8 bg-blue-600 rounded-xl flex items-center justify-center flex-shrink-0">
                                            <i class="fas fa-sticky-note text-white text-sm"></i>
                                        </div>
                                    <div class="flex-1">
                                        <label for="note" class="block text-sm font-semibold text-gray-700 mb-1">
                                            Nota de la Venta
                                        </label>
                                        <textarea name="note" id="note" rows="2"
                                            class="w-full px-2 py-1 bg-white border-2 border-blue-200 rounded-xl focus:border-blue-500 transition-all duration-300 text-gray-800 placeholder-gray-400 resize-none text-sm"
                                            placeholder="Agregue una nota adicional para esta venta (opcional)">{{ old('note', $sale->note) }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Total de la venta -->
                            <div class="bg-gradient-to-br from-green-500 to-teal-600 rounded-2xl p-3 text-white">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-emerald-600 bg-opacity-30 rounded-xl flex items-center justify-center">
                                            <i class="fas fa-receipt text-lg"></i>
                                        </div>
                                        <div>
                                            <p class="text-emerald-100 text-sm mb-0.5">Total de la Venta</p>
                                            <p class="text-xl font-bold" id="totalAmount">{{ $currency->symbol }} {{ number_format($sale->total_price, 2) }}</p>
                                            <input type="hidden" name="total_price" id="totalAmountInput" value="{{ $sale->total_price }}">
                                        </div>
                                    </div>

                                    <!-- Botones de acción -->
                                    <div class="flex items-center space-x-2">
                                        <!-- Botón Cancelar -->
                                        <button type="button" id="cancelSale"
                                            class="group relative w-10 h-10 bg-red-500 hover:bg-red-600 text-white rounded-xl transition-all duration-300 hover:scale-105 flex items-center justify-center shadow-lg">
                                            <i class="fas fa-times text-sm group-hover:scale-110 transition-transform duration-300"></i>
                                            <div class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">
                                                Cancelar
                                            </div>
                                        </button>

                                        <!-- Botón Actualizar Venta -->
                                        <button type="submit" id="submitSale"
                                            class="group relative w-12 h-12 bg-purple-600 hover:bg-purple-700 text-white rounded-xl transition-all duration-300 hover:scale-105 flex items-center justify-center shadow-lg">
                                            <i class="fas fa-save text-sm group-hover:scale-110 transition-transform duration-300"></i>
                                            <div class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">
                                                Actualizar
                                            </div>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </form>

            <!-- Modal de Búsqueda de Productos -->
            <div x-show="searchModalOpen" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-50 overflow-y-auto" 
                 style="display: none;">
                
                <!-- Overlay de fondo -->
                <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" 
                     @click="searchModalOpen = false"></div>
                
                <!-- Contenido del modal -->
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="relative bg-cyan-500 rounded-3xl shadow-2xl max-w-7xl w-full max-h-[90vh] overflow-hidden"
                         @click.stop>
                        
                        <!-- Header del Modal -->
                        <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-8 py-6 relative overflow-hidden">
                            <div class="absolute inset-0 bg-black bg-opacity-10"></div>
                            <div class="absolute -top-4 -right-4 w-32 h-32 bg-white bg-opacity-10 rounded-full"></div>
                            <div class="relative z-10 flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="w-14 h-14 bg-purple-500 bg-opacity-30 rounded-2xl flex items-center justify-center backdrop-blur-sm">
                                        <i class="fas fa-search text-white text-xl"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-2xl font-bold text-white mb-1">Búsqueda de Productos</h4>
                                        <p class="text-white text-opacity-90">Seleccione productos para agregar a la venta</p>
                                    </div>
                                </div>
                                <button type="button"
                                    class="w-10 h-10 bg-cyan-500 bg-opacity-20 hover:bg-opacity-30 text-white rounded-xl flex items-center justify-center transition-all duration-300 backdrop-blur-sm"
                                    @click="searchModalOpen = false">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Cuerpo del Modal -->
                        <div class="p-8 bg-gray-50 max-h-[calc(90vh-200px)] overflow-y-auto">
                            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                                <div class="overflow-x-auto">
                                    <table id="productsTable" class="w-full">
                                        <thead class="bg-gradient-to-r from-gray-700 to-gray-800">
                                            <tr>
                                                <th class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">
                                                    <i class="fas fa-barcode mr-2"></i>Código
                                                </th>
                                                <th class="px-6 py-4 text-center text-xs font-semibold text-white uppercase tracking-wider">
                                                    <i class="fas fa-plus-circle mr-2"></i>Acción
                                                </th>
                                                <th class="px-6 py-4 text-center text-xs font-semibold text-white uppercase tracking-wider">
                                                    <i class="fas fa-image mr-2"></i>Imagen
                                                </th>
                                                <th class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">
                                                    <i class="fas fa-box mr-2"></i>Nombre
                                                </th>
                                                <th class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">
                                                    <i class="fas fa-tags mr-2"></i>Categoría
                                                </th>
                                                <th class="px-6 py-4 text-center text-xs font-semibold text-white uppercase tracking-wider">
                                                    <i class="fas fa-warehouse mr-2"></i>Stock
                                                </th>
                                                <th class="px-6 py-4 text-right text-xs font-semibold text-white uppercase tracking-wider">
                                                    <i class="fas fa-dollar-sign mr-2"></i>Precio
                                                </th>
                                                <th class="px-6 py-4 text-center text-xs font-semibold text-white uppercase tracking-wider">
                                                    <i class="fas fa-info-circle mr-2"></i>Estado
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            @foreach ($products as $product)
                                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        {{ $product->code }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                                        <button type="button"
                                                            class="w-10 h-10 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white rounded-xl flex items-center justify-center transition-all duration-300 hover:scale-105 select-product {{ $product->stock <= 0 ? 'opacity-50 cursor-not-allowed' : '' }}"
                                                            data-code="{{ $product->code }}" data-id="{{ $product->id }}"
                                                            onclick="addProductFromModal('{{ addslashes($product->code) }}', '{{ $product->id }}', '{{ addslashes($product->name) }}', '{{ $product->image_url }}', {{ $product->stock }}, {{ $product->sale_price }}, '{{ addslashes($product->category->name) }}')"
                                                            {{ $product->stock <= 0 ? 'disabled' : '' }}>
                                                            <i class="fas fa-plus text-sm"></i>
                                                        </button>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                                        <img src="{{ $product->image_url }}" alt="N/I"
                                                            class="w-12 h-12 rounded-xl object-cover mx-auto border-2 border-gray-200">
                                                    </td>
                                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                                        {{ $product->name }}
                                                    </td>
                                                    <td class="px-6 py-4 text-sm text-gray-600">
                                                        {{ $product->category->name }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                                            {{ $product->stock > 10 ? 'bg-green-100 text-green-800' : ($product->stock > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                            {{ $product->stock }}
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900">
                                                        {{ $currency->symbol }} {{ number_format($product->sale_price, 2) }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                                            {{ $product->stock_status_label === 'Bajo' ? 'bg-red-100 text-red-800' : ($product->stock_status_label === 'Normal' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                                                            {{ $product->stock_status_label }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('vendor/sweetalert2/sweetalert2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/sales/edit.css') }}">
@endpush

@push('js')
    <script src="{{ asset('vendor/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('js/admin/sales/edit.js') }}"></script>
@endpush

