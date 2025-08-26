@extends('layouts.app')

@section('title', 'Gestión de Roles')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/roles/index.css') }}">
@endpush

@push('js')
    <script src="{{ asset('vendor/config.js') }}"></script>
    <script src="{{ asset('js/shared/table-utils.js') }}"></script>
    <script src="{{ asset('js/admin/roles/index.js') }}?v={{ time() }}" defer></script>
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
                            @if($permissions['can_create'])
                                <a href="{{ route('admin.roles.create') }}" 
                                   class="inline-flex items-center px-6 py-3 bg-white/20 backdrop-blur-sm border border-white/30 rounded-xl text-white font-semibold hover:bg-white/30">
                                    <i class="fas fa-plus-circle mr-2"></i>
                                    Crear Nuevo Rol
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard de Estadísticas -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-4 gap-2 sm:gap-3 mb-6">
            <!-- Total de Roles -->
            <x-dashboard-widget 
                title="Total de Roles"
                value="{{ $roles->count() }}"
                valueType="number"
                icon="fas fa-user-shield"
                trend="Activos"
                trendIcon="fas fa-check-circle"
                trendColor="text-green-300"
                gradientFrom="from-blue-500"
                gradientTo="to-indigo-600"
                progressWidth="100%"
                progressGradientFrom="from-blue-400"
                progressGradientTo="to-indigo-400"
            />

            <!-- Usuarios Asignados -->
            <x-dashboard-widget 
                title="Usuarios Asignados"
                value="{{ $roles->sum('users_count') }}"
                valueType="number"
                icon="fas fa-users"
                trend="Asignados"
                trendIcon="fas fa-user-check"
                trendColor="text-green-300"
                gradientFrom="from-green-500"
                gradientTo="to-emerald-600"
                progressWidth="100%"
                progressGradientFrom="from-green-400"
                progressGradientTo="to-emerald-400"
            />

            <!-- Permisos Disponibles -->
            <x-dashboard-widget 
                title="Permisos Disponibles"
                value="{{ $permissionsList->flatten()->count() }}"
                valueType="number"
                icon="fas fa-key"
                trend="Disponibles"
                trendIcon="fas fa-unlock"
                trendColor="text-green-300"
                gradientFrom="from-yellow-500"
                gradientTo="to-orange-500"
                progressWidth="100%"
                progressGradientFrom="from-yellow-400"
                progressGradientTo="to-orange-400"
            />

            <!-- Roles del Sistema -->
            <x-dashboard-widget 
                title="Roles del Sistema"
                value="{{ $roles->filter(function($role) { return $role->isSystemRole(); })->count() }}"
                valueType="number"
                icon="fas fa-shield-alt"
                trend="Sistema"
                trendIcon="fas fa-shield-check"
                trendColor="text-green-300"
                gradientFrom="from-purple-500"
                gradientTo="to-indigo-600"
                progressWidth="100%"
                progressGradientFrom="from-purple-400"
                progressGradientTo="to-indigo-400"
            />
        </div>

        <!-- Selector de Modo de Vista (solo en pantallas grandes) -->
        <div class="view-mode-selector mb-6">
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <!-- Título y Descripción -->
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-list text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Lista de Roles</h3>
                            <p class="text-sm text-gray-500">Gestiona los roles del sistema</p>
                        </div>
                    </div>

                    <!-- Controles de Búsqueda y Vista -->
                    <div class="flex flex-col sm:flex-row items-center gap-4">
                        <!-- Barra de Búsqueda -->
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" 
                                   x-model="searchTerm" 
                                   placeholder="Buscar roles..." 
                                   class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-full sm:w-64">
                        </div>

                        <!-- Toggle de Vista - Solo visible en pantallas medianas y grandes -->
                        <div class="hidden md:flex items-center space-x-2">
                            <button @click="viewMode = 'table'" 
                                    :class="viewMode === 'table' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-600'"
                                    class="px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200">
                                <i class="fas fa-table mr-2"></i>
                                Tabla
                            </button>
                            <button @click="viewMode = 'cards'" 
                                    :class="viewMode === 'cards' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-600'"
                                    class="px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200">
                                <i class="fas fa-th-large mr-2"></i>
                                Tarjetas
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel de Controles -->
        <div class="bg-white rounded-2xl shadow-lg mb-8 overflow-hidden">

            <!-- Vista de Tabla - Solo visible en pantallas medianas y grandes -->
            <div x-show="viewMode === 'table'" class="hidden md:block">
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
                                <tr class="table-row" 
                                    x-show="isRoleVisible('{{ strtolower($role->name . ' ' . ($role->isSystemRole() ? 'sistema' : 'personalizado') . ' ' . $role->users_count . ' usuarios ' . $role->permissions_count . ' permisos') }}')"
                                    data-search="{{ strtolower($role->name . ' ' . ($role->isSystemRole() ? 'sistema' : 'personalizado') . ' ' . $role->users_count . ' usuarios ' . $role->permissions_count . ' permisos') }}">
                                    <td>
                                        <div class="row-number">{{ $loop->iteration }}</div>
                                    </td>
                                    <td>
                                        <div class="role-info">
                                            <div class="role-name">{{ $role->name }}</div>
                                            <div class="role-description text-sm text-gray-500">
                                                @if($role->isSystemRole())
                                                    <span class="system-badge">Rol del Sistema</span>
                                                @else
                                                    <span class="custom-badge">Rol Personalizado</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="users-count">
                                            <span class="count-badge">{{ $role->users_count }}</span>
                                            <span class="count-label">usuarios</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="permissions-count">
                                            <span class="count-badge">{{ $role->permissions_count }}</span>
                                            <span class="count-label">permisos</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="system-status">
                                            @if($role->isSystemRole())
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
                                            @if($permissions['can_show'])
                                                <button type="button" class="btn-action btn-view" 
                                                        @click="showRole({{ $role->id }})" title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                            </button>
                        @endif
                                            @if($permissions['can_edit'])
                                                <a href="{{ route('admin.roles.edit', $role->id) }}" 
                                                   class="btn-action btn-edit" title="Editar">
                                                    <i class="fas fa-edit"></i>
                            </a>
                        @endif
                                            @if($permissions['can_assign_permissions'])
                                                <button type="button" class="btn-action btn-permissions" 
                                                        @click="assignPermissions({{ $role->id }}, '{{ $role->name }}')" title="Asignar permisos">
                                                    <i class="fas fa-key"></i>
                            </button>
                        @endif
                                            @if($permissions['can_destroy'])
                                                <button type="button" class="btn-action btn-delete" 
                                                        @click="deleteRole({{ $role->id }})" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                            </button>
                        @endif
                    </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Paginación inteligente para tabla --}}
                @if($roles->hasPages())
                    <div class="pagination-container">
                        <div class="pagination-info">
                            <span>Mostrando {{ $roles->firstItem() ?? 0 }}-{{ $roles->lastItem() ?? 0 }} de {{ $roles->total() }} roles</span>
                        </div>
                        <div class="pagination-controls">
                            @if($roles->hasPrevious)
                                <a href="{{ $roles->previousPageUrl }}" class="pagination-btn">
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
                                @foreach($roles->smartLinks as $link)
                                    @if($link === '...')
                                        <span class="page-separator">...</span>
                                    @else
                                        @if($link == $roles->currentPage())
                                            <span class="page-number active">{{ $link }}</span>
                                        @else
                                            <a href="{{ $roles->url($link) }}" class="page-number">{{ $link }}</a>
                                        @endif
                                    @endif
                                @endforeach
                            </div>

                            @if($roles->hasNext)
                                <a href="{{ $roles->nextPageUrl }}" class="pagination-btn">
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
                @endif
            </div>

            <!-- Vista de Tarjetas - Siempre visible en móviles, condicional en desktop -->
            <div x-show="viewMode === 'cards'" class="block">
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($roles as $role)
                        <div class="bg-white rounded-2xl shadow-lg hover:shadow-xl overflow-hidden border-l-4 {{ $role->isSystemRole() ? 'border-purple-500' : 'border-blue-500' }}"
                             x-show="isRoleVisible('{{ strtolower($role->name . ' ' . ($role->isSystemRole() ? 'sistema' : 'personalizado') . ' ' . $role->users_count . ' usuarios ' . $role->permissions_count . ' permisos') }}')"
                             data-search="{{ strtolower($role->name . ' ' . ($role->isSystemRole() ? 'sistema' : 'personalizado') . ' ' . $role->users_count . ' usuarios ' . $role->permissions_count . ' permisos') }}">
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
                                                @if($role->isSystemRole())
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
                                        @if($role->isSystemRole())
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
                                        <p class="text-sm font-medium text-gray-900">{{ $role->users_count }}</p>
                                    </div>

                                    <!-- Permisos -->
                                    <div class="space-y-1">
                                        <div class="flex items-center space-x-2 text-xs text-gray-500">
                                            <i class="fas fa-key"></i>
                                            <span>Permisos</span>
                                        </div>
                                        <p class="text-sm font-medium text-gray-900">{{ $role->permissions_count }}</p>
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
                        @if($permissions['can_show'])
                                        <button type="button" class="btn-action btn-view" 
                                                @click="showRole({{ $role->id }})" title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </button>
                        @endif
                        @if($permissions['can_edit'])
                                        <a href="{{ route('admin.roles.edit', $role->id) }}" 
                                           class="btn-action btn-edit" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                        @endif
                        @if($permissions['can_assign_permissions'])
                                        <button type="button" class="btn-action btn-permissions" 
                                                @click="assignPermissions({{ $role->id }}, '{{ $role->name }}')" title="Asignar permisos">
                                <i class="fas fa-key"></i>
                                        </button>
                        @endif
                        @if($permissions['can_destroy'])
                                        <button type="button" class="btn-action btn-delete" 
                                                @click="deleteRole({{ $role->id }})" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        @endif
                    </div>
                </div>
                        </div>
                    @endforeach
                </div>

                {{-- Paginación inteligente para tarjetas --}}
                @if($roles->hasPages())
                    <div class="pagination-container">
                        <div class="pagination-info">
                            <span>Mostrando {{ $roles->firstItem() ?? 0 }}-{{ $roles->lastItem() ?? 0 }} de {{ $roles->total() }} roles</span>
                        </div>
                        <div class="pagination-controls">
                            @if($roles->hasPrevious)
                                <a href="{{ $roles->previousPageUrl }}" class="pagination-btn">
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
                                @foreach($roles->smartLinks as $link)
                                    @if($link === '...')
                                        <span class="page-separator">...</span>
                                    @else
                                        @if($link == $roles->currentPage())
                                            <span class="page-number active">{{ $link }}</span>
                                        @else
                                            <a href="{{ $roles->url($link) }}" class="page-number">{{ $link }}</a>
                                        @endif
                                    @endif
                                @endforeach
                            </div>

                            @if($roles->hasNext)
                                <a href="{{ $roles->nextPageUrl }}" class="pagination-btn">
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
                @endif
            </div>
        </div>
    </div>

    {{-- Modal Moderno para mostrar rol --}}
    <div class="modal fade" id="showRoleModal" tabindex="-1" role="dialog" aria-labelledby="showRoleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modern-role-modal">
                <!-- Header con gradiente -->
                <div class="modal-header-gradient">
                    <div class="header-content">
                        <div class="role-icon">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div class="role-title">
                            <h3 id="modalRoleName">Nombre del Rol</h3>
                            <span class="role-subtitle">Detalles del Rol</span>
                        </div>
                    </div>
                </div>
                
                <!-- Contenido del modal -->
                <div class="modal-content-body">
                    <div class="details-grid">
                        <div class="detail-card">
                            <div class="detail-icon">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <div class="detail-content">
                                <span class="detail-label">Fecha de Creación</span>
                                <span class="detail-value" id="modalRoleCreated">-</span>
                            </div>
                        </div>
                        
                        <div class="detail-card">
                            <div class="detail-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="detail-content">
                                <span class="detail-label">Última Actualización</span>
                                <span class="detail-value" id="modalRoleUpdated">-</span>
                            </div>
                        </div>
                        
                        <div class="detail-card">
                            <div class="detail-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="detail-content">
                                <span class="detail-label">Usuarios Asignados</span>
                                <span class="detail-value" id="modalRoleUsers">-</span>
                            </div>
                        </div>
                        
                        <div class="detail-card">
                            <div class="detail-icon">
                                <i class="fas fa-key"></i>
                            </div>
                            <div class="detail-content">
                                <span class="detail-label">Permisos Asignados</span>
                                <span class="detail-value" id="modalRolePermissions">-</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tipo de rol -->
                    <div class="role-type-section">
                        <div class="role-type-badge" id="modalRoleType">
                            <i class="fas fa-shield-alt"></i>
                            <span>Tipo de Rol</span>
                        </div>
                    </div>
                </div>
                
                <!-- Footer del modal -->
                <div class="modal-footer border-0 bg-transparent">
                    <button type="button" class="btn modern-confirm-btn" data-bs-dismiss="modal">
                        <i class="fas fa-times mr-2"></i>Cerrar
                    </button>
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
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
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
                            @foreach ($permissionsList as $module => $modulePermissions)
                                <div class="col-xl-4 col-lg-6 col-12 mb-4">
                                    <div class="permission-module-card">
                                        <div class="permission-module-header">
                                            <div class="permission-module-title">
                                                <i class="fas fa-folder mr-2"></i>{{ $module }}
                                            </div>
                                            <div class="permission-module-selector">
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input group-selector"
                                                        id="modal_group_{{ $module }}" data-group="{{ $module }}">
                                                    <label class="custom-control-label" for="modal_group_{{ $module }}">
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
                                                            id="modal_permission_{{ $permission->id }}"
                                                            value="{{ $permission->id }}"
                                                            data-group="{{ $module }}"
                                                            data-name="{{ $permission->name }}">
                                                        <label class="custom-control-label"
                                                            for="modal_permission_{{ $permission->id }}"
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
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


