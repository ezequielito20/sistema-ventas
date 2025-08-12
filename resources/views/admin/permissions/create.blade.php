@extends('layouts.app')

@section('title', 'Crear Permiso')

@section('content')
<div class="space-y-6">
    <!-- Header con gradiente -->
    <div class="page-header">
        <div class="header-content">
            <div class="header-left">
                <div class="header-icon">
                    <i class="fas fa-plus"></i>
                </div>
                <div class="header-text">
                    <h1 class="page-title">Crear Permiso</h1>
                    <p class="page-subtitle">Crea un nuevo permiso para el sistema</p>
                </div>
            </div>
            <a href="{{ route('admin.permissions.index') }}" class="btn-back">
                <i class="fas fa-arrow-left mr-2"></i>
                Volver
            </a>
        </div>
    </div>

    <!-- Formulario -->
    <div class="form-container">
        <div class="form-card">
            <div class="form-card-header">
                <div class="header-icon-container">
                    <div class="header-icon-bg">
                        <i class="fas fa-plus"></i>
                    </div>
                </div>
                <div class="header-content">
                    <h3 class="form-card-title">Nuevo Permiso</h3>
                    <p class="form-card-subtitle">Define los datos del nuevo permiso</p>
                </div>
            </div>
            
            <div class="form-card-body">
                <form action="{{ route('admin.permissions.store') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label for="name" class="form-label">
                            <i class="fas fa-tag label-icon"></i>
                            Nombre del Permiso <span class="required">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-icon">
                                <i class="fas fa-key"></i>
                            </span>
                            <input type="text" name="name" id="name"
                                class="form-input {{ $errors->has('name') ? 'is-invalid' : '' }}"
                                value="{{ old('name') }}" 
                                required
                                placeholder="ejemplo: usuarios.crear">
                        </div>
                        @if ($errors->has('name'))
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $errors->first('name') }}
                            </div>
                        @endif
                        <div class="form-hint">
                            <i class="fas fa-info-circle"></i>
                            El nombre debe ser único y seguir el formato: modulo.accion (ej: usuarios.crear, productos.editar)
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="guard_name" class="form-label">
                            <i class="fas fa-shield-alt label-icon"></i>
                            Guard <span class="required">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-icon">
                                <i class="fas fa-shield-alt"></i>
                            </span>
                            <select name="guard_name" id="guard_name"
                                class="form-input {{ $errors->has('guard_name') ? 'is-invalid' : '' }}"
                                required>
                                <option value="">Seleccionar guard</option>
                                <option value="web" {{ old('guard_name') == 'web' ? 'selected' : '' }}>Web</option>
                                <option value="api" {{ old('guard_name') == 'api' ? 'selected' : '' }}>API</option>
                            </select>
                        </div>
                        @if ($errors->has('guard_name'))
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $errors->first('guard_name') }}
                            </div>
                        @endif
                        <div class="form-hint">
                            <i class="fas fa-info-circle"></i>
                            El guard define el contexto de autenticación para este permiso
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('admin.permissions.index') }}" class="btn-secondary">
                            <i class="fas fa-times mr-2"></i>
                            Cancelar
                        </a>
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save mr-2"></i>
                            Crear Permiso
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('css')
<style>
    /* Estilos mejorados con más color */
    .space-y-6 > * + * {
        margin-top: 2rem;
    }
    
    /* Header de la página */
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 2rem;
        border-radius: 16px;
        color: white;
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        margin-bottom: 2rem;
    }
    
    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .header-left {
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }
    
    .header-icon {
        width: 60px;
        height: 60px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }
    
    .page-title {
        font-size: 2rem;
        font-weight: 800;
        margin: 0;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .page-subtitle {
        font-size: 1rem;
        opacity: 0.9;
        margin: 0.5rem 0 0 0;
    }
    
    .btn-back {
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
    }
    
    .btn-back:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-2px);
        color: white;
    }
    
    /* Contenedor del formulario */
    .form-container {
        max-width: 800px;
        margin: 0 auto;
    }
    
    .form-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
        overflow: hidden;
    }
    
    /* Header del formulario */
    .form-card-header {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        padding: 2rem;
        display: flex;
        align-items: center;
        gap: 1.5rem;
        border-bottom: 2px solid #e5e7eb;
    }
    
    .header-icon-container {
        display: flex;
        align-items: center;
    }
    
    .header-icon-bg {
        width: 70px;
        height: 70px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.75rem;
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
    }
    
    .form-card-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }
    
    .form-card-subtitle {
        color: #64748b;
        margin: 0.5rem 0 0 0;
        font-size: 0.95rem;
    }
    
    /* Cuerpo del formulario */
    .form-card-body {
        padding: 2.5rem;
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    }
    
    /* Grupos de formulario */
    .form-group {
        margin-bottom: 2rem;
    }
    
    .form-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.75rem;
        font-size: 0.95rem;
    }
    
    .label-icon {
        color: #667eea;
        font-size: 0.875rem;
    }
    
    .required {
        color: #ef4444;
        font-weight: 700;
    }
    
    /* Input group */
    .input-group {
        display: flex;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        border: 2px solid #e5e7eb;
        transition: all 0.3s ease;
    }
    
    .input-group:focus-within {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .input-group-icon {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1rem 1.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 60px;
        font-size: 1.1rem;
    }
    
    .form-input {
        flex: 1;
        padding: 1rem 1.25rem;
        border: none;
        font-size: 1rem;
        background: white;
        color: #374151;
    }
    
    .form-input:focus {
        outline: none;
        background: #fafbfc;
    }
    
    .form-input.is-invalid {
        background: #fef2f2;
    }
    
    .form-input::placeholder {
        color: #9ca3af;
        font-style: italic;
    }
    
    /* Mensajes de error */
    .error-message {
        color: #ef4444;
        font-size: 0.875rem;
        margin-top: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem;
        background: #fef2f2;
        border-radius: 8px;
        border-left: 4px solid #ef4444;
    }
    
    /* Hints del formulario */
    .form-hint {
        color: #6b7280;
        font-size: 0.875rem;
        margin-top: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem;
        background: #f0f9ff;
        border-radius: 8px;
        border-left: 4px solid #0ea5e9;
    }
    
    /* Acciones del formulario */
    .form-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        padding-top: 2rem;
        border-top: 2px solid #f1f5f9;
        margin-top: 2rem;
    }
    
    /* Botones */
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        padding: 1rem 2rem;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
    }
    
    .btn-secondary {
        background: white;
        border: 2px solid #e5e7eb;
        color: #64748b;
        padding: 1rem 2rem;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.95rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }
    
    .btn-secondary:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #475569;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    /* Responsividad */
    @media (max-width: 768px) {
        .page-header {
            padding: 1.5rem;
            margin: 0 1rem 2rem 1rem;
        }
        
        .header-content {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }
        
        .header-left {
            flex-direction: column;
            gap: 1rem;
        }
        
        .page-title {
            font-size: 1.5rem;
        }
        
        .header-icon {
            width: 50px;
            height: 50px;
            font-size: 1.25rem;
        }
        
        .form-container {
            margin: 0 1rem;
        }
        
        .form-card-header {
            padding: 1.5rem;
            flex-direction: column;
            text-align: center;
            gap: 1rem;
        }
        
        .header-icon-bg {
            width: 60px;
            height: 60px;
            font-size: 1.5rem;
        }
        
        .form-card-body {
            padding: 1.5rem;
        }
        
        .form-actions {
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .btn-primary,
        .btn-secondary {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@push('js')
<script>
    $(document).ready(function() {
        // Convertir automáticamente a minúsculas y reemplazar espacios por puntos
        $('#name').on('input', function() {
            $(this).val($(this).val().toLowerCase().replace(/\s+/g, '.'));
        });
        
        // Efecto de carga en el botón de submit
        $('form').on('submit', function() {
            const submitBtn = $(this).find('button[type="submit"]');
            submitBtn.prop('disabled', true);
            submitBtn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Creando...');
        });
    });
</script>
@endpush
@endsection
