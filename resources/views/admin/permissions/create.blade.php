@extends('adminlte::page')

@section('title', 'Crear Permiso')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-dark font-weight-bold">Crear Nuevo Permiso</h1>
        <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i>Volver
        </a>
    </div>
@stop

@section('content')
    <div class="card card-outline card-primary col-md-6 ">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-key mr-2"></i>
                Información del Permiso
            </h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.permissions.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="name" class="required">Nombre del Permiso</label>
                            <input type="text" name="name" id="name"
                                class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}"
                                required autofocus placeholder="Ejemplo: users.create">
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="text-muted">
                                El nombre debe ser único y seguir el formato: modulo.accion
                            </small>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary" id="submitPermission">
                            <i class="fas fa-save mr-2"></i>Guardar Permiso
                        </button>
                        <a href="{{ route('admin.permissions.index') }}" class="btn btn-default">
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
        .required:after {
            content: ' *';
            color: red;
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
            // Convertir automáticamente a minúsculas y reemplazar espacios por puntos
            $('#name').on('input', function() {
                $(this).val($(this).val().toLowerCase().replace(/\s+/g, '.'));
            });
            
            // Deshabilitar botón en envío del formulario
            $('form').on('submit', function(e) {
                $('#submitPermission').prop('disabled', true);
            });
        });
    </script>
@stop
