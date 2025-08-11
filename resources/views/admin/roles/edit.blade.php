@extends('layouts.app')

@section('title', 'Editar Rol')

@section('content')
<div class="space-y-4 sm:space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Editar Rol</h1>
            <p class="text-sm sm:text-base text-gray-600">Modifica la información del rol: {{ $role->name }}</p>
        </div>
        <div class="flex items-center">
            <a href="{{ route('admin.roles.index') }}" class="btn-outline w-full sm:w-auto text-center" style="background: white; color: #64748b; border: 2px solid #e2e8f0; padding: 0.75rem 1rem; border-radius: 8px; font-weight: 600; transition: all 0.3s; text-decoration: none; font-size: 0.875rem;">
                <i class="fas fa-arrow-left mr-2"></i>Volver al listado
            </a>
        </div>
    </div>

    {{-- Formulario --}}
    <div class="modern-card" style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border: 1px solid #e2e8f0; border-radius: 12px sm:16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        <div class="modern-card-header" style="background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%); padding: 1.5rem sm:2rem; border-radius: 12px sm:16px 12px sm:16px 0 0; border-bottom: 2px solid #e2e8f0;">
            <div class="title-content" style="display: flex; flex-col sm:flex-row; align-items: center; gap: 1.5rem; text-center sm:text-left;">
                <div class="title-icon" style="width: 60px sm:70px; height: 60px sm:70px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem sm:1.75rem;">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div>
                    <h3 style="color: #1e293b; font-size: 1.5rem sm:1.75rem; font-weight: 700; margin: 0;">Editar Rol: {{ $role->name }}</h3>
                    <p style="color: #64748b; margin: 0.75rem 0 0 0; font-size: 1rem sm:1.125rem;">Modifica la información del rol</p>
                </div>
            </div>
        </div>
        <div class="modern-card-body" style="padding: 1.5rem sm:2.5rem; background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border-radius: 0 0 12px sm:16px 12px sm:16px;">
            <form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                    {{-- Nombre del Rol --}}
                    <div class="form-group" style="margin-bottom: 2rem sm:2.5rem;">
                        <label for="name" class="font-weight-bold required" style="color: #1e293b; font-size: 1rem sm:1.125rem; margin-bottom: 0.75rem sm:1rem; display: block;">
                            Nombre del Rol
                            <span class="text-danger">*</span>
                        </label>
                        <div class="input-group" style="position: relative; display: flex; align-items: center;">
                            <div class="input-group-prepend" style="position: absolute; left: 0; top: 0; bottom: 0; z-index: 10; display: flex; align-items: center; padding-left: 1.5rem sm:2rem;">
                                
                            </div>
                            <span class="input-group-text" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 0.5rem; padding: 0.75rem; display: flex; align-items: center; justify-content: center; width: 44px sm:48px; height: 44px sm:48px;">
                                <i class="fas fa-tag" style="font-size: 1rem sm:1.125rem;"></i>
                            </span>
                            <input type="text" 
                                   name="name" 
                                   id="name" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $role->name) }}" 
                                   placeholder="Ejemplo: Editor, Supervisor, etc."
                                   required
                                   style="padding: 0.75rem 1rem 0.75rem 5rem sm:5.5rem; border: 2px solid #e2e8f0; border-radius: 0.5rem; background: white; transition: all 0.3s; width: 100%; height: 52px sm:56px; font-size: 1rem sm:1.125rem;">
                            @error('name')
                                <span class="invalid-feedback" style="color: #ef4444; font-size: 0.875rem sm:1rem; margin-top: 0.75rem; display: block;">{{ $message }}</span>
                            @enderror
                        </div>
                        <small class="text-muted" style="color: #64748b; font-size: 0.875rem sm:1rem; margin-top: 0.75rem; display: block;">
                            El nombre del rol debe ser único y descriptivo.
                        </small>
                    </div>

                <div class="card-footer" style="background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%); padding: 1.5rem sm:2.5rem; border-radius: 0 0 12px sm:16px 12px sm:16px; border-top: 2px solid #e2e8f0;">
                    <div class="text-right" style="display: flex; flex-col sm:flex-row; justify-content: flex-end; gap: 1rem sm:1.5rem;">
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary w-full sm:w-auto text-center" style="background: white; color: #64748b; border: 2px solid #e2e8f0; padding: 0.875rem sm:1rem 1.5rem sm:2rem; border-radius: 8px; font-weight: 600; transition: all 0.3s; text-decoration: none; font-size: 1rem sm:1.125rem;">
                            <i class="fas fa-times mr-2"></i>
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary w-full sm:w-auto" id="submitRole" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 0.875rem sm:1rem 1.5rem sm:2rem; border-radius: 8px; font-weight: 600; transition: all 0.3s; font-size: 1rem sm:1.125rem;">
                            <i class="fas fa-save mr-2"></i>
                            Actualizar Rol
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('css/admin/sales/index.css') }}">

