@extends('adminlte::page')

@section('title', 'Editar Permiso')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-dark font-weight-bold">Editar Permiso</h1>
        <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i>Volver
        </a>
    </div>
@stop

@section('content')
    <div class="card card-outline card-primary col-md-8">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-edit mr-2"></i>
                Información del Permiso
            </h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.permissions.update', $permission->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="name" class="required">Nombre del Permiso</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-key"></i>
                                </span>
                                <input type="text" name="name" id="name"
                                    class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                                    value="{{ old('name', $permission->name) }}" required>
                                @if ($errors->has('name'))
                                    <div class="invalid-feedback">
                                        {{ $errors->first('name') }}
                                    </div>
                                @endif
                            </div>
                            <small class="text-muted">
                                El nombre debe ser único y seguir el formato: modulo.accion
                            </small>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i>
                            Actualizar Permiso
                        </button>
                        <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">
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
            border-bottom: 1px solid rgba(0, 0, 0, .125);
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

        .input-group>.form-control {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
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
        });
    </script>
@stop
