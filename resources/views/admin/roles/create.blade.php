@extends('layouts.app')

@section('title', 'Crear Rol')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/roles/create.css') }}">
@endpush

@push('js')
    <script src="{{ asset('js/admin/roles/create.js') }}" defer></script>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Crear Nuevo Rol</h1>
            <p class="text-sm sm:text-base text-gray-600">Define un nuevo rol para el sistema</p>
        </div>
        <div class="flex items-center">
            <a href="{{ route('admin.roles.index') }}" class="btn-outline w-full sm:w-auto text-center">
                <i class="fas fa-arrow-left mr-2"></i>
            </a>
        </div>
    </div>

    {{-- Formulario --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <div class="title-content">
                <div class="title-icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div>
                    <h3>Información del Rol</h3>
                    <p>Completa los datos para crear un nuevo rol</p>
                </div>
            </div>
        </div>
        <div class="modern-card-body">
            <form action="{{ route('admin.roles.store') }}" method="POST" id="createRoleForm">
                @csrf
                {{-- Nombre del Rol --}}
                <div class="form-group">
                    <label for="name" class="font-weight-bold required">
                        Nombre del Rol
                        <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-tag"></i>
                        </span>
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
            </form>
        </div>

        <div class="card-footer">
            <div class="text-right">
                <button type="submit" 
                        form="createRoleForm"
                        class="btn btn-primary" 
                        id="submitRole"
                        title="Guardar Rol">
                    <i class="fas fa-save"></i>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection