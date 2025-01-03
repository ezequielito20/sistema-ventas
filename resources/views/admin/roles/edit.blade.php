@extends('adminlte::page')

@section('title', 'Editar Rol')

@section('content')
    <div class="row">
        <h1>Editar Rol</h1>
    </div>
    <hr>

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-user-shield mr-2"></i>
                Editar Rol: {{ $role->name }}
            </h3>
        </div>

        <div class="card-body">
            <form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-12">
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
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name"
                                       value="{{ old('name', $role->name) }}" 
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
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i>
                            Actualizar Rol
                        </button>
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times mr-2"></i>
                            Cancelar
                        </a>
                    </div>
                </div>
            </form>
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
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Validación del lado del cliente
        $('form').on('submit', function(e) {
            let name = $('#name').val().trim();
            
            if (name.length === 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Error de validación',
                    text: 'El nombre del rol es obligatorio'
                });
                return false;
            }
            
            if (name.length > 255) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Error de validación',
                    text: 'El nombre no puede exceder los 255 caracteres'
                });
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
@stop