@extends('layouts.app')

@section('title', 'Gestión de Roles')

@section('content')
<div class="space-y-6">
    {{-- Dashboard de Estadísticas Moderno --}}
    <div class="stats-dashboard">
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
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
                    <div class="stats-wave">
                        <svg viewBox="0 0 1200 120" preserveAspectRatio="none">
                            <path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z" opacity=".25"></path>
                            <path d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z" opacity=".5"></path>
                            <path d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
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
                    <div class="stats-wave">
                        <svg viewBox="0 0 1200 120" preserveAspectRatio="none">
                            <path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z" opacity=".25"></path>
                            <path d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z" opacity=".5"></path>
                            <path d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
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
                    <div class="stats-wave">
                        <svg viewBox="0 0 1200 120" preserveAspectRatio="none">
                            <path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z" opacity=".25"></path>
                            <path d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z" opacity=".5"></path>
                            <path d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
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
                    <div class="stats-wave">
                        <svg viewBox="0 0 1200 120" preserveAspectRatio="none">
                            <path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z" opacity=".25"></path>
                            <path d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z" opacity=".5"></path>
                            <path d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z"></path>
                        </svg>
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
            <div class="table-responsive">
                <table id="rolesTable" class="table table-striped table-hover table-sm">
                <thead class="bg-primary text-white">
                    <tr class="text-center">
                        <th style="width: 5%">#</th>
                        <th style="width: 20%">Nombre del Rol</th>
                        <th style="width: 20%">Fecha de Creación</th>
                        <th style="width: 20%">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($roles as $role)
                        <tr class="text-center">
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <span class="font-weight-bold">{{ $role->name }}</span>
                            </td>
                            <td>{{ $role->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    @can('roles.show')
                                        <button type="button" class="btn btn-success btn-sm show-role"
                                            data-id="{{ $role->id }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    @endcan
                                    @can('roles.edit')
                                        <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan
                                    @can('roles.edit')
                                        <a type="button" class="btn btn-warning btn-sm assign-permissions"
                                            data-id="{{ $role->id }}" data-name="{{ $role->name }}">
                                            <i class="fas fa-key"></i>
                                        </a>
                                    @endcan
                                    @can('roles.destroy')
                                        <button type="button" class="btn btn-danger btn-sm delete-role"
                                            data-id="{{ $role->id }}">
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
                                <input type="text" id="searchPermission" class="form-control"
                                    placeholder="Buscar permisos...">
                                <i class="fas fa-search search-icon"></i>
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
                                <div class="col-md-6 mb-4">
                                    <div class="card card-outline card-warning h-100">
                                        <div class="card-header">
                                            <div
                                                class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                                                <h3 class="card-title text-capitalize mb-2 mb-md-0">
                                                    <i class="fas fa-folder mr-2"></i>{{ $module }}
                                                </h3>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input group-selector"
                                                        id="group_{{ $module }}" data-group="{{ $module }}">
                                                    <label class="custom-control-label pl-2"
                                                        for="group_{{ $module }}" style="margin-left: 15px;">
                                                        Seleccionar todo
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
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
    <link rel="stylesheet" href="{{ asset('vendor/datatables/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/datatables/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/datatables/buttons.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/sales/index.css') }}">

    <style>
        /* Variables CSS para consistencia */
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
            --warning-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            --info-gradient: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            --danger-gradient: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            --light-bg: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --hover-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            --border-radius: 0.75rem;
            --transition: all 0.3s ease;
        }

        /* Estilos generales responsivos */
        .space-y-6 > * + * {
            margin-top: 1.5rem;
        }

        /* Dashboard de estadísticas */
        .stats-dashboard {
            margin-bottom: 2rem;
        }

        .stats-card {
            position: relative;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            overflow: hidden;
            transition: var(--transition);
            height: 100%;
            min-height: 140px;
        }

        .stats-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--hover-shadow);
        }

        .stats-card-primary {
            border-left: 4px solid #667eea;
        }

        .stats-card-success {
            border-left: 4px solid #10b981;
        }

        .stats-card-warning {
            border-left: 4px solid #f59e0b;
        }

        .stats-card-info {
            border-left: 4px solid #3b82f6;
        }

        .stats-card-body {
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            flex-shrink: 0;
        }

        .stats-card-primary .stats-icon {
            background: var(--primary-gradient);
        }

        .stats-card-success .stats-icon {
            background: var(--success-gradient);
        }

        .stats-card-warning .stats-icon {
            background: var(--warning-gradient);
        }

        .stats-card-info .stats-icon {
            background: var(--info-gradient);
        }

        .stats-content {
            flex: 1;
        }

        .stats-value {
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
            color: #1e293b;
        }

        .stats-label {
            color: #64748b;
            margin: 0.25rem 0;
            font-weight: 500;
        }

        .stats-trend {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: #64748b;
            margin-top: 0.5rem;
        }

        .stats-wave {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 60px;
            opacity: 0.1;
        }

        .stats-wave svg {
            width: 100%;
            height: 100%;
        }

        /* Tarjeta moderna */
        .modern-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            overflow: hidden;
            transition: var(--transition);
        }

        .modern-card:hover {
            box-shadow: var(--hover-shadow);
        }

        .modern-card-header {
            background: var(--light-bg);
            padding: 2rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .title-content {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .title-content h3 {
            color: #1e293b;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }

        .title-content p {
            color: #64748b;
            margin: 0.5rem 0 0 0;
        }

        .title-icon {
            width: 60px;
            height: 60px;
            background: var(--primary-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .modern-card-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 1.5rem;
            flex-wrap: wrap;
        }

        .btn-outline {
            background: white;
            color: #64748b;
            border: 2px solid #e2e8f0;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-outline:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #475569;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
            color: white;
            text-decoration: none;
        }



        .modern-card-body {
            padding: 2rem;
        }

        /* Tabla responsiva */
        .table-responsive {
            border-radius: var(--border-radius);
            overflow: hidden;
            width: 100%;
        }

        .table {
            margin-bottom: 0;
            background: white;
            width: 100%;
        }

        /* DataTables wrapper para ancho completo */
        .dataTables_wrapper {
            width: 100%;
        }

        .dataTables_wrapper .dataTables_scroll {
            width: 100%;
        }

        .table th {
            background: var(--primary-gradient) !important;
            color: white !important;
            border: none !important;
            padding: 1rem !important;
            font-weight: 600 !important;
            text-align: center;
            vertical-align: middle;
        }

        .table td {
            padding: 1rem !important;
            border-bottom: 1px solid #e2e8f0 !important;
            vertical-align: middle !important;
            text-align: center;
        }

        .table tbody tr {
            transition: var(--transition);
        }

        .table tbody tr:hover {
            background: var(--light-bg) !important;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        /* Botones de acción */
        .btn-group {
            display: flex;
            gap: 0.25rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .btn-sm {
            border-radius: 0.5rem;
            transition: var(--transition);
            border: none;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
        }

        .btn-sm:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .btn-success {
            background: var(--success-gradient);
            color: white;
        }

        .btn-info {
            background: var(--info-gradient);
            color: white;
        }

        .btn-warning {
            background: var(--warning-gradient);
            color: white;
        }

        .btn-danger {
            background: var(--danger-gradient);
            color: white;
        }

        /* Modales */
        .modal-header {
            background: var(--primary-gradient);
            color: white;
            border-radius: 0.75rem 0.75rem 0 0;
            padding: 1.5rem;
            border: none;
        }

        .modal-content {
            border-radius: 0.75rem;
            box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.2);
            border: none;
            overflow: hidden;
        }

        .modal-body {
            padding: 2rem;
            background: white;
        }



        /* Estilos específicos para el modal de detalles del rol */
        #showRoleModal .modal-dialog {
            max-width: 600px;
        }

        #showRoleModal .modal-header {
            background: var(--primary-gradient);
            position: relative;
            overflow: hidden;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        #showRoleModal .modal-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
            pointer-events: none;
        }

        #showRoleModal .modal-title {
            font-size: 1.25rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            position: relative;
            z-index: 1;
            order: 1;
            margin: 0;
        }

        #showRoleModal .modal-title i {
            font-size: 1.5rem;
            color: rgba(255, 255, 255, 0.9);
        }

        #showRoleModal .close {
            position: relative;
            z-index: 1;
            opacity: 0.8;
            transition: var(--transition);
            background: rgba(255, 255, 255, 0.1);
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-left: auto;
            order: 2;
        }

        #showRoleModal .close:hover {
            opacity: 1;
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.1);
        }

        #showRoleModal .form-group {
            margin-bottom: 1.5rem;
        }

        #showRoleModal .form-group label {
            color: #64748b;
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
            display: block;
        }

        #showRoleModal .form-control-static {
            padding: 1rem;
            margin-bottom: 0;
            background: var(--light-bg);
            border: 2px solid #e2e8f0;
            border-radius: 0.75rem;
            font-weight: 600;
            color: #1e293b;
            font-size: 1rem;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        #showRoleModal .form-control-static::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--primary-gradient);
            opacity: 0;
            transition: var(--transition);
        }

        #showRoleModal .form-control-static:hover::before {
            opacity: 1;
        }

        #showRoleModal .form-control-static:hover {
            border-color: #cbd5e1;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }



        /* Animación del modal */
        .modal.fade .modal-dialog {
            transform: scale(0.8);
            transition: transform 0.3s ease-out;
        }

        .modal.show .modal-dialog {
            transform: scale(1);
        }

        /* Backdrop mejorado - Sin blur para evitar problemas */
        .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.5) !important;
            opacity: 1 !important;
            z-index: 1040 !important;
        }

        .modal-backdrop.show {
            background-color: rgba(0, 0, 0, 0.5) !important;
            opacity: 1 !important;
        }

        .modal-backdrop.fade {
            opacity: 0;
        }

        .modal-backdrop.fade.show {
            opacity: 1;
            background-color: rgba(0, 0, 0, 0.5) !important;
        }

        /* Asegurar que el modal esté por encima del backdrop */
        .modal {
            z-index: 1050 !important;
        }

        /* Forzar oscurecimiento del contenido de fondo */
        body.modal-open {
            overflow: hidden;
        }

        /* Modal de permisos */
        .search-box {
            position: relative;
            margin-bottom: 1rem;
        }

        .search-box .search-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
        }

        .search-box input {
            padding: 0.75rem 2.5rem 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 0.5rem;
            background: white;
            transition: var(--transition);
            width: 100%;
        }

        .search-box input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        .permissions-container {
            max-height: 70vh;
            overflow-y: auto;
            padding: 1rem;
        }

        .permissions-container::-webkit-scrollbar {
            width: 8px;
        }

        .permissions-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .permissions-container::-webkit-scrollbar-thumb {
            background: var(--primary-gradient);
            border-radius: 4px;
        }

        .permissions-container::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
        }

        .permission-item {
            padding: 0.75rem;
            border-radius: 0.5rem;
            transition: var(--transition);
            margin-bottom: 0.5rem;
        }

        .permission-item:hover {
            background: var(--light-bg);
        }

        .custom-switch .custom-control-label::before {
            width: 2.5rem;
            height: 1.5rem;
            background: #e2e8f0;
            border: none;
            border-radius: 1rem;
        }

        .custom-switch .custom-control-label::after {
            width: calc(1.25rem - 4px);
            height: calc(1.25rem - 4px);
            background: white;
            border-radius: 50%;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .custom-switch .custom-control-input:checked~.custom-control-label::before {
            background: var(--primary-gradient);
        }

        .custom-switch .custom-control-input:checked~.custom-control-label::after {
            transform: translateX(1rem);
        }

        .custom-control-label {
            padding-left: 1rem;
            cursor: pointer;
            font-weight: 500;
        }

        /* Responsividad */
        @media (max-width: 768px) {
            .stats-card-body {
                flex-direction: column;
                text-align: center;
                gap: 0.75rem;
            }

            .stats-icon {
                width: 50px;
                height: 50px;
                font-size: 1.25rem;
            }

            .stats-value {
                font-size: 1.5rem;
            }

            .title-content {
                flex-direction: column;
                text-align: center;
                gap: 0.75rem;
            }

            .title-icon {
                width: 50px;
                height: 50px;
                font-size: 1.25rem;
            }

            .modern-card-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .btn-outline,
            .btn-primary {
                justify-content: center;
                width: 100%;
            }



            .modern-card-header,
            .modern-card-body {
                padding: 1rem;
            }

            .table th,
            .table td {
                padding: 0.75rem 0.5rem !important;
                font-size: 0.875rem;
            }

            .btn-group {
                flex-direction: column;
                gap: 0.25rem;
            }

            .btn-sm {
                width: 100%;
                justify-content: center;
            }

            .modal-xl {
                max-width: 95%;
                margin: 1rem;
            }

            .permissions-container {
                max-height: 60vh;
            }

            /* Modal responsivo para tablets */
            #showRoleModal .modal-dialog {
                max-width: 90%;
                margin: 1rem;
            }

            #showRoleModal .modal-body {
                padding: 1.5rem;
            }



            #showRoleModal .form-control-static {
                padding: 0.75rem;
                font-size: 0.875rem;
            }

            /* Controles de DataTables responsivos para tablets */
            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dataTables_filter {
                margin-bottom: 1rem;
            }

            .dataTables_wrapper .dataTables_length {
                justify-content: center;
            }

            .dataTables_wrapper .dataTables_length label {
                font-size: 0.875rem;
            }

            .dataTables_wrapper .dataTables_length select {
                padding: 0.375rem 0.5rem;
                font-size: 0.875rem;
                min-width: 70px;
            }

            /* Paginado responsivo para tablets */
            .dataTables_wrapper .dataTables_paginate {
                gap: 0.25rem;
            }

            .dataTables_wrapper .paginate_button {
                padding: 0.5rem 0.75rem;
                min-width: 36px;
                height: 36px;
                font-size: 0.75rem;
            }

            .dataTables_wrapper .paginate_button.previous,
            .dataTables_wrapper .paginate_button.next {
                min-width: 40px;
            }
        }

        @media (max-width: 576px) {
            .stats-dashboard .row {
                margin: 0;
            }

            .stats-dashboard .col-lg-3,
            .stats-dashboard .col-md-6 {
                padding: 0.5rem;
            }

            .stats-card {
                min-height: 120px;
            }

            .table-responsive {
                font-size: 0.75rem;
            }

            .table th,
            .table td {
                padding: 0.5rem 0.25rem !important;
            }

            .btn-sm {
                padding: 0.375rem 0.5rem;
                font-size: 0.75rem;
                min-width: 32px;
                height: 32px;
            }

            /* Paginado responsivo para móviles pequeños */
            .dataTables_wrapper .dataTables_paginate {
                flex-wrap: wrap;
                gap: 0.25rem;
            }

            .dataTables_wrapper .paginate_button {
                padding: 0.375rem 0.5rem;
                min-width: 32px;
                height: 32px;
                font-size: 0.75rem;
            }

            .dataTables_wrapper .paginate_button.previous,
            .dataTables_wrapper .paginate_button.next {
                min-width: 36px;
            }

            .dataTables_wrapper .dataTables_info {
                font-size: 0.875rem;
                padding: 0.5rem;
            }

            /* Modal responsivo para móviles pequeños */
            #showRoleModal .modal-dialog {
                max-width: 95%;
                margin: 0.5rem;
            }

            #showRoleModal .modal-header {
                padding: 1rem;
            }

            #showRoleModal .modal-title {
                font-size: 1rem;
            }

            #showRoleModal .modal-title i {
                font-size: 1.25rem;
            }

            #showRoleModal .modal-body {
                padding: 1rem;
            }



            #showRoleModal .form-group {
                margin-bottom: 1rem;
            }

            #showRoleModal .form-group label {
                font-size: 0.75rem;
            }

            #showRoleModal .form-control-static {
                padding: 0.5rem;
                font-size: 0.875rem;
            }

            #showRoleModal .close {
                width: 28px;
                height: 28px;
            }
        }

        /* DataTables responsivo */
        .dataTables_wrapper .dt-buttons {
            margin-bottom: 1rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .dataTables_wrapper .dt-button {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: var(--transition);
        }

        .dataTables_wrapper .dt-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        }

                /* Estilos para el selector de cantidad de registros */
        .dataTables_wrapper .dataTables_length {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .dataTables_wrapper .dataTables_length label {
            color: #64748b;
            font-weight: 500;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .dataTables_wrapper .dataTables_length select {
            border: 2px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 0.5rem 0.75rem;
            background: white;
            color: #1e293b;
            font-weight: 500;
            transition: var(--transition);
            min-width: 80px;
        }

        .dataTables_wrapper .dataTables_length select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        .dataTables_wrapper .dataTables_length select:hover {
            border-color: #cbd5e1;
        }



        .dataTables_wrapper .dataTables_info {
            margin-top: 1rem;
            color: #64748b;
        }

        .dataTables_wrapper .dataTables_paginate {
            margin-top: 1.5rem;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
        }

        .dataTables_wrapper .paginate_button {
            border-radius: 0.75rem;
            border: 2px solid #e2e8f0;
            margin: 0;
            padding: 0.75rem 1rem;
            font-weight: 600;
            font-size: 0.875rem;
            color: #64748b;
            background: white;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 44px;
            height: 44px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .dataTables_wrapper .paginate_button:hover {
            background: var(--light-bg);
            border-color: #cbd5e1;
            color: #1e293b;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            text-decoration: none;
        }

        .dataTables_wrapper .paginate_button.current {
            background: var(--primary-gradient);
            color: white !important;
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
            transform: translateY(-1px);
        }

        .dataTables_wrapper .paginate_button.disabled {
            background: #f8fafc;
            color: #cbd5e1 !important;
            border-color: #e2e8f0;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .dataTables_wrapper .paginate_button.disabled:hover {
            background: #f8fafc;
            border-color: #e2e8f0;
            color: #cbd5e1 !important;
            transform: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        /* Estilos para los botones de navegación */
        .dataTables_wrapper .paginate_button.previous,
        .dataTables_wrapper .paginate_button.next {
            font-weight: 700;
            min-width: 48px;
        }

        .dataTables_wrapper .paginate_button.previous:before {
            content: "‹";
            font-size: 1.25rem;
            line-height: 1;
        }

        .dataTables_wrapper .paginate_button.next:before {
            content: "›";
            font-size: 1.25rem;
            line-height: 1;
        }

        /* Contenedor de información */
        .dataTables_wrapper .dataTables_info {
            margin-top: 1rem;
            color: #64748b;
            font-weight: 500;
            text-align: center;
            padding: 0.75rem;
            background: var(--light-bg);
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
        }

        .permission-item {
            padding: 0.75rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
            margin-bottom: 0.5rem;
        }

        .permission-item:hover {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }

        .custom-switch .custom-control-label::before {
            width: 2.5rem;
            height: 1.5rem;
            background: #e2e8f0;
            border: none;
            border-radius: 1rem;
        }

        .custom-switch .custom-control-label::after {
            width: calc(1.25rem - 4px);
            height: calc(1.25rem - 4px);
            background: white;
            border-radius: 50%;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .custom-switch .custom-control-input:checked~.custom-control-label::before {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .custom-switch .custom-control-input:checked~.custom-control-label::after {
            transform: translateX(1rem);
        }

        .custom-control-label {
            padding-left: 1rem;
            cursor: pointer;
            font-weight: 500;
        }

        .modal-xl {
            max-width: 1200px;
        }

        .permissions-container {
            max-height: 70vh;
            overflow-y: auto;
            padding: 1rem;
        }

        .permissions-container::-webkit-scrollbar {
            width: 8px;
        }

        .permissions-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .permissions-container::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 4px;
        }

        .permissions-container::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
        }

        /* Estilos para botones de acción */
        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            color: white;
        }

        .btn-info {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border: none;
            color: white;
        }

        .btn-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border: none;
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border: none;
            color: white;
        }
    </style>
@endpush

@push('js')
    <script src="{{ asset('vendor/config.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Cargar todas las librerías necesarias
            loadDataTables(function() {

                // Inicialización de DataTables
                $('#rolesTable').DataTable({
                    responsive: true,
                    autoWidth: true,
                    dom: 'lfrtip',
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]],

                    "language": {
                        "emptyTable": "No hay información",
                        "info": "Mostrando _START_ a _END_ de _TOTAL_ Roles",
                        "infoEmpty": "Mostrando 0 a 0 de 0 Roles",
                        "infoFiltered": "(Filtrado de _MAX_ total Roles)",
                        "infoPostFix": "",
                        "thousands": ",",
                        "lengthMenu": "Mostrar _MENU_ Roles",
                        "loadingRecords": "Cargando...",
                        "processing": "Procesando...",
                        "search": "Buscador:",
                        "zeroRecords": "Sin resultados encontrados",
                        "paginate": {
                            "first": "Primero",
                            "last": "Último",
                            "next": "Siguiente",
                            "previous": "Anterior"
                        }
                    }
                });

            // Manejo de eliminación de roles
            $('.delete-role').click(function() {
                const roleId = $(this).data('id');

                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "Esta acción no se puede revertir",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Token CSRF
                        const csrfToken = $('meta[name="csrf-token"]').attr('content');

                        // Enviar solicitud de eliminación
                        $.ajax({
                            url: `/roles/delete/${roleId}`,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken
                            },
                            success: function(response) {
                                if (response.status === 'success') {
                                    Swal.fire({
                                        title: '¡Eliminado!',
                                        text: response.message,
                                        icon: 'success'
                                    }).then(() => {
                                        // Recargar la página o eliminar la fila
                                        window.location.reload();
                                    });
                                } else {
                                    Swal.fire(
                                        'Error',
                                        response.message,
                                        'error'
                                    );
                                }
                            },
                            error: function(xhr) {
                                let errorMessage = 'No se pudo eliminar el rol';
                                let detailsHtml = '';
                                
                                try {
                                    const response = xhr.responseJSON;
                                    if (response && response.message) {
                                        errorMessage = response.message;
                                        
                                        // Agregar detalles si están disponibles
                                        if (response.details) {
                                            let details = [];
                                            
                                            if (response.details.error_type) {
                                                details.push(`Tipo: ${response.details.error_type}`);
                                            }
                                            
                                            if (response.details.users_count) {
                                                details.push(`Usuarios asignados: ${response.details.users_count}`);
                                            }
                                            
                                            if (response.details.role_name) {
                                                details.push(`Rol: ${response.details.role_name}`);
                                            }
                                            
                                            if (details.length > 0) {
                                                detailsHtml = '<br><small class="text-muted"><strong>Información:</strong><br>' + 
                                                             details.join('<br>') + '</small>';
                                            }
                                        }
                                    }
                                } catch (e) {
                                    console.error('Error al procesar respuesta de error:', e);
                                    if (xhr.status) {
                                        errorMessage += ` (Código: ${xhr.status})`;
                                    }
                                }
                                
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error al Eliminar Rol',
                                    html: errorMessage + detailsHtml,
                                    confirmButtonText: 'Entendido',
                                    customClass: {
                                        confirmButton: 'btn btn-danger'
                                    },
                                    buttonsStyling: false
                                });
                            }
                        });
                    }
                });
            });

            // Manejo de visualización de rol
            $('.show-role').click(function() {
                const roleId = $(this).data('id');

                // Mostrar loading
                Swal.fire({
                    title: 'Cargando...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Asegurar que el backdrop se muestre correctamente
                $('#showRoleModal').on('shown.bs.modal', function () {
                    // Forzar backdrop suave sin blur
                    $('.modal-backdrop').css({
                        'background-color': 'rgba(0, 0, 0, 0.5)',
                        'background': 'rgba(0, 0, 0, 0.5)',
                        'opacity': '1',
                        'z-index': '1040'
                    });
                });

                // Limpiar cuando se cierre el modal
                $('#showRoleModal').on('hidden.bs.modal', function () {
                    // Forzar limpieza del backdrop
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    $('body').css('padding-right', '');
                });

                // Manejar el cierre del modal
                $('#showRoleModal .close').click(function() {
                    $('#showRoleModal').modal('hide');
                });

                // También permitir cerrar con ESC y haciendo clic fuera del modal
                $('#showRoleModal').on('keydown', function(e) {
                    if (e.key === 'Escape') {
                        $('#showRoleModal').modal('hide');
                        // Limpiar backdrop inmediatamente
                        setTimeout(function() {
                            $('.modal-backdrop').remove();
                            $('body').removeClass('modal-open');
                            $('body').css('padding-right', '');
                        }, 100);
                    }
                });

                // Cerrar al hacer clic en el backdrop
                $('#showRoleModal').on('click', function(e) {
                    if (e.target === this) {
                        $('#showRoleModal').modal('hide');
                        // Limpiar backdrop inmediatamente
                        setTimeout(function() {
                            $('.modal-backdrop').remove();
                            $('body').removeClass('modal-open');
                            $('body').css('padding-right', '');
                        }, 100);
                    }
                });

                // Obtener datos del rol
                $.ajax({
                    url: `/roles/${roleId}`,
                    type: 'GET',
                    success: function(response) {
                        if (response.status === 'success') {
                            // Llenar datos en el modal
                            $('#roleName').text(response.role.name);
                            $('#roleCreated').text(response.role.created_at);
                            $('#roleUpdated').text(response.role.updated_at);
                            $('#roleUsers').text(response.role.users_count + ' usuario(s)');
                            $('#rolePermissions').text(response.role.permissions_count +
                                ' permiso(s)');

                            // Cerrar loading y mostrar modal
                            Swal.close();
                            $('#showRoleModal').modal('show');
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error al Obtener Datos',
                                text: 'No se pudieron obtener los datos del rol',
                                confirmButtonText: 'Entendido',
                                customClass: {
                                    confirmButton: 'btn btn-danger'
                                },
                                buttonsStyling: false
                            });
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = 'No se pudieron obtener los datos del rol';
                        
                        try {
                            const response = xhr.responseJSON;
                            if (response && response.message) {
                                errorMessage = response.message;
                            }
                        } catch (e) {
                            console.error('Error al procesar respuesta:', e);
                            if (xhr.status) {
                                errorMessage += ` (Código: ${xhr.status})`;
                            }
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error al Obtener Datos',
                            text: errorMessage,
                            confirmButtonText: 'Entendido',
                            customClass: {
                                confirmButton: 'btn btn-danger'
                            },
                            buttonsStyling: false
                        });
                    }
                });
            });

            // Scripts para el modal de permisos
            $('.assign-permissions').click(function() {
                const roleId = $(this).data('id');
                const roleName = $(this).data('name');

                $('#roleId').val(roleId);
                $('#roleName').text(roleName);

                // Limpiar checkboxes
                $('.permission-checkbox').prop('checked', false);

                // Cargar permisos actuales del rol
                $.get(`/roles/${roleId}/permissions`)
                    .done(function(data) {
                        if (data.status === 'success') {
                            data.permissions.forEach(function(permission) {
                                $(`#permission_${permission.id}`).prop('checked', true);
                            });

                            // Actualizar estados de los selectores de grupo
                            updateGroupSelectors();
                            
                            // Mostrar información adicional del rol si está disponible
                            if (data.role_info && data.role_info.is_system_role) {
                                $('#roleName').append(' <small class="badge badge-warning">Rol del Sistema</small>');
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error al Cargar Permisos',
                                text: data.message || 'No se pudieron cargar los permisos del rol'
                            });
                        }
                    })
                    .fail(function(xhr) {
                        let errorMessage = 'No se pudieron cargar los permisos del rol';
                        
                        try {
                            const response = xhr.responseJSON;
                            if (response && response.message) {
                                errorMessage = response.message;
                            }
                        } catch (e) {
                            console.error('Error al procesar respuesta:', e);
                            if (xhr.status) {
                                errorMessage += ` (Código: ${xhr.status})`;
                            }
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error al Cargar Permisos',
                            text: errorMessage,
                            confirmButtonText: 'Cerrar',
                            customClass: {
                                confirmButton: 'btn btn-danger'
                            },
                            buttonsStyling: false
                        }).then(() => {
                            $('#permissionsModal').modal('hide');
                        });
                    });

                $('#permissionsModal').modal('show');
            });

            // Selector para todos los permisos
            $('#selectAllPermissions').change(function() {
                const isChecked = $(this).prop('checked');
                const searchTerm = $('#searchPermission').val().toLowerCase();

                if (searchTerm) {
                    // Si hay término de búsqueda, solo seleccionar los permisos visibles
                    $('.permission-checkbox').each(function() {
                        const $permissionItem = $(this).closest('.permission-item');
                        if ($permissionItem.is(':visible')) {
                            $(this).prop('checked', isChecked);
                        }
                    });

                    // Actualizar los selectores de grupo
                    $('.group-selector').each(function() {
                        const $card = $(this).closest('.card');
                        if ($card.closest('.col-md-6').is(':visible')) {
                            const totalVisible = $card.find('.permission-item:visible').length;
                            const checkedVisible = $card.find(
                                '.permission-item:visible .permission-checkbox:checked').length;
                            $(this).prop('checked', totalVisible === checkedVisible);
                        }
                    });
                } else {
                    // Si no hay búsqueda, comportamiento normal
                    $('.permission-checkbox').prop('checked', isChecked);
                    $('.group-selector').prop('checked', isChecked);
                }
            });

            // Búsqueda de permisos
            $('#searchPermission').on('input', function() {
                const searchTerm = $(this).val().toLowerCase();

                $('.permissions-container .card').each(function() {
                    const $card = $(this);
                    const $permissionItems = $card.find('.permission-item');
                    let hasVisiblePermissions = false;

                    $permissionItems.each(function() {
                        const friendlyText = $(this).find('label').text().toLowerCase();
                        const technicalText = $(this).find('input').data('name')
                            .toLowerCase();
                        const isVisible = friendlyText.includes(searchTerm) || technicalText
                            .includes(searchTerm);
                        $(this).toggle(isVisible);
                        if (isVisible) {
                            hasVisiblePermissions = true;
                        }
                    });

                    $card.closest('.col-md-6').toggle(hasVisiblePermissions);
                });

                // Resetear el checkbox general
                $('#selectAllPermissions').prop('checked', false);
            });

            // Selector de grupo
            $('.group-selector').change(function() {
                const group = $(this).data('group');
                const checked = $(this).prop('checked');

                $(`.permission-checkbox[data-group="${group}"]`).prop('checked', checked);
            });

            // Actualizar selector de grupo cuando cambian los permisos individuales
            $('.permission-checkbox').change(function() {
                updateGroupSelectors();
            });

            // Guardar cambios
            $('#savePermissions').click(function() {
                const roleId = $('#roleId').val();
                const permissions = $('.permission-checkbox:checked').map(function() {
                    return $(this).val();
                }).get();

                $.ajax({
                    url: `/roles/${roleId}/permissions`,
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        permissions: permissions
                    },
                    success: function(response) {
                        $('#permissionsModal').modal('hide');

                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: 'Los permisos han sido actualizados correctamente',
                            showConfirmButton: false,
                            timer: 1500
                        });

                        // Recargar la tabla de roles
                        window.LaravelDataTables['rolesTable'].ajax.reload();
                    },
                    error: function(xhr) {
                        let errorMessage = 'Hubo un problema al actualizar los permisos';
                        let detailsHtml = '';
                        
                        try {
                            const response = xhr.responseJSON;
                            if (response && response.message) {
                                errorMessage = response.message;
                                
                                // Agregar detalles adicionales si están disponibles
                                if (response.details) {
                                    let details = [];
                                    
                                    if (response.details.error_type) {
                                        details.push(`Tipo: ${response.details.error_type}`);
                                    }
                                    
                                    if (response.details.debug_info) {
                                        const debug = response.details.debug_info;
                                        if (debug.role_id && debug.role_id !== 'N/A') {
                                            details.push(`ID del rol: ${debug.role_id}`);
                                        }
                                        if (debug.company_id && debug.company_id !== 'N/A') {
                                            details.push(`ID de empresa: ${debug.company_id}`);
                                        }
                                        if (debug.permissions_received !== undefined) {
                                            details.push(`Permisos recibidos: ${debug.permissions_received}`);
                                        }
                                        if (debug.user_authenticated !== undefined) {
                                            details.push(`Usuario autenticado: ${debug.user_authenticated ? 'Sí' : 'No'}`);
                                        }
                                    }
                                    
                                    if (details.length > 0) {
                                        detailsHtml = '<br><small class="text-muted"><strong>Detalles:</strong><br>' + 
                                                     details.join('<br>') + '</small>';
                                    }
                                }
                            }
                        } catch (e) {
                            console.error('Error al procesar respuesta de error:', e);
                            // Fallback a mensaje genérico si no se puede parsear la respuesta
                            if (xhr.status) {
                                errorMessage += ` (Código: ${xhr.status})`;
                            }
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error al Actualizar Permisos',
                            html: errorMessage + detailsHtml,
                            confirmButtonText: 'Entendido',
                            customClass: {
                                confirmButton: 'btn btn-danger'
                            },
                            buttonsStyling: false,
                            allowOutsideClick: false
                        });
                    }
                });
            });

                function updateGroupSelectors() {
                    $('.group-selector').each(function() {
                        const group = $(this).data('group');
                        const totalPermissions = $(`.permission-checkbox[data-group="${group}"]`).length;
                        const checkedPermissions = $(`.permission-checkbox[data-group="${group}"]:checked`)
                            .length;

                        $(this).prop('checked', totalPermissions === checkedPermissions);
                    });
                }

                // Event listener adicional para el botón de cerrar del modal
                $(document).on('click', '#showRoleModal .close', function() {
                    $('#showRoleModal').modal('hide');
                    // Limpiar backdrop inmediatamente
                    setTimeout(function() {
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open');
                        $('body').css('padding-right', '');
                    }, 100);
                });

                // Forzar backdrop oscuro cuando se abra el modal
                $('#showRoleModal').on('show.bs.modal', function () {
                    // Crear backdrop manualmente si no existe
                    if ($('.modal-backdrop').length === 0) {
                        $('body').append('<div class="modal-backdrop fade show"></div>');
                    }
                    
                    // Aplicar estilos inmediatamente
                    setTimeout(function() {
                        $('.modal-backdrop').css({
                            'background-color': 'rgba(0, 0, 0, 0.5)',
                            'background': 'rgba(0, 0, 0, 0.5)',
                            'opacity': '1',
                            'z-index': '1040',
                            'position': 'fixed',
                            'top': '0',
                            'left': '0',
                            'width': '100%',
                            'height': '100%'
                        });
                    }, 10);
                });

                // También agregar event listener para el botón de cerrar con data-dismiss
                $(document).on('click', '[data-dismiss="modal"]', function() {
                    $(this).closest('.modal').modal('hide');
                });

                // Limpiar backdrop cuando se cierre cualquier modal
                $(document).on('hidden.bs.modal', function () {
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    $('body').css('padding-right', '');
                });

            });
            
        });
    </script>
@endpush
