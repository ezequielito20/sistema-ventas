@extends('layouts.app')

@section('title', 'Crear Producto')

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
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <div class="icon-glow"></div>
                </div>
                <div class="header-text">
                    <h1 class="header-title">Crear Nuevo Producto</h1>
                    <p class="header-subtitle">Agrega un nuevo producto al inventario con toda la información necesaria</p>
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
        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" id="productForm">
            @csrf
            
            <!-- Form Card -->
            <div class="form-card">
                <div class="card-header">
                    <div class="header-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="header-text">
                        <h3>Información del Producto</h3>
                        <p>Completa todos los campos para crear el producto</p>
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
                                        id="code" name="code" value="{{ old('code') }}" placeholder="Ej: PROD001" required>
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
                                        id="name" name="name" value="{{ old('name') }}" placeholder="Nombre del producto" required>
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
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}"
                                                {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                                        id="entry_date" name="entry_date" value="{{ old('entry_date', date('Y-m-d')) }}"
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
                                    placeholder="Describe las características del producto...">{{ old('description') }}</textarea>
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
                                <div class="upload-placeholder">
                                    <div class="upload-icon">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                    </div>
                                    <h4>Selecciona una imagen</h4>
                                    <p>Haz clic para subir una imagen del producto</p>
                                    <span class="upload-info">JPG, PNG, GIF hasta 2MB</span>
                                </div>
                            </div>
                            
                            <div class="file-input-wrapper">
                                <input type="file" class="file-input @error('image') is-invalid @enderror"
                                    id="image" name="image" accept="image/*">
                                <label for="image" class="file-label">
                                    <i class="fas fa-camera"></i>
                                    <span>Seleccionar Imagen</span>
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
                                        id="stock" name="stock" value="{{ old('stock', 0) }}" min="0" required>
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
                                        id="min_stock" name="min_stock" value="{{ old('min_stock', 0) }}" min="0" required>
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
                                        id="max_stock" name="max_stock" value="{{ old('max_stock', 0) }}" min="0" required>
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
                                        id="purchase_price" name="purchase_price" value="{{ old('purchase_price', 0) }}" 
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
                                        id="sale_price" name="sale_price" value="{{ old('sale_price', 0) }}" 
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
                            <span>Guardar Producto</span>
                        </div>
                        <div class="btn-bg"></div>
                    </button>
                    
                    <button type="button" class="btn-modern btn-secondary" id="clearForm">
                        <div class="btn-content">
                            <i class="fas fa-eraser"></i>
                            <span>Limpiar</span>
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
    /* Variables CSS */
    :root {
        --primary-color: #667eea;
        --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --success-color: #10b981;
        --warning-color: #f59e0b;
        --info-color: #3b82f6;
        --danger-color: #ef4444;
        --dark-color: #1f2937;
        --light-color: #f8fafc;
        --border-color: #e2e8f0;
        --shadow-light: 0 2px 8px rgba(0,0,0,0.07);
        --shadow-medium: 0 4px 16px rgba(0,0,0,0.12);
        --shadow-heavy: 0 20px 40px rgba(0,0,0,0.1);
    }

    /* Estilos básicos */
    .page-background {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: 
            radial-gradient(circle at 20% 30%, rgba(139, 92, 246, 0.08) 0%, transparent 50%),
            radial-gradient(circle at 80% 70%, rgba(59, 130, 246, 0.06) 0%, transparent 50%),
            radial-gradient(circle at 40% 80%, rgba(16, 185, 129, 0.05) 0%, transparent 50%),
            linear-gradient(135deg, #f8fafc 0%, #e2e8f0 50%, #f1f5f9 100%);
        z-index: -1;
    }

    .page-background::before {
        content: '';
        position: absolute;
        inset: 0;
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
        font-size: 0.9rem;
        color: #64748b;
        margin-top: 0.25rem;
        font-weight: 500;
    }

    .header-actions {
        display: flex;
        gap: 1rem;
    }

    .btn-glass {
        position: relative;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.625rem 1.25rem;
        border: 1px solid transparent;
        border-radius: 12px;
        font-size: 0.85rem;
        font-weight: 600;
        transition: all 0.3s ease;
        cursor: pointer;
        text-decoration: none;
        backdrop-filter: blur(10px);
    }

    .btn-glass:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .btn-secondary-glass {
        background: rgba(255, 255, 255, 0.8);
        border-color: rgba(226, 232, 240, 0.6);
        color: #64748b;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .btn-secondary-glass:hover {
        background: rgba(248, 250, 252, 0.9);
        border-color: rgba(203, 213, 224, 0.8);
        color: #475569;
    }

    .btn-ripple {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: scale(0);
        opacity: 0.6;
        transition: transform 0.6s ease-out, opacity 0.6s ease-out;
    }

    .btn-glass:hover .btn-ripple {
        transform: scale(10);
        opacity: 0;
    }

    /* Contenedor del formulario */
    .form-container {
        max-width: 1000px;
        margin: 0 auto;
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

    /* Tarjeta del formulario */
    .form-card {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.3);
        margin-bottom: 1.5rem;
    }

    .card-header {
        background: linear-gradient(135deg, rgba(139, 92, 246, 0.9) 0%, rgba(59, 130, 246, 0.9) 100%);
        color: white;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        position: relative;
        overflow: hidden;
    }

    .card-header::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        animation: float 6s ease-in-out infinite;
    }

    .card-header .header-icon {
        width: 48px;
        height: 48px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.4rem;
        position: relative;
        z-index: 1;
        backdrop-filter: blur(10px);
        border: 2px solid rgba(255,255,255,0.2);
    }

    .card-header .header-text {
        position: relative;
        z-index: 1;
    }

    .card-header h3 {
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
        line-height: 1.2;
    }

    .card-header p {
        font-size: 0.9rem;
        opacity: 0.9;
        margin: 0.25rem 0 0 0;
    }

    .card-body {
        padding: 2rem;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.5) 0%, rgba(248, 250, 252, 0.3) 100%);
        backdrop-filter: blur(10px);
    }

    /* Grid del formulario */
    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    /* Grupos de campos */
    .field-group {
        display: flex;
        flex-direction: column;
    }

    .field-group.full-width {
        grid-column: 1 / -1;
    }

    .field-label {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-weight: 600;
        color: var(--dark-color);
        margin-bottom: 0.75rem;
        font-size: 0.9rem;
    }

    .field-label i {
        color: var(--primary-color);
        font-size: 1rem;
        background: rgba(102, 126, 234, 0.1);
        padding: 0.5rem;
        border-radius: 8px;
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
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        background: white;
        color: var(--dark-color);
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
        width: 100%;
        height: 2px;
        background: var(--primary-color);
        border-radius: 2px;
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .form-input:focus + .input-border {
        transform: scaleX(1);
    }

    /* Símbolo de moneda */
    .currency-symbol {
        position: absolute;
        left: 1rem;
        color: #64748b;
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
        gap: 1.5rem;
    }

    .image-preview {
        width: 100%;
        height: 300px;
        border: 3px dashed #d1d5db;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }

    .image-preview:hover {
        border-color: var(--primary-color);
        background-color: rgba(248, 250, 252, 0.5);
    }

    .upload-placeholder {
        text-align: center;
        color: #64748b;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1rem;
    }

    .upload-icon {
        width: 80px;
        height: 80px;
        background: rgba(102, 126, 234, 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-color);
        font-size: 2rem;
        transition: all 0.3s ease;
    }

    .image-preview:hover .upload-icon {
        background: rgba(102, 126, 234, 0.2);
        transform: scale(1.1);
    }

    .upload-placeholder h4 {
        font-size: 1.25rem;
        font-weight: 700;
        margin: 0;
        color: var(--dark-color);
    }

    .upload-placeholder p {
        font-size: 1rem;
        margin: 0;
        color: #64748b;
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
        gap: 0.75rem;
        padding: 1rem 2rem;
        background: var(--gradient-primary);
        color: white;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 600;
        font-size: 1rem;
        box-shadow: 0 4px 20px rgba(139, 92, 246, 0.3);
    }

    .file-label:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(139, 92, 246, 0.4);
    }

    /* Indicador de beneficio */
    .profit-indicator {
        margin-top: 1.5rem;
        padding: 1.5rem;
        border-radius: 16px;
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .profit-indicator.alert-danger {
        background: rgba(254, 242, 242, 0.8);
        border-color: var(--danger-color);
    }

    .profit-indicator.alert-warning {
        background: rgba(255, 251, 235, 0.8);
        border-color: var(--warning-color);
    }

    .profit-indicator.alert-success {
        background: rgba(240, 253, 244, 0.8);
        border-color: var(--success-color);
    }

    .profit-content {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .profit-icon {
        width: 48px;
        height: 48px;
        background: var(--gradient-primary);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
        box-shadow: 0 4px 20px rgba(139, 92, 246, 0.3);
    }

    .profit-info {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .profit-label {
        font-size: 0.875rem;
        color: #64748b;
        font-weight: 500;
    }

    .profit-value {
        font-size: 1.5rem;
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
        gap: 0.5rem;
        font-weight: 500;
    }

    .error-message::before {
        content: '⚠';
        font-size: 0.75rem;
        background: rgba(239, 68, 68, 0.1);
        padding: 0.25rem;
        border-radius: 4px;
    }

    /* Acciones del formulario */
    .form-actions {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.3);
        padding: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1.5rem;
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
    }

    .btn-modern {
        position: relative;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 1rem 2rem;
        border: 1px solid transparent;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 600;
        color: white;
        transition: all 0.3s ease;
        cursor: pointer;
        box-shadow: var(--shadow-medium);
        text-decoration: none;
        min-width: 140px;
    }

    .btn-modern:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-heavy);
    }

    .btn-primary {
        background: var(--gradient-primary);
        border-color: transparent;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
    }

    .btn-secondary {
        background: white;
        border-color: #e2e8f0;
        color: #64748b;
    }

    .btn-secondary:hover {
        background: #f8fafc;
        border-color: #cbd5e0;
        color: #4a5568;
    }

    .btn-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border-color: transparent;
    }

    .btn-success:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
    }

    .btn-content {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .btn-bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
        border-radius: 12px;
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: -1;
    }

    .btn-modern:hover .btn-bg {
        opacity: 1;
    }

    .action-links {
        display: flex;
        gap: 1rem;
    }

    .link-secondary {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #64748b;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.5);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(226, 232, 240, 0.6);
    }

    .link-secondary:hover {
        color: #475569;
        background: rgba(248, 250, 252, 0.8);
        transform: translateY(-1px);
        box-shadow: var(--shadow-light);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .main-container {
            padding: 1rem;
        }

        .floating-header {
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .header-content {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }

        .header-left {
            flex-direction: column;
            gap: 0.75rem;
        }

        .header-icon-wrapper {
            width: 40px;
            height: 40px;
        }

        .header-icon {
            font-size: 1.2rem;
        }

        .header-title {
            font-size: 1.5rem;
        }

        .header-subtitle {
            font-size: 0.85rem;
        }

        .progress-indicator {
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .progress-steps {
            flex-direction: column;
            gap: 1rem;
        }

        .step {
            flex-direction: row;
            gap: 0.75rem;
        }

        .step-icon {
            width: 36px;
            height: 36px;
            font-size: 0.9rem;
        }

        .step-label {
            font-size: 0.8rem;
        }

        .card-header {
            padding: 1rem;
            flex-direction: column;
            text-align: center;
            gap: 0.75rem;
        }

        .card-header .header-icon {
            width: 40px;
            height: 40px;
            font-size: 1.2rem;
        }

        .card-header h3 {
            font-size: 1.25rem;
        }

        .card-header p {
            font-size: 0.8rem;
        }

        .card-body {
            padding: 1rem;
        }

        .form-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .field-label {
            font-size: 0.85rem;
        }

        .field-label i {
            font-size: 0.9rem;
            padding: 0.4rem;
        }

        .image-preview {
            height: 250px;
        }

        .upload-icon {
            width: 60px;
            height: 60px;
            font-size: 1.5rem;
        }

        .upload-placeholder h4 {
            font-size: 1.1rem;
        }

        .upload-placeholder p {
            font-size: 0.9rem;
        }

        .file-label {
            padding: 0.75rem 1.5rem;
            font-size: 0.9rem;
        }

        .form-actions {
            flex-direction: column;
            gap: 1rem;
            padding: 1rem;
        }

        .action-buttons {
            width: 100%;
            justify-content: center;
        }

        .btn-modern {
            flex: 1;
            min-width: auto;
            padding: 0.75rem 1.5rem;
            font-size: 0.9rem;
        }

        .action-links {
            width: 100%;
            justify-content: center;
        }

        .link-secondary {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 480px) {
        .main-container {
            padding: 0.75rem;
        }

        .floating-header {
            padding: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .header-icon-wrapper {
            width: 36px;
            height: 36px;
        }

        .header-icon {
            font-size: 1rem;
        }

        .header-title {
            font-size: 1.25rem;
        }

        .header-subtitle {
            font-size: 0.8rem;
        }

        .progress-indicator {
            padding: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .step-icon {
            width: 32px;
            height: 32px;
            font-size: 0.8rem;
        }

        .step-label {
            font-size: 0.7rem;
        }

        .card-header {
            padding: 0.75rem;
        }

        .card-header .header-icon {
            width: 36px;
            height: 36px;
            font-size: 1rem;
        }

        .card-header h3 {
            font-size: 1.1rem;
        }

        .card-header p {
            font-size: 0.75rem;
        }

        .card-body {
            padding: 0.75rem;
        }

        .field-label {
            font-size: 0.8rem;
        }

        .field-label i {
            font-size: 0.8rem;
            padding: 0.3rem;
        }

        .form-input {
            padding: 0.75rem 0.875rem;
            font-size: 0.85rem;
        }

        .image-preview {
            height: 200px;
        }

        .upload-icon {
            width: 50px;
            height: 50px;
            font-size: 1.25rem;
        }

        .upload-placeholder h4 {
            font-size: 1rem;
        }

        .upload-placeholder p {
            font-size: 0.8rem;
        }

        .upload-info {
            font-size: 0.75rem;
            padding: 0.4rem 0.8rem;
        }

        .file-label {
            padding: 0.6rem 1.25rem;
            font-size: 0.85rem;
        }

        .profit-indicator {
            padding: 1rem;
        }

        .profit-icon {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }

        .profit-value {
            font-size: 1.25rem;
        }

        .form-actions {
            padding: 0.75rem;
        }

        .btn-modern {
            padding: 0.6rem 1.25rem;
            font-size: 0.85rem;
        }
    }

    /* Sin animaciones para mayor velocidad */
</style>
@endpush

@push('js')
<script>
    // Variables globales
    let formData = {};

    // Inicializar la página
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Product create page loaded');
        initializeForm();
        initializeEventListeners();
    });

    // Inicializar el formulario
    function initializeForm() {
        console.log('Initializing form...');
        initializeImagePreview();
        initializeProfitCalculator();
        initializeCodeGenerator();
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
        localStorage.setItem('productFormData', JSON.stringify(formData));
    }

    // Restaurar datos del formulario
    function restoreFormData() {
        const savedData = localStorage.getItem('productFormData');
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

    // Limpiar formulario
    function clearForm() {
        if (confirm('¿Estás seguro de que quieres limpiar todos los campos del formulario?')) {
            document.getElementById('productForm').reset();
            
            // Limpiar preview de imagen
            const imagePreview = document.getElementById('imagePreview');
            if (imagePreview) {
                imagePreview.style.backgroundImage = 'none';
                imagePreview.style.border = '3px dashed #d1d5db';
                
                const placeholder = imagePreview.querySelector('.upload-placeholder');
                if (placeholder) {
                    placeholder.style.display = 'flex';
                }
                
                const overlay = imagePreview.querySelector('.image-overlay');
                if (overlay) {
                    overlay.remove();
                }
            }

            // Limpiar errores
            document.querySelectorAll('.is-invalid').forEach(input => {
                clearFieldError(input);
            });

            // Limpiar localStorage
            localStorage.removeItem('productFormData');
            
            showAlert('Éxito', 'Formulario limpiado correctamente', 'success');
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
                imagePreview.style.backgroundImage = 'none';
                imagePreview.style.border = '3px dashed #d1d5db';
                if (placeholder) {
                    placeholder.style.display = 'flex';
                }
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
    }

    // Auto-generar código
    function initializeCodeGenerator() {
        const nameInput = document.getElementById('name');
        const codeInput = document.getElementById('code');

        if (!nameInput || !codeInput) {
            console.log('Code generator elements not found, skipping initialization');
            return;
        }

        nameInput.addEventListener('blur', function() {
            const code = codeInput.value.trim();
            const name = this.value.trim();
            
            if (!code && name) {
                const generatedCode = 'PROD' + Date.now().toString().slice(-6);
                codeInput.value = generatedCode;
            }
        });
    }

    // Inicializar event listeners
    function initializeEventListeners() {
        console.log('Initializing event listeners...');

        // Botón limpiar formulario
        const clearBtn = document.getElementById('clearForm');
        if (clearBtn) {
            clearBtn.addEventListener('click', clearForm);
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
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
                }

                // Limpiar localStorage después de envío exitoso
                localStorage.removeItem('productFormData');
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
