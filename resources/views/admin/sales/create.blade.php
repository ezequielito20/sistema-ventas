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
                <div class="mb-6 bg-white rounded-3xl shadow-xl border border-gray-100">
                    <!-- Header de la sección -->
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center">
                                    <i class="fas fa-info-circle text-white text-xl"></i>
                                </div>
                                <h3 class="text-2xl font-bold text-white">Información de la Venta</h3>
                            </div>
                            <div>
                                <button type="button" @click="bulkSalesModalOpen = true"
                                    class="bg-white/20 hover:bg-white/30 text-white px-6 py-3 rounded-xl font-medium transition-all duration-300 backdrop-blur-sm border border-white/20 hover:scale-105 transform">
                                    <i class="fas fa-file-upload mr-2"></i>
                                    Cargar Ventas Masivas
                                </button>
                            </div>
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
                                    <div x-show="codeSuggestions.length > 0" x-cloak x-transition
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
                                <div class="flex space-x-2">
                                    <div class="relative flex-1" x-data="{
                                        isOpen: false,
                                        searchTerm: '',
                                        filteredCustomers: @js($customers),
                                        selectedCustomerName: 'Seleccione un cliente',
                                        selectedCustomerDebt: 0,
                                        init() {
                                            // Asegurar que el dropdown esté cerrado al inicializar
                                            this.isOpen = false;
                                    
                                            // Auto-seleccionar cliente si viene en la URL
                                            const urlParams = new URLSearchParams(window.location.search);
                                            const customerId = urlParams.get('customer_id');
                                    
                                            if (customerId && window.saleCreateData && window.saleCreateData.customers) {
                                                const customerIdNum = parseInt(customerId);
                                                const customer = window.saleCreateData.customers.find(c => c.id === customerIdNum);
                                    
                                                if (customer) {
                                                    this.selectedCustomerName = customer.name;
                                                    this.selectedCustomerDebt = parseFloat(customer.total_debt || 0);
                                    
                                                    // Actualizar el selectedCustomerId en el componente padre
                                                    if (typeof window.saleCreateData !== 'undefined' && window.saleCreateData.selectedCustomerId !== undefined) {
                                                        window.saleCreateData.selectedCustomerId = customer.id;
                                                    }
                                    
                                                }
                                            }
                                        },
                                        filterCustomers() {
                                            if (!this.searchTerm) {
                                                this.filteredCustomers = @js($customers);
                                                return;
                                            }
                                            const term = this.searchTerm.toLowerCase();
                                            this.filteredCustomers = @js($customers).filter(customer =>
                                                customer.name.toLowerCase().includes(term)
                                            );
                                        },
                                        selectCustomer(customer) {
                                            // Actualizar la variable global del componente padre
                                            if (typeof window.saleCreateData !== 'undefined') {
                                                window.saleCreateData.selectedCustomerId = customer.id;
                                            }
                                    
                                            this.selectedCustomerName = customer.name;
                                            this.selectedCustomerDebt = parseFloat(customer.total_debt || 0);
                                            this.isOpen = false;
                                            this.searchTerm = '';
                                    
                                            // Llamar a la función del componente padre si existe
                                            if (typeof onCustomerChange === 'function') {
                                                onCustomerChange();
                                            }
                                        }
                                    }" @click.away="isOpen = false"
                                        x-init="init()">

                                        <!-- Select Button -->
                                        <button type="button"
                                            @click="isOpen = !isOpen; if (isOpen) { $nextTick(() => $refs.customerSearch.focus()) }"
                                            class="relative w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-3 py-2.5 pr-10 text-left cursor-pointer focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-300 hover:bg-white hover:border-gray-300 h-11">
                                            <div class="flex items-center justify-between min-w-0">
                                                <span class="block truncate text-gray-700 text-sm flex-1"
                                                    x-text="selectedCustomerName"></span>
                                                <div class="ml-2 flex-shrink-0"
                                                    x-show="selectedCustomerName !== 'Seleccione un cliente'">
                                                    <!-- Badge de deuda (rojo) -->
                                                    <span x-show="selectedCustomerDebt > 0"
                                                        x-text="'$' + selectedCustomerDebt.toFixed(2)"
                                                        class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-red-100 text-red-800 border border-red-200 whitespace-nowrap">
                                                    </span>
                                                    <!-- Badge sin deuda (verde) -->
                                                    <span x-show="selectedCustomerDebt === 0"
                                                        class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-green-100 text-green-800 border border-green-200 whitespace-nowrap">
                                                        Sin deuda
                                                    </span>
                                                </div>
                                            </div>
                                            <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                                                <svg class="h-5 w-5 text-gray-400 transition-transform duration-200"
                                                    :class="{ 'rotate-180': isOpen }" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            </span>
                                        </button>

                                        <!-- Dropdown -->
                                        <div x-show="isOpen" x-cloak x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 translate-y-1"
                                            x-transition:enter-end="opacity-1 translate-y-0"
                                            x-transition:leave="transition ease-in duration-150"
                                            x-transition:leave-start="opacity-1 translate-y-0"
                                            x-transition:leave-end="opacity-0 translate-y-1"
                                            class="absolute z-50 mt-1 w-full bg-white shadow-lg max-h-60 rounded-xl border border-gray-200 overflow-auto">

                                            <!-- Search Input -->
                                            <div class="px-3 py-2 border-b border-gray-100">
                                                <input type="text" x-ref="customerSearch" x-model="searchTerm"
                                                    @input="filterCustomers()" @keydown.escape="isOpen = false"
                                                    placeholder="Buscar cliente..."
                                                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                            </div>

                                            <!-- Options List -->
                                            <div class="max-h-48 overflow-y-auto">
                                                <template x-for="customer in filteredCustomers" :key="customer.id">
                                                    <div @click="selectCustomer(customer)"
                                                        class="cursor-pointer select-none relative py-2.5 pl-3 pr-3 hover:bg-gray-50 transition-colors duration-150">
                                                        <div class="flex items-center justify-between min-w-0">
                                                            <span
                                                                class="block text-sm text-gray-900 font-medium flex-1 min-w-0 truncate"
                                                                x-text="customer.name"></span>
                                                            <div class="ml-2 flex-shrink-0">
                                                                <!-- Badge de deuda (rojo) -->
                                                                <span x-show="parseFloat(customer.total_debt || 0) > 0"
                                                                    x-text="'$' + parseFloat(customer.total_debt || 0).toFixed(2)"
                                                                    class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-red-100 text-red-800 border border-red-200 whitespace-nowrap">
                                                                </span>
                                                                <!-- Badge sin deuda (verde) -->
                                                                <span x-show="parseFloat(customer.total_debt || 0) === 0"
                                                                    class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-green-100 text-green-800 border border-green-200 whitespace-nowrap">
                                                                    Sin deuda
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>

                                                <!-- No results -->
                                                <div x-show="filteredCustomers.length === 0"
                                                    class="px-3 py-4 text-sm text-gray-500 text-center">
                                                    No se encontraron clientes
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Add Customer Button -->
                                    <a href="{{ route('admin.customers.create') }}?return_to=sales.create"
                                        class="w-11 h-16 bg-green-500 hover:bg-green-600 text-white rounded-xl flex items-center justify-center transition-all duration-300 shadow-sm hover:shadow-md flex-shrink-0">
                                        <i class="fas fa-plus text-sm"></i>
                                    </a>
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

                                <!-- Select personalizado de pago -->
                                <div class="relative" x-data="{
                                    isOpen: false,
                                    selectedPaymentText: 'No',
                                    selectPayment(value, text) {
                                        if (value === '1') {
                                            Swal.fire({
                                                title: '¿Confirmar pago automático?',
                                                text: 'Al seleccionar Sí, se registrará automáticamente el pago de esta venta. ¿Está seguro?',
                                                icon: 'question',
                                                showCancelButton: true,
                                                confirmButtonColor: '#10b981',
                                                cancelButtonColor: '#6b7280',
                                                confirmButtonText: 'Sí, confirmar',
                                                cancelButtonText: 'Cancelar'
                                            }).then((result) => {
                                                if (result.isConfirmed) {
                                                    alreadyPaid = value;
                                                    this.selectedPaymentText = text;
                                                    this.isOpen = false;
                                
                                                    Swal.fire({
                                                        title: '¡Pago automático activado!',
                                                        text: 'El pago se registrará automáticamente al crear la venta.',
                                                        icon: 'success',
                                                        timer: 2000,
                                                        showConfirmButton: false
                                                    });
                                                }
                                            });
                                        } else {
                                            alreadyPaid = value;
                                            this.selectedPaymentText = text;
                                            this.isOpen = false;
                                        }
                                    }
                                }" @click.away="isOpen = false"
                                    x-init="init()">

                                    <!-- Select Button -->
                                    <button type="button" @click="isOpen = !isOpen"
                                        class="relative w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-3 py-2.5 pr-10 text-left cursor-pointer focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-300 hover:bg-white hover:border-gray-300 h-11">
                                        <span class="block truncate text-gray-700 text-sm"
                                            x-text="selectedPaymentText"></span>
                                        <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                                            <svg class="h-5 w-5 text-gray-400 transition-transform duration-200"
                                                :class="{ 'rotate-180': isOpen }" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </span>
                                    </button>

                                    <!-- Dropdown -->
                                    <div x-show="isOpen" x-cloak x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 translate-y-1"
                                        x-transition:enter-end="opacity-1 translate-y-0"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-1 translate-y-0"
                                        x-transition:leave-end="opacity-0 translate-y-1"
                                        class="absolute z-50 mt-1 w-full bg-white shadow-lg rounded-xl border border-gray-200 overflow-hidden">

                                        <!-- Options List -->
                                        <div @click="selectPayment('0', 'No')"
                                            class="cursor-pointer select-none relative py-3 px-3 hover:bg-gray-50 transition-colors duration-150">
                                            <div class="flex items-center">
                                                <i class="fas fa-times-circle text-red-500 mr-2"></i>
                                                <span class="block text-sm text-gray-900">No</span>
                                            </div>
                                        </div>

                                        <div @click="selectPayment('1', 'Sí')"
                                            class="cursor-pointer select-none relative py-3 px-3 hover:bg-gray-50 transition-colors duration-150 border-t border-gray-100">
                                            <div class="flex items-center">
                                                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                                <span class="block text-sm text-gray-900">Sí</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <p class="text-gray-500 text-xs mt-2 flex items-center">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Si selecciona "Sí", se registrará automáticamente el pago
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección de Productos -->
                <div class="bg-white rounded-3xl shadow-xl border border-gray-100">
                    <!-- Header de la sección -->
                    <div class="bg-gradient-to-r from-emerald-600 to-teal-600 px-4 sm:px-6 py-4">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                            <div class="flex items-center space-x-3">
                                <div
                                    class="w-10 h-10 sm:w-12 sm:h-12 bg-white/20 rounded-2xl flex items-center justify-center">
                                    <i class="fas fa-shopping-bag text-white text-lg sm:text-xl"></i>
                                </div>
                                <h3 class="text-xl sm:text-2xl font-bold text-white">Productos en la Venta</h3>
                            </div>

                            <!-- Contadores en el header -->
                            <div class="flex items-center space-x-2 sm:space-x-4">
                                <div
                                    class="bg-white/20 px-3 py-1.5 sm:px-4 sm:py-2 rounded-xl flex items-center space-x-1.5 sm:space-x-2">
                                    <i class="fas fa-boxes text-white text-sm"></i>
                                    <span class="text-white font-semibold text-sm sm:text-base"
                                        x-text="`${saleItems.length} productos`"></span>
                                </div>
                                <div
                                    class="bg-white/20 px-3 py-1.5 sm:px-4 sm:py-2 rounded-xl flex items-center space-x-1.5 sm:space-x-2">
                                    <i class="fas fa-calculator text-white text-sm"></i>
                                    <span class="text-white font-bold text-sm sm:text-base"
                                        x-text="`{{ $currency->symbol }} ${totalAmount.toFixed(2)}`"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 sm:p-6">
                        <!-- Tabla de productos -->
                        <div class="relative">
                            <!-- Tabla normal cuando hay productos -->
                            <template x-if="saleItems.length > 0">
                                <div class="overflow-x-auto bg-gray-50 rounded-2xl border-2 border-gray-100">
                                    <table class="w-full modern-table">
                                        <thead class="bg-gradient-to-r from-blue-600 to-indigo-600">
                                            <tr>
                                                <th
                                                    class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">
                                                    <i class="fas fa-barcode mr-1 sm:mr-2"></i><span
                                                        class="hidden sm:inline">Código</span>
                                                </th>
                                                <th
                                                    class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">
                                                    <i class="fas fa-box mr-1 sm:mr-2"></i><span
                                                        class="hidden sm:inline">Producto</span>
                                                </th>
                                                <th
                                                    class="px-3 sm:px-6 py-3 sm:py-4 text-center text-xs font-semibold text-white uppercase tracking-wider">
                                                    <i class="fas fa-warehouse mr-1 sm:mr-2"></i><span
                                                        class="hidden sm:inline">Stock</span>
                                                </th>
                                                <th
                                                    class="px-3 sm:px-6 py-3 sm:py-4 text-center text-xs font-semibold text-white uppercase tracking-wider">
                                                    <i class="fas fa-sort-numeric-up mr-1 sm:mr-2"></i><span
                                                        class="hidden sm:inline">Cantidad</span>
                                                </th>
                                                <th
                                                    class="px-3 sm:px-6 py-3 sm:py-4 text-center text-xs font-semibold text-white uppercase tracking-wider">
                                                    <i class="fas fa-percentage mr-1 sm:mr-2"></i><span
                                                        class="hidden sm:inline">Descuento</span>
                                                </th>
                                                <th
                                                    class="px-3 sm:px-6 py-3 sm:py-4 text-right text-xs font-semibold text-white uppercase tracking-wider">
                                                    <i class="fas fa-dollar-sign mr-1 sm:mr-2"></i><span
                                                        class="hidden sm:inline">Precio</span>
                                                </th>
                                                <th
                                                    class="px-3 sm:px-6 py-3 sm:py-4 text-right text-xs font-semibold text-white uppercase tracking-wider">
                                                    <i class="fas fa-calculator mr-1 sm:mr-2"></i><span
                                                        class="hidden sm:inline">Subtotal</span>
                                                </th>
                                                <th
                                                    class="px-3 sm:px-6 py-3 sm:py-4 text-center text-xs font-semibold text-white uppercase tracking-wider">
                                                    <i class="fas fa-cogs mr-1 sm:mr-2"></i><span
                                                        class="hidden sm:inline">Acciones</span>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            <template x-for="(item, index) in saleItems" :key="item.id">
                                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                                    <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm font-medium text-gray-900"
                                                        x-text="item.code"></td>
                                                    <td class="px-3 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm text-gray-900"
                                                        x-text="item.name"></td>
                                                    <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-center">
                                                        <span
                                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold"
                                                            :class="item.stock > 10 ? 'bg-green-100 text-green-800' : (item
                                                                .stock > 0 ? 'bg-yellow-100 text-yellow-800' :
                                                                'bg-red-100 text-red-800')"
                                                            x-text="item.stock"></span>
                                                    </td>
                                                    <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-center">
                                                        <div
                                                            class="flex items-center justify-center space-x-1 sm:space-x-2">
                                                            <button type="button" @click="decreaseQuantity(index)"
                                                                class="w-6 h-6 sm:w-8 sm:h-8 bg-red-500 hover:bg-red-600 text-white rounded-lg flex items-center justify-center transition-all duration-300"
                                                                :disabled="item.quantity <= 1">
                                                                <i class="fas fa-minus text-xs"></i>
                                                            </button>
                                                            <input type="number" x-model.number="item.quantity"
                                                                @input="updateItemSubtotal(index)" min="1"
                                                                :max="item.stock"
                                                                class="w-12 sm:w-16 text-center border border-gray-300 rounded-lg px-1 sm:px-2 py-1 text-xs sm:text-sm">
                                                            <button type="button" @click="increaseQuantity(index)"
                                                                class="w-6 h-6 sm:w-8 sm:h-8 bg-green-500 hover:bg-green-600 text-white rounded-lg flex items-center justify-center transition-all duration-300"
                                                                :disabled="item.quantity >= item.stock">
                                                                <i class="fas fa-plus text-xs"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                    <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-center">
                                                        <div class="flex items-center justify-center space-x-1">
                                                            <input type="number" x-model="item.discountValue"
                                                                @input="updateItemDiscount(index)" min="0"
                                                                :max="item.discountIsPercentage ? 100 : item.price"
                                                                step="0.1"
                                                                class="w-16 sm:w-20 text-center border border-gray-300 rounded-lg px-1 py-1 text-xs sm:text-sm"
                                                                placeholder="0">
                                                            <button type="button" @click="toggleItemDiscountType(index)"
                                                                class="w-6 h-6 sm:w-8 sm:h-8 rounded-lg flex items-center justify-center transition-all duration-300 text-xs sm:text-sm font-bold"
                                                                :class="item.discountIsPercentage ?
                                                                    'bg-blue-500 hover:bg-blue-600 text-white' :
                                                                    'bg-gray-500 hover:bg-gray-600 text-white'"
                                                                :title="item.discountIsPercentage ? 'Cambiar a descuento fijo' :
                                                                    'Cambiar a descuento porcentual'">
                                                                <span
                                                                    x-text="item.discountIsPercentage ? '%' : '$'"></span>
                                                            </button>
                                                        </div>
                                                    </td>
                                                    <td
                                                        class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-right text-xs sm:text-sm font-semibold text-gray-900">
                                                        <div class="flex flex-col items-end">
                                                            <span class="line-through text-gray-400"
                                                                x-text="`{{ $currency->symbol }} ${item.price.toFixed(2)}`"></span>
                                                            <span class="text-green-600 font-bold"
                                                                x-text="`{{ $currency->symbol }} ${getItemPriceWithDiscount(item).toFixed(2)}`"></span>
                                                        </div>
                                                    </td>
                                                    <td
                                                        class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-right text-xs sm:text-sm font-semibold text-gray-900">
                                                        <div class="flex flex-col items-end">
                                                            <span class="line-through text-gray-400"
                                                                x-text="`{{ $currency->symbol }} ${(item.price * item.quantity).toFixed(2)}`"></span>
                                                            <span class="text-green-600 font-bold"
                                                                x-text="`{{ $currency->symbol }} ${getItemSubtotalWithDiscount(item).toFixed(2)}`"></span>
                                                        </div>
                                                    </td>
                                                    <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-center">
                                                        <button type="button" @click="removeItem(index)"
                                                            class="w-6 h-6 sm:w-8 sm:h-8 bg-red-500 hover:bg-red-600 text-white rounded-lg flex items-center justify-center transition-all duration-300">
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
                                    <div class="text-center py-12 sm:py-16">
                                        <div
                                            class="w-20 h-20 sm:w-24 sm:h-24 mx-auto mb-4 sm:mb-6 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center">
                                            <i class="fas fa-shopping-cart text-3xl sm:text-4xl text-gray-400"></i>
                                        </div>
                                        <h4 class="text-lg sm:text-xl font-semibold text-gray-600 mb-2">No hay productos
                                            agregados
                                        </h4>
                                        <p class="text-sm sm:text-base text-gray-500">Agregue productos escaneando códigos
                                            o usando el buscador
                                        </p>
                                        <!-- Debug info -->
                                        <p class="text-xs text-gray-400 mt-2"
                                            x-text="`Debug: ${saleItems.length} productos`"></p>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Total de la venta y Nota -->
                        <div class="mt-4 sm:mt-6 grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
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
                                <div
                                    class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                                            <i class="fas fa-receipt text-lg"></i>
                                        </div>
                                        <div>
                                            <p class="text-emerald-100 text-sm mb-0.5">Total de la Venta</p>
                                            <div class="flex flex-col">
                                                <span class="line-through text-emerald-200 text-sm"
                                                    x-text="`{{ $currency->symbol }} ${getSubtotalBeforeGeneralDiscount().toFixed(2)}`"></span>
                                                <span class="text-lg sm:text-xl font-bold"
                                                    x-text="`{{ $currency->symbol }} ${totalAmount.toFixed(2)}`"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Descuento General -->
                                    <div class="flex items-center space-x-2 bg-white/10 rounded-xl p-2">
                                        <div class="flex items-center space-x-1">
                                            <input type="number" x-model="generalDiscountValue"
                                                @input="updateGeneralDiscount()" min="0"
                                                :max="generalDiscountIsPercentage ? 100 : getSubtotalBeforeGeneralDiscount()"
                                                step="0.1"
                                                class="w-16 sm:w-20 text-center bg-white/20 border border-white/30 rounded-lg px-1 py-1 text-white text-xs sm:text-sm placeholder-white/70"
                                                placeholder="0">
                                            <button type="button" @click="toggleGeneralDiscountType()"
                                                class="w-6 h-6 sm:w-8 sm:h-8 rounded-lg flex items-center justify-center transition-all duration-300 text-xs sm:text-sm font-bold bg-white/20 hover:bg-white/30 text-white"
                                                :title="generalDiscountIsPercentage ? 'Cambiar a descuento fijo' :
                                                    'Cambiar a descuento porcentual'">
                                                <span x-text="generalDiscountIsPercentage ? '%' : '$'"></span>
                                            </button>
                                        </div>
                                        <span class="text-white text-xs">Descuento</span>
                                    </div>

                                    <!-- Botones de acción -->
                                    <div class="flex items-center justify-center sm:justify-end space-x-2">
                                        <!-- Botón Procesar Venta -->
                                        <button type="button" @click.prevent="processSale('save')"
                                            x-bind:disabled="!canProcessSale"
                                            class="group relative w-10 h-10 sm:w-12 sm:h-12 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white rounded-xl transition-all duration-300 hover:scale-105 flex items-center justify-center shadow-lg">
                                            <i
                                                class="fas fa-save text-sm group-hover:scale-110 transition-transform duration-300"></i>
                                            <div
                                                class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">
                                                Procesar
                                            </div>
                                        </button>

                                        <!-- Botón Procesar y Nueva Venta -->
                                        <button type="button" @click.prevent="processSale('save_and_new')"
                                            x-bind:disabled="!canProcessSale"
                                            class="group relative w-10 h-10 sm:w-12 sm:h-12 bg-amber-500 hover:bg-amber-600 disabled:bg-gray-400 disabled:cursor-not-allowed text-white rounded-xl transition-all duration-300 hover:scale-105 flex items-center justify-center shadow-lg">
                                            <i
                                                class="fas fa-plus text-sm group-hover:scale-110 transition-transform duration-300"></i>
                                            <div
                                                class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">
                                                Procesar y Nueva
                                            </div>
                                        </button>

                                        <!-- Botón Cancelar -->
                                        <button type="button" @click="cancelSale()"
                                            class="group relative w-10 h-10 sm:w-12 sm:h-12 bg-red-500 hover:bg-red-600 text-white rounded-xl transition-all duration-300 hover:scale-105 flex items-center justify-center shadow-lg">
                                            <i
                                                class="fas fa-times text-sm group-hover:scale-110 transition-transform duration-300"></i>
                                            <div
                                                class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">
                                                Cancelar
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
                        <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-4 sm:px-6 py-3 sm:py-4 text-white">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2 sm:space-x-4">
                                    <div
                                        class="flex h-8 w-8 sm:h-12 sm:w-12 items-center justify-center rounded-full bg-white bg-opacity-25 backdrop-blur-sm shadow-lg border border-white border-opacity-30">
                                        <i class="fas fa-boxes text-sm sm:text-xl text-blue-600"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg sm:text-xl font-bold">Búsqueda de Productos</h3>
                                        <p class="text-blue-100 text-sm sm:text-base">Seleccione los productos para agregar
                                            a la venta</p>
                                    </div>
                                </div>
                                <button type="button" @click="searchModalOpen = false"
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
                                    <input type="text" x-model="productSearchTerm"
                                        @input.debounce.300ms="filterProducts()" @keyup="filterProducts()"
                                        class="block w-full pl-10 pr-3 py-2 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm sm:text-base"
                                        placeholder="Buscar productos por nombre o código...">
                                </div>
                            </div>

                            <!-- Products Table -->
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gradient-to-r from-blue-600 to-purple-600">
                                        <tr>
                                            <th
                                                class="px-2 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                                                Código
                                            </th>
                                            <th
                                                class="px-2 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                                                Acción
                                            </th>
                                            <th
                                                class="px-2 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                                                Producto
                                            </th>
                                            <th
                                                class="px-2 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-white uppercase tracking-wider hidden sm:table-cell">
                                                Categoría
                                            </th>
                                            <th
                                                class="px-2 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                                                Stock
                                            </th>
                                            <th
                                                class="px-2 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-white uppercase tracking-wider hidden md:table-cell">
                                                Precio
                                            </th>
                                            <th
                                                class="px-2 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                                                Estado
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <template x-for="product in filteredProducts" :key="product.id">
                                            <tr class="hover:bg-gray-50 transition-colors duration-200">
                                                <!-- Código -->
                                                <td class="px-2 sm:px-6 py-2 sm:py-4 whitespace-nowrap">
                                                    <span class="text-xs sm:text-sm font-mono text-gray-900"
                                                        x-text="product.code"></span>
                                                </td>

                                                <!-- Acción -->
                                                <td
                                                    class="px-2 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-xs sm:text-sm font-medium">
                                                    <button type="button" @click="addProductToSale(product)"
                                                        class="inline-flex items-center px-2 sm:px-3 py-1 sm:py-2 border border-transparent text-xs sm:text-sm leading-4 font-medium rounded-md text-white transition-colors"
                                                        :class="product.stock <= 0 || isProductInSale(product.id) ?
                                                            'bg-gray-400 cursor-not-allowed' :
                                                            'bg-blue-600 hover:bg-blue-700'"
                                                        :disabled="product.stock <= 0 || isProductInSale(product.id)">
                                                        <i class="fas fa-plus-circle mr-1 sm:mr-2 text-xs sm:text-sm"></i>
                                                    </button>
                                                </td>

                                                <!-- Producto -->
                                                <td class="px-2 sm:px-6 py-2 sm:py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 h-8 w-8 sm:h-10 sm:w-10">
                                                            <img :src="getProductImageUrl(product)" alt="N/I"
                                                                class="h-8 w-8 sm:h-10 sm:w-10 rounded-lg object-cover">
                                                        </div>
                                                        <div class="ml-2 sm:ml-4">
                                                            <div class="text-xs sm:text-sm font-medium text-gray-900"
                                                                x-text="product.name"></div>
                                                            <div class="text-xs sm:text-sm text-gray-500 hidden sm:block"
                                                                x-text="product.code"></div>
                                                        </div>
                                                    </div>
                                                </td>

                                                <!-- Categoría -->
                                                <td
                                                    class="px-2 sm:px-6 py-2 sm:py-4 whitespace-nowrap hidden sm:table-cell">
                                                    <span
                                                        class="inline-flex px-1 sm:px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800"
                                                        x-text="product.category?.name || 'Sin categoría'">
                                                    </span>
                                                </td>

                                                <!-- Stock -->
                                                <td class="px-2 sm:px-6 py-2 sm:py-4 whitespace-nowrap">
                                                    <span
                                                        class="inline-flex px-1 sm:px-2 py-1 text-xs font-semibold rounded-full"
                                                        :class="product.stock < 10 ? 'bg-red-100 text-red-800' : (product
                                                            .stock < 50 ? 'bg-yellow-100 text-yellow-800' :
                                                            'bg-green-100 text-green-800')"
                                                        x-text="product.stock">
                                                    </span>
                                                </td>

                                                <!-- Precio -->
                                                <td
                                                    class="px-2 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900 hidden md:table-cell">
                                                    <span
                                                        x-text="`{{ $currency->symbol }} ${parseFloat(product.sale_price || 0).toFixed(2)}`"></span>
                                                </td>

                                                <!-- Estado -->
                                                <td class="px-2 sm:px-6 py-2 sm:py-4 whitespace-nowrap">
                                                    <span
                                                        class="inline-flex px-1 sm:px-2 py-1 text-xs font-semibold rounded-full"
                                                        :class="product.stock_status_label === 'Bajo' ?
                                                            'bg-red-100 text-red-800' : (product
                                                                .stock_status_label === 'Normal' ?
                                                                'bg-yellow-100 text-yellow-800' :
                                                                'bg-green-100 text-green-800')"
                                                        x-text="product.stock_status_label">
                                                    </span>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>

                                <!-- Mensaje cuando no hay productos disponibles -->
                                <div x-show="filteredProducts.length === 0" class="text-center py-8">
                                    <div
                                        class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-search text-2xl text-gray-400"></i>
                                    </div>
                                    <h4 class="text-lg font-semibold text-gray-600 mb-2">No se encontraron productos
                                    </h4>
                                    <p class="text-gray-500">Intenta con otros términos de búsqueda o verifica que haya
                                        productos disponibles</p>
                                </div>

                                <!-- Mensaje cuando todos los productos están ya agregados -->
                                <div x-show="filteredProducts.length > 0 && filteredProducts.every(p => isProductInSale(p.id))"
                                    class="text-center py-8">
                                    <div
                                        class="w-16 h-16 mx-auto mb-4 bg-green-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-check text-2xl text-green-500"></i>
                                    </div>
                                    <h4 class="text-lg font-semibold text-green-600 mb-2">Todos los productos agregados
                                    </h4>
                                    <p class="text-green-500">Ya tienes todos los productos disponibles en tu venta</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Script con datos iniciales -->
    <script>
        window.saleCreateData = {
            products: @json($products),
            customers: @json($customers),
            currency: @json($currency),
            selectedCustomerId: @json($selectedCustomerId ?? null)
        };

        // Establecer fecha actual de Caracas inmediatamente
        document.addEventListener('DOMContentLoaded', function() {
            // Usar una aproximación más simple: obtener la fecha local y ajustar
            const now = new Date();

            // Obtener la fecha en formato local (que debería ser la fecha actual)
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');

            // Establecer valores por defecto
            window.defaultSaleDate = `${year}-${month}-${day}`;
            window.defaultSaleTime = `${hours}:${minutes}`;

        });
    </script>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('vendor/sweetalert2/sweetalert2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/sales/create.css') }}">
@endpush

@push('js')
    <script>
        window.saleCreateRoutes = {
            store: "{{ route('admin.sales.store') }}",
            index: "{{ route('admin.sales.index') }}"
        };
        // Guard de limpieza: si venimos de una creación exitosa, limpiar storage lo antes posible
        (function() {
            try {
                const params = new URLSearchParams(window.location.search);
                if (params.has('sale_created') || params.has('sale_created_form')) {
                    localStorage.removeItem('saleCreateData');
                }
            } catch (e) {
                /* noop */
            }
        })();
    </script>
    <script src="{{ asset('vendor/config.js') }}"></script>
    <script src="{{ asset('vendor/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('js/admin/sales/create.js') }}" defer></script>
@endpush
