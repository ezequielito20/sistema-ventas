@extends('layouts.app')

@section('title', 'Nueva Venta')

@section('content')
    <div class="space-y-6" x-data="saleCreateSPA()" x-init="init()">

        <!-- Header -->
        <div class="bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 mb-6 relative overflow-hidden rounded-2xl">
            <!-- Elementos decorativos de fondo -->
            <div class="absolute inset-0 bg-black/10"></div>
            <div class="absolute -top-4 -right-4 w-32 h-32 bg-white/10 rounded-full"></div>
            <div class="absolute top-1/2 -left-8 w-24 h-24 bg-white/5 rounded-full"></div>

            <div class="relative z-10 px-6 py-8">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center space-x-4 mb-4 lg:mb-0">
                        <div
                            class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-sm border border-white/20">
                            <i class="fas fa-shopping-cart text-2xl text-white"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-white mb-1">Nueva Venta</h1>
                            <p class="text-white text-opacity-90 text-lg">Registre una nueva transacción de venta</p>
                        </div>
                    </div>
                    <div>
                        <button onclick="window.history.back()"
                            class="bg-white/20 hover:bg-white/30 text-white px-6 py-3 rounded-xl font-medium transition-all duration-300 backdrop-blur-sm border border-white/20 hover:scale-105 transform">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Volver
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full space-y-8">
            <form @submit.prevent="processSale" id="saleForm">
                @csrf

                <!-- Sección de Información Básica -->
                <div class="mb-6 bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
                    <!-- Header de la sección -->
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-5">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center">
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
                                    <input type="text" x-model="productCode"
                                        @input.debounce.300ms="searchProductByCode()"
                                        @keydown.enter.prevent="addProductByCode()"
                                        class="w-full pl-3 pr-20 py-2.5 bg-gray-50 border-2 border-gray-200 rounded-xl focus:border-indigo-500 focus:bg-white transition-all duration-300 text-gray-800 placeholder-gray-400 text-sm"
                                        placeholder="Código del producto">

                                    <!-- Autocompletado de códigos -->
                                    <div x-show="codeSuggestions.length > 0" x-transition
                                        class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                                        <template x-for="suggestion in codeSuggestions" :key="suggestion.code">
                                            <div @click="selectCodeSuggestion(suggestion)"
                                                class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm border-b border-gray-100 last:border-b-0">
                                                <div class="font-medium" x-text="suggestion.code"></div>
                                                <div class="text-gray-600 text-xs" x-text="suggestion.name"></div>
                                            </div>
                                        </template>
                                    </div>

                                    <div class="absolute right-1 top-1 flex space-x-1">
                                        <button type="button"
                                            class="w-8 h-8 bg-blue-500 hover:bg-blue-600 text-white rounded-lg flex items-center justify-center transition-all duration-300"
                                            @click="searchModalOpen = true">
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
                                    <div class="relative">
                                        <select x-model="selectedCustomerId" @change="onCustomerChange()"
                                            class="w-full pl-3 pr-10 py-2.5 bg-gray-50 border-2 border-gray-200 rounded-xl focus:border-gray-500 focus:bg-white transition-all duration-300 text-gray-700 text-sm h-11 appearance-none cursor-pointer"
                                            required>
                                            <option value="">Seleccione un cliente</option>
                                            @foreach ($customers as $customer)
                                                <option value="{{ $customer->id }}" data-debt="{{ $customer->total_debt }}"
                                                    {{ isset($selectedCustomerId) && $selectedCustomerId == $customer->id ? 'selected' : '' }}>
                                                    {{ $customer->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="add-customer-button">
                                        <a href="{{ route('admin.customers.create') }}?return_to=sales.create"
                                            class="w-8 h-8 bg-green-500 hover:bg-green-600 text-white rounded-lg flex items-center justify-center transition-all duration-300 shadow-lg hover:shadow-xl">
                                            <i class="fas fa-plus text-xs"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Fecha de Venta -->
                            <div>
                                <label for="sale_date" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-calendar text-indigo-500 mr-1"></i>
                                    Fecha <span class="text-red-500">*</span>
                                </label>
                                <input type="date" x-model="saleDate"
                                    class="w-full px-3 py-2.5 bg-gray-50 border-2 border-gray-200 rounded-xl focus:border-indigo-500 focus:bg-white transition-all duration-300 text-gray-800 text-sm"
                                    required>
                            </div>

                            <!-- Hora de Venta -->
                            <div>
                                <label for="sale_time" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-clock text-indigo-500 mr-1"></i>
                                    Hora <span class="text-red-500">*</span>
                                </label>
                                <input type="time" x-model="saleTime"
                                    class="w-full px-3 py-2.5 bg-gray-50 border-2 border-gray-200 rounded-xl focus:border-indigo-500 focus:bg-white transition-all duration-300 text-gray-800 text-sm"
                                    required>
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
                                <select x-model="alreadyPaid"
                                    class="w-full px-3 py-2.5 bg-gray-50 border-2 border-gray-200 rounded-xl focus:border-indigo-500 focus:bg-white transition-all duration-300 text-gray-800 text-sm h-11">
                                    <option value="0">No</option>
                                    <option value="1">Sí</option>
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
                                <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center">
                                    <i class="fas fa-shopping-bag text-white text-xl"></i>
                                </div>
                                <h3 class="text-2xl font-bold text-white">Productos en la Venta</h3>
                            </div>

                            <!-- Contadores en el header -->
                            <div class="flex items-center space-x-4">
                                <div class="bg-white/20 px-4 py-2 rounded-xl flex items-center space-x-2">
                                    <i class="fas fa-boxes text-white"></i>
                                    <span class="text-white font-semibold"
                                        x-text="`${saleItems.length} productos`"></span>
                                </div>
                                <div class="bg-white/20 px-4 py-2 rounded-xl flex items-center space-x-2">
                                    <i class="fas fa-calculator text-white"></i>
                                    <span class="text-white font-bold"
                                        x-text="`{{ $currency->symbol }} ${totalAmount.toFixed(2)}`"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <!-- Tabla de productos -->
                        <div class="relative">
                            <!-- Tabla normal cuando hay productos -->
                            <template x-if="saleItems.length > 0">
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
                                        <tbody class="divide-y divide-gray-200">
                                            <template x-for="(item, index) in saleItems" :key="item.id">
                                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                                                        x-text="item.code"></td>
                                                    <td class="px-6 py-4 text-sm text-gray-900" x-text="item.name"></td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                                        <span
                                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                                                            :class="item.stock > 10 ? 'bg-green-100 text-green-800' : (item
                                                                .stock > 0 ? 'bg-yellow-100 text-yellow-800' :
                                                                'bg-red-100 text-red-800')"
                                                            x-text="item.stock"></span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                                        <div class="flex items-center justify-center space-x-2">
                                                            <button type="button" @click="decreaseQuantity(index)"
                                                                class="w-8 h-8 bg-red-500 hover:bg-red-600 text-white rounded-lg flex items-center justify-center transition-all duration-300"
                                                                :disabled="item.quantity <= 1">
                                                                <i class="fas fa-minus text-xs"></i>
                                                            </button>
                                                            <input type="number" x-model.number="item.quantity"
                                                                @input="updateItemSubtotal(index)" min="1"
                                                                :max="item.stock"
                                                                class="w-16 text-center border border-gray-300 rounded-lg px-2 py-1 text-sm">
                                                            <button type="button" @click="increaseQuantity(index)"
                                                                class="w-8 h-8 bg-green-500 hover:bg-green-600 text-white rounded-lg flex items-center justify-center transition-all duration-300"
                                                                :disabled="item.quantity >= item.stock">
                                                                <i class="fas fa-plus text-xs"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900"
                                                        x-text="`{{ $currency->symbol }} ${item.price.toFixed(2)}`"></td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900"
                                                        x-text="`{{ $currency->symbol }} ${item.subtotal.toFixed(2)}`">
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                                        <button type="button" @click="removeItem(index)"
                                                            class="w-8 h-8 bg-red-500 hover:bg-red-600 text-white rounded-lg flex items-center justify-center transition-all duration-300">
                                                            <i class="fas fa-trash text-xs"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </template>

                            <!-- Estado vacío cuando no hay productos -->
                            <template x-if="saleItems.length === 0">
                                <div class="empty-state bg-gray-50 rounded-2xl border-2 border-gray-100">
                                    <div class="text-center py-16">
                                        <div
                                            class="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center">
                                            <i class="fas fa-shopping-cart text-4xl text-gray-400"></i>
                                        </div>
                                        <h4 class="text-xl font-semibold text-gray-600 mb-2">No hay productos agregados
                                        </h4>
                                        <p class="text-gray-500">Agregue productos escaneando códigos o usando el buscador
                                        </p>
                                        <!-- Debug info -->
                                        <p class="text-xs text-gray-400 mt-2"
                                            x-text="`Debug: ${saleItems.length} productos`"></p>
                                    </div>
                                </div>
                            </template>
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
                                        <textarea x-model="saleNote" rows="2"
                                            class="w-full px-2 py-1 bg-white border-2 border-blue-200 rounded-xl focus:border-blue-500 transition-all duration-300 text-gray-800 placeholder-gray-400 resize-none text-sm"
                                            placeholder="Agregue una nota adicional para esta venta (opcional)"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Total de la venta -->
                            <div class="bg-gradient-to-br from-green-500 to-teal-600 rounded-2xl p-3 text-white">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                                            <i class="fas fa-receipt text-lg"></i>
                                        </div>
                                        <div>
                                            <p class="text-emerald-100 text-sm mb-0.5">Total de la Venta</p>
                                            <p class="text-xl font-bold"
                                                x-text="`{{ $currency->symbol }} ${totalAmount.toFixed(2)}`"></p>
                                        </div>
                                    </div>

                                    <!-- Botones de acción -->
                                    <div class="flex items-center space-x-2">
                                        <!-- Botón Cancelar -->
                                        <button type="button" @click="cancelSale()"
                                            class="group relative w-10 h-10 bg-red-500 hover:bg-red-600 text-white rounded-xl transition-all duration-300 hover:scale-105 flex items-center justify-center shadow-lg">
                                            <i
                                                class="fas fa-times text-sm group-hover:scale-110 transition-transform duration-300"></i>
                                            <div
                                                class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">
                                                Cancelar
                                            </div>
                                        </button>

                                        <!-- Botón Procesar Venta -->
                                        <button type="submit" @click="processSale('save')" :disabled="!canProcessSale"
                                            class="group relative w-12 h-12 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white rounded-xl transition-all duration-300 hover:scale-105 flex items-center justify-center shadow-lg">
                                            <i
                                                class="fas fa-save text-sm group-hover:scale-110 transition-transform duration-300"></i>
                                            <div
                                                class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">
                                                Procesar
                                            </div>
                                        </button>

                                        <!-- Botón Procesar y Nueva Venta -->
                                        <button type="submit" @click="processSale('save_and_new')"
                                            :disabled="!canProcessSale"
                                            class="group relative w-10 h-10 bg-amber-500 hover:bg-amber-600 disabled:bg-gray-400 disabled:cursor-not-allowed text-white rounded-xl transition-all duration-300 hover:scale-105 flex items-center justify-center shadow-lg">
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
            <div x-show="searchModalOpen" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">

                <!-- Overlay de fondo -->
                <div class="fixed inset-0 bg-black/50 transition-opacity" @click="searchModalOpen = false"></div>

                <!-- Contenido del modal -->
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="relative bg-white rounded-3xl shadow-2xl max-w-7xl w-full max-h-[90vh] overflow-hidden"
                        @click.stop>

                        <!-- Header del Modal -->
                        <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-8 py-6 relative overflow-hidden">
                            <div class="absolute inset-0 bg-black/10"></div>
                            <div class="absolute -top-4 -right-4 w-32 h-32 bg-white/10 rounded-full"></div>
                            <div class="relative z-10 flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div
                                        class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-sm">
                                        <i class="fas fa-search text-white text-xl"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-2xl font-bold text-white mb-1">Búsqueda de Productos</h4>
                                        <p class="text-white text-opacity-90">Seleccione productos para agregar a la venta
                                        </p>
                                    </div>
                                </div>
                                <button type="button"
                                    class="w-10 h-10 bg-white/20 hover:bg-white/30 text-white rounded-xl flex items-center justify-center transition-all duration-300 backdrop-blur-sm"
                                    @click="searchModalOpen = false">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Filtros de búsqueda -->
                        <div class="bg-gray-50 px-8 py-4 border-b border-gray-200">
                            <div class="flex items-center justify-center">
                                <div class="w-full max-w-2xl relative">
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-search text-gray-400"></i>
                                        </div>
                                        <input type="text" x-model="productSearchTerm"
                                            @input.debounce.300ms="filterProducts()"
                                            placeholder="Buscar por código, nombre o categoría..."
                                            class="w-full pl-10 pr-12 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-300 modal-search-input">
                                        <button type="button" x-show="productSearchTerm.length > 0"
                                            @click="productSearchTerm = ''; filterProducts()"
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors modal-search-clear-btn">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cuerpo del Modal -->
                        <div class="p-8 bg-gray-50 max-h-[calc(90vh-300px)] overflow-y-auto">
                            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                                <div class="overflow-x-auto">
                                    <table class="w-full">
                                        <thead class="bg-gradient-to-r from-gray-700 to-gray-800">
                                            <tr>
                                                <th
                                                    class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">
                                                    <i class="fas fa-barcode mr-2"></i>Código
                                                </th>
                                                <th
                                                    class="px-6 py-4 text-center text-xs font-semibold text-white uppercase tracking-wider">
                                                    <i class="fas fa-plus-circle mr-2"></i>Acción
                                                </th>
                                                <th
                                                    class="px-6 py-4 text-center text-xs font-semibold text-white uppercase tracking-wider">
                                                    <i class="fas fa-image mr-2"></i>Imagen
                                                </th>
                                                <th
                                                    class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">
                                                    <i class="fas fa-box mr-2"></i>Nombre
                                                </th>
                                                <th
                                                    class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">
                                                    <i class="fas fa-tags mr-2"></i>Categoría
                                                </th>
                                                <th
                                                    class="px-6 py-4 text-center text-xs font-semibold text-white uppercase tracking-wider">
                                                    <i class="fas fa-warehouse mr-2"></i>Stock
                                                </th>
                                                <th
                                                    class="px-6 py-4 text-right text-xs font-semibold text-white uppercase tracking-wider">
                                                    <i class="fas fa-dollar-sign mr-2"></i>Precio
                                                </th>
                                                <th
                                                    class="px-6 py-4 text-center text-xs font-semibold text-white uppercase tracking-wider">
                                                    <i class="fas fa-info-circle mr-2"></i>Estado
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            <template x-for="product in filteredProducts" :key="product.id">
                                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                                                        x-text="product.code"></td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                                        <button type="button" @click="addProductToSale(product)"
                                                            class="w-10 h-10 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white rounded-xl flex items-center justify-center transition-all duration-300 hover:scale-105"
                                                            :class="product.stock <= 0 || isProductInSale(product.id) ?
                                                                'opacity-50 cursor-not-allowed' : ''"
                                                            :disabled="product.stock <= 0 || isProductInSale(product.id)">
                                                            <i class="fas fa-plus text-sm"></i>
                                                        </button>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                                        <div class="product-avatar">
                                                            <img :src="getProductImageUrl(product)" :alt="product.name"
                                                                class="w-12 h-12 rounded-xl object-cover border-2 border-gray-200"
                                                                @error="$el.src = '/img/no-image.svg'">
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 text-sm font-medium text-gray-900"
                                                        x-text="product.name"></td>
                                                    <td class="px-6 py-4 text-sm text-gray-600"
                                                        x-text="product.category?.name || 'Sin categoría'"></td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                                        <span
                                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                                                            :class="product.stock > 10 ? 'bg-green-100 text-green-800' : (
                                                                product.stock > 0 ?
                                                                'bg-yellow-100 text-yellow-800' :
                                                                'bg-red-100 text-red-800')"
                                                            x-text="product.stock"></span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900"
                                                        x-text="`{{ $currency->symbol }} ${parseFloat(product.sale_price || 0).toFixed(2)}`">
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                                        <span
                                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                                                            :class="product.stock_status_label === 'Bajo' ?
                                                                'bg-red-100 text-red-800' : (product
                                                                    .stock_status_label === 'Normal' ?
                                                                    'bg-yellow-100 text-yellow-800' :
                                                                    'bg-green-100 text-green-800')"
                                                            x-text="product.stock_status_label"></span>
                                                    </td>
                                                </tr>
                                            </template>
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

    <!-- Script con datos iniciales -->

@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('vendor/sweetalert2/sweetalert2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/sales/create.css') }}">
@endpush

@push('js')
    <script>
        window.saleCreateData = {
            products: @json($products),
            customers: @json($customers),
            currency: @json($currency),
            selectedCustomerId: @json($selectedCustomerId ?? null)
        };
    </script>
    <script>
        window.saleCreateRoutes = {
            store: "{{ route('admin.sales.store') }}",
            index: "{{ route('admin.sales.index') }}"
        };
    </script>
    <script src="{{ asset('vendor/config.js') }}"></script>
    <script src="{{ asset('vendor/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('js/admin/sales/create.js') }}" defer></script>
@endpush