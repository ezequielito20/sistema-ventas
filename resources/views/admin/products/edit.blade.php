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
                            <div class="image-preview" id="imagePreview">
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
<style>
    :root {
        --primary-color: #667eea;
        --primary-light: #764ba2;
        --secondary-color: #f093fb;
        --success-color: #10b981;
        --warning-color: #f59e0b;
        --danger-color: #ef4444;
        --dark-color: #1f2937;
        --light-color: #f8fafc;
        --gradient-primary: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
        --gradient-secondary: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        --border-radius: 12px;
        --border-radius-lg: 16px;
        --border-radius-xl: 20px;
    }

    /* Fondo de página */
    .page-background {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        z-index: -1;
    }

    .page-background::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%236366f1' fill-opacity='0.03'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3Ccircle cx='10' cy='10' r='1'/%3E%3Ccircle cx='50' cy='10' r='1'/%3E%3Ccircle cx='10' cy='50' r='1'/%3E%3Ccircle cx='50' cy='50' r='1'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        background-size: 60px 60px;
        /* Sin animación para mayor velocidad */
    }

    .main-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 1.5rem;
        position: relative;
        z-index: 1;
    }

    /* Header flotante */
    .floating-header {
        position: relative;
        width: 100%;
        padding: 1rem 1.5rem;
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 20px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
        z-index: 10;
        margin-bottom: 1.5rem;
    }

    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        max-width: 1200px;
        margin: 0 auto;
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .header-icon-wrapper {
        position: relative;
        width: 48px;
        height: 48px;
    }

    .header-icon {
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(139, 92, 246, 0.9) 0%, rgba(59, 130, 246, 0.9) 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.4rem;
        position: relative;
        z-index: 1;
        box-shadow: 0 4px 20px rgba(139, 92, 246, 0.3);
    }

    .icon-glow {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 120%;
        height: 120%;
        background: radial-gradient(circle, rgba(139, 92, 246, 0.2) 0%, transparent 70%);
        border-radius: 50%;
        opacity: 0.6;
        z-index: -1;
        /* Sin animación para mayor velocidad */
    }

    .header-text {
        flex: 1;
    }

    .header-title {
        font-size: 1.8rem;
        font-weight: 700;
        background: linear-gradient(135deg, #1e293b 0%, #475569 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin: 0;
        line-height: 1.2;
    }

    .header-subtitle {
        font-size: 0.95rem;
        color: #64748b;
        margin: 0.25rem 0 0 0;
        font-weight: 500;
    }

    .header-actions {
        display: flex;
        gap: 0.75rem;
    }

    .btn-glass {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.25rem;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        transition: all 0.2s ease;
        text-decoration: none;
        color: inherit;
    }

    .btn-secondary-glass {
        background: rgba(255, 255, 255, 0.2);
        color: #475569;
        border: 1px solid rgba(255, 255, 255, 0.3);
        backdrop-filter: blur(10px);
    }

    .btn-secondary-glass:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-1px);
        color: #1e293b;
    }

    .btn-ripple {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: scale(0);
        transition: transform 0.3s ease;
        pointer-events: none;
    }

    /* Contenedor del formulario */
    .form-container {
        position: relative;
        z-index: 1;
    }

    /* Tarjeta del formulario */
    .form-card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(20px);
        border-radius: var(--border-radius-xl);
        box-shadow: var(--shadow-xl);
        border: 1px solid rgba(255, 255, 255, 0.3);
        overflow: hidden;
        margin-bottom: 1.5rem;
    }

    .card-header {
        background: var(--gradient-primary);
        padding: 1.5rem;
        color: white;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .card-header .header-icon {
        width: 40px;
        height: 40px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }

    .card-header .header-text h3 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 600;
    }

    .card-header .header-text p {
        margin: 0.25rem 0 0 0;
        opacity: 0.9;
        font-size: 0.875rem;
    }

    .card-body {
        padding: 2rem;
    }

    /* Secciones del formulario */
    .form-section {
        margin-bottom: 2rem;
        padding: 1.5rem;
        background: rgba(255, 255, 255, 0.5);
        backdrop-filter: blur(10px);
        border-radius: 16px;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .section-title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--dark-color);
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid rgba(102, 126, 234, 0.1);
    }

    .section-title i {
        color: var(--primary-color);
        font-size: 1rem;
        background: rgba(102, 126, 234, 0.1);
        padding: 0.5rem;
        border-radius: 8px;
    }

    .full-width {
        grid-column: 1 / -1;
    }

    /* Grid del formulario */
    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    /* Grupos de campos */
    .field-group {
        display: flex;
        flex-direction: column;
    }

    .field-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
        color: var(--dark-color);
        margin-bottom: 0.75rem;
        font-size: 0.875rem;
    }

    .field-label i {
        color: var(--primary-color);
        font-size: 0.875rem;
    }

    /* Contenedor de input */
    .input-container {
        position: relative;
        display: flex;
        align-items: center;
    }

    .form-input {
        width: 100%;
        padding: 0.875rem 1rem;
        border: 2px solid #e5e7eb;
        border-radius: var(--border-radius);
        font-size: 0.875rem;
        background: white;
        transition: all 0.2s ease;
        position: relative;
        z-index: 1;
    }

    .form-input:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .form-input.is-invalid {
        border-color: var(--danger-color);
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }

    .input-border {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: var(--gradient-primary);
        transform: scaleX(0);
        transition: transform 0.3s ease;
        z-index: 2;
    }

    .form-input:focus + .input-border {
        transform: scaleX(1);
    }

    /* Símbolo de moneda */
    .currency-symbol {
        position: absolute;
        left: 1rem;
        color: #6b7280;
        font-weight: 600;
        z-index: 2;
        pointer-events: none;
    }

    .form-input[type="number"] {
        padding-left: 2.5rem;
    }

    /* Sección de upload de imagen */
    .image-upload-section {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .image-preview {
        width: 100%;
        height: 250px;
        border: 3px dashed #d1d5db;
        border-radius: var(--border-radius-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        transition: all 0.2s ease;
    }

    .image-preview:hover {
        border-color: var(--primary-color);
        background-color: #f8fafc;
    }

    @if($product->image)
        .image-preview {
            background-image: url('{{ $product->image_url }}');
            border: 3px solid var(--primary-color);
        }
    @endif

    .upload-placeholder {
        text-align: center;
        color: #6b7280;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.75rem;
    }

    .upload-icon {
        width: 60px;
        height: 60px;
        background: rgba(102, 126, 234, 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-color);
        font-size: 1.5rem;
    }

    .upload-placeholder h4 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--dark-color);
    }

    .upload-placeholder p {
        margin: 0;
        font-size: 0.875rem;
        color: #6b7280;
    }

    .upload-info {
        font-size: 0.875rem;
        color: #94a3b8;
        background: rgba(102, 126, 234, 0.1);
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 500;
    }

    .image-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(transparent, rgba(0, 0, 0, 0.7));
        padding: 1rem;
        border-radius: 0 0 16px 16px;
    }

    .image-info {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: white;
    }

    .image-info i {
        color: var(--success-color);
        font-size: 1.1rem;
    }

    .image-info span {
        font-size: 0.875rem;
        font-weight: 500;
    }

    .file-input-wrapper {
        display: flex;
        justify-content: center;
    }

    .file-input {
        display: none;
    }

    .file-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        background: var(--gradient-primary);
        color: white;
        border-radius: var(--border-radius);
        cursor: pointer;
        transition: all 0.2s ease;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .file-label:hover {
        transform: translateY(-1px);
        box-shadow: var(--shadow-md);
    }

    /* Indicador de beneficio */
    .profit-indicator {
        margin-top: 1rem;
        padding: 1rem;
        border-radius: var(--border-radius);
        background: #f0f9ff;
        border: 1px solid #0ea5e9;
    }

    .profit-indicator.alert-danger {
        background: #fef2f2;
        border-color: var(--danger-color);
    }

    .profit-indicator.alert-warning {
        background: #fffbeb;
        border-color: var(--warning-color);
    }

    .profit-indicator.alert-success {
        background: #f0fdf4;
        border-color: var(--success-color);
    }

    .profit-content {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .profit-icon {
        width: 32px;
        height: 32px;
        background: rgba(59, 130, 246, 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--info-color);
    }

    .profit-info {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .profit-label {
        font-size: 0.875rem;
        color: #6b7280;
    }

    .profit-value {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--dark-color);
    }

    /* Mensajes de error */
    .error-message {
        color: var(--danger-color);
        font-size: 0.875rem;
        margin-top: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .error-message::before {
        content: '⚠';
        font-size: 0.75rem;
    }

    /* Acciones del formulario */
    .form-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 2px solid rgba(102, 126, 234, 0.1);
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
    }

    .action-links {
        display: flex;
        gap: 1rem;
    }

    /* Botones modernos */
    .btn-modern {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.875rem 1.5rem;
        border: none;
        border-radius: var(--border-radius);
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        transition: all 0.2s ease;
        text-decoration: none;
        color: white;
    }

    .btn-success {
        background: var(--success-color);
    }

    .btn-secondary {
        background: #6b7280;
    }

    .btn-content {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        position: relative;
        z-index: 1;
    }

    .btn-bg {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.1);
        transform: scaleX(0);
        transition: transform 0.3s ease;
        transform-origin: left;
    }

    .btn-modern:hover .btn-bg {
        transform: scaleX(1);
    }

    .btn-modern:hover {
        transform: translateY(-1px);
        box-shadow: var(--shadow-md);
    }

    .link-secondary {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.25rem;
        color: #6b7280;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.875rem;
        border-radius: var(--border-radius);
        transition: all 0.2s ease;
    }

    .link-secondary:hover {
        background: rgba(107, 114, 128, 0.1);
        color: #374151;
        text-decoration: none;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .main-container {
            padding: 1rem;
        }

        .floating-header {
            padding: 1rem;
        }

        .header-title {
            font-size: 1.5rem;
        }

        .header-subtitle {
            font-size: 0.875rem;
        }

        .header-content {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }

        .card-body {
            padding: 1.5rem;
        }

        .form-section {
            padding: 1rem;
        }

        .form-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .section-title {
            flex-direction: column;
            text-align: center;
            gap: 0.5rem;
        }

        .image-preview {
            height: 200px;
        }

        .form-actions {
            flex-direction: column;
            gap: 1rem;
        }

        .action-buttons {
            width: 100%;
            justify-content: center;
        }

        .action-links {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 480px) {
        .floating-header {
            padding: 0.75rem;
        }

        .header-title {
            font-size: 1.25rem;
        }

        .card-body {
            padding: 1rem;
        }

        .form-section {
            padding: 0.75rem;
        }

        .image-preview {
            height: 180px;
        }

        .upload-icon {
            width: 50px;
            height: 50px;
            font-size: 1.25rem;
        }

        .upload-placeholder h4 {
            font-size: 1rem;
        }

        .btn-modern {
            padding: 0.75rem 1.25rem;
            font-size: 0.8rem;
        }
    }

    /* Sin animaciones para mayor velocidad */
</style>
@endpush

@push('js')
<script>
    // Variables globales
    let originalFormData = {};
    let formData = {};

    // Inicializar la página
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Product edit page loaded');
        initializeForm();
        initializeEventListeners();
        saveOriginalFormData();
    });

    // Guardar datos originales del formulario
    function saveOriginalFormData() {
        const inputs = document.querySelectorAll('#productForm input, #productForm select, #productForm textarea');
        
        originalFormData = {};
        inputs.forEach(input => {
            if (input.type !== 'file') {
                originalFormData[input.name] = input.value;
            }
        });
    }

    // Inicializar el formulario
    function initializeForm() {
        console.log('Initializing form...');
        initializeImagePreview();
        initializeProfitCalculator();
        restoreFormData();
    }

    // Validar formulario completo
    function validateForm() {
        const requiredFields = [
            'code', 'name', 'category_id', 'entry_date', 
            'stock', 'min_stock', 'max_stock', 'purchase_price', 'sale_price'
        ];
        
        let isValid = true;
        let firstErrorField = null;

        requiredFields.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (field && field.hasAttribute('required') && !field.value.trim()) {
                showFieldError(field, 'Este campo es requerido');
                isValid = false;
                if (!firstErrorField) firstErrorField = field;
            } else if (field) {
                clearFieldError(field);
            }
        });

        // Validaciones específicas
        const stock = document.getElementById('stock');
        const minStock = document.getElementById('min_stock');
        const maxStock = document.getElementById('max_stock');
        const purchasePrice = document.getElementById('purchase_price');
        const salePrice = document.getElementById('sale_price');

        if (stock && parseInt(stock.value) < 0) {
            showFieldError(stock, 'El stock no puede ser negativo');
            isValid = false;
            if (!firstErrorField) firstErrorField = stock;
        }

        if (minStock && maxStock) {
            const minValue = parseInt(minStock.value) || 0;
            const maxValue = parseInt(maxStock.value) || 0;
            if (minValue >= maxValue && maxValue > 0) {
                showFieldError(maxStock, 'El stock máximo debe ser mayor que el stock mínimo');
                isValid = false;
                if (!firstErrorField) firstErrorField = maxStock;
            }
        }

        if (purchasePrice && parseFloat(purchasePrice.value) < 0) {
            showFieldError(purchasePrice, 'El precio de compra no puede ser negativo');
            isValid = false;
            if (!firstErrorField) firstErrorField = purchasePrice;
        }

        if (salePrice && parseFloat(salePrice.value) < 0) {
            showFieldError(salePrice, 'El precio de venta no puede ser negativo');
            isValid = false;
            if (!firstErrorField) firstErrorField = salePrice;
        }

        // Validar imagen si se seleccionó
        const imageInput = document.getElementById('image');
        if (imageInput && imageInput.files.length > 0) {
            const file = imageInput.files[0];
            const maxSize = 2 * 1024 * 1024; // 2MB

            if (file.size > maxSize) {
                showAlert('Error', 'La imagen no puede ser mayor a 2MB', 'error');
                isValid = false;
            }

            if (!file.type.match('image.*')) {
                showAlert('Error', 'Por favor selecciona un archivo de imagen válido', 'error');
                isValid = false;
            }
        }

        if (!isValid && firstErrorField) {
            firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            showAlert('Error', 'Por favor completa todos los campos requeridos correctamente', 'error');
        }

        return isValid;
    }

    // Mostrar error de campo
    function showFieldError(input, message) {
        input.classList.add('is-invalid');
        let errorElement = input.parentNode.querySelector('.error-message');
        
        if (!errorElement) {
            errorElement = document.createElement('span');
            errorElement.className = 'error-message';
            input.parentNode.appendChild(errorElement);
        }
        
        errorElement.textContent = message;
    }

    // Limpiar error de campo
    function clearFieldError(input) {
        input.classList.remove('is-invalid');
        const errorElement = input.parentNode.querySelector('.error-message');
        if (errorElement) {
            errorElement.remove();
        }
    }

    // Guardar datos del formulario
    function saveFormData() {
        const inputs = document.querySelectorAll('#productForm input, #productForm select, #productForm textarea');
        
        formData = {};
        inputs.forEach(input => {
            if (input.type === 'file') {
                // Para archivos, guardamos el nombre si existe
                if (input.files.length > 0) {
                    formData[input.name] = input.files[0].name;
                }
            } else {
                formData[input.name] = input.value;
            }
        });

        // Guardar en localStorage
        localStorage.setItem('productEditFormData', JSON.stringify(formData));
    }

    // Restaurar datos del formulario
    function restoreFormData() {
        const savedData = localStorage.getItem('productEditFormData');
        if (savedData) {
            try {
                formData = JSON.parse(savedData);
                
                Object.keys(formData).forEach(fieldName => {
                    const input = document.querySelector(`[name="${fieldName}"]`);
                    if (input && input.type !== 'file') {
                        input.value = formData[fieldName];
                    }
                });

                // Restaurar preview de imagen si existe
                const imageName = formData['image'];
                if (imageName) {
                    const imagePreview = document.getElementById('imagePreview');
                    if (imagePreview) {
                        imagePreview.style.backgroundImage = `url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 200 200"><rect width="200" height="200" fill="%23f3f4f6"/><text x="100" y="100" text-anchor="middle" dy=".3em" fill="%236b7280" font-family="Arial" font-size="14">${imageName}</text></svg>')`;
                        imagePreview.style.border = '3px solid var(--primary-color)';
                        
                        const placeholder = imagePreview.querySelector('.upload-placeholder');
                        if (placeholder) {
                            placeholder.style.display = 'none';
                        }
                    }
                }
            } catch (e) {
                console.log('Error restoring form data:', e);
            }
        }
    }

    // Restaurar formulario a valores originales
    function restoreForm() {
        if (confirm('¿Estás seguro de que quieres restaurar todos los campos a sus valores originales?')) {
            Object.keys(originalFormData).forEach(fieldName => {
                const input = document.querySelector(`[name="${fieldName}"]`);
                if (input) {
                    input.value = originalFormData[fieldName];
                }
            });

            // Restaurar imagen original
            const imagePreview = document.getElementById('imagePreview');
            if (imagePreview) {
                @if($product->image)
                    imagePreview.style.backgroundImage = 'url("{{ $product->image_url }}")';
                    imagePreview.style.border = '3px solid var(--primary-color)';
                    
                    const placeholder = imagePreview.querySelector('.upload-placeholder');
                    if (placeholder) {
                        placeholder.style.display = 'none';
                    }
                @else
                    imagePreview.style.backgroundImage = 'none';
                    imagePreview.style.border = '3px dashed #d1d5db';
                    
                    const placeholder = imagePreview.querySelector('.upload-placeholder');
                    if (placeholder) {
                        placeholder.style.display = 'flex';
                    }
                @endif
            }

            // Limpiar input de imagen
            const imageInput = document.getElementById('image');
            if (imageInput) {
                imageInput.value = '';
            }

            // Limpiar errores
            document.querySelectorAll('.is-invalid').forEach(input => {
                clearFieldError(input);
            });

            // Limpiar localStorage
            localStorage.removeItem('productEditFormData');
            
            showAlert('Éxito', 'Formulario restaurado a valores originales', 'success');
        }
    }

    // Inicializar preview de imagen
    function initializeImagePreview() {
        const imageInput = document.getElementById('image');
        const imagePreview = document.getElementById('imagePreview');
        
        if (!imageInput || !imagePreview) {
            console.log('Image elements not found, skipping image preview initialization');
            return;
        }

        const placeholder = imagePreview.querySelector('.upload-placeholder');

        imageInput.addEventListener('change', function() {
            const file = this.files[0];
            
            if (file) {
                // Validar tipo de archivo
                if (!file.type.match('image.*')) {
                    showAlert('Error', 'Por favor selecciona un archivo de imagen válido', 'error');
                    return;
                }

                // Validar tamaño (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    showAlert('Error', 'La imagen no puede ser mayor a 2MB', 'error');
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(event) {
                    imagePreview.style.backgroundImage = `url(${event.target.result})`;
                    imagePreview.style.border = '3px solid var(--primary-color)';
                    if (placeholder) {
                        placeholder.style.display = 'none';
                    }
                    
                    // Agregar overlay con información del archivo
                    const overlay = document.createElement('div');
                    overlay.className = 'image-overlay';
                    overlay.innerHTML = `
                        <div class="image-info">
                            <i class="fas fa-check-circle"></i>
                            <span>${file.name}</span>
                        </div>
                    `;
                    imagePreview.appendChild(overlay);
                };
                reader.readAsDataURL(file);
            } else {
                // Restaurar imagen original si no hay archivo seleccionado
                @if($product->image)
                    imagePreview.style.backgroundImage = 'url("{{ $product->image_url }}")';
                    imagePreview.style.border = '3px solid var(--primary-color)';
                    if (placeholder) {
                        placeholder.style.display = 'none';
                    }
                @else
                    imagePreview.style.backgroundImage = 'none';
                    imagePreview.style.border = '3px dashed #d1d5db';
                    if (placeholder) {
                        placeholder.style.display = 'flex';
                    }
                @endif
                
                const overlay = imagePreview.querySelector('.image-overlay');
                if (overlay) {
                    overlay.remove();
                }
            }
        });

        // Click en preview para seleccionar archivo
        imagePreview.addEventListener('click', function() {
            imageInput.click();
        });
    }

    // Inicializar calculadora de beneficio
    function initializeProfitCalculator() {
        const purchasePriceInput = document.getElementById('purchase_price');
        const salePriceInput = document.getElementById('sale_price');
        const profitIndicator = document.getElementById('profitIndicator');
        const profitValue = document.getElementById('profitValue');

        if (!purchasePriceInput || !salePriceInput || !profitIndicator || !profitValue) {
            console.log('Profit calculator elements not found, skipping initialization');
            return;
        }

        function calculateProfit() {
            const purchasePrice = parseFloat(purchasePriceInput.value) || 0;
            const salePrice = parseFloat(salePriceInput.value) || 0;

            if (purchasePrice > 0) {
                const profit = ((salePrice - purchasePrice) / purchasePrice) * 100;
                profitValue.textContent = profit.toFixed(2) + '%';
                profitIndicator.style.display = 'block';

                // Cambiar color según el margen
                profitIndicator.className = 'profit-indicator';
                
                if (profit < 0) {
                    profitIndicator.classList.add('alert-danger');
                    profitIndicator.querySelector('i').className = 'fas fa-arrow-down';
                } else if (profit < 20) {
                    profitIndicator.classList.add('alert-warning');
                    profitIndicator.querySelector('i').className = 'fas fa-exclamation-triangle';
                } else {
                    profitIndicator.classList.add('alert-success');
                    profitIndicator.querySelector('i').className = 'fas fa-arrow-up';
                }
            } else {
                profitIndicator.style.display = 'none';
            }
        }

        purchasePriceInput.addEventListener('input', calculateProfit);
        salePriceInput.addEventListener('input', calculateProfit);
        
        // Calcular beneficio inicial
        calculateProfit();
    }

    // Inicializar event listeners
    function initializeEventListeners() {
        console.log('Initializing event listeners...');

        // Botón restaurar formulario
        const restoreBtn = document.getElementById('restoreForm');
        if (restoreBtn) {
            restoreBtn.addEventListener('click', restoreForm);
        }

        // Validación en tiempo real
        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('blur', function() {
                if (this.hasAttribute('required') && !this.value.trim()) {
                    showFieldError(this, 'Este campo es requerido');
                } else {
                    clearFieldError(this);
                }
            });

            input.addEventListener('input', function() {
                clearFieldError(this);
                saveFormData(); // Guardar datos en tiempo real
            });
        });

        // Validación del formulario al enviar
        const productForm = document.getElementById('productForm');
        if (productForm) {
            productForm.addEventListener('submit', function(e) {
                if (!validateForm()) {
                    e.preventDefault();
                    return false;
                }

                // Mostrar indicador de carga
                const submitBtn = document.getElementById('submitProduct');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Actualizando...';
                }

                // Limpiar localStorage después de envío exitoso
                localStorage.removeItem('productEditFormData');
            });
        }

        // Guardar datos cuando se cambia la imagen
        const imageInput = document.getElementById('image');
        if (imageInput) {
            imageInput.addEventListener('change', saveFormData);
        }

        console.log('Event listeners initialized');
    }

    // Mostrar alerta
    function showAlert(title, text, icon) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: title,
                text: text,
                icon: icon,
                confirmButtonText: 'Entendido',
                timer: icon === 'success' ? 3000 : undefined,
                timerProgressBar: icon === 'success'
            });
        } else {
            alert(`${title}: ${text}`);
        }
    }

    // Mostrar notificación de éxito
    function showSuccessNotification(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¡Éxito!',
                text: message,
                icon: 'success',
                confirmButtonText: 'Entendido',
                timer: 3000,
                timerProgressBar: true
            });
        } else {
            alert('Éxito: ' + message);
        }
    }

    // Mostrar notificación de error
    function showErrorNotification(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Error',
                text: message,
                icon: 'error',
                confirmButtonText: 'Entendido'
            });
        } else {
            alert('Error: ' + message);
        }
    }
</script>
@endpush
