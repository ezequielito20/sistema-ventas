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
                        
                        <div class="form-grid">
                            <div class="field-group">
                                <label for="code" class="field-label">
                                    <i class="fas fa-barcode"></i>
                                    <span>Código del Producto</span>
                                </label>
                                <div class="input-container">
                                    <input type="text" class="form-input @error('code') is-invalid @enderror"
                                        id="code" name="code" value="{{ old('code', $product->code) }}" placeholder="Ej: PROD001" required>
                                    <div class="input-border"></div>
                                </div>
                                @error('code')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="field-group">
                                <label for="name" class="field-label">
                                    <i class="fas fa-box"></i>
                                    <span>Nombre del Producto</span>
                                </label>
                                <div class="input-container">
                                    <input type="text" class="form-input @error('name') is-invalid @enderror" 
                                        id="name" name="name" value="{{ old('name', $product->name) }}" placeholder="Nombre del producto" required>
                                    <div class="input-border"></div>
                                </div>
                                @error('name')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="field-group">
                                <label for="category_id" class="field-label">
                                    <i class="fas fa-tag"></i>
                                    <span>Categoría</span>
                                </label>
                                <div class="input-container">
                                    <select class="form-input @error('category_id') is-invalid @enderror"
                                        id="category_id" name="category_id" required>
                                        <option value="">Seleccionar categoría</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}"
                                                {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="input-border"></div>
                                </div>
                                @error('category_id')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="field-group">
                                <label for="entry_date" class="field-label">
                                    <i class="fas fa-calendar"></i>
                                    <span>Fecha de Ingreso</span>
                                </label>
                                <div class="input-container">
                                    <input type="date" class="form-input @error('entry_date') is-invalid @enderror"
                                        id="entry_date" name="entry_date" 
                                        value="{{ old('entry_date', $product->entry_date->format('Y-m-d')) }}"
                                        max="{{ date('Y-m-d') }}" required>
                                    <div class="input-border"></div>
                                </div>
                                @error('entry_date')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="field-group full-width">
                            <label for="description" class="field-label">
                                <i class="fas fa-align-left"></i>
                                <span>Descripción</span>
                            </label>
                            <div class="input-container">
                                <textarea class="form-input @error('description') is-invalid @enderror" 
                                    id="description" name="description" rows="4" 
                                    placeholder="Describe las características del producto...">{{ old('description', $product->description) }}</textarea>
                                <div class="input-border"></div>
                            </div>
                            @error('description')
                                <span class="error-message">{{ $message }}</span>
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
                        
                        <div class="form-grid">
                            <div class="field-group">
                                <label for="stock" class="field-label">
                                    <i class="fas fa-cubes"></i>
                                    <span>Stock Actual</span>
                                </label>
                                <div class="input-container">
                                    <input type="number" class="form-input @error('stock') is-invalid @enderror"
                                        id="stock" name="stock" value="{{ old('stock', $product->stock) }}" min="0" required>
                                    <div class="input-border"></div>
                                </div>
                                @error('stock')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="field-group">
                                <label for="min_stock" class="field-label">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <span>Stock Mínimo</span>
                                </label>
                                <div class="input-container">
                                    <input type="number" class="form-input @error('min_stock') is-invalid @enderror"
                                        id="min_stock" name="min_stock" value="{{ old('min_stock', $product->min_stock) }}" min="0" required>
                                    <div class="input-border"></div>
                                </div>
                                @error('min_stock')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="field-group">
                                <label for="max_stock" class="field-label">
                                    <i class="fas fa-warehouse"></i>
                                    <span>Stock Máximo</span>
                                </label>
                                <div class="input-container">
                                    <input type="number" class="form-input @error('max_stock') is-invalid @enderror"
                                        id="max_stock" name="max_stock" value="{{ old('max_stock', $product->max_stock) }}" min="0" required>
                                    <div class="input-border"></div>
                                </div>
                                @error('max_stock')
                                    <span class="error-message">{{ $message }}</span>
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
                        
                        <div class="form-grid">
                            <div class="field-group">
                                <label for="purchase_price" class="field-label">
                                    <i class="fas fa-shopping-cart"></i>
                                    <span>Precio de Compra</span>
                                </label>
                                <div class="input-container">
                                    <span class="currency-symbol">{{ $currency->symbol }}</span>
                                    <input type="number" class="form-input @error('purchase_price') is-invalid @enderror"
                                        id="purchase_price" name="purchase_price" 
                                        value="{{ old('purchase_price', $product->purchase_price) }}" 
                                        step="0.01" min="0" required>
                                    <div class="input-border"></div>
                                </div>
                                @error('purchase_price')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="field-group">
                                <label for="sale_price" class="field-label">
                                    <i class="fas fa-cash-register"></i>
                                    <span>Precio de Venta</span>
                                </label>
                                <div class="input-container">
                                    <span class="currency-symbol">{{ $currency->symbol }}</span>
                                    <input type="number" class="form-input @error('sale_price') is-invalid @enderror"
                                        id="sale_price" name="sale_price" 
                                        value="{{ old('sale_price', $product->sale_price) }}" 
                                        step="0.01" min="0" required>
                                    <div class="input-border"></div>
                                </div>
                                @error('sale_price')
                                    <span class="error-message">{{ $message }}</span>
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
