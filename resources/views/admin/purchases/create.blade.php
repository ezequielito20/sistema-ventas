@extends('layouts.app')

@section('title', 'Nueva Compra')

@section('content')
    <!-- Background Pattern -->
    <div class="page-background"></div>

    <!-- Main Container -->
    <div class="main-container" x-data="purchaseForm()">
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

        <!-- Alertas Modernas -->
        @if (session('message'))
            <div class="alert-container">
                <div class="alert-modern alert-{{ session('icons') == 'success' ? 'success' : 'danger' }}">
                    <div class="alert-icon">
                        <i class="fas fa-{{ session('icons') == 'success' ? 'check-circle' : 'exclamation-triangle' }}"></i>
                    </div>
                    <div class="alert-content">
                        <span>{{ session('message') }}</span>
                    </div>
                    <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert-container">
                <div class="alert-modern alert-danger">
                    <div class="alert-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="alert-content">
                        <strong>¡Errores encontrados!</strong>
                        <ul class="error-list">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        @endif

        <!-- Form Container -->
        <div class="form-container">
            <form id="purchaseForm" action="{{ route('admin.purchases.store') }}" method="POST"
                enctype="multipart/form-data">
                @csrf

                <!-- Main Content -->
                <div class="content-grid">
                    <!-- Left Panel - Main Form -->
                    <div class="main-panel">
                        <!-- Information Panel -->
                        <div class="form-card">
                            <div class="card-header">
                                <div class="header-content">
                                    <div class="title-section">
                                        <div class="title-icon">
                                            <i class="fas fa-info-circle"></i>
                                        </div>
                                        <div class="title-text">
                                            <h3 class="panel-title">Información de la Compra</h3>
                                            <p class="panel-subtitle">Complete los datos básicos de la compra</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="form-grid">
                                    <!-- Código de Producto -->
                                    <div class="field-group full-width">
                                        <div class="field-wrapper">
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
                                                            <span>Buscar</span>
                                                        </div>
                                                        <div class="btn-bg"></div>
                                                    </button>
                                                    <a href="/products/create" class="btn-modern btn-success">
                                                        <div class="btn-content">
                                                            <i class="fas fa-plus"></i>
                                                            <span>Nuevo</span>
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

                                                <div class="field-help">
                                                    <i class="fas fa-lightbulb"></i>
                                                    <span>Presione Enter después de escanear o escribir el código</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Fecha y Hora de Compra -->
                                    <div class="field-group">
                                        <div class="field-wrapper">
                                            <label for="purchase_date" class="field-label">
                                                <div class="label-content">
                                                    <div class="label-icon">
                                                        <i class="fas fa-calendar"></i>
                                                    </div>
                                                    <span>Fecha de Compra</span>
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
                                    </div>

                                    <div class="field-group">
                                        <div class="field-wrapper">
                                            <label for="purchase_time" class="field-label">
                                                <div class="label-content">
                                                    <div class="label-icon">
                                                        <i class="fas fa-clock"></i>
                                                    </div>
                                                    <span>Hora de Compra</span>
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

                        <!-- Products Panel -->
                        <div class="form-card">
                            <div class="card-header">
                                <div class="header-content">
                                    <div class="title-section">
                                        <div class="title-icon">
                                            <i class="fas fa-shopping-basket"></i>
                                        </div>
                                        <div class="title-text">
                                            <h3 class="panel-title">Productos en la Compra</h3>
                                            <p class="panel-subtitle">Gestiona los productos de esta compra</p>
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
                                <div class="table-container">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Producto</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Stock</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Cantidad</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Precio Unit.</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Subtotal</th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <template x-for="(product, index) in products" :key="product.id">
                                                <tr class="hover:bg-gray-50 transition-colors">
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="flex items-center">
                                                            <div class="flex-shrink-0 h-10 w-10">
                                                                <img class="h-10 w-10 rounded-lg object-cover"
                                                                    :src="product.image_url || '/img/no-image.png'"
                                                                    :alt="product.name">
                                                            </div>
                                                            <div class="ml-4">
                                                                <div class="text-sm font-medium text-gray-900"
                                                                    x-text="product.name"></div>
                                                                <div class="text-sm text-gray-500" x-text="product.code">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <span
                                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                                            :class="{
                                                                'bg-red-100 text-red-800': product.stock < 10,
                                                                'bg-yellow-100 text-yellow-800': product.stock >= 10 &&
                                                                    product.stock < 50,
                                                                'bg-green-100 text-green-800': product.stock >= 50
                                                            }"
                                                            x-text="product.stock"></span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <input type="number" :value="product.quantity"
                                                            @input="updateProduct(index, 'quantity', $event.target.value)"
                                                            class="w-20 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-center"
                                                            min="1" step="1">
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="flex items-center">
                                                            <span
                                                                class="text-gray-500 mr-2">{{ $currency->symbol }}</span>
                                                            <input type="number" :value="product.price"
                                                                @input="updateProduct(index, 'price', $event.target.value)"
                                                                class="w-24 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                                step="0.01">
                                                        </div>
                                                    </td>
                                                    <td
                                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                                                        <span
                                                            x-text="'{{ $currency->symbol }} ' + product.subtotal.toFixed(2)"></span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <button @click="removeProduct(index)"
                                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                                                            <i class="fas fa-trash mr-2"></i>
                                                            Eliminar
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
                                        <div class="flex items-center">
                                            <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                                            <p class="text-sm text-blue-700">
                                                <strong>Consejo:</strong> Si solo hay un producto con stock disponible, se
                                                agregará automáticamente.
                                            </p>
                                        </div>
                                        <button @click="checkAndAddSingleProduct()"
                                            class="mt-2 px-3 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600 transition-colors">
                                            <i class="fas fa-magic mr-1"></i>
                                            Verificar producto único
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Panel - Summary and Actions -->
                    <div class="sidebar-panel">
                        <div class="form-card">
                            <div class="card-header">
                                <div class="title-section">
                                    <div class="title-icon">
                                        <i class="fas fa-shopping-cart"></i>
                                    </div>
                                    <div class="title-text">
                                        <h3 class="panel-title">Resumen y Acciones</h3>
                                        <p class="panel-subtitle">Información y opciones de la compra</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Summary Section -->
                            <div class="card-section">
                                <div class="section-header">
                                    <h4 class="section-title">
                                        <i class="fas fa-calculator"></i>
                                        Resumen de Compra
                                    </h4>
                                </div>
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
                                                x-text="'{{ $currency->symbol }} ' + totalAmount.toFixed(2)">
                                                {{ $currency->symbol }} 0.00</div>
                                            <div class="summary-label">Total a Pagar</div>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="total_price" x-model="totalAmount">
                            </div>

                            <!-- Actions Section -->
                            <div class="card-section">
                                <div class="section-header">
                                    <h4 class="section-title">
                                        <i class="fas fa-tasks"></i>
                                        Acciones Disponibles
                                    </h4>
                                </div>
                                <div class="action-buttons">
                                    <button type="submit" class="btn-modern btn-primary" @click="submitForm()"
                                        title="Guardar esta compra y volver al listado">
                                        <div class="btn-content">
                                            <i class="fas fa-save"></i>
                                            <span>Guardar</span>
                                        </div>
                                        <div class="btn-bg"></div>
                                        <div class="btn-shine"></div>
                                    </button>

                                    <button type="submit" class="btn-modern btn-success" name="action"
                                        value="save_and_new" @click="submitForm()"
                                        title="Guardar esta compra y crear una nueva">
                                        <div class="btn-content">
                                            <i class="fas fa-plus-circle"></i>
                                            <span>Guardar y Nueva</span>
                                        </div>
                                        <div class="btn-bg"></div>
                                    </button>

                                    <button type="button" class="btn-modern btn-danger" @click="cancelPurchase()">
                                        <div class="btn-content">
                                            <i class="fas fa-times-circle"></i>
                                            <span>Cancelar</span>
                                        </div>
                                        <div class="btn-bg"></div>
                                    </button>

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
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-7xl transform overflow-hidden rounded-2xl bg-white shadow-2xl transition-all"
                x-show="isOpen" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-6 py-4 text-white">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-full bg-white bg-opacity-20 backdrop-blur-sm">
                                <i class="fas fa-search text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold">Búsqueda de Productos</h3>
                                <p class="text-blue-100">Seleccione los productos para agregar a la compra</p>
                            </div>
                        </div>
                        <button @click="closeModal()"
                            class="rounded-full p-2 text-white hover:bg-white hover:bg-opacity-20 transition-colors">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="p-6">
                    <!-- Search Bar -->
                    <div class="mb-6">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" x-model="searchTerm" @input="filterProductsInModal()"
                                class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Buscar productos por nombre o código...">
                        </div>
                    </div>

                    <!-- Products Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Código</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Producto</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Categoría</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Stock</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Precio Compra</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Estado</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Acción</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($products as $product)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm font-mono text-gray-900">{{ $product->code }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <img class="h-10 w-10 rounded-lg object-cover"
                                                        src="{{ $product->image_url }}" alt="{{ $product->name }}">
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ $product->name }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">{{ $product->code }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                                {{ $product->category->name }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                            @if ($product->stock < 10) bg-red-100 text-red-800
                                            @elseif($product->stock < 50) bg-yellow-100 text-yellow-800
                                            @else bg-green-100 text-green-800 @endif">
                                                {{ $product->stock }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span>{{ $currency->symbol }}
                                                {{ number_format($product->purchase_price, 2) }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                            @if ($product->stock_status_label === 'Bajo') bg-red-100 text-red-800
                                            @elseif($product->stock_status_label === 'Normal') bg-yellow-100 text-yellow-800
                                            @else bg-green-100 text-green-800 @endif">
                                                {{ $product->stock_status_label }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button
                                                @click="addProductFromModal({{ $product->id }}, '{{ $product->code }}', '{{ $product->name }}', '{{ $product->image_url }}', {{ $product->stock }}, {{ $product->purchase_price }}, '{{ $product->category->name }}')"
                                                data-product-id="{{ $product->id }}"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                                <i class="fas fa-plus-circle mr-2"></i>
                                                Agregar
                                            </button>
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
                <div class="bg-gray-50 px-6 py-4 flex justify-end">
                    <button @click="closeModal()"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <i class="fas fa-times mr-2"></i>
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@push('css')
    <style>
        /* ===== VARIABLES GLOBALES ===== */
        :root {
            /* Alpine.js x-cloak */
            [x-cloak] {
                display: none !important;
            }
            
            /* Colores principales */
            --primary-50: #f0f9ff;
            --primary-100: #e0f2fe;
            --primary-200: #bae6fd;
            --primary-300: #7dd3fc;
            --primary-400: #38bdf8;
            --primary-500: #0ea5e9;
            --primary-600: #0284c7;
            --primary-700: #0369a1;
            --primary-800: #075985;
            --primary-900: #0c4a6e;

            /* Colores secundarios */
            --secondary-50: #faf5ff;
            --secondary-100: #f3e8ff;
            --secondary-200: #e9d5ff;
            --secondary-300: #d8b4fe;
            --secondary-400: #c084fc;
            --secondary-500: #a855f7;
            --secondary-600: #9333ea;
            --secondary-700: #7c3aed;
            --secondary-800: #6b21a8;
            --secondary-900: #581c87;

            /* Gradientes */
            --gradient-primary: linear-gradient(135deg, #0ea5e9 0%, #3b82f6 50%, #8b5cf6 100%);
            --gradient-secondary: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            --gradient-glass: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
            --gradient-success: linear-gradient(135deg, #10b981 0%, #059669 100%);
            --gradient-warning: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            --gradient-danger: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);

            /* Colores neutros */
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e0;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --gray-900: #0f172a;

            /* Sombras */
            --shadow-xs: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px -1px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            --shadow-2xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            --shadow-inner: inset 0 2px 4px 0 rgba(0, 0, 0, 0.05);

            /* Efectos especiales */
            --glow-primary: 0 0 20px rgba(14, 165, 233, 0.3);
            --glow-secondary: 0 0 20px rgba(168, 85, 247, 0.3);
            --blur-backdrop: blur(20px);
            --border-radius: 16px;
            --border-radius-lg: 24px;
            --border-radius-xl: 32px;

            /* Animaciones */
            --transition-fast: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-normal: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-slow: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-bounce: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        /* ===== RESET Y BASE ===== */
        * {
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            overflow-x: hidden;
        }

        /* ===== FONDO ANIMADO ===== */
        .page-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            overflow: hidden;
        }

        .page-background::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background:
                radial-gradient(circle at 20% 80%, rgba(14, 165, 233, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(168, 85, 247, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(59, 130, 246, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 60% 60%, rgba(16, 185, 129, 0.1) 0%, transparent 50%);
            animation: float 20s ease-in-out infinite;
        }

        .page-background::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background:
                linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.05) 50%, transparent 70%);
            animation: shimmer 3s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translate(0, 0) rotate(0deg);
            }

            33% {
                transform: translate(30px, -30px) rotate(120deg);
            }

            66% {
                transform: translate(-20px, 20px) rotate(240deg);
            }
        }

        @keyframes shimmer {

            0%,
            100% {
                opacity: 0.3;
            }

            50% {
                opacity: 0.7;
            }
        }

        /* ===== CONTENEDOR PRINCIPAL ===== */
        .main-container {
            position: relative;
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
            min-height: 100vh;
            z-index: 1;
        }

            /* Responsive Main Container */
    @media (max-width: 768px) {
        .main-container {
            padding: 1rem;
        }
        
        .form-container {
            padding: 0;
        }
    }
    
    @media (max-width: 480px) {
        .main-container {
            padding: 0.5rem;
        }
        
        .form-container {
            padding: 0;
        }
        
        /* Mejorar experiencia táctil en móviles */
        .btn-modern,
        .btn-glass,
        .modern-input {
            min-height: 44px; /* Tamaño mínimo recomendado para toques */
        }
        
        /* Asegurar que los botones sean fáciles de tocar */
        .action-buttons .btn-modern {
            min-height: 48px;
        }
    }

        /* ===== HEADER FLOTANTE ===== */
        .floating-header {
            position: relative;
            margin-bottom: 2rem;
            animation: slideDown 0.6s ease-out;
            z-index: 10;
        }

        .header-content {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(248, 250, 252, 0.95) 100%);
            backdrop-filter: var(--blur-backdrop);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: var(--border-radius-lg);
            padding: 1.5rem 2rem;
            box-shadow: var(--shadow-xl);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 2rem;
            position: relative;
            overflow: hidden;
        }

            /* Responsive Header */
    @media (max-width: 768px) {
        .header-content {
            flex-direction: column;
            text-align: center;
            gap: 1.5rem;
            padding: 1rem 1.5rem;
        }
        
        .header-left {
            flex-direction: column;
            gap: 1rem;
        }
        
        .header-icon {
            width: 56px;
            height: 56px;
            font-size: 1.25rem;
        }
        
        .header-text h1 {
            font-size: 1.5rem;
        }
        
        .header-text p {
            font-size: 0.875rem;
        }
        
        .header-actions {
            width: 100%;
            justify-content: center;
        }
        
        .btn-glass {
            width: 100%;
            max-width: 200px;
        }
    }
    
    @media (max-width: 480px) {
        .header-content {
            padding: 1rem;
            gap: 1rem;
        }
        
        .header-icon {
            width: 48px;
            height: 48px;
            font-size: 1rem;
        }
        
        .header-text h1 {
            font-size: 1.25rem;
        }
        
        .header-text p {
            font-size: 0.8rem;
        }
        
        .btn-glass {
            max-width: 150px;
            font-size: 0.875rem;
        }
    }

        .header-content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #0ea5e9, #3b82f6, #8b5cf6, #10b981);
            z-index: 1;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .header-icon-wrapper {
            position: relative;
        }

        .header-icon {
            width: 64px;
            height: 64px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            box-shadow: var(--shadow-lg);
            position: relative;
            z-index: 2;
        }

        .icon-glow {
            position: absolute;
            inset: -4px;
            background: var(--gradient-primary);
            border-radius: 50%;
            opacity: 0.3;
            filter: blur(8px);
            animation: pulse 2s ease-in-out infinite;
        }

        .header-text h1 {
            font-size: 1.75rem;
            font-weight: 800;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0;
            line-height: 1.2;
        }

        .header-text p {
            color: var(--gray-600);
            margin: 0.25rem 0 0 0;
            font-weight: 500;
        }

        .header-actions {
            display: flex;
            gap: 1rem;
        }

        /* ===== BOTONES GLASS ===== */
        .btn-glass {
            position: relative;
            padding: 0.75rem 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            background: var(--gradient-glass);
            backdrop-filter: var(--blur-backdrop);
            color: var(--gray-700);
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition-normal);
            overflow: hidden;
            cursor: pointer;
        }

        .btn-glass::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
            opacity: 0;
            transition: var(--transition-fast);
        }

        .btn-glass:hover::before {
            opacity: 1;
        }

        .btn-glass:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-primary-glass {
            background: var(--gradient-primary);
            color: white;
            box-shadow: var(--glow-primary);
        }

        .btn-secondary-glass {
            background: rgba(255, 255, 255, 0.8);
            color: var(--gray-700);
        }

        .btn-ripple {
            position: absolute;
            inset: 0;
            overflow: hidden;
        }

        /* ===== ALERTAS MODERNAS ===== */
        .alert-container {
            margin-bottom: 1.5rem;
        }

        .alert-modern {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            margin-bottom: 1rem;
            animation: slideInDown 0.5s ease-out;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: #065f46;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #991b1b;
        }

        .alert-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .alert-success .alert-icon {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
        }

        .alert-danger .alert-icon {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }

        .alert-content {
            flex: 1;
        }

        .error-list {
            margin: 0.5rem 0 0 0;
            padding-left: 1.5rem;
        }

        .alert-close {
            background: none;
            border: none;
            color: inherit;
            font-size: 1.25rem;
            padding: 0.25rem;
            border-radius: 50%;
            transition: var(--transition-normal);
            cursor: pointer;
        }

        .alert-close:hover {
            background: rgba(0, 0, 0, 0.1);
        }

        /* ===== CONTENEDOR DEL FORMULARIO ===== */
        .form-container {
            position: relative;
            z-index: 1;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 2rem;
            align-items: start;
        }
        
        @media (max-width: 1400px) {
            .content-grid {
                grid-template-columns: 1fr 350px;
                gap: 1.5rem;
            }
        }

            /* Responsive Grid */
    @media (max-width: 1200px) {
        .content-grid {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        
        .sidebar-panel {
            order: -1;
            position: static;
        }
    }
    
    @media (max-width: 768px) {
        .content-grid {
            gap: 1rem;
        }
        
        .main-panel {
            flex-direction: column;
        }
    }
    
    @media (max-width: 640px) {
        .content-grid {
            gap: 0.75rem;
        }
    }

        .main-panel {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .sidebar-panel {
            position: sticky;
            top: 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: var(--border-radius-xl);
            box-shadow: var(--shadow-2xl);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            width: 100%;
            max-width: 400px;
        }

        /* Asegurar que el texto del resumen sea visible */
        .sidebar-panel .summary-value,
        .sidebar-panel .summary-label {
            color: #000000 !important;
        }

        .sidebar-panel .summary-item {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.5rem;
        }

        .sidebar-panel .summary-item.total {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .sidebar-panel .summary-item.total .summary-value,
        .sidebar-panel .summary-item.total .summary-label {
            color: #ffffff !important;
        }

            /* Responsive Sidebar */
    @media (max-width: 1200px) {
        .sidebar-panel {
            position: static;
            margin-bottom: 1rem;
            max-width: 100%;
        }
    }
    
    @media (max-width: 768px) {
        .sidebar-panel {
            border-radius: var(--border-radius);
            max-width: 100%;
        }
        
        .card-section {
            padding: 1rem 1.5rem;
        }
        
        .summary-item {
            padding: 0.75rem;
            margin-bottom: 0.5rem;
        }
        
        .summary-value {
            font-size: 1rem;
        }
        
        .summary-label {
            font-size: 0.75rem;
        }
        
        .action-buttons {
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .action-buttons .btn-modern {
            width: 100%;
            justify-content: center;
        }
    }
    
    @media (max-width: 480px) {
        .sidebar-panel {
            margin: 0 -0.5rem 1rem -0.5rem;
            border-radius: 0;
        }
        
        .card-section {
            padding: 0.75rem 1rem;
        }
        
        .summary-item {
            padding: 0.5rem;
            margin-bottom: 0.25rem;
        }
        
        .summary-value {
            font-size: 0.875rem;
        }
        
        .summary-label {
            font-size: 0.7rem;
        }
        
        .action-buttons {
            gap: 0.25rem;
        }
        
        .action-buttons .btn-modern {
            padding: 0.75rem;
            font-size: 0.875rem;
        }
    }
    
            @media (max-width: 360px) {
            .sidebar-panel {
                margin: 0 -0.25rem 0.75rem -0.25rem;
            }
            
            .card-section {
                padding: 0.5rem 0.75rem;
            }
            
            .summary-item {
                padding: 0.375rem;
                margin-bottom: 0.2rem;
            }
            
            .summary-value {
                font-size: 0.8rem;
            }
            
            .summary-label {
                font-size: 0.65rem;
            }
            
            .action-buttons .btn-modern {
                padding: 0.625rem;
                font-size: 0.8rem;
            }
            
            /* Asegurar que el contenido no se desborde */
            .summary-content {
                min-width: 0;
                overflow: hidden;
            }
            
            .summary-value,
            .summary-label {
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
        }
        
        @media (max-width: 320px) {
            .sidebar-panel {
                margin: 0;
                border-radius: 0;
            }
            
            .card-section {
                padding: 0.375rem 0.5rem;
            }
            
            .summary-item {
                padding: 0.25rem;
                gap: 0.2rem;
            }
            
            .summary-icon {
                width: 20px;
                height: 20px;
                font-size: 0.5rem;
            }
            
            .summary-value {
                font-size: 0.75rem;
            }
            
            .summary-label {
                font-size: 0.6rem;
            }
            
            .action-buttons .btn-modern {
                padding: 0.5rem;
                font-size: 0.75rem;
            }
        }

        .sidebar-panel::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #10b981, #3b82f6, #8b5cf6);
            z-index: 1;
        }

        /* ===== TARJETAS DE FORMULARIO ===== */
        .form-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(248, 250, 252, 0.95) 100%);
            backdrop-filter: var(--blur-backdrop);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: var(--border-radius-xl);
            box-shadow: var(--shadow-2xl);
            overflow: hidden;
            animation: slideUp 0.8s ease-out both;
            position: relative;
        }

        .form-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-primary);
            z-index: 1;
        }

        .form-card:nth-child(1) {
            animation-delay: 0.1s;
            border-left: 4px solid #0ea5e9;
        }

        .form-card:nth-child(2) {
            animation-delay: 0.2s;
            border-left: 4px solid #10b981;
        }

        .card-header {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 50%, #cbd5e0 100%);
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--gray-200);
            position: relative;
        }

            /* Responsive Card Header */
    @media (max-width: 768px) {
        .card-header {
            padding: 1rem 1.5rem;
        }
        
        .header-content {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }
        
        .title-section {
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .title-icon {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }
        
        .panel-title {
            font-size: 1.125rem;
        }
        
        .panel-subtitle {
            font-size: 0.8rem;
        }
        
        .counter-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
    }
    
    @media (max-width: 480px) {
        .card-header {
            padding: 0.75rem 1rem;
        }
        
        .title-icon {
            width: 35px;
            height: 35px;
            font-size: 0.875rem;
        }
        
        .panel-title {
            font-size: 1rem;
        }
        
        .panel-subtitle {
            font-size: 0.75rem;
        }
        
        .counter-badge {
            font-size: 0.7rem;
            padding: 0.2rem 0.4rem;
        }
    }

        .card-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--gradient-primary);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        .title-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .title-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #0ea5e9 0%, #3b82f6 50%, #8b5cf6 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);
            position: relative;
            overflow: hidden;
        }

        .title-icon::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .title-icon:hover::before {
            left: 100%;
        }

        .panel-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--gray-800);
            margin: 0;
        }

        .panel-subtitle {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin: 0.125rem 0 0 0;
        }

        .header-controls {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .counter-badge {
            background: var(--gradient-primary);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .card-body {
            padding: 2rem;
        }

            /* Responsive Card Body */
    @media (max-width: 768px) {
        .card-body {
            padding: 1.5rem;
        }
        
        .form-card {
            margin-bottom: 1rem;
        }
    }
    
    @media (max-width: 480px) {
        .card-body {
            padding: 1rem;
        }
        
        .form-card {
            margin-bottom: 0.75rem;
        }
    }

        .card-section {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
        }

        .card-section:last-child {
            border-bottom: none;
        }

        .card-section:nth-child(odd) {
            background: rgba(255, 255, 255, 0.08);
        }

        /* ===== GRID DE FORMULARIO ===== */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .field-group.full-width {
            grid-column: 1 / -1;
        }

            /* Responsive Form Grid */
    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .field-group {
            margin-bottom: 1rem;
        }
        
        .input-container {
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .input-actions {
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .input-actions .btn-modern {
            width: 100%;
            justify-content: center;
        }
    }
    
    @media (max-width: 480px) {
        .form-grid {
            gap: 0.75rem;
        }
        
        .field-group {
            margin-bottom: 0.75rem;
        }
        
        .modern-input {
            font-size: 0.875rem;
            padding: 0.5rem 0.75rem;
        }
        
        .btn-modern {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }
    }

        .field-group {
            margin-bottom: 1.5rem;
        }

        .field-wrapper {
            position: relative;
        }

        .field-label {
            margin-bottom: 1rem;
        }

        .label-content {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .label-icon {
            width: 32px;
            height: 32px;
            background: var(--gradient-primary);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.875rem;
        }

        .label-content span {
            font-size: 1rem;
            font-weight: 700;
            color: var(--gray-800);
        }

        .required-indicator {
            color: #ef4444;
            font-weight: 800;
            font-size: 1.1rem;
            margin-left: 0.25rem;
        }

        /* ===== INPUTS MODERNOS ===== */
        .input-container {
            position: relative;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: stretch;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            transition: var(--transition-normal);
            overflow: hidden;
        }

        .input-wrapper:hover {
            box-shadow: var(--shadow-md);
        }

        .input-wrapper:focus-within {
            box-shadow: var(--glow-primary);
            transform: translateY(-1px);
        }

        .input-icon {
            width: 48px;
            background: var(--gray-50);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-500);
            font-size: 1rem;
            border-right: 1px solid var(--gray-200);
        }

        .modern-input {
            flex: 1;
            padding: 1rem 1.25rem;
            border: none;
            background: transparent;
            font-size: 1rem;
            font-weight: 500;
            color: var(--gray-800);
            transition: var(--transition-fast);
            outline: none;
        }

        .modern-input::placeholder {
            color: var(--gray-400);
            font-weight: 400;
        }
        
        /* Responsive Inputs */
        @media (max-width: 768px) {
            .modern-input {
                padding: 0.75rem 1rem;
                font-size: 0.875rem;
            }
            
            .input-wrapper {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .input-icon {
                position: static;
                transform: none;
                margin-bottom: 0.25rem;
            }
        }
        
        @media (max-width: 480px) {
            .modern-input {
                padding: 0.625rem 0.875rem;
                font-size: 0.8rem;
            }
        }

        .input-border {
            position: absolute;
            inset: 0;
            border: 2px solid transparent;
            border-radius: var(--border-radius);
            pointer-events: none;
            transition: var(--transition-fast);
        }

        .input-wrapper:focus-within .input-border {
            border-color: var(--primary-500);
        }

        .input-wrapper.error .input-border {
            border-color: #ef4444;
        }

        .input-focus-effect {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--gradient-primary);
            transform: scaleX(0);
            transition: var(--transition-normal);
        }

        .input-wrapper:focus-within .input-focus-effect {
            transform: scaleX(1);
        }

        /* ===== ACCIONES DE INPUT ===== */
        .input-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 0.75rem;
        }

        /* Responsive Input Actions */
        @media (max-width: 768px) {
            .input-actions {
                flex-direction: column;
                gap: 0.5rem;
            }

            .input-actions .btn-modern {
                width: 100%;
                justify-content: center;
            }
        }

        /* ===== BOTONES MODERNOS ===== */
        .btn-modern {
            position: relative;
            padding: 0.75rem 1.25rem;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition-bounce);
            overflow: hidden;
            text-decoration: none;
        }

        .btn-content {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-bg {
            position: absolute;
            inset: 0;
            transition: var(--transition-normal);
        }
        
        /* Responsive Buttons */
        @media (max-width: 768px) {
            .btn-modern {
                padding: 0.625rem 1rem;
                font-size: 0.8rem;
            }
        }
        
        @media (max-width: 480px) {
            .btn-modern {
                padding: 0.5rem 0.875rem;
                font-size: 0.75rem;
            }
        }

        .btn-primary {
            color: white;
        }

        .btn-primary .btn-bg {
            background: linear-gradient(135deg, #0ea5e9 0%, #3b82f6 50%, #8b5cf6 100%);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(14, 165, 233, 0.4);
        }

        .btn-primary:hover .btn-bg {
            background: linear-gradient(135deg, #0284c7 0%, #2563eb 50%, #7c3aed 100%);
        }

        .btn-success {
            color: white;
        }

        .btn-success .btn-bg {
            background: linear-gradient(135deg, #10b981 0%, #059669 50%, #047857 100%);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.4);
        }

        .btn-success:hover .btn-bg {
            background: linear-gradient(135deg, #059669 0%, #047857 50%, #065f46 100%);
        }

        .btn-danger {
            color: white;
        }

        .btn-danger .btn-bg {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 50%, #b91c1c 100%);
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(239, 68, 68, 0.4);
        }

        .btn-danger:hover .btn-bg {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 50%, #991b1b 100%);
        }

        .btn-secondary {
            color: var(--gray-700);
        }

        .btn-secondary .btn-bg {
            background: white;
            border: 2px solid var(--gray-200);
        }

        .btn-secondary:hover {
            color: var(--gray-800);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-secondary:hover .btn-bg {
            background: var(--gray-50);
            border-color: var(--gray-300);
        }

        .btn-shine {
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.3) 50%, transparent 70%);
            transform: translateX(-100%);
            transition: var(--transition-slow);
        }

        .btn-modern:hover .btn-shine {
            transform: translateX(100%);
        }

        /* ===== MENSAJES DE ERROR Y AYUDA ===== */
        .error-message {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #ef4444;
            font-weight: 600;
            font-size: 0.875rem;
            margin-top: 0.75rem;
            padding: 0.5rem 0.75rem;
            background: rgba(239, 68, 68, 0.05);
            border-radius: 8px;
            border-left: 3px solid #ef4444;
        }

        .field-help {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--gray-500);
            font-size: 0.875rem;
            margin-top: 0.75rem;
            font-weight: 500;
        }

        .field-help i {
            color: var(--primary-500);
        }

        /* ===== SECCIONES ===== */
        .section-header {
            margin-bottom: 1rem;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray-800);
            margin: 0;
        }

        .section-title i {
            color: var(--primary-500);
        }

        /* ===== RESUMEN DE COMPRA ===== */
        .summary-stats {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .summary-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
            border-radius: var(--border-radius);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: var(--transition-normal);
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }

        .summary-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.5s;
        }

        .summary-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0.08) 100%);
        }

        .summary-item:hover::before {
            left: 100%;
        }

        .summary-item.total {
            background: linear-gradient(135deg, #10b981 0%, #059669 50%, #047857 100%);
            color: white;
            border: none;
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
        }

        .summary-item.total:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 50%, #065f46 100%);
            box-shadow: 0 12px 35px rgba(16, 185, 129, 0.4);
        }

        .summary-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0ea5e9 0%, #3b82f6 50%, #8b5cf6 100%);
            color: white;
            font-size: 1rem;
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);
            position: relative;
            overflow: hidden;
        }

        .summary-icon::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }

        .summary-item:hover .summary-icon::before {
            left: 100%;
        }

        .summary-item.total .summary-icon {
            background: rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 12px rgba(255, 255, 255, 0.2);
        }

        .summary-content {
            flex: 1;
        }

        .summary-value {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.125rem;
            color: #000000 !important;
        }
        
        /* Responsive Summary Items */
        @media (max-width: 768px) {
            .summary-stats {
                gap: 0.5rem;
            }
            
            .summary-item {
                padding: 0.75rem;
                gap: 0.5rem;
            }
            
            .summary-icon {
                width: 35px;
                height: 35px;
                font-size: 0.875rem;
            }
            
            .summary-value {
                font-size: 1rem;
            }
            
            .summary-label {
                font-size: 0.75rem;
            }
        }
        
        @media (max-width: 480px) {
            .summary-stats {
                gap: 0.375rem;
            }
            
            .summary-item {
                padding: 0.5rem;
                gap: 0.375rem;
            }
            
            .summary-icon {
                width: 30px;
                height: 30px;
                font-size: 0.75rem;
            }
            
            .summary-value {
                font-size: 0.875rem;
            }
            
            .summary-label {
                font-size: 0.7rem;
            }
        }
        
        @media (max-width: 360px) {
            .summary-item {
                padding: 0.375rem;
                gap: 0.25rem;
            }
            
            .summary-icon {
                width: 25px;
                height: 25px;
                font-size: 0.625rem;
            }
            
            .summary-value {
                font-size: 0.8rem;
            }
            
            .summary-label {
                font-size: 0.65rem;
            }
        }

        .summary-label {
            font-size: 0.8rem;
            font-weight: 500;
            opacity: 0.9;
            color: #000000 !important;
        }

        .summary-divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--gray-300), transparent);
            margin: 0.75rem 0;
        }

        .total-amount {
            font-size: 1.75rem;
            font-weight: 800;
            color: #000000 !important;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }



        /* ===== BOTONES DE ACCIÓN ===== */
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

            /* Responsive Action Buttons */
    @media (max-width: 768px) {
        .action-buttons {
            gap: 0.5rem;
        }
        
        .action-buttons .btn-modern {
            width: 100%;
            justify-content: center;
            padding: 1rem;
            font-size: 1rem;
        }
        
        .action-buttons .text-xs {
            font-size: 0.7rem;
            line-height: 1.2;
        }
    }
    
    @media (max-width: 480px) {
        .action-buttons .btn-modern {
            padding: 0.75rem;
            font-size: 0.875rem;
        }
        
        .action-buttons .text-xs {
            font-size: 0.65rem;
        }
    }

        /* ===== TABLA MODERNA ===== */
        .table-container {
            overflow: hidden;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
        }

        .modern-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
        }

        .modern-table thead {
            background: var(--gradient-primary);
        }

        .modern-table th {
            padding: 1rem;
            color: white;
            font-weight: 600;
            text-align: left;
            border: none;
            font-size: 0.875rem;
        }

        .modern-table th:first-child {
            border-top-left-radius: var(--border-radius);
        }

        .modern-table th:last-child {
            border-top-right-radius: var(--border-radius);
        }

        .modern-table tbody tr {
            transition: var(--transition-normal);
            border-bottom: 1px solid var(--gray-100);
        }

        .modern-table tbody tr:hover {
            background: var(--gray-50);
            transform: scale(1.01);
        }

        .modern-table td {
            padding: 1rem;
            vertical-align: middle;
            border: none;
        }

        /* Responsive Table */
        @media (max-width: 1024px) {
            .table-container {
                overflow-x: auto;
            }

            .modern-table {
                min-width: 800px;
            }
        }

        @media (max-width: 768px) {
            .table-container {
                overflow-x: auto;
                border-radius: 0;
            }

            .modern-table {
                min-width: 700px;
            }

            .modern-table th,
            .modern-table td {
                padding: 0.5rem 0.25rem;
                font-size: 0.75rem;
                white-space: nowrap;
            }

            .modern-table th {
                font-size: 0.7rem;
            }

            /* Ocultar columnas menos importantes en móvil */
            .modern-table th:nth-child(3),
            .modern-table td:nth-child(3) {
                display: none;
            }
        }

        @media (max-width: 640px) {
            .modern-table {
                min-width: 600px;
            }

            .modern-table th,
            .modern-table td {
                padding: 0.25rem 0.125rem;
                font-size: 0.7rem;
            }

            /* Ocultar más columnas en pantallas muy pequeñas */
            .modern-table th:nth-child(5),
            .modern-table td:nth-child(5) {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .modern-table {
                min-width: 500px;
            }

            .modern-table th,
            .modern-table td {
                padding: 0.125rem;
                font-size: 0.65rem;
            }
            
            /* Ocultar columnas menos importantes en pantallas muy pequeñas */
            .modern-table th:nth-child(4),
            .modern-table td:nth-child(4) {
                display: none;
            }
        }
        
        @media (max-width: 360px) {
            .modern-table {
                min-width: 400px;
            }
            
            .modern-table th,
            .modern-table td {
                padding: 0.1rem;
                font-size: 0.6rem;
            }
            
            /* Solo mostrar columnas esenciales */
            .modern-table th:not(:nth-child(1)):not(:nth-child(6)),
            .modern-table td:not(:nth-child(1)):not(:nth-child(6)) {
                display: none;
            }
        }

        /* ===== ESTADO VACÍO ===== */
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--gray-500);
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--gray-600);
        }

        .empty-description {
            font-size: 0.875rem;
            max-width: 300px;
            margin: 0 auto;
            line-height: 1.5;
        }

        /* ===== MODAL MODERNO ===== */
        .modern-modal {
            border-radius: var(--border-radius-xl);
            border: none;
            box-shadow: var(--shadow-2xl);
            overflow: hidden;
        }

        /* Responsive Modal */
        @media (max-width: 768px) {
            .modern-modal {
                border-radius: var(--border-radius);
                margin: 1rem;
            }

            .modal-title {
                font-size: 1.25rem;
            }

            .modal-subtitle {
                font-size: 0.8rem;
            }
        }

        @media (max-width: 480px) {
            .modern-modal {
                margin: 0.5rem;
            }

            .modal-title {
                font-size: 1.125rem;
            }
        }

        .modern-modal .modal-header {
            background: var(--gradient-primary);
            color: white;
            border-bottom: none;
            padding: 2rem;
        }

        .modal-title-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .modal-icon {
            width: 56px;
            height: 56px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .modal-icon i {
            font-size: 1.5rem;
            color: white;
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }

        .modal-subtitle {
            font-size: 0.875rem;
            opacity: 0.9;
            margin: 0.25rem 0 0 0;
        }

        .modal-close {
            background: none;
            border: none;
            color: white;
            font-size: 1.25rem;
            padding: 0.5rem;
            border-radius: 50%;
            transition: var(--transition-normal);
            cursor: pointer;
        }

        .modal-close:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: rotate(90deg);
        }

        .modern-modal .modal-body {
            padding: 2rem;
        }

        .modern-modal .modal-footer {
            background: var(--gray-50);
            border-top: 1px solid var(--gray-200);
            padding: 1.5rem 2rem;
        }

        /* ===== ELEMENTOS DE PRODUCTO ===== */
        .product-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .product-thumbnail {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            object-fit: cover;
            border: 2px solid var(--gray-200);
        }

        .product-details {
            flex: 1;
        }

        .product-name {
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 0.25rem;
        }

        .product-code {
            font-size: 0.875rem;
            color: var(--gray-500);
            font-family: 'Courier New', monospace;
        }

        .category-badge {
            background: var(--gray-100);
            color: var(--gray-700);
            padding: 0.5rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .stock-badge {
            font-size: 0.875rem;
            padding: 0.5rem 0.75rem;
            border-radius: 20px;
        }

        .price-text {
            font-weight: 600;
            color: var(--success-color);
        }

        .status-badge {
            font-size: 0.875rem;
            padding: 0.5rem 0.75rem;
            border-radius: 20px;
        }

        /* ===== ANIMACIONES ===== */
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 0.3;
                transform: scale(1);
            }

            50% {
                opacity: 0.5;
                transform: scale(1.05);
            }
        }

        /* ===== RESPONSIVE DESIGN ===== */
        @media (max-width: 1200px) {
            .content-grid {
                grid-template-columns: 1fr;
            }

            .sidebar-panel {
                position: static;
                order: -1;
            }
        }

        @media (max-width: 768px) {
            .main-container {
                padding: 1rem;
            }

            .header-content {
                flex-direction: column;
                text-align: center;
                gap: 1.5rem;
            }

            .header-left {
                flex-direction: column;
                gap: 1rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .input-actions {
                flex-direction: column;
            }

            .card-header {
                padding: 1rem 1.5rem;
            }

            .card-body {
                padding: 1.5rem;
            }

            .card-section {
                padding: 1rem 1.5rem;
            }

            .action-buttons {
                gap: 0.5rem;
            }

            .btn-modern {
                width: 100%;
                justify-content: center;
            }

            .summary-item {
                padding: 0.75rem;
            }

            .summary-icon {
                width: 35px;
                height: 35px;
                font-size: 0.875rem;
            }

            .summary-value {
                font-size: 1.125rem;
            }

            .summary-label {
                font-size: 0.75rem;
            }

            .modern-table {
                font-size: 0.8rem;
            }

            .modern-table th,
            .modern-table td {
                padding: 0.75rem;
            }

            .product-info {
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
            }

            .product-thumbnail {
                width: 40px;
                height: 40px;
            }

            .modal-title-section {
                flex-direction: column;
                text-align: center;
                gap: 0.5rem;
            }

            .modal-title {
                font-size: 1.25rem;
            }

            .modal-subtitle {
                font-size: 0.8rem;
            }
        }

        @media (max-width: 480px) {
            .header-icon {
                width: 56px;
                height: 56px;
                font-size: 1.25rem;
            }

            .header-text h1 {
                font-size: 1.5rem;
            }

            .card-header {
                padding: 0.75rem 1rem;
            }

            .card-body {
                padding: 1rem;
            }

            .card-section {
                padding: 0.75rem 1rem;
            }

            .btn-modern {
                padding: 0.625rem 1rem;
                font-size: 0.8rem;
            }

            .summary-item {
                padding: 0.5rem;
                gap: 0.5rem;
            }

            .summary-icon {
                width: 30px;
                height: 30px;
                font-size: 0.75rem;
            }

            .summary-value {
                font-size: 1rem;
            }

            .summary-label {
                font-size: 0.7rem;
            }

            .total-amount {
                font-size: 1.25rem;
            }

            .empty-state {
                padding: 2rem 0.75rem;
            }

            .empty-icon {
                font-size: 3rem;
                margin-bottom: 0.75rem;
            }

            .empty-title {
                font-size: 1.125rem;
            }

            .empty-description {
                font-size: 0.8rem;
                max-width: 250px;
            }

            .modern-table {
                font-size: 0.75rem;
            }

            .modern-table th,
            .modern-table td {
                padding: 0.5rem;
            }

            .product-thumbnail {
                width: 35px;
                height: 35px;
            }

            .product-name {
                font-size: 0.8rem;
            }

            .product-code {
                font-size: 0.7rem;
            }
        }
    </style>
@endpush

@push('js')
    <script>
        // Función principal del formulario de compra
        function purchaseForm() {
            // Verificar que Alpine.js esté disponible
            if (typeof Alpine === 'undefined') {
                console.warn('Alpine.js no está disponible');
                return {};
            }
            return {
                formChanged: false,
                products: [],
                totalAmount: 0,
                totalProducts: 0,
                totalQuantity: 0,
                productCode: '',
                autoAddExecuted: false,

                init() {
                    // Evitar múltiples inicializaciones
                    if (window.purchaseFormInstance) {
                        return;
                    }
                    
                    // Esperar a que Alpine.js esté completamente cargado
                    this.$nextTick(() => {
                        this.initializeEventListeners();
                        this.updateEmptyState();
                        window.purchaseFormInstance = this;

                        // Verificar producto único inmediatamente
                        this.checkAndAddSingleProduct();
                    });
                },



                // Verificar si hay un solo producto y agregarlo automáticamente
                checkAndAddSingleProduct() {
                    try {
                        // Verificar si ya se ejecutó
                        if (this.autoAddExecuted) {
                            return;
                        }
                        
                        const productRows = document.querySelectorAll('#searchProductModal tbody tr');

                        if (productRows.length === 1) {
                            const row = productRows[0];
                            const stockElement = row.querySelector('td:nth-child(4) span');
                            const stock = parseInt(stockElement?.textContent || '0');

                            if (stock > 0) {
                                const addButton = row.querySelector('button[class*="bg-blue-600"]');

                                if (addButton) {
                                    const codeElement = row.querySelector('td:first-child span');
                                    const nameElement = row.querySelector('td:nth-child(2) .text-sm.font-medium');
                                    const priceElement = row.querySelector('td:nth-child(5) span');
                                    const categoryElement = row.querySelector('td:nth-child(3) span');

                                    if (codeElement && nameElement && priceElement && categoryElement) {
                                        const code = codeElement.textContent.trim();
                                        const name = nameElement.textContent.trim();
                                        const priceText = priceElement.textContent.trim();
                                        const categoryName = categoryElement.textContent.trim();

                                        const price = parseFloat(priceText.replace(/[^\d.,]/g, '').replace(',', '.'));

                                        if (!this.products.some(p => p.code === code)) {
                                            // Obtener el ID real del producto desde el botón
                                            let productId = Date.now(); // ID temporal por defecto

                                            const dataProductId = addButton.getAttribute('data-product-id');
                                            if (dataProductId) {
                                                productId = parseInt(dataProductId);
                                            }

                                            // Obtener la imagen del producto desde el DOM
                                            const imageElement = row.querySelector('img');
                                            let imageUrl = '/img/no-image.png';
                                            if (imageElement) {
                                                imageUrl = imageElement.getAttribute('src');
                                            }

                                            // Crear objeto producto
                                            const product = {
                                                id: productId,
                                                code: code,
                                                name: name,
                                                image_url: imageUrl,
                                                stock: stock,
                                                purchase_price: price,
                                                category: {
                                                    name: categoryName
                                                },
                                                quantity: 1,
                                                price: price,
                                                subtotal: price
                                            };

                                            this.addProductToTable(product);
                                            this.showToast(`${name} se agregó automáticamente`, 'info');
                                        }
                                    }
                                }
                            }
                        }
                        
                        // Marcar como ejecutado
                        this.autoAddExecuted = true;
                    } catch (error) {
                        // Silenciar errores
                    }
                },







                // Inicializar event listeners
                initializeEventListeners() {
                    this.$watch('productCode', (value) => {
                        if (value && this.productCode) {
                            this.searchProductByCode(this.productCode);
                        }
                    });

                    this.$el.addEventListener('input', () => {
                        this.formChanged = true;
                    });
                },

                // Buscar producto por código
                searchProductByCode(code) {
                    if (!code) return;

                    if (this.products.some(p => p.code === code)) {
                        this.showToast('Este producto ya está en la lista de compra', 'warning');
                        return;
                    }

                    fetch(`/admin/purchases/product-by-code/${code}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                this.addProductToTable(data.product);
                                this.productCode = '';
                            } else {
                                this.showToast(data.message || 'No se encontró el producto', 'error');
                            }
                        })
                        .catch(error => {
                            this.showToast('No se encontró el producto', 'error');
                        });
                },

                // Agregar producto a la tabla
                addProductToTable(product) {
                    if (!product.id) {
                        this.showToast('El producto no tiene un ID válido', 'error');
                        return;
                    }

                    if (this.products.some(p => p.code === product.code)) {
                        this.showToast('Este producto ya está en la lista de compra', 'warning');
                        return;
                    }

                    this.products.push({
                        ...product,
                        quantity: 1,
                        price: product.purchase_price || product.price || 0,
                        subtotal: product.purchase_price || product.price || 0
                    });

                    this.updateTotal();
                    this.updateEmptyState();
                    this.showToast(`${product.name} se agregó a la lista de compra`, 'success');
                },

                // Actualizar cantidad o precio
                updateProduct(index, field, value) {
                    this.products[index][field] = parseFloat(value) || 0;
                    this.products[index].subtotal = this.products[index].quantity * this.products[index].price;
                    this.updateTotal();
                },

                // Eliminar producto
                removeProduct(index) {
                    this.products.splice(index, 1);
                    this.updateTotal();
                    this.updateEmptyState();
                },

                // Actualizar totales
                updateTotal() {
                    this.totalAmount = this.products.reduce((sum, product) => sum + product.subtotal, 0);
                    this.totalProducts = this.products.length;
                    this.totalQuantity = this.products.reduce((sum, product) => sum + product.quantity, 0);

                    const totalInput = document.getElementById('totalAmountInput');
                    if (totalInput) {
                        totalInput.value = this.totalAmount.toFixed(2);
                    }

                    this.updateProductCounter();
                },

                // Actualizar contador de productos en el header
                updateProductCounter() {
                    const counterElement = document.querySelector('.counter-badge');
                    if (counterElement) {
                        counterElement.textContent = `${this.totalProducts} producto${this.totalProducts !== 1 ? 's' : ''}`;
                    }
                },

                // Actualizar estado vacío
                updateEmptyState() {
                    const emptyState = document.getElementById('emptyState');
                    if (emptyState) {
                        emptyState.style.display = this.products.length === 0 ? 'block' : 'none';
                    }
                },

                // Abrir modal de búsqueda
                openSearchModal() {
                    window.dispatchEvent(new CustomEvent('openSearchModal'));
                },

                // Cancelar compra
                cancelPurchase() {
                    if (this.formChanged) {
                        if (confirm('¿Está seguro? Se perderán todos los datos ingresados en esta compra')) {
                            this.goBack();
                        }
                    } else {
                        this.goBack();
                    }
                },

                // Volver atrás
                goBack() {
                    // Verificar si hay una URL de referencia guardada en la sesión
                    const referrerUrl = '{{ session('purchases_referrer') }}';

                    if (referrerUrl && referrerUrl !== '') {
                        // Usar la URL guardada en la sesión
                        window.location.href = referrerUrl;
                    } else if (document.referrer &&
                        !document.referrer.includes('purchases/create') &&
                        !document.referrer.includes('purchases/edit')) {
                        // Usar document.referrer si no es del mismo formulario
                        window.history.back();
                    } else {
                        // Fallback: ir al listado de compras
                        window.location.href = '{{ route('admin.purchases.index') }}';
                    }
                },

                // Enviar formulario
                submitForm() {
                    if (this.products.length === 0) {
                        this.showToast('Debe agregar al menos un producto a la compra', 'warning');
                        return false;
                    }

                    if (!document.getElementById('purchase_date').value) {
                        this.showToast('Debe seleccionar una fecha de compra', 'warning');
                        return false;
                    }

                    this.prepareFormData();
                    return true;
                },

                // Preparar datos del formulario
                prepareFormData() {
                    this.products.forEach((product, index) => {
                        const quantityInput = document.createElement('input');
                        quantityInput.type = 'hidden';
                        quantityInput.name = `items[${product.id}][quantity]`;
                        quantityInput.value = product.quantity;

                        const priceInput = document.createElement('input');
                        priceInput.type = 'hidden';
                        priceInput.name = `items[${product.id}][price]`;
                        priceInput.value = product.price;

                        document.getElementById('purchaseForm').appendChild(quantityInput);
                        document.getElementById('purchaseForm').appendChild(priceInput);
                    });
                },

                // Mostrar toast
                showToast(message, type = 'success') {
                    if (typeof Swal !== 'undefined') {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });

                        Toast.fire({
                            icon: type,
                            title: message
                        });
                    } else {
                        console.log(`${type.toUpperCase()}: ${message}`);
                    }
                }
            }
        }

        // Función del modal de búsqueda
        function searchModal() {
            // Verificar que Alpine.js esté disponible
            if (typeof Alpine === 'undefined') {
                console.warn('Alpine.js no está disponible para searchModal');
                return {};
            }
            return {
                isOpen: false,
                searchTerm: '',

                init() {
                    // Esperar a que Alpine.js esté completamente cargado
                    this.$nextTick(() => {
                        this.listenForOpenEvent();
                    });
                },

                // Escuchar evento para abrir modal
                listenForOpenEvent() {
                    window.addEventListener('openSearchModal', () => {
                        this.openModal();
                    });
                },

                // Abrir modal
                openModal() {
                    this.isOpen = true;
                    this.searchTerm = '';
                    document.body.style.overflow = 'hidden';
                },

                // Cerrar modal
                closeModal() {
                    this.isOpen = false;
                    document.body.style.overflow = 'auto';
                },

                // Filtrar productos en el modal
                filterProductsInModal() {
                    const searchTerm = this.searchTerm.toLowerCase();
                    const rows = document.querySelectorAll('#searchProductModal tbody tr');

                    rows.forEach(row => {
                        const code = row.querySelector('td:first-child span').textContent.toLowerCase();
                        const name = row.querySelector('td:nth-child(2) .text-sm.font-medium').textContent
                            .toLowerCase();
                        const category = row.querySelector('td:nth-child(3) span').textContent.toLowerCase();

                        const matches = code.includes(searchTerm) ||
                            name.includes(searchTerm) ||
                            category.includes(searchTerm);

                        row.style.display = matches ? '' : 'none';
                    });
                },

                // Agregar producto desde el modal
                addProductFromModal(id, code, name, imageUrl, stock, price, categoryName) {
                    // Verificar si ya está en la lista
                    const existingProduct = window.purchaseFormInstance.products.find(p => p.code === code);
                    if (existingProduct) {
                        this.showToast('Este producto ya está en la lista de compra', 'warning');
                        return;
                    }

                    // Crear objeto producto
                    const product = {
                        id: id,
                        code: code,
                        name: name,
                        image_url: imageUrl || '/img/no-image.png',
                        stock: stock,
                        purchase_price: price,
                        category: {
                            name: categoryName
                        },
                        quantity: 1,
                        price: price,
                        subtotal: price
                    };

                    // Agregar al formulario principal
                    window.purchaseFormInstance.addProductToTable(product);

                    // Cerrar modal
                    this.closeModal();

                    // Mostrar mensaje de éxito
                    this.showToast(`${name} se agregó a la lista de compra`, 'success');
                },

                // Mostrar toast
                showToast(message, type = 'success') {
                    if (typeof Swal !== 'undefined') {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });

                        Toast.fire({
                            icon: type,
                            title: message
                        });
                    } else {
                        console.log(`${type.toUpperCase()}: ${message}`);
                    }
                }
            }
        }



        // Función global para volver atrás
        window.goBack = function() {
            // Verificar si hay una URL de referencia guardada en la sesión
            const referrerUrl = '{{ session('purchases_referrer') }}';

            if (referrerUrl && referrerUrl !== '') {
                // Usar la URL guardada en la sesión
                window.location.href = referrerUrl;
            } else if (document.referrer &&
                !document.referrer.includes('purchases/create') &&
                !document.referrer.includes('purchases/edit')) {
                // Usar document.referrer si no es del mismo formulario
                window.history.back();
            } else {
                // Fallback: ir al listado de compras
                window.location.href = '{{ route('admin.purchases.index') }}';
            }
        };

        // Confirmación antes de salir
        window.addEventListener('beforeunload', (event) => {
            // Verificar si hay cambios en el formulario
            if (window.purchaseFormInstance && window.purchaseFormInstance.formChanged) {
                event.preventDefault();
                event.returnValue = '';
            }
        });
        
        // Verificar que Alpine.js esté disponible antes de continuar
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof Alpine === 'undefined') {
                console.warn('Alpine.js no se cargó correctamente');
            } else {
                console.log('Alpine.js cargado correctamente');
            }
        });
    </script>
@endpush
