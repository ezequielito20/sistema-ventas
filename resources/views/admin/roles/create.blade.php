@extends('adminlte::page')

@section('title', 'Crear Rol')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-dark font-weight-bold">Crear Nuevo Rol</h1>
        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i>Volver al listado
        </a>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-shield mr-2"></i>
                    Información del Rol
                </h3>
            </div>

            <form action="{{ route('admin.roles.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    {{-- Nombre del Rol --}}
                    <div class="form-group">
                        <label for="name" class="font-weight-bold required">
                            Nombre del Rol
                            <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="fas fa-tag"></i>
                                </span>
                            </div>
                            <input type="text" 
                                   name="name" 
                                   id="name" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name') }}" 
                                   placeholder="Ejemplo: Editor, Supervisor, etc."
                                   required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <small class="text-muted">
                            El nombre del rol debe ser único y descriptivo.
                        </small>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="text-right">
                        <button type="button" 
                                onclick="window.history.back()" 
                                class="btn btn-secondary">
                            <i class="fas fa-times mr-2"></i>
                            Cancelar
                        </button>
                        <button type="submit" 
                                class="btn btn-primary" id="submitRole">
                            <i class="fas fa-save mr-2"></i>
                            Guardar Rol
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .required::after {
        content: " *";
        color: red;
    }
    .card {
        border-radius: 0.75rem;
    }
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid rgba(0,0,0,.125);
    }
    .input-group-text {
        background-color: #f8f9fa;
    }
    .btn {
        border-radius: 0.5rem;
    }
    .form-control {
        border-radius: 0.5rem;
    }
    .input-group > .form-control {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
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
</style>
@stop

@section('js')
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
    });
</script>
@stop