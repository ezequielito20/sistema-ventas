@extends('layouts.app')

@section('title', 'Editar Producto')

@section('content')
<!-- Background Pattern -->
<div class="page-background"></div>

<!-- Main Container -->
<div class="main-container">
    <!-- Floating Header -->
    <div class="floating-header">
        <div class="header-content">
            <div class="header-left">
                <div class="header-icon-wrapper">
                    <div class="header-icon">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div class="icon-glow"></div>
                </div>
                <div class="header-text">
                    <h1 class="header-title">Editar Producto</h1>
                    <p class="header-subtitle">Modifica la información del producto "{{ $product->name }}"</p>
                </div>
            </div>
            <div class="header-actions">
                <button onclick="window.history.back()" class="btn-glass btn-secondary-glass">
                    <i class="fas fa-arrow-left"></i>
                    <span>Volver</span>
                    <div class="btn-ripple"></div>
                </button>
            </div>
        </div>
    </div>

    <!-- Form Container -->
    <div class="form-container">
        <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" id="productForm">
            @csrf
            @method('PUT')
            
            <!-- Form Card -->
            <div class="form-card">
                <div class="card-header">
                    <div class="header-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="header-text">
                        <h3>Información del Producto</h3>
                        <p>Modifica los campos necesarios para actualizar el producto</p>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Información Básica -->
                    <div class="form-section">
                        <h4 class="section-title">
                            <i class="fas fa-info-circle"></i>
                            Información Básica
                        </h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Código del Producto -->
                            <div class="space-y-1.5">
                                <label for="code" class="flex items-center gap-2 text-sm font-medium text-gray-700">
                                    <i class="fas fa-barcode text-blue-500"></i>
                                    <span>Código</span>
                                </label>
                                <input type="text" 
                                       class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg bg-white text-gray-800 transition-all duration-200 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 @error('code') border-red-400 focus:border-red-500 focus:ring-red-100 @enderror"
                                       id="code" name="code" 
                                       value="{{ old('code', $product->code) }}" 
                                       placeholder="PROD001" required>
                                @error('code')
                                    <span class="text-xs text-red-500">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Nombre del Producto -->
                            <div class="space-y-1.5 md:col-span-2">
                                <label for="name" class="flex items-center gap-2 text-sm font-medium text-gray-700">
                                    <i class="fas fa-box text-blue-500"></i>
                                    <span>Nombre del Producto</span>
                                </label>
                                <input type="text" 
                                       class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg bg-white text-gray-800 transition-all duration-200 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 @error('name') border-red-400 focus:border-red-500 focus:ring-red-100 @enderror"
                                       id="name" name="name" 
                                       value="{{ old('name', $product->name) }}" 
                                       placeholder="Nombre del producto" required>
                                @error('name')
                                    <span class="text-xs text-red-500">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Categoría -->
                            <div class="space-y-1.5" 
                                 x-data="{ 
                                     isOpen: false, 
                                     searchTerm: '', 
                                     categories: @js($categories),
                                     filteredCategories: @js($categories),
                                     selectedCategoryName: '{{ old('category_id', $product->category_id) ? ($product->category ? $product->category->name : 'Seleccionar') : 'Seleccionar' }}',
                                     selectedCategoryId: '{{ old('category_id', $product->category_id) }}',
                                     filterCategories() {
                                         if (!this.searchTerm) {
                                             this.filteredCategories = this.categories;
                                             return;
                                         }
                                         const term = this.searchTerm.toLowerCase();
                                         this.filteredCategories = this.categories.filter(category => 
                                             category.name.toLowerCase().includes(term)
                                         );
                                     },
                                     selectCategory(category) {
                                         if (category) {
                                             this.selectedCategoryName = category.name;
                                             this.selectedCategoryId = category.id;
                                             document.getElementById('category_id').value = category.id;
                                         } else {
                                             this.selectedCategoryName = 'Seleccionar';
                                             this.selectedCategoryId = '';
                                             document.getElementById('category_id').value = '';
                                         }
                                         this.isOpen = false;
                                         this.searchTerm = '';
                                         this.filteredCategories = this.categories;
                                     }
                                 }" 
                                 @click.away="isOpen = false">
                                
                                <label class="flex items-center gap-2 text-sm font-medium text-gray-700">
                                    <i class="fas fa-tag text-blue-500"></i>
                                    <span>Categoría</span>
                                </label>
                                
                                <div class="relative">
                                    <input type="hidden" id="category_id" name="category_id" value="{{ old('category_id', $product->category_id) }}" required>
                                    
                                    <button type="button" 
                                            @click="isOpen = !isOpen; if (isOpen) { $nextTick(() => $refs.categorySearch.focus()) }"
                                            class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg bg-white text-gray-800 transition-all duration-200 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 flex items-center justify-between text-left @error('category_id') border-red-400 focus:border-red-500 focus:ring-red-100 @enderror">
                                        <span class="block truncate" x-text="selectedCategoryName"></span>
                                        <svg class="h-4 w-4 text-gray-400 transition-transform duration-200" 
                                             :class="{ 'rotate-180': isOpen }" 
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>

                                    <!-- Dropdown -->
                                    <div x-show="isOpen" 
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="transform opacity-0 scale-95"
                                         x-transition:enter-end="transform opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="transform opacity-100 scale-100"
                                         x-transition:leave-end="transform opacity-0 scale-95"
                                         class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-auto">
                                        
                                        <!-- Search Input -->
                                        <div class="p-2 border-b border-gray-100">
                                            <input type="text" 
                                                   x-ref="categorySearch"
                                                   x-model="searchTerm" 
                                                   @input="filterCategories()"
                                                   class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                   placeholder="Buscar...">
                                        </div>
                                        
                                        <!-- Options -->
                                        <div class="py-1">
                                            <template x-for="category in filteredCategories" :key="category.id">
                                                <button type="button" 
                                                        @click="selectCategory(category)"
                                                        class="w-full px-3 py-2 text-left text-sm hover:bg-gray-50 flex items-center gap-2 transition-colors duration-150"
                                                        :class="{ 'bg-blue-50 text-blue-700 font-medium': selectedCategoryId == category.id }">
                                                    <i class="fas fa-tag text-gray-400 text-xs"></i>
                                                    <span x-text="category.name"></span>
                                                </button>
                                            </template>
                                            
                                            <div x-show="filteredCategories.length === 0" 
                                                 class="px-3 py-2 text-sm text-gray-500 text-center">
                                                No se encontraron categorías
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @error('category_id')
                                    <span class="text-xs text-red-500">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Fecha de Ingreso -->
                            <div class="space-y-1.5">
                                <label for="entry_date" class="flex items-center gap-2 text-sm font-medium text-gray-700">
                                    <i class="fas fa-calendar text-blue-500"></i>
                                    <span>Fecha Ingreso</span>
                                </label>
                                <input type="date" 
                                       class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg bg-white text-gray-800 transition-all duration-200 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 @error('entry_date') border-red-400 focus:border-red-500 focus:ring-red-100 @enderror"
                                       id="entry_date" name="entry_date" 
                                       value="{{ old('entry_date', $product->entry_date->format('Y-m-d')) }}"
                                       max="{{ date('Y-m-d') }}" required>
                                @error('entry_date')
                                    <span class="text-xs text-red-500">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label for="description" class="flex items-center gap-2 text-sm font-medium text-gray-700">
                                <i class="fas fa-align-left text-blue-500"></i>
                                <span>Descripción</span>
                            </label>
                            <textarea class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg bg-white text-gray-800 transition-all duration-200 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 min-h-[100px] resize-y leading-relaxed @error('description') border-red-400 focus:border-red-500 focus:ring-red-100 @enderror" 
                                      id="description" name="description" rows="3" 
                                      placeholder="Describe las características del producto...">{{ old('description', $product->description) }}</textarea>
                            @error('description')
                                <span class="text-xs text-red-500">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Imagen del Producto -->
                    <div class="form-section">
                        <h4 class="section-title">
                            <i class="fas fa-image"></i>
                            Imagen del Producto
                        </h4>
                        
                        <div class="image-upload-section">
                            <div class="image-preview {{ $product->image ? 'has-image' : '' }}" 
                                 id="imagePreview"
                                 data-has-original-image="{{ $product->image ? 'true' : 'false' }}"
                                 data-original-image-url="{{ $product->image ? $product->image_url : '' }}"
                                 style="{{ $product->image ? "background-image: url('{$product->image_url}'); border: 3px solid var(--primary-color);" : '' }}">
                                
                                @if($product->image)
                                    <div class="upload-placeholder" style="display: none;">
                                        <div class="upload-icon">
                                            <i class="fas fa-cloud-upload-alt"></i>
                                        </div>
                                        <h4>Selecciona una imagen</h4>
                                        <p>Haz clic para subir una imagen del producto</p>
                                        <span class="upload-info">JPG, PNG, GIF hasta 2MB</span>
                                    </div>
                                    <div class="image-overlay">
                                        <div class="image-info">
                                            <i class="fas fa-check-circle"></i>
                                            <span>Imagen actual</span>
                                        </div>
                                    </div>
                                @else
                                    <div class="upload-placeholder">
                                        <div class="upload-icon">
                                            <i class="fas fa-cloud-upload-alt"></i>
                                        </div>
                                        <h4>Selecciona una imagen</h4>
                                        <p>Haz clic para subir una imagen del producto</p>
                                        <span class="upload-info">JPG, PNG, GIF hasta 2MB</span>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="file-input-wrapper">
                                <input type="file" class="file-input @error('image') is-invalid @enderror"
                                    id="image" name="image" accept="image/*">
                                <label for="image" class="file-label">
                                    <i class="fas fa-camera"></i>
                                    <span>{{ $product->image ? 'Cambiar Imagen' : 'Seleccionar Imagen' }}</span>
                                </label>
                            </div>
                            
                            @error('image')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Gestión de Stock -->
                    <div class="form-section">
                        <h4 class="section-title">
                            <i class="fas fa-boxes"></i>
                            Gestión de Stock
                        </h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="space-y-1.5">
                                <label for="stock" class="flex items-center gap-2 text-sm font-medium text-gray-700">
                                    <i class="fas fa-cubes text-blue-500"></i>
                                    <span>Stock Actual</span>
                                </label>
                                <input type="number" 
                                       class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg bg-white text-gray-800 transition-all duration-200 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 @error('stock') border-red-400 focus:border-red-500 focus:ring-red-100 @enderror"
                                       id="stock" name="stock" 
                                       value="{{ old('stock', $product->stock) }}" 
                                       min="0" required>
                                @error('stock')
                                    <span class="text-xs text-red-500">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="space-y-1.5">
                                <label for="min_stock" class="flex items-center gap-2 text-sm font-medium text-gray-700">
                                    <i class="fas fa-exclamation-triangle text-orange-500"></i>
                                    <span>Stock Mínimo</span>
                                </label>
                                <input type="number" 
                                       class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg bg-white text-gray-800 transition-all duration-200 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 @error('min_stock') border-red-400 focus:border-red-500 focus:ring-red-100 @enderror"
                                       id="min_stock" name="min_stock" 
                                       value="{{ old('min_stock', $product->min_stock) }}" 
                                       min="0" required>
                                @error('min_stock')
                                    <span class="text-xs text-red-500">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="space-y-1.5">
                                <label for="max_stock" class="flex items-center gap-2 text-sm font-medium text-gray-700">
                                    <i class="fas fa-warehouse text-green-500"></i>
                                    <span>Stock Máximo</span>
                                </label>
                                <input type="number" 
                                       class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg bg-white text-gray-800 transition-all duration-200 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 @error('max_stock') border-red-400 focus:border-red-500 focus:ring-red-100 @enderror"
                                       id="max_stock" name="max_stock" 
                                       value="{{ old('max_stock', $product->max_stock) }}" 
                                       min="0" required>
                                @error('max_stock')
                                    <span class="text-xs text-red-500">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Información de Precios -->
                    <div class="form-section">
                        <h4 class="section-title">
                            <i class="fas fa-dollar-sign"></i>
                            Información de Precios
                        </h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label for="purchase_price" class="flex items-center gap-2 text-sm font-medium text-gray-700">
                                    <i class="fas fa-shopping-cart text-red-500"></i>
                                    <span>Precio de Compra</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm">{{ $currency->symbol }}</span>
                                    <input type="number" 
                                           class="w-full pl-8 pr-3 py-2.5 text-sm border border-gray-300 rounded-lg bg-white text-gray-800 transition-all duration-200 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 @error('purchase_price') border-red-400 focus:border-red-500 focus:ring-red-100 @enderror"
                                           id="purchase_price" name="purchase_price" 
                                           value="{{ old('purchase_price', $product->purchase_price) }}" 
                                           step="0.01" min="0" required>
                                </div>
                                @error('purchase_price')
                                    <span class="text-xs text-red-500">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="space-y-1.5">
                                <label for="sale_price" class="flex items-center gap-2 text-sm font-medium text-gray-700">
                                    <i class="fas fa-cash-register text-green-500"></i>
                                    <span>Precio de Venta</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm">{{ $currency->symbol }}</span>
                                    <input type="number" 
                                           class="w-full pl-8 pr-3 py-2.5 text-sm border border-gray-300 rounded-lg bg-white text-gray-800 transition-all duration-200 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 @error('sale_price') border-red-400 focus:border-red-500 focus:ring-red-100 @enderror"
                                           id="sale_price" name="sale_price" 
                                           value="{{ old('sale_price', $product->sale_price) }}" 
                                           step="0.01" min="0" required>
                                </div>
                                @error('sale_price')
                                    <span class="text-xs text-red-500">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="profit-indicator" id="profitIndicator" style="display: none;">
                            <div class="profit-content">
                                <div class="profit-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div class="profit-info">
                                    <span class="profit-label">Margen de Beneficio:</span>
                                    <span class="profit-value" id="profitValue">0%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <div class="action-buttons">
                    <button type="submit" class="btn-modern btn-success" id="submitProduct">
                        <div class="btn-content">
                            <i class="fas fa-save"></i>
                            <span>Actualizar Producto</span>
                        </div>
                        <div class="btn-bg"></div>
                    </button>
                    
                    <button type="button" class="btn-modern btn-secondary" id="restoreForm">
                        <div class="btn-content">
                            <i class="fas fa-undo"></i>
                            <span>Restaurar</span>
                        </div>
                        <div class="btn-bg"></div>
                    </button>
                </div>
                
                <div class="action-links">
                    <a href="{{ route('admin.products.index') }}" class="link-secondary">
                        <i class="fas fa-times"></i>
                        <span>Cancelar</span>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('css/admin/products/edit.css') }}">
@endpush

@push('js')
<script src="{{ asset('js/admin/products/edit.js') }}"></script>
@endpush
