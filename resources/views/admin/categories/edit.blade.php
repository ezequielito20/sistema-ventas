@extends('layouts.app')

@section('title', 'Editar Categoría')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/categories/edit.css') }}">
@endpush

@push('js')
    <script src="{{ asset('js/admin/categories/edit.js') }}" defer></script>
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
                        <i class="fas fa-edit"></i>
                    </div>
                    <div class="icon-glow"></div>
                </div>
                <div class="header-text">
                    <h1 class="header-title">Editar Categoría</h1>
                    <p class="header-subtitle">Actualiza la información de "{{ $category->name }}"</p>
                </div>
            </div>
            <div class="header-actions">
                <button onclick="window.categoryEdit.goBack()" class="btn-glass btn-secondary-glass">
                    <i class="fas fa-arrow-left"></i>
                    <span>Volver</span>
                    <div class="btn-ripple"></div>
                </button>
                <button type="submit" form="categoryForm" class="btn-glass btn-primary-glass">
                    <i class="fas fa-save"></i>
                    <span>Actualizar</span>
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
                        <i class="fas fa-edit"></i>
                    </div>
                    <span>Editar Información</span>
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
                <form id="categoryForm" action="{{ route('admin.categories.update', $category->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
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
                                           value="{{ old('name', $category->name) }}" 
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
                                              placeholder="Describe qué tipo de productos incluye esta categoría...">{{ old('description', $category->description) }}</textarea>
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

                        
                        <div class="actions-right">
                            <button type="button" onclick="window.categoryEdit.goBack()" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition-all duration-300">
                                <i class="fas fa-times mr-2"></i>
                                <span>Cancelar</span>
                            </button>
                            
                            <button type="submit" id="submitCategory" class="btn-modern btn-submit">
                                <div class="btn-content">
                                    <i class="fas fa-save"></i>
                                    <span>Actualizar Categoría</span>
                                </div>
                                <div class="btn-bg"></div>
                                <div class="btn-shine"></div>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Category Info Card -->
        <div class="info-card" id="infoCard">
            <div class="info-header">
                <div class="info-icon">
                    <i class="fas fa-info-circle"></i>
                </div>
                <h3>Información de la Categoría</h3>
            </div>
            <div class="info-content">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">ID de Categoría</div>
                        <div class="info-value">{{ $category->id }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Fecha de Creación</div>
                        <div class="info-value">{{ \Carbon\Carbon::parse($category->created_at)->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Última Actualización</div>
                        <div class="info-value">{{ \Carbon\Carbon::parse($category->updated_at)->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Estado</div>
                        <div class="info-value">
                            <span class="status-badge status-active">
                                <i class="fas fa-check-circle"></i>
                                Activa
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection