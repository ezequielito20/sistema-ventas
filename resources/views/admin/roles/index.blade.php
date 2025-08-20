@extends('layouts.app')

@section('title', 'Gestión de Roles')

@section('content')
<div class="space-y-6">
    {{-- Dashboard de Estadísticas Moderno --}}
    <div class="stats-dashboard">
        <div class="stats-grid">
                <div class="stats-card stats-card-primary">
                    <div class="stats-card-body">
                        <div class="stats-icon">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div class="stats-content">
                            <h3 class="stats-value">{{ $roles->count() }}</h3>
                            <p class="stats-label">Total de Roles</p>
                            <div class="stats-trend">
                                <i class="fas fa-shield-alt"></i>
                                <span>Sistema Seguro</span>
                            </div>
                    </div>
                </div>
            </div>

                <div class="stats-card stats-card-success">
                    <div class="stats-card-body">
                        <div class="stats-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stats-content">
                            <h3 class="stats-value">{{ $roles->sum(function($role) { return $role->users->count(); }) }}</h3>
                            <p class="stats-label">Usuarios Asignados</p>
                            <div class="stats-trend">
                                <i class="fas fa-user-check"></i>
                                <span>Activos</span>
                            </div>
                    </div>
                </div>
            </div>

                <div class="stats-card stats-card-warning">
                    <div class="stats-card-body">
                        <div class="stats-icon">
                            <i class="fas fa-key"></i>
                        </div>
                        <div class="stats-content">
                            <h3 class="stats-value">{{ $permissions->count() }}</h3>
                            <p class="stats-label">Permisos Disponibles</p>
                            <div class="stats-trend">
                                <i class="fas fa-lock"></i>
                                <span>Seguridad</span>
                            </div>
                    </div>
                </div>
            </div>

                <div class="stats-card stats-card-info">
                    <div class="stats-card-body">
                        <div class="stats-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="stats-content">
                            <h3 class="stats-value">{{ $roles->where('is_system_role', true)->count() }}</h3>
                            <p class="stats-label">Roles del Sistema</p>
                            <div class="stats-trend">
                                <i class="fas fa-cog"></i>
                                <span>Protegidos</span>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Contenido Principal --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <div class="title-content">
                <div class="title-icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div>
                    <h3>Gestión de Roles</h3>
                    <p>Administra y gestiona los roles y permisos del sistema</p>
                </div>
            </div>
            <div class="modern-card-actions">
                @can('roles.create')
                    <a href="{{ route('admin.roles.create') }}" class="btn-primary">
                        <i class="fas fa-plus-circle"></i>
                        Crear Nuevo Rol
                    </a>
                @endcan
            </div>
        </div>
        <div class="modern-card-body">
            @php
                $rolesData = $roles->map(function($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'created_at' => $role->created_at->format('d/m/Y H:i'),
                        'users_count' => $role->users->count(),
                        'permissions_count' => $role->permissions->count(),
                        'is_system' => $role->is_system_role ? 'Sí' : 'No'
                    ];
                })->toArray();
            @endphp
            
            <x-simple-table 
                id="roles-table"
                :items="$rolesData"
                :columns="[
                    ['key' => 'name', 'label' => 'Nombre del Rol', 'sortable' => true],
                    ['key' => 'users_count', 'label' => 'Usuarios', 'sortable' => true, 'format' => 'number'],
                    ['key' => 'permissions_count', 'label' => 'Permisos', 'sortable' => true, 'format' => 'number'],
                    ['key' => 'is_system', 'label' => 'Sistema', 'sortable' => true],
                    ['key' => 'created_at', 'label' => 'Fecha de Creación', 'sortable' => true]
                ]"
                :actions="'
                    <div class=\"flex items-center gap-2\">
                        @can(\'roles.show\')
                            <button type=\"button\" class=\"w-8 h-8 flex items-center justify-center rounded-lg bg-blue-500 hover:bg-blue-600 text-white transition-colors\" 
                                    @click=\"showRole(item.id)\" title=\"Ver detalles\">
                                <i class=\"fas fa-eye text-sm\"></i>
                            </button>
                        @endcan
                        @can(\'roles.edit\')
                            <a href=\"{{ route(\'admin.roles.edit\', \'\') }}\" + item.id class=\"w-8 h-8 flex items-center justify-center rounded-lg bg-yellow-500 hover:bg-yellow-600 text-white transition-colors\" title=\"Editar\">
                                <i class=\"fas fa-edit text-sm\"></i>
                            </a>
                        @endcan
                        @can(\'roles.edit\')
                            <button type=\"button\" class=\"w-8 h-8 flex items-center justify-center rounded-lg bg-purple-500 hover:bg-purple-600 text-white transition-colors\" 
                                    @click=\"assignPermissions(item.id, item.name)\" title=\"Asignar permisos\">
                                <i class=\"fas fa-key text-sm\"></i>
                            </button>
                        @endcan
                        @can(\'roles.destroy\')
                            <button type=\"button\" class=\"w-8 h-8 flex items-center justify-center rounded-lg bg-red-500 hover:bg-red-600 text-white transition-colors\" 
                                    @click=\"deleteRole(item.id)\" title=\"Eliminar\">
                                <i class=\"fas fa-trash text-sm\"></i>
                            </button>
                        @endcan
                    </div>
                '"
            />
        </div>
        </div>
    </div>

    {{-- Modo Tarjetas para Móviles --}}
    <div class="mobile-cards">
        @foreach ($roles as $role)
            <div class="mobile-card">
                <div class="mobile-card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 font-weight-bold text-primary">{{ $role->name }}</h6>
                        <span class="badge badge-secondary">#{{ $loop->iteration }}</span>
                    </div>
                </div>
                <div class="mobile-card-body">
                    <div class="mobile-info">
                        <span class="mobile-info-label">Fecha de Creación:</span>
                        <span class="mobile-info-value">{{ $role->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    
                    <div class="mobile-card-actions">
                        @can('roles.show')
                            <button type="button" class="btn btn-success btn-sm show-role"
                                data-id="{{ $role->id }}" title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </button>
                        @endcan
                        @can('roles.edit')
                            <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-info btn-sm" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                        @endcan
                        @can('roles.edit')
                            <a type="button" class="btn btn-warning btn-sm assign-permissions"
                                data-id="{{ $role->id }}" data-name="{{ $role->name }}" title="Asignar permisos">
                                <i class="fas fa-key"></i>
                            </a>
                        @endcan
                        @can('roles.destroy')
                            <button type="button" class="btn btn-danger btn-sm delete-role"
                                data-id="{{ $role->id }}" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        @endcan
                    </div>
                </div>
            </div>
        @endforeach
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

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/roles/index.css') }}">
@endpush

@push('js')
    <script src="{{ asset('vendor/config.js') }}"></script>
    <script src="{{ asset('js/admin/roles/index.js') }}" defer></script>
@endpush
