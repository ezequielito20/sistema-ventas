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
                @can('users.report')
                    <a href="{{ route('admin.users.report') }}" class="btn-action btn-report" target="_blank">
                        <i class="fas fa-file-pdf mr-2"></i>
                        Reporte
                    </a>
                @endcan
                @can('users.create')
                    <a href="{{ route('admin.users.create') }}" class="btn-action btn-create">
                        <i class="fas fa-user-plus mr-2"></i>
                        Nuevo Usuario
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total de Usuarios -->
        <div class="group relative bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
            <!-- Gradient Background -->
            <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-blue-600 opacity-5 group-hover:opacity-10 transition-opacity duration-300"></div>

            <!-- Content -->
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-users text-white text-xl"></i>
                    </div>
                </div>

                <div class="space-y-2">
                    <div class="text-3xl font-bold text-gray-900" data-stat="total-users">
                        {{ $users->count() }}
                    </div>
                    <div class="text-sm font-medium text-gray-600">Total de Usuarios</div>

                    <!-- Progress Bar -->
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-3">
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-2 rounded-full" style="width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Usuarios Verificados -->
        <div class="group relative bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
            <!-- Gradient Background -->
            <div class="absolute inset-0 bg-gradient-to-br from-green-500 to-emerald-600 opacity-5 group-hover:opacity-10 transition-opacity duration-300"></div>

            <!-- Content -->
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-user-check text-white text-xl"></i>
                    </div>
                </div>

                <div class="space-y-2">
                    <div class="text-3xl font-bold text-gray-900" data-stat="verified-users">
                        <span>{{ $users->where('email_verified_at', '!=', null)->count() }}</span>
                        <span class="text-lg text-gray-500">/{{ $users->count() }}</span>
                    </div>
                    <div class="text-sm font-medium text-gray-600">Usuarios Verificados</div>

                    <!-- Progress Bar -->
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-3">
                        <div class="bg-gradient-to-r from-green-500 to-emerald-600 h-2 rounded-full"
                            style="width: {{ $users->count() > 0 ? ($users->where('email_verified_at', '!=', null)->count() / $users->count()) * 100 : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Usuarios Pendientes -->
        <div class="group relative bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
            <!-- Gradient Background -->
            <div class="absolute inset-0 bg-gradient-to-br from-yellow-500 to-orange-500 opacity-5 group-hover:opacity-10 transition-opacity duration-300"></div>

            <!-- Content -->
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-yellow-500 to-orange-500 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-user-clock text-white text-xl"></i>
                    </div>
                </div>

                <div class="space-y-2">
                    <div class="text-3xl font-bold text-gray-900" data-stat="pending-users">
                        {{ $users->where('email_verified_at', null)->count() }}
                    </div>
                    <div class="text-sm font-medium text-gray-600">Pendientes de Verificación</div>

                    <!-- Progress Bar -->
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-3">
                        <div class="bg-gradient-to-r from-yellow-500 to-orange-500 h-2 rounded-full"
                            style="width: {{ $users->count() > 0 ? ($users->where('email_verified_at', null)->count() / $users->count()) * 100 : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Usuarios con Roles -->
        <div class="group relative bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
            <!-- Gradient Background -->
            <div class="absolute inset-0 bg-gradient-to-br from-purple-500 to-indigo-600 opacity-5 group-hover:opacity-10 transition-opacity duration-300"></div>

            <!-- Content -->
            <div class="relative p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-user-tie text-white text-xl"></i>
                    </div>
                </div>

                <div class="space-y-2">
                    <div class="text-3xl font-bold text-gray-900" data-stat="users-with-roles">
                        <span>{{ $users->filter(function ($user) {return $user->roles->count() > 0;})->count() }}</span>
                        <span class="text-lg text-gray-500">/{{ $users->count() }}</span>
                    </div>
                    <div class="text-sm font-medium text-gray-600">Con Roles Asignados</div>

                    <!-- Progress Bar -->
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-3">
                        <div class="bg-gradient-to-r from-purple-500 to-indigo-600 h-2 rounded-full"
                            style="width: {{ $users->count() > 0 ? ($users->filter(function ($user) {return $user->roles->count() > 0;})->count() / $users->count()) * 100 : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Selector de Modo de Vista (solo en pantallas grandes) -->
    <div class="view-mode-selector">
        <div class="view-mode-buttons">
            <button type="button" class="view-mode-btn" :class="{ 'active': viewMode === 'table' }" @click="changeViewMode('table')">
                <i class="fas fa-table"></i>
                Vista Tabla
            </button>
            <button type="button" class="view-mode-btn" :class="{ 'active': viewMode === 'cards' }" @click="changeViewMode('cards')">
                <i class="fas fa-th-large"></i>
                Vista Tarjetas
            </button>
        </div>
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
                        <div class="search-input-group">
                            <input type="text" class="search-input" x-model="searchTerm" placeholder="Buscar usuarios...">
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
                                        @can('users.show')
                                            <button type="button" class="action-btn action-view"
                                                @click="showUserDetails({{ $user->id }})" title="Ver Detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        @endcan
                                        @can('users.edit')
                                            <a href="{{ route('admin.users.edit', $user->id) }}" class="action-btn action-edit"
                                                title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan
                                        @if ($user->id !== auth()->id())
                                            @can('users.destroy')
                                                <button type="button" class="action-btn action-delete"
                                                    @click="deleteUser({{ $user->id }}, '{{ $user->name }}')" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endcan
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Vista de Tarjetas para Escritorio -->
    <div class="desktop-view" x-show="viewMode === 'cards'">
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
                            @can('users.show')
                                <button type="button" class="action-btn action-view"
                                    @click="showUserDetails({{ $user->id }})" title="Ver Detalles">
                                    <i class="fas fa-eye"></i>
                                </button>
                            @endcan
                            @can('users.edit')
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="action-btn action-edit"
                                    title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endcan
                            @if ($user->id !== auth()->id())
                                @can('users.destroy')
                                    <button type="button" class="action-btn action-delete"
                                        @click="deleteUser({{ $user->id }}, '{{ $user->name }}')" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endcan
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
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
                            @can('users.show')
                                <button type="button" class="action-btn action-view"
                                    @click="showUserDetails({{ $user->id }})" title="Ver Detalles">
                                    <i class="fas fa-eye"></i>
                                </button>
                            @endcan
                            @can('users.edit')
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="action-btn action-edit"
                                    title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endcan
                            @if ($user->id !== auth()->id())
                                @can('users.destroy')
                                    <button type="button" class="action-btn action-delete"
                                        @click="deleteUser({{ $user->id }}, '{{ $user->name }}')" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endcan
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
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
