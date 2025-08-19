@extends('layouts.app')

@section('title', 'Crear Permiso')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/permissions/create.css') }}">
@endpush

@push('js')
    <script src="{{ asset('js/admin/permissions/create.js') }}" defer></script>
@endpush

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
@endsection
