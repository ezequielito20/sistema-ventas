@extends('adminlte::page')

@section('title', 'Editar Categoría')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-dark font-weight-bold">Editar Categoría</h1>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-tag mr-2"></i>
                        Editar Categoría: {{ $category->name }}
                    </h3>
                </div>
                <form action="{{ route('admin.categories.update', $category->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Nombre <span class="text-danger">*</span></label>
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
                                       value="{{ old('name', $category->name) }}" 
                                       required>
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <small class="text-muted">
                                El nombre de la categoría debe ser único y descriptivo.
                            </small>
                        </div>
                        <div class="form-group">
                            <label for="description">Descripción</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="fas fa-align-left"></i>
                                    </span>
                                </div>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" 
                                          name="description" 
                                          rows="3">{{ old('description', $category->description) }}</textarea>
                                @error('description')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <small class="text-muted">
                                La descripción es opcional y puede contener hasta 255 caracteres.
                            </small>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times mr-2"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i>Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .card {
            border-radius: 0.75rem;
        }

        .card-footer {
            border-bottom-left-radius: 0.75rem !important;
            border-bottom-right-radius: 0.75rem !important;
        }

        .input-group-text {
            background-color: #f8f9fa;
        }

        .form-control {
            border-radius: 0.5rem;
        }

        .input-group > .form-control {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        .btn {
            border-radius: 0.5rem;
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
                    text: 'El nombre de la categoría es obligatorio'
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
