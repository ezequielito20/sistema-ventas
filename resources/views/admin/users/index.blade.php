@extends('layouts.app')

@section('title', 'Gestión de Usuarios')

@section('content')
<div class="space-y-6" x-data="usersApp()">
    <!-- Header con gradiente -->
    <div class="page-header">
        <div class="header-content">
            <div class="header-left">
                <div class="header-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="header-text">
                    <h1 class="page-title">Gestión de Usuarios</h1>
                    <p class="page-subtitle">Administra los usuarios del sistema</p>
                </div>
            </div>
            <div class="header-actions">
                @if($permissions['users.report'])
                    <a href="{{ route('admin.users.report') }}" class="btn-action btn-report" target="_blank">
                        <i class="fas fa-file-pdf mr-2"></i>
                        Reporte
                    </a>
                @endif
                @if($permissions['users.create'])
                    <a href="{{ route('admin.users.create') }}" class="btn-action btn-create">
                        <i class="fas fa-user-plus mr-2"></i>
                        Nuevo Usuario
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-4 gap-2 sm:gap-3 mb-6">
        <!-- Total de Usuarios -->
        <x-dashboard-widget 
            title="Total de Usuarios"
            value="{{ $users->count() }}"
            valueType="number"
            icon="fas fa-users"
            trend="Registrados"
            trendIcon="fas fa-user-plus"
            trendColor="text-green-300"
            gradientFrom="from-blue-500"
            gradientTo="to-blue-600"
            progressWidth="100%"
            progressGradientFrom="from-blue-400"
            progressGradientTo="to-blue-500"
        />

        <!-- Usuarios Verificados -->
        <x-dashboard-widget 
            title="Usuarios Verificados"
            value="{{ $users->where('email_verified_at', '!=', null)->count() }}"
            valueType="number"
            icon="fas fa-user-check"
            trend="Verificados"
            trendIcon="fas fa-check-circle"
            trendColor="text-green-300"
            subtitle="{{ $users->count() > 0 ? round(($users->where('email_verified_at', '!=', null)->count() / $users->count()) * 100, 1) . '% del total' : '0% del total' }}"
            subtitleIcon="fas fa-percentage"
            gradientFrom="from-green-500"
            gradientTo="to-emerald-600"
            progressWidth="{{ $users->count() > 0 ? ($users->where('email_verified_at', '!=', null)->count() / $users->count()) * 100 : 0 }}%"
            progressGradientFrom="from-green-400"
            progressGradientTo="to-emerald-500"
        />

        <!-- Usuarios Pendientes -->
        <x-dashboard-widget 
            title="Pendientes de Verificación"
            value="{{ $users->where('email_verified_at', null)->count() }}"
            valueType="number"
            icon="fas fa-user-clock"
            trend="Pendientes"
            trendIcon="fas fa-clock"
            trendColor="text-yellow-300"
            subtitle="{{ $users->count() > 0 ? round(($users->where('email_verified_at', null)->count() / $users->count()) * 100, 1) . '% del total' : '0% del total' }}"
            subtitleIcon="fas fa-percentage"
            gradientFrom="from-yellow-500"
            gradientTo="to-orange-500"
            progressWidth="{{ $users->count() > 0 ? ($users->where('email_verified_at', null)->count() / $users->count()) * 100 : 0 }}%"
            progressGradientFrom="from-yellow-400"
            progressGradientTo="to-orange-400"
        />

        <!-- Usuarios con Roles -->
        <x-dashboard-widget 
            title="Con Roles Asignados"
            value="{{ $users->filter(function ($user) {return $user->roles->count() > 0;})->count() }}"
            valueType="number"
            icon="fas fa-user-tie"
            trend="Asignados"
            trendIcon="fas fa-user-shield"
            trendColor="text-green-300"
            subtitle="{{ $users->count() > 0 ? round(($users->filter(function ($user) {return $user->roles->count() > 0;})->count() / $users->count()) * 100, 1) . '% del total' : '0% del total' }}"
            subtitleIcon="fas fa-percentage"
            gradientFrom="from-purple-500"
            gradientTo="to-indigo-600"
            progressWidth="{{ $users->count() > 0 ? ($users->filter(function ($user) {return $user->roles->count() > 0;})->count() / $users->count()) * 100 : 0 }}%"
            progressGradientFrom="from-purple-400"
            progressGradientTo="to-indigo-500"
        />
    </div>

    

    <!-- Vista de Escritorio -->
    <div class="desktop-view" x-show="viewMode === 'table'">
        <div class="table-container">
            <div class="table-header">
                <div class="table-header-content">
                    <h3 class="table-title">
                        <i class="fas fa-table mr-2"></i>
                        Lista de Usuarios
                    </h3>
                    <div class="table-search-container">
                        <div class="flex items-center space-x-4">
                            <div class="search-input-group">
                                <input type="text" class="search-input" x-model="searchTerm" placeholder="Buscar usuarios...">
                            </div>
                            <!-- Toggle de Vista - Solo visible en pantallas medianas y grandes -->
                            <div class="hidden md:flex items-center space-x-2">
                                <button type="button" class="view-mode-btn" :class="{ 'active': viewMode === 'table' }" @click="changeViewMode('table')">
                                    <i class="fas fa-table mr-2"></i>
                                </button>
                                <button type="button" class="view-mode-btn" :class="{ 'active': viewMode === 'cards' }" @click="changeViewMode('cards')">
                                    <i class="fas fa-th-large mr-2"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-content">
                <table id="usersTable" class="users-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Usuario</th>
                            <th>Email</th>
                            <th>Empresa</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Último Acceso</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr x-show="isUserVisible('{{ strtolower($user->name . ' ' . $user->email . ' ' . ($user->company->name ?? '') . ' ' . $user->roles->pluck('name')->implode(' ')) }}')"
                                data-search="{{ strtolower($user->name . ' ' . $user->email . ' ' . ($user->company->name ?? '') . ' ' . $user->roles->pluck('name')->implode(' ')) }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar-small">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </div>
                                        <div class="user-details">
                                            <div class="user-name">{{ $user->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->company->name ?? 'N/A' }}</td>
                                <td>
                                    @foreach ($user->roles as $role)
                                        <span class="badge badge-role">{{ $role->name }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    @if ($user->email_verified_at)
                                        <span class="badge badge-success">Verificado</span>
                                    @else
                                        <span class="badge badge-warning">Pendiente</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($user->last_login)
                                        <span class="last-login" title="{{ $user->last_login }}">
                                            {{ $user->last_login->diffForHumans() }}
                                        </span>
                                    @else
                                        <span class="text-muted">Nunca</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        @if($permissions['users.show'])
                                            <button type="button" class="action-btn action-view"
                                                @click="showUserDetails({{ $user->id }})" title="Ver Detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        @endif
                                        @if($permissions['users.edit'])
                                            <a href="{{ route('admin.users.edit', $user->id) }}" class="action-btn action-edit"
                                                title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        @if ($user->id !== auth()->id())
                                            @if($permissions['users.destroy'])
                                                <button type="button" class="action-btn action-delete"
                                                    @click="deleteUser({{ $user->id }}, '{{ $user->name }}')" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Paginación inteligente para tabla --}}
            @if($users->hasPages())
                <div class="pagination-container">
                    <div class="pagination-info">
                        <span>Mostrando {{ $users->firstItem() ?? 0 }}-{{ $users->lastItem() ?? 0 }} de {{ $users->total() }} usuarios</span>
                    </div>
                    <div class="pagination-controls">
                        @if($users->hasPrevious)
                            <a href="{{ $users->previousPageUrl }}" class="pagination-btn">
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
                            @foreach($users->smartLinks as $link)
                                @if($link === '...')
                                    <span class="page-separator">...</span>
                                @else
                                    @if($link == $users->currentPage())
                                        <span class="page-number active">{{ $link }}</span>
                                    @else
                                        <a href="{{ $users->url($link) }}" class="page-number">{{ $link }}</a>
                                    @endif
                                @endif
                            @endforeach
                        </div>

                        @if($users->hasNext)
                            <a href="{{ $users->nextPageUrl }}" class="pagination-btn">
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

    <!-- Vista de Tarjetas para Escritorio -->
    <div class="desktop-view" x-show="viewMode === 'cards'">
        <div class="search-container search-container-right">
            <div class="flex items-center space-x-4">
                <div class="search-input-group">
                    <input type="text" class="search-input" x-model="searchTerm" placeholder="Buscar usuarios...">
                </div>
                <!-- Toggle de Vista - Solo visible en pantallas medianas y grandes -->
                <div class="hidden md:flex items-center space-x-2">
                    <button type="button" class="view-mode-btn" :class="{ 'active': viewMode === 'table' }" @click="changeViewMode('table')">
                        <i class="fas fa-table"></i>
                    </button>
                    <button type="button" class="view-mode-btn" :class="{ 'active': viewMode === 'cards' }" @click="changeViewMode('cards')">
                        <i class="fas fa-th-large"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="users-grid">
            @foreach ($users as $user)
                <div class="user-card" 
                     x-show="isUserVisible('{{ strtolower($user->name . ' ' . $user->email . ' ' . ($user->company->name ?? '')) }}')"
                     data-search="{{ strtolower($user->name . ' ' . $user->email . ' ' . ($user->company->name ?? '')) }}">
                    <div class="user-card-header">
                        <div class="user-card-avatar">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                        <div class="user-card-info">
                            <h4 class="user-card-name">{{ $user->name }}</h4>
                            <p class="user-card-email">{{ $user->email }}</p>
                        </div>
                        <div class="user-card-status">
                            @if ($user->email_verified_at)
                                <span class="badge badge-success">Verificado</span>
                            @else
                                <span class="badge badge-warning">Pendiente</span>
                            @endif
                        </div>
                    </div>
                    <div class="user-card-body">
                        <div class="user-card-detail">
                            <span class="detail-label">Empresa:</span>
                            <span class="detail-value">{{ $user->company->name ?? 'N/A' }}</span>
                        </div>
                        <div class="user-card-detail">
                            <span class="detail-label">Roles:</span>
                            <div class="detail-value">
                                @foreach ($user->roles as $role)
                                    <span class="badge badge-role">{{ $role->name }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="user-card-detail">
                            <span class="detail-label">Último Acceso:</span>
                            <span class="detail-value">
                                @if ($user->last_login)
                                    {{ $user->last_login->diffForHumans() }}
                                @else
                                    Nunca
                                @endif
                            </span>
                        </div>
                        <div class="user-card-actions">
                            @if($permissions['users.show'])
                                <button type="button" class="action-btn action-view"
                                    @click="showUserDetails({{ $user->id }})" title="Ver Detalles">
                                    <i class="fas fa-eye"></i>
                                </button>
                            @endif
                            @if($permissions['users.edit'])
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="action-btn action-edit"
                                    title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endif
                            @if ($user->id !== auth()->id())
                                @if($permissions['users.destroy'])
                                    <button type="button" class="action-btn action-delete"
                                        @click="deleteUser({{ $user->id }}, '{{ $user->name }}')" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Paginación inteligente para tarjetas --}}
        @if($users->hasPages())
            <div class="pagination-container">
                <div class="pagination-info">
                    <span>Mostrando {{ $users->firstItem() ?? 0 }}-{{ $users->lastItem() ?? 0 }} de {{ $users->total() }} usuarios</span>
                </div>
                <div class="pagination-controls">
                    @if($users->hasPrevious)
                        <a href="{{ $users->previousPageUrl }}" class="pagination-btn">
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
                        @foreach($users->smartLinks as $link)
                            @if($link === '...')
                                <span class="page-separator">...</span>
                            @else
                                @if($link == $users->currentPage())
                                    <span class="page-number active">{{ $link }}</span>
                                @else
                                    <a href="{{ $users->url($link) }}" class="page-number">{{ $link }}</a>
                                @endif
                            @endif
                        @endforeach
                    </div>

                    @if($users->hasNext)
                        <a href="{{ $users->nextPageUrl }}" class="pagination-btn">
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

    <!-- Vista Móvil -->
    <div class="mobile-view">
        <div class="search-container search-container-right">
            <div class="search-input-group">
                <input type="text" class="search-input" x-model="searchTerm" placeholder="Buscar usuarios...">
            </div>
        </div>

        <div class="users-grid">
            @foreach ($users as $user)
                <div class="user-card"
                     x-show="isUserVisible('{{ strtolower($user->name . ' ' . $user->email . ' ' . ($user->company->name ?? '')) }}')"
                     data-search="{{ strtolower($user->name . ' ' . $user->email . ' ' . ($user->company->name ?? '')) }}">
                    <div class="user-card-header">
                        <div class="user-card-avatar">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                        <div class="user-card-info">
                            <h4 class="user-card-name">{{ $user->name }}</h4>
                            <p class="user-card-email">{{ $user->email }}</p>
                        </div>
                        <div class="user-card-status">
                            @if ($user->email_verified_at)
                                <span class="badge badge-success">Verificado</span>
                            @else
                                <span class="badge badge-warning">Pendiente</span>
                            @endif
                        </div>
                    </div>
                    <div class="user-card-body">
                        <div class="user-card-detail">
                            <span class="detail-label">Empresa:</span>
                            <span class="detail-value">{{ $user->company->name ?? 'N/A' }}</span>
                        </div>
                        <div class="user-card-detail">
                            <span class="detail-label">Roles:</span>
                            <div class="detail-value">
                                @foreach ($user->roles as $role)
                                    <span class="badge badge-role">{{ $role->name }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="user-card-detail">
                            <span class="detail-label">Último Acceso:</span>
                            <span class="detail-value">
                                @if ($user->last_login)
                                    {{ $user->last_login->diffForHumans() }}
                                @else
                                    Nunca
                                @endif
                            </span>
                        </div>
                        <div class="user-card-actions">
                            @if($permissions['users.show'])
                                <button type="button" class="action-btn action-view"
                                    @click="showUserDetails({{ $user->id }})" title="Ver Detalles">
                                    <i class="fas fa-eye"></i>
                                </button>
                            @endif
                            @if($permissions['users.edit'])
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="action-btn action-edit"
                                    title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endif
                            @if ($user->id !== auth()->id())
                                @if($permissions['users.destroy'])
                                    <button type="button" class="action-btn action-delete"
                                        @click="deleteUser({{ $user->id }}, '{{ $user->name }}')" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Paginación inteligente para móvil --}}
        @if($users->hasPages())
            <div class="pagination-container">
                <div class="pagination-info">
                    <span>Mostrando {{ $users->firstItem() ?? 0 }}-{{ $users->lastItem() ?? 0 }} de {{ $users->total() }} usuarios</span>
                </div>
                <div class="pagination-controls">
                    @if($users->hasPrevious)
                        <a href="{{ $users->previousPageUrl }}" class="pagination-btn">
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
                        @foreach($users->smartLinks as $link)
                            @if($link === '...')
                                <span class="page-separator">...</span>
                            @else
                                @if($link == $users->currentPage())
                                    <span class="page-number active">{{ $link }}</span>
                                @else
                                    <a href="{{ $users->url($link) }}" class="page-number">{{ $link }}</a>
                                @endif
                            @endif
                        @endforeach
                    </div>

                    @if($users->hasNext)
                        <a href="{{ $users->nextPageUrl }}" class="pagination-btn">
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

    <!-- Modal de Detalles -->
    <div class="modal-overlay" x-show="showModal" x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" 
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0" @click="closeModal()" style="display: none;">
        <div class="modal-container" @click.stop>
            <div class="modal-header">
                <h3 class="modal-title">
                    <i class="fas fa-user-circle mr-2"></i>
                    Detalles del Usuario
                </h3>
                <button type="button" class="modal-close" @click="closeModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="modal-content-grid">
                    <div class="modal-content-column">
                        <div class="modal-detail-item">
                            <span class="detail-label">Nombre:</span>
                            <span class="detail-value" x-text="selectedUser.name || ''"></span>
                        </div>
                        <div class="modal-detail-item">
                            <span class="detail-label">Email:</span>
                            <span class="detail-value" x-text="selectedUser.email || ''"></span>
                        </div>
                        <div class="modal-detail-item">
                            <span class="detail-label">Empresa:</span>
                            <span class="detail-value" x-text="selectedUser.company_name || 'N/A'"></span>
                        </div>
                    </div>
                    <div class="modal-content-column">
                        <div class="modal-detail-item">
                            <span class="detail-label">Roles:</span>
                            <div class="detail-value" x-html="selectedUser.roles_html || '<span class=\'text-muted\'>Sin rol asignado</span>'"></div>
                        </div>
                        <div class="modal-detail-item">
                            <span class="detail-label">Estado:</span>
                            <div class="detail-value" x-html="selectedUser.verification_html || ''"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" @click="closeModal()">
                    <i class="fas fa-times mr-2"></i>Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/users/index.css') }}">
@endpush

@push('js')
    <script src="{{ asset('js/admin/users/index.js') }}"></script>
@endpush
@endsection
