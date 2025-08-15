@extends('layouts.app')

@section('title', 'Nueva Venta')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 mb-6 relative overflow-hidden rounded-2xl">
            <!-- Elementos decorativos de fondo -->
            <div class="absolute inset-0 bg-black bg-opacity-10"></div>
            <div class="absolute -top-4 -right-4 w-32 h-32 bg-white bg-opacity-10 rounded-full"></div>
            <div class="absolute top-1/2 -left-8 w-24 h-24 bg-white bg-opacity-5 rounded-full"></div>

            <div class="relative z-10 px-6 py-8">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center space-x-4 mb-4 lg:mb-0">
                        <div
                            class="w-16 h-16 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center backdrop-blur-sm border border-white border-opacity-20">
                            <i class="fas fa-shopping-cart text-2xl text-white"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-white mb-1">Nueva Venta</h1>
                            <p class="text-white text-opacity-90 text-lg">Registre una nueva transacción de venta</p>
                        </div>
                    </div>
                    <div>
                        <button onclick="window.history.back()"
                            class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-6 py-3 rounded-xl font-medium transition-all duration-300 backdrop-blur-sm border border-white border-opacity-20 hover:scale-105 transform">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Volver
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div x-data="saleForm()" class="w-full space-y-8">
            <form action="{{ route('admin.sales.store') }}" method="POST" enctype="multipart/form-data" id="saleForm">
                @csrf

                <!-- Sección de Información Básica -->
                <div class="mb-6 bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
                    <!-- Header de la sección -->
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-5">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center">
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
                                        class="w-full pl-3 pr-10 py-2.5 bg-gray-50 border-2 border-gray-200 rounded-xl focus:border-gray-500 focus:bg-white transition-all duration-300 text-gray-800 select2 text-sm h-11 @error('customer_id') border-red-300 @enderror"
                                        required>
                                        <option value="">Seleccione un cliente</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}"
                                                {{ isset($selectedCustomerId) && $selectedCustomerId == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->name }} - {{ $currency->symbol }}
                                                {{ number_format($customer->total_debt, 2) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="absolute right-1 top-1">
                                        <a href="{{ route('admin.customers.create') }}?return_to=sales.create"
                                            class="w-8 h-8 bg-green-500 hover:bg-green-600 text-white rounded-lg flex items-center justify-center transition-all duration-300">
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
                                    value="{{ old('sale_date', date('Y-m-d')) }}" required>
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
                                    value="{{ old('sale_time', date('H:i')) }}" required>
                                @error('sale_time')
                                    <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Segunda fila: ¿Ya pagó? -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- ¿Ya pagó? -->
                            <div>
                                <label for="already_paid" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-credit-card text-indigo-500 mr-1"></i>
                                    ¿Ya pagó?
                                </label>
                                <select name="already_paid" id="already_paid"
                                    class="w-full px-3 py-2.5 bg-gray-50 border-2 border-gray-200 rounded-xl focus:border-indigo-500 focus:bg-white transition-all duration-300 text-gray-800 text-sm h-11">
                                    <option value="0" {{ old('already_paid', '0') == '0' ? 'selected' : '' }}>No
                                    </option>
                                    <option value="1" {{ old('already_paid', '0') == '1' ? 'selected' : '' }}>Sí
                                    </option>
                                </select>
                                <p class="text-gray-500 text-xs mt-2 flex items-center">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Si selecciona "Sí", se registrará automáticamente el pago
                                </p>
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
                                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center">
                                    <i class="fas fa-shopping-bag text-white text-xl"></i>
                                </div>
                                <h3 class="text-2xl font-bold text-white">Productos en la Venta</h3>
                            </div>

                            <!-- Contadores en el header -->
                            <div class="flex items-center space-x-4">
                                <div class="bg-white bg-opacity-20 px-4 py-2 rounded-xl flex items-center space-x-2">
                                    <i class="fas fa-boxes text-white"></i>
                                    <span class="products-count text-white font-semibold">0 productos</span>
                                </div>
                                <div class="bg-white bg-opacity-20 px-4 py-2 rounded-xl flex items-center space-x-2">
                                    <i class="fas fa-calculator text-white"></i>
                                    <span class="total-amount-display text-white font-bold">{{ $currency->symbol }}
                                        0.00</span>
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
                                            <th
                                                class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">
                                                <i class="fas fa-barcode mr-2"></i>Código
                                            </th>
                                            <th
                                                class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">
                                                <i class="fas fa-box mr-2"></i>Producto
                                            </th>
                                            <th
                                                class="px-6 py-4 text-center text-xs font-semibold text-white uppercase tracking-wider">
                                                <i class="fas fa-warehouse mr-2"></i>Stock
                                            </th>
                                            <th
                                                class="px-6 py-4 text-center text-xs font-semibold text-white uppercase tracking-wider">
                                                <i class="fas fa-sort-numeric-up mr-2"></i>Cantidad
                                            </th>
                                            <th
                                                class="px-6 py-4 text-right text-xs font-semibold text-white uppercase tracking-wider">
                                                <i class="fas fa-dollar-sign mr-2"></i>Precio Unit.
                                            </th>
                                            <th
                                                class="px-6 py-4 text-right text-xs font-semibold text-white uppercase tracking-wider">
                                                <i class="fas fa-calculator mr-2"></i>Subtotal
                                            </th>
                                            <th
                                                class="px-6 py-4 text-center text-xs font-semibold text-white uppercase tracking-wider">
                                                <i class="fas fa-cogs mr-2"></i>Acciones
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="saleItems" class="divide-y divide-gray-200">
                                        <!-- Los productos se agregarán dinámicamente aquí -->
                                    </tbody>
                                </table>
                            </div>

                            <!-- Estado vacío -->
                            <div class="empty-state" id="emptyState">
                                <div class="text-center py-16">
                                    <div
                                        class="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center">
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
                            <div
                                class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-3 border border-blue-100">
                                <div class="flex items-start space-x-3">
                                    <div
                                        class="w-8 h-8 bg-blue-500 rounded-xl flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-sticky-note text-white text-sm"></i>
                                    </div>
                                    <div class="flex-1">
                                        <label for="note" class="block text-sm font-semibold text-gray-700 mb-1">
                                            Nota de la Venta
                                        </label>
                                        <textarea name="note" id="note" rows="2"
                                            class="w-full px-2 py-1 bg-white border-2 border-blue-200 rounded-xl focus:border-blue-500 transition-all duration-300 text-gray-800 placeholder-gray-400 resize-none text-sm"
                                            placeholder="Agregue una nota adicional para esta venta (opcional)">{{ old('note') }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Total de la venta -->
                            <div class="bg-gradient-to-br from-green-500 to-teal-600 rounded-2xl p-3 text-white">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div
                                            class="w-10 h-10 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                                            <i class="fas fa-receipt text-lg"></i>
                                        </div>
                                        <div>
                                            <p class="text-emerald-100 text-sm mb-0.5">Total de la Venta</p>
                                            <p class="text-xl font-bold" id="totalAmount">{{ $currency->symbol }} 0.00
                                            </p>
                                            <input type="hidden" name="total_price" id="totalAmountInput"
                                                value="0">
                                        </div>
                                    </div>

                                    <!-- Botones de acción -->
                                    <div class="flex items-center space-x-2">
                                        <!-- Botón Cancelar -->
                                        <button type="button" id="cancelSale"
                                            class="group relative w-10 h-10 bg-red-500 hover:bg-red-600 text-white rounded-xl transition-all duration-300 hover:scale-105 flex items-center justify-center shadow-lg">
                                            <i
                                                class="fas fa-times text-sm group-hover:scale-110 transition-transform duration-300"></i>
                                            <div
                                                class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">
                                                Cancelar
                                            </div>
                                        </button>

                                        <!-- Botón Procesar Venta -->
                                        <button type="submit" id="submitSale" name="action" value="save"
                                            class="group relative w-12 h-12 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-all duration-300 hover:scale-105 flex items-center justify-center shadow-lg">
                                            <i
                                                class="fas fa-save text-sm group-hover:scale-110 transition-transform duration-300"></i>
                                            <div
                                                class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">
                                                Procesar
                                            </div>
                                        </button>

                                        <!-- Botón Procesar y Nueva Venta -->
                                        <button type="submit" id="submitSaleAndNew" name="action" value="save_and_new"
                                            class="group relative w-10 h-10 bg-amber-500 hover:bg-amber-600 text-white rounded-xl transition-all duration-300 hover:scale-105 flex items-center justify-center shadow-lg">
                                            <i
                                                class="fas fa-plus text-sm group-hover:scale-110 transition-transform duration-300"></i>
                                            <div
                                                class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">
                                                Procesar y Nueva
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
                    <div class="relative bg-white rounded-3xl shadow-2xl max-w-7xl w-full max-h-[90vh] overflow-hidden"
                         @click.stop>
                        
                        <!-- Header del Modal -->
                        <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-8 py-6 relative overflow-hidden">
                            <div class="absolute inset-0 bg-black bg-opacity-10"></div>
                            <div class="absolute -top-4 -right-4 w-32 h-32 bg-white bg-opacity-10 rounded-full"></div>
                            <div class="relative z-10 flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="w-14 h-14 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center backdrop-blur-sm">
                                        <i class="fas fa-search text-white text-xl"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-2xl font-bold text-white mb-1">Búsqueda de Productos</h4>
                                        <p class="text-white text-opacity-90">Seleccione productos para agregar a la venta</p>
                                    </div>
                                </div>
                                <button type="button"
                                    class="w-10 h-10 bg-white bg-opacity-20 hover:bg-opacity-30 text-white rounded-xl flex items-center justify-center transition-all duration-300 backdrop-blur-sm"
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
    <link href="{{ asset('vendor/select2/select2.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('vendor/select2/select2-bootstrap4.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('vendor/sweetalert2/sweetalert2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/datatables/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/datatables/responsive.bootstrap4.min.css') }}">

    <style>
        /* Estilos personalizados para complementar Tailwind CSS */

        /* Animaciones personalizadas */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Aplicar animaciones a las secciones */
        .max-w-7xl>div:nth-child(1) {
            animation: fadeInUp 0.6s ease-out;
        }

        .max-w-7xl>div:nth-child(2) {
            animation: fadeInUp 0.6s ease-out 0.1s both;
        }

        .max-w-7xl>div:nth-child(3) {
            animation: fadeInUp 0.6s ease-out 0.2s both;
        }

        /* Estado vacío mejorado */
        .empty-state:not(.hidden) {
            display: block !important;
        }

        .empty-state.hidden {
            display: none !important;
        }

        /* Tabla moderna - ocultar cuando esté vacía */
        .modern-table:has(tbody:empty) {
            display: none;
        }

        /* Scrollbar personalizado */
        .overflow-x-auto::-webkit-scrollbar {
            height: 8px;
        }

        .overflow-x-auto::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb {
            background: linear-gradient(90deg, #6366f1, #8b5cf6);
            border-radius: 4px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(90deg, #4f46e5, #7c3aed);
        }

        /* Input de cantidad personalizado */
        .quantity-input {
            width: 80px !important;
            text-align: center;
            font-weight: 600;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 0.5rem;
            transition: all 0.3s ease;
        }

        .quantity-input:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        /* Botones de acción en la tabla */
        .btn-action-remove {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            border: none;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-action-remove:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        }

        /* Select2 con estilos de Tailwind */
        .select2-container--bootstrap4 .select2-selection--single {
            height: 44px !important; /* h-11 = 44px */
            border: 2px solid #e5e7eb !important;
            border-radius: 1rem !important;
            background: #f9fafb !important;
            padding: 0.625rem 1rem !important; /* py-2.5 px-3 */
            min-height: 44px !important;
            line-height: 1.5 !important;
        }

        .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
            line-height: 1.5 !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            color: #374151 !important; /* text-gray-800 */
        }

        .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
            height: 44px !important;
            line-height: 44px !important;
            right: 8px !important;
        }

        .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow b {
            border-color: #6b7280 transparent transparent transparent !important;
        }

        .select2-container--bootstrap4 .select2-selection--single:focus-within,
        .select2-container--bootstrap4.select2-container--open .select2-selection--single {
            border-color: #6b7280 !important;
            background: white !important;
            box-shadow: 0 0 0 3px rgba(107, 114, 128, 0.1) !important;
        }

        /* Dropdown styles con color gris */
        .select2-container--bootstrap4 .select2-dropdown {
            border: 2px solid #6b7280 !important;
            border-radius: 12px !important;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
            z-index: 9999 !important;
            background: white !important;
        }

        /* Estilos para las opciones del dropdown - color gris */
        .select2-container--bootstrap4 .select2-results__option--highlighted[aria-selected] {
            background-color: #6b7280 !important;
            color: white !important;
        }

        .select2-container--bootstrap4 .select2-results__option[aria-selected=true] {
            background-color: #9ca3af !important;
            color: white !important;
        }

        .select2-container--bootstrap4 .select2-results__option:hover {
            background-color: #d1d5db !important;
            color: #374151 !important;
        }

        /* Glassmorphism effect para algunos elementos */
        .glass-effect {
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        /* Mejoras para dispositivos móviles */
        @media (max-width: 768px) {
            .max-w-7xl {
                padding: 0 1rem;
            }

            .grid-cols-1.md\\:grid-cols-2.lg\\:grid-cols-5 {
                gap: 1rem;
            }

            .text-3xl {
                font-size: 1.875rem;
            }

            .text-2xl {
                font-size: 1.5rem;
            }
        }

        /* Indicador de carga para los botones */
        .btn-loading {
            position: relative;
            pointer-events: none;
        }

        .btn-loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Mejora de accesibilidad */
        .focus\\:outline-none:focus {
            outline: 2px solid transparent;
            outline-offset: 2px;
        }

        .focus\\:ring-2:focus {
            --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
            --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(2px + var(--tw-ring-offset-width)) var(--tw-ring-color);
            box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
        }
    </style>
@endpush

@push('js')
    <script src="{{ asset('vendor/config.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Cargar Select2, DataTables y SweetAlert2
            loadSelect2(function() {
                loadDataTables(function() {
                    loadSweetAlert2(function() {
                        // Guardar la URL original cuando se carga la página por primera vez
                        if (!sessionStorage.getItem('sales_original_referrer')) {
                            const referrer = document.referrer;
                            if (referrer && !referrer.includes('/sales/create')) {
                                sessionStorage.setItem('sales_original_referrer', referrer);
                            }
                        }

                        // Inicializar Select2 con opciones mejoradas
                        $('#customer_id').select2({
                            theme: 'bootstrap4',
                            placeholder: 'Seleccione un cliente',
                            allowClear: true,
                            width: '100%',
                            dropdownAutoWidth: false,
                            dropdownParent: $(
                                'body'
                            ), // Cambiar a body para evitar problemas de z-index
                            escapeMarkup: function(markup) {
                                return markup;
                            },
                            language: {
                                noResults: function() {
                                    return "No se encontraron resultados";
                                },
                                searching: function() {
                                    return "Buscando...";
                                },
                                loadingMore: function() {
                                    return "Cargando más resultados...";
                                }
                            },
                            templateResult: formatCustomer,
                            templateSelection: formatCustomerSelection,
                            matcher: function(params, data) {
                                // Si no hay término de búsqueda, mostrar todas las opciones
                                if ($.trim(params.term) === '') {
                                    return data;
                                }

                                // Si no hay datos, no mostrar nada
                                if (typeof data.text === 'undefined') {
                                    return null;
                                }

                                // Buscar en el texto completo (nombre y deuda)
                                const searchTerm = params.term.toLowerCase();
                                const fullText = data.text.toLowerCase();

                                if (fullText.indexOf(searchTerm) > -1) {
                                    return data;
                                }

                                // No hay coincidencia
                                return null;
                            }
                        });

                        // Función para verificar si solo hay un producto disponible
                        function checkAndAddSingleProduct() {
                            // Contar productos disponibles desde la tabla del modal
                            const availableProducts = $('#productsTable tbody tr').length;

                            if (availableProducts === 1) {
                                // Solo hay un producto, obtener sus datos directamente de la fila
                                const productRow = $('#productsTable tbody tr:first');
                                const productCode = productRow.find('td:eq(0)').text()
                                    .trim();
                                const productName = productRow.find('td:eq(3)').text()
                                    .trim();
                                const productImage = productRow.find('td:eq(2) img').attr(
                                    'src');
                                const productStock = productRow.find(
                                    'td:eq(5) span').text().trim();
                                const productPriceText = productRow.find('td:eq(6)').text()
                                    .trim();
                                const productPrice = parseFloat(productPriceText.replace(
                                    /[^\d.,]/g, '').replace(',', '.')) || 0;

                                // Obtener el ID real del producto desde el botón
                                const productId = productRow.find('button.select-product')
                                    .data('id');


                                // Crear objeto producto con los datos disponibles
                                const product = {
                                    id: productId, // ID real del producto
                                    code: productCode,
                                    name: productName,
                                    image: productImage,
                                    stock: parseInt(productStock),
                                    sale_price: productPrice
                                };


                                setTimeout(function() {
                                        // Agregar el producto a la tabla silenciosamente (sin alerta porque es automático)
                                        addProductToTable(product, false);

                                        // No abrir automáticamente el select de clientes
                                        // El usuario puede seleccionar el cliente cuando lo desee
                                    },
                                    500
                                ); // Pequeño delay para asegurar que la página esté completamente cargada
                            } else if (availableProducts === 0) {
                                // No hay productos en inventario
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Sin productos disponibles',
                                    text: 'No hay productos disponibles en el inventario para realizar ventas',
                                    confirmButtonText: 'Entendido'
                                });
                            }
                            // Si hay más de un producto, no hacer nada (comportamiento normal)
                        }

                        // Si hay un cliente preseleccionado, asegurarse de que Select2 lo muestre correctamente
                        @if (isset($selectedCustomerId))
                            $('#customer_id').val('{{ $selectedCustomerId }}').trigger(
                                'change');
                        @endif

                        // Asegurar que Select2 se renderice correctamente después de la inicialización
                        setTimeout(function() {
                            $('#customer_id').trigger('change.select2');
                        }, 100);

                        // Manejar eventos de apertura y cierre del dropdown
                        $('#customer_id').on('select2:open', function() {
                            // Asegurar que el dropdown tenga el z-index correcto
                            $('.select2-dropdown').css('z-index', 9999);

                            // Enfocar el campo de búsqueda si existe
                            setTimeout(function() {
                                $('.select2-search__field').focus();
                            }, 100);
                        });

                        $('#customer_id').on('select2:close', function() {
                            // Remover cualquier estilo temporal
                            $('.select2-dropdown').css('z-index', '');
                        });

                        // Función para formatear las opciones en el dropdown
                        function formatCustomer(customer) {
                            if (!customer.id) {
                                return customer.text;
                            }

                            // Extraer nombre y deuda del texto
                            const parts = customer.text.split(' - ');
                            if (parts.length < 2) {
                                return customer.text;
                            }

                            const name = parts[0].trim();
                            const debt = parts[1].trim();

                            // Determinar el tipo de badge basado en la deuda
                            const badgeClass = debt.includes('0.00') ? 'success' : 'danger';

                            // Crear un elemento HTML con formato mejorado
                            const $container = $(
                                `<div class="d-flex justify-content-between align-items-center" style="width: 100%; padding: 2px 0;">
                        <div style="flex: 1;">
                            <strong style="color: #2d3748; font-size: 0.95rem;">${name}</strong>
                        </div>
                        <div style="flex-shrink: 0; margin-left: 1rem;">
                            <span class="badge badge-${badgeClass}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 12px; font-weight: 600;">${debt}</span>
                        </div>
                    </div>`
                            );

                            return $container;
                        }

                        // Función para formatear la opción seleccionada
                        function formatCustomerSelection(customer) {
                            if (!customer.id) {
                                return customer.text;
                            }
                            return customer.text;
                        }

                        // Inicializar DataTable
                        $('#productsTable').DataTable({
                            responsive: true,
                            autoWidth: false,
                            language: window.DataTablesSpanishConfig,
                            columnDefs: [{
                                    responsivePriority: 1,
                                    targets: [0, 1, 3] // Código, Acción y Nombre siempre visibles
                                },
                                {
                                    responsivePriority: 2,
                                    targets: [5, 6] // Stock y Precio siguiente prioridad
                                },
                                {
                                    responsivePriority: 3,
                                    targets: '_all' // El resto menos prioritario
                                }
                            ],
                            initComplete: function() {
                                // Reagregar event listeners después de que DataTable esté listo
                                $('.select-product').off('click').on('click', function() {
                                    const code = $(this).data('code');
                                    const productId = $(this).data('id');

                                    $.ajax({
                                        url: `/sales/product-details/${code}`,
                                        method: 'GET',
                                        success: function(response) {
                                            if (response.success) {
                                                // Asegurar que el producto tenga el ID correcto
                                                response.product.id = productId;
                                                addProductToTable(response.product);
                                                
                                                // Cerrar el modal - ahora está en el mismo componente Alpine.js
                                                const parentComponent = document.querySelector('[x-data="saleForm()"]');
                                                if (parentComponent && parentComponent._x_dataStack) {
                                                    const alpineData = parentComponent._x_dataStack[0];
                                                    if (alpineData && typeof alpineData.searchModalOpen !== 'undefined') {
                                                        alpineData.searchModalOpen = false;
                                                    }
                                                }
                                            } else {
                                                Swal.fire('Error', response.message, 'error');
                                            }
                                        },
                                        error: function() {
                                            Swal.fire('Error', 'Error al obtener detalles del producto', 'error');
                                        }
                                    });
                                });
                            }
                        });

                        // Verificar si solo hay un producto disponible y agregarlo automáticamente
                        // Hacerlo después de que DataTable esté inicializado
                        setTimeout(function() {
                            checkAndAddSingleProduct();
                        }, 100);

                        // Función para agregar producto a la tabla
                        // showAlert = true: muestra alerta cuando el usuario agrega manualmente
                        // showAlert = false: no muestra alerta cuando se agrega automáticamente
                        function addProductToTable(product, showAlert = true) {
                            // Verificar si el producto ya está en la tabla
                            const existingRow = $(
                                `#saleItems tr[data-product-id="${product.id}"]`);

                            if (existingRow.length > 0) {
                                // Si el producto ya existe, incrementar la cantidad
                                const quantityInput = existingRow.find('.quantity-input');
                                const currentQuantity = parseInt(quantityInput.val()) || 0;
                                const newQuantity = currentQuantity + 1;

                                // Verificar stock
                                const maxStock = parseInt(quantityInput.attr('max'));
                                if (newQuantity > maxStock) {
                                    if (showAlert) {
                                        Swal.fire({
                                            icon: 'warning',
                                            title: 'Stock insuficiente',
                                            text: `Solo hay ${maxStock} unidades disponibles`,
                                            toast: true,
                                            position: 'top-end',
                                            showConfirmButton: false,
                                            timer: 3000
                                        });
                                    }
                                    return;
                                }

                                quantityInput.val(newQuantity).trigger('input');
                            } else {
                                // Si es un producto nuevo, agregar una nueva fila
                                // Asegurar que tenemos una imagen válida
                                let imageUrl = product.image;
                                if (!imageUrl || imageUrl === '') {
                                    imageUrl = '/img/no-image.png';
                                } else if (!imageUrl.startsWith('http') && !imageUrl
                                    .startsWith('/')) {
                                    imageUrl = '/' + imageUrl;
                                }

                                // Asegurar que el stock sea un número válido
                                const stockValue = parseInt(product.stock) || 0;
                                
                                // Asegurar que el precio sea un número válido
                                const priceValue = parseFloat(product.sale_price) || 0;

                                const row = `
                        <tr data-product-id="${product.id}" data-product-code="${product.code}" class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${product.code}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">${product.name}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold ${stockValue > 10 ? 'bg-green-100 text-green-800' : (stockValue > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')}">
                                    ${stockValue}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <input type="number" class="quantity-input" 
                                       value="1" min="1" max="${stockValue}" step="1">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900">
                                {{ $currency->symbol }} ${priceValue.toFixed(2)}
                                <input type="hidden" class="price-input" value="${priceValue}">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900">
                                <span class="subtotal-display">{{ $currency->symbol }} ${priceValue.toFixed(2)}</span>
                                <span class="subtotal-value hidden">${priceValue}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <button type="button" class="btn-action-remove remove-item">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            </td>
                        </tr>
                    `;

                                $('#saleItems').append(row);
                                updateTotal();
                                updateEmptyState();
                            }

                            // Mostrar notificación solo si showAlert es true
                            if (showAlert) {
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Producto agregado!',
                                    text: `${product.name} se agregó a la lista de venta`,
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000,
                                    timerProgressBar: true,
                                    background: '#e8f5e8',
                                    color: '#2e7d32'
                                });
                            }
                        }

                        // Función para actualizar el estado vacío
                        function updateEmptyState() {
                            const hasProducts = $('#saleItems tr').length > 0;

                            if (hasProducts) {
                                $('#emptyState').addClass('hidden');
                                $('.modern-table').removeClass('hidden');
                            } else {
                                $('#emptyState').removeClass('hidden');
                                $('.modern-table').addClass('hidden');
                            }
                        }

                        // Función para actualizar contadores
                        function updateCounters() {
                            const productCount = $('#saleItems tr').length;
                            $('.products-count').text(
                                `${productCount} producto${productCount !== 1 ? 's' : ''}`
                            );
                        }

                        // Buscar producto por código
                        $('#product_code').on('keypress', function(e) {
                            if (e.which == 13) { // Enter key
                                e.preventDefault();
                                const code = $(this).val();
                                if (code) {
                                    $.ajax({
                                        url: `/sales/product-by-code/${code}`,
                                        method: 'GET',
                                        success: function(response) {
                                            if (response.success) {
                                                addProductToTable(
                                                    response.product
                                                );
                                                $('#product_code').val(
                                                    '').focus();
                                            } else {
                                                Swal.fire('Error',
                                                    'Producto no encontrado',
                                                    'error');
                                            }
                                        },
                                        error: function() {
                                            Swal.fire('Error',
                                                'Error al buscar el producto',
                                                'error');
                                        }
                                    });
                                }
                            }
                        });



                        // Actualizar subtotal cuando cambie cantidad
                        $(document).on('input', '.quantity-input', function() {
                            const row = $(this).closest('tr');
                            const quantity = parseFloat($(this).val()) || 0;
                            const price = parseFloat(row.find('.price-input')
                                .val()) || 0;
                            const stock = parseInt($(this).attr('max'));

                            // Validar que la cantidad no exceda el stock
                            if (quantity > stock) {
                                $(this).val(stock);
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Stock insuficiente',
                                    text: `Solo hay ${stock} unidades disponibles`,
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                                return;
                            }

                            const subtotal = quantity * price;
                            row.find('.subtotal-value').text(subtotal.toFixed(2));
                            row.find('.subtotal-display').text(
                                '{{ $currency->symbol }} ' + subtotal.toFixed(
                                    2));
                            updateTotal();
                        });

                        // Eliminar producto de la tabla
                        $(document).on('click', '.remove-item', function() {
                            const row = $(this).closest('tr');
                            Swal.fire({
                                title: '¿Eliminar producto?',
                                text: "¿Está seguro de eliminar este producto de la venta?",
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
                                confirmButtonText: 'Sí, eliminar',
                                cancelButtonText: 'Cancelar'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    row.remove();
                                    updateTotal();
                                    updateEmptyState();
                                    updateCounters();
                                }
                            });
                        });

                        // Actualizar total general
                        function updateTotal() {
                            let total = 0;
                            $('.subtotal-value').each(function() {
                                total += parseFloat($(this).text()) || 0;
                            });
                            $('#totalAmount').text('{{ $currency->symbol }} ' + total
                                .toFixed(2));
                            $('#totalAmountInput').val(total.toFixed(2));
                            $('.total-amount-display').text('{{ $currency->symbol }} ' +
                                total.toFixed(2));
                            updateCounters();
                        }

                        // Limpiar formulario
                        $('#clearForm').click(function() {
                            Swal.fire({
                                title: '¿Limpiar formulario?',
                                text: "Se eliminarán todos los productos agregados",
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
                                confirmButtonText: 'Sí, limpiar',
                                cancelButtonText: 'Cancelar'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $('#saleItems').empty();
                                    $('#customer_id').val('').trigger(
                                        'change');
                                    // Resetear fecha y hora a valores actuales
                                    $('#sale_date').val(
                                        '{{ date('Y-m-d') }}');
                                    $('#sale_time').val(
                                        '{{ date('H:i') }}');
                                    updateTotal();
                                    updateEmptyState();
                                }
                            });
                        });

                        // Manejar envío del formulario
                        $('form').on('submit', function(e) {
                            e.preventDefault();

                            // Deshabilitar ambos botones para prevenir múltiples envíos
                            $('#submitSale, #submitSaleAndNew').prop('disabled',
                                true);

                            // Verificar si hay productos en la tabla
                            if ($('#saleItems tr').length === 0) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Debe agregar al menos un producto a la venta'
                                });
                                // Rehabilitar botones si hay error
                                $('#submitSale, #submitSaleAndNew').prop('disabled',
                                    false);
                                return false;
                            }

                            // Verificar si se seleccionó un cliente
                            if (!$('#customer_id').val()) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Debe seleccionar un cliente'
                                });
                                // Rehabilitar botones si hay error
                                $('#submitSale, #submitSaleAndNew').prop('disabled',
                                    false);
                                return false;
                            }

                            // Verificar si se seleccionó el estado de pago
                            if (!$('#already_paid').val()) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Debe seleccionar si el cliente ya pagó o no'
                                });
                                // Rehabilitar botones si hay error
                                $('#submitSale, #submitSaleAndNew').prop('disabled',
                                    false);
                                return false;
                            }

                            // Preparar los datos de los productos
                            const items = [];
                            $('#saleItems tr').each(function() {
                                const row = $(this);
                                const productId = row.data('product-id');

                                items.push({
                                    product_id: productId,
                                    quantity: parseFloat(row.find(
                                            '.quantity-input')
                                        .val()),
                                    price: parseFloat(row.find(
                                            '.price-input')
                                        .val()),
                                    subtotal: parseFloat(row.find(
                                            '.subtotal-value')
                                        .text())
                                });
                            });


                            // Crear campos ocultos para los items
                            $('#itemsContainer')
                                .remove(); // Eliminar contenedor previo si existe
                            const container = $('<div id="itemsContainer"></div>');

                            // Agregar cada item como un campo oculto
                            items.forEach((item, index) => {
                                container.append(
                                    `<input type="hidden" name="items[${index}][product_id]" value="${item.product_id}">`
                                );
                                container.append(
                                    `<input type="hidden" name="items[${index}][quantity]" value="${item.quantity}">`
                                );
                                container.append(
                                    `<input type="hidden" name="items[${index}][price]" value="${item.price}">`
                                );
                                container.append(
                                    `<input type="hidden" name="items[${index}][subtotal]" value="${item.subtotal}">`
                                );
                            });

                            // Agregar el contenedor al formulario
                            $(this).append(container);

                            // Remover el event listener temporalmente para evitar bucles
                            $(this).off('submit');

                            // Enviar el formulario
                            $(this).submit();
                        });

                        // Manejar clics en botones específicos para capturar la acción
                        $('button[type="submit"]').on('click', function(e) {
                            // Remover cualquier input de action previo
                            $('input[name="action"]').remove();

                            // Agregar el valor de action correspondiente al botón presionado
                            const actionValue = $(this).val();
                            $(this).closest('form').append(
                                `<input type="hidden" name="action" value="${actionValue}">`
                            );
                        });

                        // Manejar el botón de cancelar venta - retroceder a la vista anterior
                        $('#cancelSale').click(function() {
                            Swal.fire({
                                title: '¿Está seguro?',
                                text: "Se perderán todos los datos ingresados en esta venta",
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
                                confirmButtonText: 'Sí, cancelar venta',
                                cancelButtonText: 'No, continuar editando'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    // Retroceder a la página anterior
                                    goBack();
                                }
                            });
                        });

                        // Función para navegar de vuelta a la vista original
                        function goBack() {
                            // Verificar si hay una URL de referencia guardada en sessionStorage
                            const originalReferrer = sessionStorage.getItem(
                                'sales_original_referrer');

                            if (originalReferrer && originalReferrer !== window.location
                                .href) {
                                // Si tenemos una URL original guardada, ir allí
                                window.location.href = originalReferrer;
                            } else {
                                // Comportamiento normal del botón volver
                                window.history.back();
                            }
                        }

                        // Event listener para el botón volver
                        $('button[onclick="window.history.back()"]').removeAttr('onclick')
                            .click(function() {
                                goBack();
                            });

                        // Inicializar estado vacío
                        updateEmptyState();
                        updateCounters();

                        // Manejar cambio en el campo "¿Ya pagó?"
                        $('#already_paid').on('change', function() {
                            const alreadyPaid = $(this).val() === '1';
                            const customerId = $('#customer_id').val();

                            if (customerId && alreadyPaid) {
                                // Mostrar información sobre el pago automático
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Pago Automático',
                                    text: 'Al seleccionar "Sí", se registrará automáticamente el pago de esta venta y no se incrementará la deuda del cliente.',
                                    confirmButtonText: 'Entendido',
                                    confirmButtonColor: '#667eea'
                                });
                            }
                        });



                        // Detectar scroll horizontal en la tabla
                        function checkTableScroll() {
                            const tableWrapper = $('.table-wrapper');
                            const table = tableWrapper.find('.modern-table');

                            if (table.width() > tableWrapper.width()) {
                                tableWrapper.addClass('has-scroll');
                            } else {
                                tableWrapper.removeClass('has-scroll');
                            }
                        }

                        // Verificar scroll al cargar y al cambiar el tamaño de la ventana
                        $(window).on('load resize', function() {
                            setTimeout(checkTableScroll, 100);
                        });

                        // Verificar scroll cuando se agregan productos (usando MutationObserver en lugar de DOMNodeInserted)
                        const saleItemsContainer = document.getElementById('saleItems');
                        if (saleItemsContainer) {
                            const observer = new MutationObserver(function(mutations) {
                                mutations.forEach(function(mutation) {
                                    if (mutation.type === 'childList') {
                                        setTimeout(checkTableScroll, 100);
                                    }
                                });
                            });

                            observer.observe(saleItemsContainer, {
                                childList: true,
                                subtree: true
                            });
                        }

                    });
                });
            });
        });

        // Función de Alpine.js para el formulario de ventas
        function saleForm() {
            return {
                loading: false,
                productCount: 0,
                totalAmount: 0.00,
                searchModalOpen: false,

                init() {
                    // Inicialización si es necesaria
                    this.updateCounters();
                },

                updateCounters() {
                    this.productCount = document.querySelectorAll('#saleItems tr').length;
                    this.updateTotal();
                },

                updateTotal() {
                    let total = 0;
                    document.querySelectorAll('.subtotal-value').forEach(element => {
                        total += parseFloat(element.textContent) || 0;
                    });
                    this.totalAmount = total;
                },

                setLoading(state) {
                    this.loading = state;
                }
            }
        }
    </script>
@endpush
