@extends('layouts.app')

@section('title', 'Gestión de Roles')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/roles/index.css') }}">
@endpush

@push('js')
    <script src="{{ asset('vendor/config.js') }}"></script>
    <script src="{{ asset('js/shared/table-utils.js') }}"></script>
    <script src="{{ asset('js/admin/roles/index.js') }}" defer></script>
@endpush

@section('content')
    <!-- Contenedor Principal con Gradiente de Fondo -->
    <div class="min-h-screen bg-gradient-to-br from-purple-50 via-blue-50 to-indigo-100" x-data="rolesManager()">

        <!-- Hero Section con Tailwind y Alpine.js -->
        <div class="relative overflow-hidden bg-gradient-to-br from-blue-600 via-purple-600 to-indigo-700 rounded-2xl shadow-2xl mb-8"
            x-data="heroSection()">
            <!-- Background Pattern -->
            <div class="absolute inset-0 bg-black bg-opacity-10">
                <div class="absolute inset-0 bg-gradient-to-r from-white/5 to-transparent"></div>
                <!-- Decorative circles -->
                <div class="absolute top-0 left-0 w-72 h-72 bg-white rounded-full mix-blend-multiply filter blur-xl opacity-10 animate-blob"></div>
                <div class="absolute top-0 right-0 w-72 h-72 bg-purple-300 rounded-full mix-blend-multiply filter blur-xl opacity-10 animate-blob animation-delay-2000"></div>
                <div class="absolute -bottom-8 left-20 w-72 h-72 bg-blue-300 rounded-full mix-blend-multiply filter blur-xl opacity-10 animate-blob animation-delay-4000"></div>
            </div>

            <div class="relative px-6 py-8 sm:px-8 lg:px-12">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                    <!-- Hero Content -->
                    <div class="flex-1 lg:pr-8">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0">
                                <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center">
                                    <i class="fas fa-user-shield text-3xl text-white"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h1 class="text-3xl sm:text-4xl font-bold text-white">
                                    Gestión de Roles
                                </h1>
                                <p class="mt-2 text-lg text-blue-100">
                                    Administra y gestiona los roles y permisos del sistema
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Hero Actions -->
                    <div class="mt-6 lg:mt-0 lg:ml-8">
                        <div class="flex flex-col sm:flex-row gap-4">
                            @can('roles.create')
                                <a href="{{ route('admin.roles.create') }}" 
                                   class="inline-flex items-center px-6 py-3 bg-white/20 backdrop-blur-sm border border-white/30 rounded-xl text-white font-semibold hover:bg-white/30 transition-all duration-300 transform hover:scale-105">
                                    <i class="fas fa-plus-circle mr-2"></i>
                                    Crear Nuevo Rol
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard de Estadísticas -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total de Roles -->
            <div class="group relative bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-indigo-600 opacity-5 group-hover:opacity-10 transition-opacity duration-300"></div>
                <div class="relative p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-user-shield text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div class="text-3xl font-bold text-gray-900">{{ $roles->count() }}</div>
                        <div class="text-sm font-medium text-gray-600">Total de Roles</div>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-3">
                            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 h-2 rounded-full" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Usuarios Asignados -->
            <div class="group relative bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-green-500 to-emerald-600 opacity-5 group-hover:opacity-10 transition-opacity duration-300"></div>
                <div class="relative p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-users text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div class="text-3xl font-bold text-gray-900">{{ $roles->sum(function($role) { return $role->users->count(); }) }}</div>
                        <div class="text-sm font-medium text-gray-600">Usuarios Asignados</div>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-3">
                            <div class="bg-gradient-to-r from-green-500 to-emerald-600 h-2 rounded-full" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Permisos Disponibles -->
            <div class="group relative bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-yellow-500 to-orange-500 opacity-5 group-hover:opacity-10 transition-opacity duration-300"></div>
                <div class="relative p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-yellow-500 to-orange-500 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-key text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div class="text-3xl font-bold text-gray-900">{{ $permissions->count() }}</div>
                        <div class="text-sm font-medium text-gray-600">Permisos Disponibles</div>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-3">
                            <div class="bg-gradient-to-r from-yellow-500 to-orange-500 h-2 rounded-full" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Roles del Sistema -->
            <div class="group relative bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-purple-500 to-indigo-600 opacity-5 group-hover:opacity-10 transition-opacity duration-300"></div>
                <div class="relative p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-shield-alt text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div class="text-3xl font-bold text-gray-900">{{ $roles->where('is_system_role', true)->count() }}</div>
                        <div class="text-sm font-medium text-gray-600">Roles del Sistema</div>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-3">
                            <div class="bg-gradient-to-r from-purple-500 to-indigo-600 h-2 rounded-full" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel de Controles -->
        <div class="bg-white rounded-2xl shadow-lg mb-8 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-list text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Lista de Roles</h3>
                            <p class="text-sm text-gray-500">Gestiona los roles del sistema</p>
                        </div>
                    </div>

                    <!-- Toggle de Vista -->
                    <div class="flex items-center space-x-2">
                        <button @click="viewMode = 'table'" 
                                :class="viewMode === 'table' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-600'"
                                class="px-4 py-2 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <i class="fas fa-table mr-2"></i>
                            Tabla
                        </button>
                        <button @click="viewMode = 'cards'" 
                                :class="viewMode === 'cards' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-600'"
                                class="px-4 py-2 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <i class="fas fa-th-large mr-2"></i>
                            Tarjetas
                        </button>
                    </div>
                </div>
            </div>

            <!-- Vista de Tabla -->
            <div x-show="viewMode === 'table'" class="block">
                <div class="table-container">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>
                                    <div class="th-content">
                                        <i class="fas fa-hashtag"></i>
                                        <span>#</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="fas fa-user-shield"></i>
                                        <span>Nombre del Rol</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="fas fa-users"></i>
                                        <span>Usuarios</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="fas fa-key"></i>
                                        <span>Permisos</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="fas fa-shield-alt"></i>
                                        <span>Sistema</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="fas fa-calendar"></i>
                                        <span>Fecha de Creación</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <i class="fas fa-cogs"></i>
                                        <span>Acciones</span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($roles as $role)
                                <tr class="table-row">
                                    <td>
                                        <div class="row-number">{{ $loop->iteration }}</div>
                                    </td>
                                    <td>
                                        <div class="role-info">
                                            <div class="role-name">{{ $role->name }}</div>
                                            <div class="role-description text-sm text-gray-500">
                                                @if($role->is_system_role)
                                                    <span class="system-badge">Rol del Sistema</span>
                                                @else
                                                    <span class="custom-badge">Rol Personalizado</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="users-count">
                                            <span class="count-badge">{{ $role->users->count() }}</span>
                                            <span class="count-label">usuarios</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="permissions-count">
                                            <span class="count-badge">{{ $role->permissions->count() }}</span>
                                            <span class="count-label">permisos</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="system-status">
                                            @if($role->is_system_role)
                                                <span class="status-badge status-system">
                                                    <i class="fas fa-shield-alt"></i>
                                                    Sistema
                                                </span>
                                            @else
                                                <span class="status-badge status-custom">
                                                    <i class="fas fa-user-edit"></i>
                                                    Personalizado
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="date-info">
                                            <div class="date-value">{{ $role->created_at->format('d/m/Y') }}</div>
                                            <div class="date-time text-sm text-gray-500">{{ $role->created_at->format('H:i') }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            @can('roles.show')
                                                <button type="button" class="btn-action btn-view" 
                                                        @click="showRole({{ $role->id }})" title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            @endcan
                                            @can('roles.edit')
                                                <a href="{{ route('admin.roles.edit', $role->id) }}" 
                                                   class="btn-action btn-edit" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan
                                            @can('roles.edit')
                                                <button type="button" class="btn-action btn-permissions" 
                                                        @click="assignPermissions({{ $role->id }}, '{{ $role->name }}')" title="Asignar permisos">
                                                    <i class="fas fa-key"></i>
                                                </button>
                                            @endcan
                                            @can('roles.destroy')
                                                <button type="button" class="btn-action btn-delete" 
                                                        @click="deleteRole({{ $role->id }})" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Vista de Tarjetas -->
            <div x-show="viewMode === 'cards'" class="block">
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($roles as $role)
                        <div class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden border-l-4 {{ $role->is_system_role ? 'border-purple-500' : 'border-blue-500' }}">
                            <!-- Header de la Tarjeta -->
                            <div class="p-6 pb-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white font-bold text-xl">
                                            {{ strtoupper(substr($role->name, 0, 1)) }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-lg font-semibold text-gray-900 truncate">{{ $role->name }}</h3>
                                            <div class="flex items-center space-x-1 text-sm text-gray-500 mt-1">
                                                @if($role->is_system_role)
                                                    <i class="fas fa-shield-alt text-xs"></i>
                                                    <span class="truncate">Rol del Sistema</span>
                                                @else
                                                    <i class="fas fa-user-edit text-xs"></i>
                                                    <span class="truncate">Rol Personalizado</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Estado -->
                                    <div class="flex-shrink-0">
                                        @if($role->is_system_role)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                <i class="fas fa-shield-alt mr-1"></i>
                                                Sistema
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <i class="fas fa-user-edit mr-1"></i>
                                                Personalizado
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Información Principal -->
                            <div class="px-6 pb-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <!-- Usuarios -->
                                    <div class="space-y-1">
                                        <div class="flex items-center space-x-2 text-xs text-gray-500">
                                            <i class="fas fa-users"></i>
                                            <span>Usuarios</span>
                                        </div>
                                        <p class="text-sm font-medium text-gray-900">{{ $role->users->count() }}</p>
                                    </div>

                                    <!-- Permisos -->
                                    <div class="space-y-1">
                                        <div class="flex items-center space-x-2 text-xs text-gray-500">
                                            <i class="fas fa-key"></i>
                                            <span>Permisos</span>
                                        </div>
                                        <p class="text-sm font-medium text-gray-900">{{ $role->permissions->count() }}</p>
                                    </div>
                                </div>

                                <!-- Fecha de Creación -->
                                <div class="mt-4 pt-4 border-t border-gray-100">
                                    <div class="flex items-center space-x-2 text-xs text-gray-500">
                                        <i class="fas fa-calendar"></i>
                                        <span>Creado: {{ $role->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Acciones -->
                            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                                <div class="flex items-center justify-center space-x-2">
                                    @can('roles.show')
                                        <button type="button" class="btn-action btn-view" 
                                                @click="showRole({{ $role->id }})" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    @endcan
                                    @can('roles.edit')
                                        <a href="{{ route('admin.roles.edit', $role->id) }}" 
                                           class="btn-action btn-edit" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan
                                    @can('roles.edit')
                                        <button type="button" class="btn-action btn-permissions" 
                                                @click="assignPermissions({{ $role->id }}, '{{ $role->name }}')" title="Asignar permisos">
                                            <i class="fas fa-key"></i>
                                        </button>
                                    @endcan
                                    @can('roles.destroy')
                                        <button type="button" class="btn-action btn-delete" 
                                                @click="deleteRole({{ $role->id }})" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Modal para mostrar rol --}}
    <div class="modal fade" id="showRoleModal" tabindex="-1" role="dialog" aria-labelledby="showRoleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="showRoleModalLabel">
                        <i class="fas fa-user-shield mr-2"></i>
                        Detalles del Rol
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="font-weight-bold">Nombre del Rol:</label>
                                <p id="roleName" class="form-control-static"></p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Fecha de Creación:</label>
                                <p id="roleCreated" class="form-control-static"></p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Última Actualización:</label>
                                <p id="roleUpdated" class="form-control-static"></p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Usuarios Asignados:</label>
                                <p id="roleUsers" class="form-control-static"></p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Permisos Asignados:</label>
                                <p id="rolePermissions" class="form-control-static"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de Asignación de Permisos --}}
    <div class="modal fade" id="permissionsModal" tabindex="-1" role="dialog" aria-labelledby="permissionsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="permissionsModalLabel">
                        <i class="fas fa-key mr-2"></i>
                        Asignar Permisos al Rol: <span id="roleName" class="font-weight-bold"></span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <div class="search-box">
                                <input type="text" id="searchPermission" class="form-control" autocomplete="off"
                                    placeholder="Buscar permisos...">
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-center justify-content-end">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="selectAllPermissions">
                                <label class="custom-control-label pl-2" for="selectAllPermissions"
                                    style="margin-left: 15px;">
                                    Seleccionar todos los permisos
                                </label>
                            </div>
                        </div>
                    </div>

                    <form id="permissionsForm">
                        @csrf
                        <input type="hidden" id="roleId" name="role_id">

                        <div class="row permissions-container">
                            @foreach ($permissions as $module => $modulePermissions)
                                <div class="col-xl-4 col-lg-6 col-12 mb-4">
                                    <div class="permission-module-card">
                                        <div class="permission-module-header">
                                            <div class="permission-module-title">
                                                <i class="fas fa-folder mr-2"></i>{{ $module }}
                                            </div>
                                            <div class="permission-module-selector">
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input group-selector"
                                                        id="group_{{ $module }}" data-group="{{ $module }}">
                                                    <label class="custom-control-label" for="group_{{ $module }}">
                                                        Seleccionar todo
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="permission-module-body">
                                            @foreach ($modulePermissions as $permission)
                                                <div class="permission-item" data-group="{{ $module }}">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox"
                                                            class="custom-control-input permission-checkbox"
                                                            id="permission_{{ $permission->id }}"
                                                            value="{{ $permission->id }}"
                                                            data-group="{{ $module }}"
                                                            data-name="{{ $permission->name }}">
                                                        <label class="custom-control-label"
                                                            for="permission_{{ $permission->id }}"
                                                            title="{{ $permission->name }}">
                                                            {{ $permission->friendly_name }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-2"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-warning" id="savePermissions">
                        <i class="fas fa-save mr-2"></i>Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