<style>
    .required::after {
        content: " *";
        color: #ef4444;
    }
    
    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        outline: none;
    }
    
    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }
    
    /* Estilo para botones deshabilitados */
    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none !important;
    }

    .btn:disabled:hover {
        transform: none !important;
        box-shadow: none !important;
    }

    /* Responsive improvements */
    @media (max-width: 640px) {
        .space-y-4 > * + * {
            margin-top: 1.5rem;
        }
        
        .modern-card {
            margin: 0 -0.5rem;
            border-radius: 12px;
        }
        
        .modern-card-header {
            border-radius: 12px 12px 0 0;
        }
        
        .modern-card-body {
            border-radius: 0 0 12px 12px;
        }
        
        .card-footer {
            border-radius: 0 0 12px 12px;
        }
        
        .title-content {
            flex-direction: column;
            text-align: center;
            gap: 1.5rem;
        }
        
        .title-icon {
            margin-bottom: 0;
        }
        
        .btn {
            font-size: 1rem;
            padding: 0.875rem 1.5rem;
            min-height: 48px;
        }
        
        .form-control {
            font-size: 16px; /* Prevents zoom on iOS */
        }
        
        .form-group {
            margin-bottom: 2rem;
        }
        
        .input-group {
            margin-bottom: 0.75rem;
        }
    }
    
    @media (max-width: 480px) {
        .modern-card {
            margin: 0 -1rem;
            border-radius: 0;
        }
        
        .modern-card-header {
            border-radius: 0;
        }
        
        .modern-card-body {
            border-radius: 0;
        }
        
        .card-footer {
            border-radius: 0;
        }
    }
</style>
@endpush

@push('js')
<script>
    $(document).ready(function() {
        // Validación del lado del cliente
        $('form').on('submit', function(e) {
            // Deshabilitar botón para prevenir múltiples envíos
            $('#submitRole').prop('disabled', true);
            
            let name = $('#name').val().trim();
            
            if (name.length === 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Error de validación',
                    text: 'El nombre del rol es obligatorio'
                });
                // Rehabilitar botón si hay error
                $('#submitRole').prop('disabled', false);
                return false;
            }
            
            if (name.length > 255) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Error de validación',
                    text: 'El nombre no puede exceder los 255 caracteres'
                });
                // Rehabilitar botón si hay error
                $('#submitRole').prop('disabled', false);
                return false;
            }
        });

        // Confirmación antes de salir si hay cambios
        let formChanged = false;
        
        $('form').on('change', function() {
            formChanged = true;
        });

        window.onbeforeunload = function() {
            if (formChanged) {
                return "¿Estás seguro de que quieres salir? Los cambios no guardados se perderán.";
            }
        };

        // Desactivar la advertencia al enviar el formulario
        $('form').on('submit', function() {
            window.onbeforeunload = null;
        });
    });
</script>
@endpush