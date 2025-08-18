@extends('layouts.app')

@section('title', 'Crear Categoría')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/categories/create.css') }}">
@endpush

@push('js')
    <script src="{{ asset('js/admin/categories/create.js') }}" defer></script>
@endpush

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
                        <i class="fas fa-tag"></i>
                    </div>
                    <div class="icon-glow"></div>
                </div>
                <div class="header-text">
                    <h1 class="header-title">Crear Nueva Categoría</h1>
                    <p class="header-subtitle">Organiza tus productos con categorías personalizadas</p>
                </div>
            </div>
            <div class="header-actions">
                <button onclick="window.categoryCreate.goBack()" class="btn-glass btn-secondary-glass">
                    <i class="fas fa-arrow-left"></i>
                    <span>Volver</span>
                    <div class="btn-ripple"></div>
                </button>
                <button type="submit" form="categoryForm" class="btn-glass btn-primary-glass">
                    <i class="fas fa-save"></i>
                    <span>Guardar</span>
                    <div class="btn-ripple"></div>
                </button>
            </div>
        </div>
    </div>

    <!-- Form Container -->
    <div class="form-container">
        <div class="form-card">
            <!-- Progress Indicator -->
            <div class="progress-indicator">
                <div class="progress-step active">
                    <div class="step-icon">
                        <i class="fas fa-tag"></i>
                    </div>
                    <span>Información Básica</span>
                </div>
                <div class="progress-line"></div>
                <div class="progress-step">
                    <div class="step-icon">
                        <i class="fas fa-check"></i>
                    </div>
                    <span>Completar</span>
                </div>
            </div>
        
            <!-- Form Content -->
            <div class="form-content">
                <form id="categoryForm" action="{{ route('admin.categories.store') }}" method="POST">
                    @csrf
                    
                    <!-- Name Field -->
                    <div class="field-group">
                        <div class="field-wrapper">
                            <label for="name" class="field-label">
                                <div class="label-content">
                                    <div class="label-icon">
                                        <i class="fas fa-tag"></i>
                                    </div>
                                    <span>Nombre de la Categoría</span>
                                    <span class="required-indicator">*</span>
                                </div>
                            </label>
                            
                            <div class="input-container">
                                <div class="input-wrapper">
                                    <div class="input-icon">
                                        <i class="fas fa-tag"></i>
                                    </div>
                                    <input type="text" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name') }}" 
                                           class="modern-input @error('name') error @enderror"
                                           placeholder="Ej: Electrónicos, Ropa, Hogar..."
                                           required>
                                    <div class="input-border"></div>
                                    <div class="input-focus-effect"></div>
                                </div>
                                
                                @error('name')
                                    <div class="error-message">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <span>{{ $message }}</span>
                                    </div>
                                @enderror
                                
                                <div class="field-help">
                                    <i class="fas fa-info-circle"></i>
                                    <span>Nombre descriptivo y único (máximo 255 caracteres)</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description Field -->
                    <div class="field-group">
                        <div class="field-wrapper">
                            <label for="description" class="field-label">
                                <div class="label-content">
                                    <div class="label-icon">
                                        <i class="fas fa-align-left"></i>
                                    </div>
                                    <span>Descripción</span>
                                    <span class="optional-indicator">Opcional</span>
                                </div>
                            </label>
                            
                            <div class="input-container">
                                <div class="textarea-wrapper">
                                    <div class="input-icon">
                                        <i class="fas fa-align-left"></i>
                                    </div>
                                    <textarea id="description" 
                                              name="description" 
                                              rows="4" 
                                              class="modern-textarea @error('description') error @enderror"
                                              placeholder="Describe qué tipo de productos incluye esta categoría...">{{ old('description') }}</textarea>
                                    <div class="input-border"></div>
                                    <div class="input-focus-effect"></div>
                                </div>
                                
                                @error('description')
                                    <div class="error-message">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <span>{{ $message }}</span>
                                    </div>
                                @enderror
                                
                                <div class="field-help">
                                    <i class="fas fa-lightbulb"></i>
                                    <span>Ayuda a organizar y encontrar productos más fácilmente</span>
                                    <span class="char-counter" id="charCounter"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="form-actions">
                        <div class="actions-left">
                            <button type="button" onclick="window.categoryCreate.resetForm()" class="btn-modern btn-reset">
                                <div class="btn-content">
                                    <i class="fas fa-undo"></i>
                                    <span>Limpiar</span>
                                </div>
                                <div class="btn-bg"></div>
                            </button>
                        </div>
                        
                        <div class="actions-right">
                            <button type="button" onclick="window.categoryCreate.goBack()" class="btn-modern btn-cancel">
                                <div class="btn-content">
                                    <i class="fas fa-times"></i>
                                    <span>Cancelar</span>
                                </div>
                                <div class="btn-bg"></div>
                            </button>
                            
                            <button type="submit" id="submitCategory" class="btn-modern btn-submit">
                                <div class="btn-content">
                                    <i class="fas fa-save"></i>
                                    <span>Crear Categoría</span>
                                </div>
                                <div class="btn-bg"></div>
                                <div class="btn-shine"></div>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Success Preview Card -->
        <div class="preview-card" id="previewCard">
            <div class="preview-header">
                <div class="preview-icon">
                    <i class="fas fa-eye"></i>
                </div>
                <h3>Vista Previa</h3>
            </div>
            <div class="preview-content">
                <div class="category-preview" id="categoryPreview">
                    <div class="category-icon">
                        <i class="fas fa-tag"></i>
                    </div>
                    <div class="category-info">
                        <h4 id="previewName">Nombre de la categoría</h4>
                        <p id="previewDescription">Descripción de la categoría</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

