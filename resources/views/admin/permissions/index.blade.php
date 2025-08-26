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
                @if($permissions['can_report'])
                    <a href="{{ route('admin.permissions.report') }}" class="btn-outline" target="_blank">
                        <i class="fas fa-file-pdf mr-2"></i>
                        <span>Reporte</span>
                    </a>
                @endif
                @if($permissions['can_create'])
                    <a href="{{ route('admin.permissions.create') }}" class="btn-primary">
                        <i class="fas fa-plus-circle mr-2"></i>
                        <span>Nuevo Permiso</span>
                    </a>
                @endif
            </div>
        </div>

        {{-- Dashboard de Estadísticas Moderno --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-4 gap-2 sm:gap-3 mb-6">
            <!-- Total de Permisos -->
            <x-dashboard-widget 
                title="Total de Permisos"
                value="{{ $totalPermissions }}"
                valueType="number"
                icon="fas fa-lock"
                trend="Sistema Seguro"
                trendIcon="fas fa-shield-alt"
                trendColor="text-green-300"
                gradientFrom="from-blue-500"
                gradientTo="to-indigo-600"
                progressWidth="100%"
                progressGradientFrom="from-blue-400"
                progressGradientTo="to-indigo-400"
            />

            <!-- Permisos Activos -->
            <x-dashboard-widget 
                title="Permisos Activos"
                value="{{ $activePermissions }}"
                valueType="number"
                icon="fas fa-check-circle"
                trend="En Uso"
                trendIcon="fas fa-users"
                trendColor="text-green-300"
                gradientFrom="from-green-500"
                gradientTo="to-emerald-600"
                progressWidth="100%"
                progressGradientFrom="from-green-400"
                progressGradientTo="to-emerald-400"
            />

            <!-- Roles Asociados -->
            <x-dashboard-widget 
                title="Roles Asociados"
                value="{{ $rolesCount }}"
                valueType="number"
                icon="fas fa-user-shield"
                trend="Conectados"
                trendIcon="fas fa-link"
                trendColor="text-green-300"
                gradientFrom="from-yellow-500"
                gradientTo="to-orange-500"
                progressWidth="100%"
                progressGradientFrom="from-yellow-400"
                progressGradientTo="to-orange-400"
            />

            <!-- Permisos Sin Usar -->
            <x-dashboard-widget 
                title="Sin Usar"
                value="{{ $unusedPermissions }}"
                valueType="number"
                icon="fas fa-exclamation-triangle"
                trend="Pendientes"
                trendIcon="fas fa-warning"
                trendColor="text-yellow-300"
                gradientFrom="from-red-500"
                gradientTo="to-pink-600"
                progressWidth="100%"
                progressGradientFrom="from-red-400"
                progressGradientTo="to-pink-400"
            />
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
                            <input type="text" 
                                   placeholder="Buscar permisos..." 
                                   id="searchInput" 
                                   class="search-input"
                                   autocomplete="off"
                                   spellcheck="false"
                                   autocorrect="off"
                                   autocapitalize="off">
                        </div>
                    </div>
                    <div class="view-toggles view-toggle-buttons">
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
                            @foreach ($permissionsList as $permission)
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
                                            @if($permissions['can_show'])
                                                <button type="button" class="action-button show"
                                                    data-id="{{ $permission->id }}">
                                                    <i class="fas fa-eye"></i>
                                                    <span class="action-text">Ver</span>
                                                </button>
                                            @endif
                                            @if($permissions['can_edit'])
                                                <a href="{{ route('admin.permissions.edit', $permission->id) }}"
                                                    class="action-button edit">
                                                    <i class="fas fa-edit"></i>
                                                    <span class="action-text">Editar</span>
                                                </a>
                                            @endif
                                            @if($permissions['can_destroy'])
                                                <button type="button" class="action-button delete"
                                                    data-id="{{ $permission->id }}">
                                                    <i class="fas fa-trash"></i>
                                                    <span class="action-text">Eliminar</span>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Vista de Tarjetas Móviles -->
                <div class="mobile-cards-view" id="mobileCardsView">
                    @foreach ($permissionsList as $permission)
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
                                    @if($permissions['can_show'])
                                        <button type="button" class="action-button show" data-id="{{ $permission->id }}">
                                            <i class="fas fa-eye"></i>
                                            <span class="action-text">Ver</span>
                                        </button>
                                    @endif
                                    @if($permissions['can_edit'])
                                        <a href="{{ route('admin.permissions.edit', $permission->id) }}"
                                            class="action-button edit">
                                            <i class="fas fa-edit"></i>
                                            <span class="action-text">Editar</span>
                                        </a>
                                    @endif
                                    @if($permissions['can_destroy'])
                                        <button type="button" class="action-button delete" data-id="{{ $permission->id }}">
                                            <i class="fas fa-trash"></i>
                                            <span class="action-text">Eliminar</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Vista de Tarjetas -->
                <div class="cards-view" id="cardsView">
                    <div class="row">
                        @foreach ($permissionsList as $permission)
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
                                            @if($permissions['can_show'])
                                                <button type="button" class="action-button show"
                                                    data-id="{{ $permission->id }}" title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            @endif
                                            @if($permissions['can_edit'])
                                                <a href="{{ route('admin.permissions.edit', $permission->id) }}"
                                                    class="action-button edit" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                            @if($permissions['can_destroy'])
                                                <button type="button" class="action-button delete"
                                                    data-id="{{ $permission->id }}" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Paginación Inteligente -->
        @if($permissionsList->hasPages())
            <div class="mt-8 px-6">
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="pagination-container">
                        <div class="pagination-info">
                            <span>Mostrando {{ $permissionsList->firstItem() ?? 0 }}-{{ $permissionsList->lastItem() ?? 0 }} de {{ $permissionsList->total() }} permisos</span>
                        </div>
                        <div class="pagination-controls">
                            @if($permissionsList->hasPrevious)
                                <a href="{{ $permissionsList->previousPageUrl }}" class="pagination-btn">
                                    <i class="fas fa-chevron-left"></i>
                                    <span>Anterior</span>
                                </a>
                            @else
                                <button class="pagination-btn" disabled>
                                    <i class="fas fa-chevron-left"></i>
                                    <span>Anterior</span>
                                </button>
                            @endif

                            <div class="page-numbers">
                                @foreach($permissionsList->smartLinks as $link)
                                    @if($link === '...')
                                        <span class="page-separator">...</span>
                                    @else
                                        @if($link == $permissionsList->currentPage())
                                            <span class="page-number active">{{ $link }}</span>
                                        @else
                                            <a href="{{ $permissionsList->url($link) }}" class="page-number">{{ $link }}</a>
                                        @endif
                                    @endif
                                @endforeach
                            </div>

                            @if($permissionsList->hasNext)
                                <a href="{{ $permissionsList->nextPageUrl }}" class="pagination-btn">
                                    <span>Siguiente</span>
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            @else
                                <button class="pagination-btn" disabled>
                                    <span>Siguiente</span>
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
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
