@extends('layouts.app')

@section('title', 'Gestión de Permisos')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/permissions/index.css') }}">
@endpush

@push('js')
    <script src="{{ asset('js/admin/permissions/index.js') }}" defer></script>
@endpush

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Gestión de Permisos</h1>
            </div>
            <div class="flex items-center space-x-3">
                @can('permissions.report')
                    <a href="{{ route('admin.permissions.report') }}" class="btn-outline" target="_blank">
                        <i class="fas fa-file-pdf mr-2"></i>
                        Reporte
                    </a>
                @endcan
                @can('permissions.create')
                    <a href="{{ route('admin.permissions.create') }}" class="btn-primary">
                        <i class="fas fa-plus-circle mr-2"></i>
                        Nuevo Permiso
                    </a>
                @endcan
            </div>
        </div>

        {{-- Dashboard de Estadísticas Moderno --}}
        <div class="stats-dashboard">
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="stats-card stats-card-primary" title="Total de permisos en el sistema">
                        <div class="stats-card-body">
                            <div class="stats-icon">
                                <i class="fas fa-lock"></i>
                            </div>
                            <div class="stats-content">
                                <h3 class="stats-value">{{ $totalPermissions }}</h3>
                                <p class="stats-label">Total de Permisos</p>
                                <div class="stats-trend">
                                    <i class="fas fa-shield-alt"></i>
                                    <span>Sistema Seguro</span>
                                </div>
                            </div>
                        </div>
                        <div class="stats-wave">
                            <svg viewBox="0 0 1200 120" preserveAspectRatio="none">
                                <path
                                    d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z"
                                    opacity=".25"></path>
                                <path
                                    d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z"
                                    opacity=".5"></path>
                                <path
                                    d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="stats-card stats-card-success" title="Permisos activos y en uso">
                        <div class="stats-card-body">
                            <div class="stats-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stats-content">
                                <h3 class="stats-value">{{ $activePermissions }}</h3>
                                <p class="stats-label">Permisos Activos</p>
                                <div class="stats-trend">
                                    <i class="fas fa-users"></i>
                                    <span>En Uso</span>
                                </div>
                            </div>
                        </div>
                        <div class="stats-wave">
                            <svg viewBox="0 0 1200 120" preserveAspectRatio="none">
                                <path
                                    d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z"
                                    opacity=".25"></path>
                                <path
                                    d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z"
                                    opacity=".5"></path>
                                <path
                                    d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="stats-card stats-card-warning" title="Roles que utilizan permisos">
                        <div class="stats-card-body">
                            <div class="stats-icon">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <div class="stats-content">
                                <h3 class="stats-value">{{ $rolesCount }}</h3>
                                <p class="stats-label">Roles Asociados</p>
                                <div class="stats-trend">
                                    <i class="fas fa-link"></i>
                                    <span>Conectados</span>
                                </div>
                            </div>
                        </div>
                        <div class="stats-wave">
                            <svg viewBox="0 0 1200 120" preserveAspectRatio="none">
                                <path
                                    d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z"
                                    opacity=".25"></path>
                                <path
                                    d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z"
                                    opacity=".5"></path>
                                <path
                                    d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="stats-card stats-card-info" title="Permisos sin asignar">
                        <div class="stats-card-body">
                            <div class="stats-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="stats-content">
                                <h3 class="stats-value">{{ $unusedPermissions }}</h3>
                                <p class="stats-label">Sin Usar</p>
                                <div class="stats-trend">
                                    <i class="fas fa-warning"></i>
                                    <span>Pendientes</span>
                                </div>
                            </div>
                        </div>
                        <div class="stats-wave">
                            <svg viewBox="0 0 1200 120" preserveAspectRatio="none">
                                <path
                                    d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z"
                                    opacity=".25"></path>
                                <path
                                    d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z"
                                    opacity=".5"></path>
                                <path
                                    d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z">
                                </path>
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
                        <i class="fas fa-key"></i>
                    </div>
                    <div>
                        <h3 class="modern-title">Lista de Permisos</h3>
                        <p class="modern-subtitle">Gestiona y visualiza todos los permisos del sistema</p>
                    </div>
                </div>
                <div class="modern-card-actions">
                    <div class="search-container">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" placeholder="Buscar permisos..." id="searchInput" class="search-input">
                        </div>
                    </div>
                    <div class="view-toggles">
                        <button class="view-toggle active" data-view="table">
                            <i class="fas fa-table"></i>
                            <span>Tabla</span>
                        </button>
                        <button class="view-toggle" data-view="cards">
                            <i class="fas fa-th-large"></i>
                            <span>Tarjetas</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="modern-card-body">
                <!-- Tabla de Permisos -->
                <div class="table-responsive" id="tableView">
                    <table id="permissionsTable" class="table permissions-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nombre del Permiso</th>
                                <th>Guard</th>
                                <th>Roles Asignados</th>
                                <th>Usuarios</th>
                                <th>Fecha de Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($permissions as $permission)
                                <tr class="table-row-hover">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <span class="permission-name">{{ $permission->name }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="guard-badge">{{ $permission->guard_name }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="roles-badge">
                                            {{ $permission->roles->count() }}
                                            <i class="fas fa-user-shield"></i>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="users-badge">
                                            {{ $permission->users_count ?? 0 }}
                                            <i class="fas fa-users"></i>
                                        </span>
                                    </td>
                                    <td class="text-center">{{ $permission->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-center">
                                        <div class="action-buttons">
                                            @can('permissions.show')
                                                <button type="button" class="action-button show"
                                                    data-id="{{ $permission->id }}">
                                                    <i class="fas fa-eye"></i>
                                                    <span class="action-text">Ver</span>
                                                </button>
                                            @endcan
                                            @can('permissions.edit')
                                                <a href="{{ route('admin.permissions.edit', $permission->id) }}"
                                                    class="action-button edit">
                                                    <i class="fas fa-edit"></i>
                                                    <span class="action-text">Editar</span>
                                                </a>
                                            @endcan
                                            @can('permissions.destroy')
                                                <button type="button" class="action-button delete"
                                                    data-id="{{ $permission->id }}">
                                                    <i class="fas fa-trash"></i>
                                                    <span class="action-text">Eliminar</span>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Vista de Tarjetas Móviles -->
                <div class="mobile-cards-view" id="mobileCardsView">
                    @foreach ($permissions as $permission)
                        <div class="mobile-permission-card">
                            <!-- Header de la tarjeta -->
                            <div class="card-header">
                                <div>
                                    <h6>{{ $permission->name }}</h6>
                                    <small>ID: {{ $permission->id }}</small>
                                </div>
                                <span>#{{ $loop->iteration }}</span>
                            </div>

                            <!-- Contenido de la tarjeta -->
                            <div class="card-body">
                                <!-- Información principal -->
                                <div class="permission-info">
                                    <div class="info-row">
                                        <span class="info-label">
                                            <i class="fas fa-shield-alt"></i>
                                            Guard:
                                        </span>
                                        <span class="guard-badge">
                                            {{ $permission->guard_name }}
                                        </span>
                                    </div>

                                    <div class="info-row">
                                        <span class="info-label">
                                            <i class="fas fa-user-shield"></i>
                                            Roles Asignados:
                                        </span>
                                        <span class="roles-badge">
                                            {{ $permission->roles->count() }}
                                            <i class="fas fa-user-shield"></i>
                                        </span>
                                    </div>

                                    <div class="info-row">
                                        <span class="info-label">
                                            <i class="fas fa-users"></i>
                                            Usuarios:
                                        </span>
                                        <span class="users-badge">
                                            {{ $permission->users_count ?? 0 }}
                                            <i class="fas fa-users"></i>
                                        </span>
                                    </div>

                                    <div class="info-row">
                                        <span class="info-label">
                                            <i class="fas fa-calendar"></i>
                                            Creado:
                                        </span>
                                        <span class="info-value">{{ $permission->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                </div>

                                <!-- Botones de acción -->
                                <div class="card-actions">
                                    @can('permissions.show')
                                        <button type="button" class="action-button show" data-id="{{ $permission->id }}">
                                            <i class="fas fa-eye"></i>
                                            <span class="action-text">Ver</span>
                                        </button>
                                    @endcan
                                    @can('permissions.edit')
                                        <a href="{{ route('admin.permissions.edit', $permission->id) }}"
                                            class="action-button edit">
                                            <i class="fas fa-edit"></i>
                                            <span class="action-text">Editar</span>
                                        </a>
                                    @endcan
                                    @can('permissions.destroy')
                                        <button type="button" class="action-button delete" data-id="{{ $permission->id }}">
                                            <i class="fas fa-trash"></i>
                                            <span class="action-text">Eliminar</span>
                                        </button>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Vista de Tarjetas -->
                <div class="cards-view" id="cardsView">
                    <div class="row">
                        @foreach ($permissions as $permission)
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="permission-card">
                                    <div class="card-header">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0 font-weight-bold">{{ $permission->name }}</h6>
                                            <span class="badge badge-light">#{{ $loop->iteration }}</span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="permission-info">
                                            <div class="info-row">
                                                <span>Guard:</span>
                                                <span class="guard-badge">{{ $permission->guard_name }}</span>
                                            </div>
                                            <div class="info-row">
                                                <span>Roles:</span>
                                                <span class="roles-badge">
                                                    {{ $permission->roles->count() }}
                                                    <i class="fas fa-user-shield"></i>
                                                </span>
                                            </div>
                                            <div class="info-row">
                                                <span>Usuarios:</span>
                                                <span class="users-badge">
                                                    {{ $permission->users_count ?? 0 }}
                                                    <i class="fas fa-users"></i>
                                                </span>
                                            </div>
                                            <div class="info-row">
                                                <span>Creado:</span>
                                                <span>{{ $permission->created_at->format('d/m/Y') }}</span>
                                            </div>
                                        </div>
                                        <div class="card-actions">
                                            @can('permissions.show')
                                                <button type="button" class="action-button show"
                                                    data-id="{{ $permission->id }}" title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            @endcan
                                            @can('permissions.edit')
                                                <a href="{{ route('admin.permissions.edit', $permission->id) }}"
                                                    class="action-button edit" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan
                                            @can('permissions.destroy')
                                                <button type="button" class="action-button delete"
                                                    data-id="{{ $permission->id }}" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Paginación -->
        <div class="pagination-container">
            <div class="pagination-info">
                <span class="pagination-text-desktop">Mostrando {{ $permissions->firstItem() ?? 0 }} a
                    {{ $permissions->lastItem() ?? 0 }} de {{ $permissions->total() }} permisos</span>
                <span class="pagination-text-mobile">Página {{ $permissions->currentPage() }} de
                    {{ $permissions->lastPage() }}</span>
            </div>
            <div class="pagination-links">
                @if ($permissions->hasPages())
                    <!-- Botón Anterior -->
                    @if ($permissions->onFirstPage())
                        <span class="disabled">
                            <i class="fas fa-chevron-left"></i>
                            <span class="pagination-text-desktop">Anterior</span>
                            <span class="pagination-text-mobile">Ant</span>
                        </span>
                    @else
                        <a href="{{ $permissions->previousPageUrl() }}">
                            <i class="fas fa-chevron-left"></i>
                            <span class="pagination-text-desktop">Anterior</span>
                            <span class="pagination-text-mobile">Ant</span>
                        </a>
                    @endif

                    <!-- Números de página -->
                    @foreach ($permissions->getUrlRange(1, $permissions->lastPage()) as $page => $url)
                        @if ($page == $permissions->currentPage())
                            <span class="active">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach

                    <!-- Botón Siguiente -->
                    @if ($permissions->hasMorePages())
                        <a href="{{ $permissions->nextPageUrl() }}">
                            <span class="pagination-text-desktop">Siguiente</span>
                            <span class="pagination-text-mobile">Sig</span>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    @else
                        <span class="disabled">
                            <span class="pagination-text-desktop">Siguiente</span>
                            <span class="pagination-text-mobile">Sig</span>
                            <i class="fas fa-chevron-right"></i>
                        </span>
                    @endif
                @endif
            </div>
        </div>
    </div>
    </div>

    <!-- Modal para Ver Detalles del Permiso -->
    <div x-data="permissionModal()" id="permissionModal" x-cloak 
         x-show="isOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="modal-overlay"
         @click.self="closeModal()"
         @keydown.escape.window="closeModal()"
         style="display: none;">
        
        <div class="modal-container"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95">
            
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-key"></i>
                        Detalles del Permiso
                    </h5>
                    <button type="button" 
                            class="modal-close-btn" 
                            @click="closeModal()" 
                            aria-label="Close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="modal-body">
                    <!-- Información Principal -->
                    <div class="permission-main-info">
                        <div>
                            <i class="fas fa-key"></i>
                            <h3 x-text="permissionData.name || 'Cargando...'"></h3>
                            <div x-text="permissionData.guard || 'Cargando...'"></div>
                        </div>
                    </div>

                    <!-- Estadísticas -->
                    <div class="permission-stats">
                        <div class="stat-card">
                            <div class="stat-content">
                                <div class="stat-icon"
                                    style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div>
                                    <div class="stat-label">Roles Asignados</div>
                                    <div class="stat-value" x-text="permissionData.roles || '0'" style="color: #10b981;"></div>
                                </div>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-content">
                                <div class="stat-icon"
                                    style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
                                    <i class="fas fa-user-friends"></i>
                                </div>
                                <div>
                                    <div class="stat-label">Usuarios con Permiso</div>
                                    <div class="stat-value" x-text="permissionData.users || '0'" style="color: #8b5cf6;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información de Fechas -->
                    <div class="permission-dates">
                        <h4>
                            <i class="fas fa-calendar-alt"></i>
                            Información Temporal
                        </h4>

                        <div class="date-grid">
                            <div class="date-info created">
                                <div class="date-icon">
                                    <i class="fas fa-plus"></i>
                                </div>
                                <div>
                                    <div class="date-label">Fecha de Creación</div>
                                    <div class="date-value" x-text="permissionData.created_at || 'Cargando...'"></div>
                                </div>
                            </div>

                            <div class="date-info updated">
                                <div class="date-icon">
                                    <i class="fas fa-edit"></i>
                                </div>
                                <div>
                                    <div class="date-label">Última Actualización</div>
                                    <div class="date-value" x-text="permissionData.updated_at || 'Cargando...'"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <div>
                        <button type="button" 
                                class="modal-close-button" 
                                @click="closeModal()">
                            <i class="fas fa-times"></i>Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
