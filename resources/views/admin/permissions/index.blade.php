@extends('layouts.app')

@section('title', 'Gestión de Permisos')

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
    <div class="modern-card" style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        <div class="modern-card-header" style="background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%); padding: 2rem; border-radius: 16px 16px 0 0; border-bottom: 2px solid #e2e8f0;">
            <div class="title-content" style="display: flex; align-items: center; gap: 1rem;">
                <div class="title-icon" style="width: 60px; height: 60px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                    <i class="fas fa-key"></i>
                </div>
                <div>
                    <h3 style="color: #1e293b; font-size: 1.5rem; font-weight: 700; margin: 0;">Lista de Permisos</h3>
                    <p style="color: #64748b; margin: 0.5rem 0 0 0;">Gestiona y visualiza todos los permisos del sistema</p>
                </div>
            </div>
            <div class="modern-card-actions" style="display: flex; align-items: center; gap: 1.5rem; margin-top: 1.5rem;">
                <div class="search-container" style="flex: 1; max-width: 400px;">
                    <div class="search-box" style="position: relative;">
                        <i class="fas fa-search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #64748b;"></i>
                        <input type="text" placeholder="Buscar permisos..." id="searchInput" style="width: 100%; padding: 0.75rem 1rem 0.75rem 2.5rem; border: 2px solid #e2e8f0; border-radius: 8px; background: white; transition: all 0.3s;">
                    </div>
                </div>
                <div class="view-toggles" style="display: flex; gap: 0.5rem;">
                    <button class="view-toggle active" data-view="table" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 0.75rem 1rem; border-radius: 8px; font-weight: 600; transition: all 0.3s;">
                        <i class="fas fa-table"></i>
                        <span>Tabla</span>
                    </button>
                    <button class="view-toggle" data-view="cards" style="background: white; color: #64748b; border: 2px solid #e2e8f0; padding: 0.75rem 1rem; border-radius: 8px; font-weight: 600; transition: all 0.3s;">
                        <i class="fas fa-th-large"></i>
                        <span>Tarjetas</span>
                    </button>
                </div>
            </div>
        </div>
        
        <div class="modern-card-body" style="padding: 2rem; background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border-radius: 0 0 16px 16px;">
            <!-- Tabla de Permisos -->
            <div class="table-responsive" id="tableView" style="background: white; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); overflow: hidden; width: 100%;">
                <table id="permissionsTable" class="table" style="margin-bottom: 0; border: none; width: 100%;">
                    <thead style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <tr style="border: none;">
                            <th style="width: 5%; padding: 1.25rem 1rem; border: none; font-weight: 700; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.5px; text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);">#</th>
                            <th style="width: 25%; padding: 1.25rem 1rem; border: none; font-weight: 700; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.5px; text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);">Nombre del Permiso</th>
                            <th style="width: 10%; padding: 1.25rem 1rem; border: none; font-weight: 700; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.5px; text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);">Guard</th>
                            <th style="width: 15%; padding: 1.25rem 1rem; border: none; font-weight: 700; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.5px; text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);">Roles Asignados</th>
                            <th style="width: 15%; padding: 1.25rem 1rem; border: none; font-weight: 700; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.5px; text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);">Usuarios</th>
                            <th style="width: 15%; padding: 1.25rem 1rem; border: none; font-weight: 700; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.5px; text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);">Fecha de Creación</th>
                            <th style="width: 15%; padding: 1.25rem 1rem; border: none; font-weight: 700; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.5px; text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($permissions as $permission)
                            <tr style="border-bottom: 1px solid #f1f5f9; transition: all 0.3s ease;" class="table-row-hover">
                                <td style="padding: 1.25rem 1rem; vertical-align: middle; border: none; font-weight: 600; color: #64748b; text-align: center;">{{ $loop->iteration }}</td>
                                <td style="padding: 1.25rem 1rem; vertical-align: middle; border: none;">
                                    <span style="font-weight: 700; color: #667eea; font-size: 0.95rem;">{{ $permission->name }}</span>
                                </td>
                                <td style="padding: 1.25rem 1rem; vertical-align: middle; border: none; text-align: center;">
                                    <span style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); color: white; padding: 0.5rem 0.75rem; border-radius: 8px; font-size: 0.75rem; font-weight: 600; box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2);">{{ $permission->guard_name }}</span>
                                </td>
                                <td style="padding: 1.25rem 1rem; vertical-align: middle; border: none; text-align: center;">
                                    <span style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 0.5rem 0.75rem; border-radius: 8px; font-size: 0.75rem; font-weight: 600; box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);">
                                        {{ $permission->roles->count() }}
                                        <i class="fas fa-user-shield" style="margin-left: 0.25rem;"></i>
                                    </span>
                                </td>
                                <td style="padding: 1.25rem 1rem; vertical-align: middle; border: none; text-align: center;">
                                    <span style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: white; padding: 0.5rem 0.75rem; border-radius: 8px; font-size: 0.75rem; font-weight: 600; box-shadow: 0 2px 4px rgba(139, 92, 246, 0.2);">
                                        {{ $permission->users_count ?? 0 }}
                                        <i class="fas fa-users" style="margin-left: 0.25rem;"></i>
                                    </span>
                                </td>
                                <td style="padding: 1.25rem 1rem; vertical-align: middle; border: none; text-align: center; color: #64748b; font-weight: 500;">{{ $permission->created_at->format('d/m/Y H:i') }}</td>
                                <td style="padding: 1.25rem 1rem; vertical-align: middle; border: none; text-align: center;">
                                    <div class="action-buttons" style="display: flex; gap: 0.5rem; justify-content: center;">
                                        @can('permissions.show')
                                            <button type="button" class="btn-show-permission" data-id="{{ $permission->id }}" style="width: 36px; height: 36px; border-radius: 8px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none; color: white; display: flex; align-items: center; justify-content: center; font-size: 0.875rem; transition: all 0.3s; box-shadow: 0 2px 8px rgba(16, 185, 129, 0.2);">
                                                <i class="fas fa-eye"></i>
                                                <span class="action-text">Ver</span>
                                            </button>
                                        @endcan
                                        @can('permissions.edit')
                                            <a href="{{ route('admin.permissions.edit', $permission->id) }}" style="width: 36px; height: 36px; border-radius: 8px; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); border: none; color: white; display: flex; align-items: center; justify-content: center; font-size: 0.875rem; transition: all 0.3s; box-shadow: 0 2px 8px rgba(59, 130, 246, 0.2); text-decoration: none;">
                                                <i class="fas fa-edit"></i>
                                                <span class="action-text">Editar</span>
                                            </a>
                                        @endcan
                                        @can('permissions.destroy')
                                            <button type="button" class="btn-delete-permission" data-id="{{ $permission->id }}" style="width: 36px; height: 36px; border-radius: 8px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border: none; color: white; display: flex; align-items: center; justify-content: center; font-size: 0.875rem; transition: all 0.3s; box-shadow: 0 2px 8px rgba(239, 68, 68, 0.2);">
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
            <div class="mobile-cards-view" id="mobileCardsView" style="display: none;">
                @foreach ($permissions as $permission)
                    <div class="mobile-permission-card" style="background: white; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); margin-bottom: 1rem; overflow: hidden; transition: all 0.3s ease;">
                        <!-- Header de la tarjeta -->
                        <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <h6 style="margin: 0; font-weight: 700; font-size: 1rem;">{{ $permission->name }}</h6>
                                    <small style="opacity: 0.9; font-size: 0.8rem;">ID: {{ $permission->id }}</small>
                                </div>
                                <span style="background: rgba(255, 255, 255, 0.2); padding: 0.25rem 0.5rem; border-radius: 6px; font-size: 0.75rem; font-weight: 600;">#{{ $loop->iteration }}</span>
                            </div>
                        </div>
                        
                        <!-- Contenido de la tarjeta -->
                        <div class="card-body" style="padding: 1.5rem;">
                            <!-- Información principal -->
                            <div class="permission-info" style="margin-bottom: 1.5rem;">
                                <div class="info-row" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9;">
                                    <span style="color: #64748b; font-weight: 600; font-size: 0.875rem;">
                                        <i class="fas fa-shield-alt" style="color: #3b82f6; margin-right: 0.5rem;"></i>
                                        Guard:
                                    </span>
                                    <span style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); color: white; padding: 0.5rem 0.75rem; border-radius: 8px; font-size: 0.75rem; font-weight: 600; box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2);">
                                        {{ $permission->guard_name }}
                                    </span>
                                </div>
                                
                                <div class="info-row" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9;">
                                    <span style="color: #64748b; font-weight: 600; font-size: 0.875rem;">
                                        <i class="fas fa-user-shield" style="color: #10b981; margin-right: 0.5rem;"></i>
                                        Roles Asignados:
                                    </span>
                                    <span style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 0.5rem 0.75rem; border-radius: 8px; font-size: 0.75rem; font-weight: 600; box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);">
                                        {{ $permission->roles->count() }}
                                        <i class="fas fa-user-shield" style="margin-left: 0.25rem;"></i>
                                    </span>
                                </div>
                                
                                <div class="info-row" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9;">
                                    <span style="color: #64748b; font-weight: 600; font-size: 0.875rem;">
                                        <i class="fas fa-users" style="color: #8b5cf6; margin-right: 0.5rem;"></i>
                                        Usuarios:
                                    </span>
                                    <span style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: white; padding: 0.5rem 0.75rem; border-radius: 8px; font-size: 0.75rem; font-weight: 600; box-shadow: 0 2px 4px rgba(139, 92, 246, 0.2);">
                                        {{ $permission->users_count ?? 0 }}
                                        <i class="fas fa-users" style="margin-left: 0.25rem;"></i>
                                    </span>
                                </div>
                                
                                <div class="info-row" style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0;">
                                    <span style="color: #64748b; font-weight: 600; font-size: 0.875rem;">
                                        <i class="fas fa-calendar" style="color: #f59e0b; margin-right: 0.5rem;"></i>
                                        Creado:
                                    </span>
                                    <span style="color: #1e293b; font-weight: 500; font-size: 0.875rem;">{{ $permission->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                            </div>
                            
                            <!-- Botones de acción -->
                            <div class="card-actions" style="display: flex; gap: 0.75rem; justify-content: center; padding-top: 1rem; border-top: 1px solid #f1f5f9;">
                                @can('permissions.show')
                                    <button type="button" class="btn-show-permission" data-id="{{ $permission->id }}" style="width: 40px; height: 40px; border-radius: 8px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none; color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; transition: all 0.3s; box-shadow: 0 2px 8px rgba(16, 185, 129, 0.2);">
                                        <i class="fas fa-eye"></i>
                                        <span class="action-text">Ver</span>
                                    </button>
                                @endcan
                                @can('permissions.edit')
                                    <a href="{{ route('admin.permissions.edit', $permission->id) }}" style="width: 40px; height: 40px; border-radius: 8px; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); border: none; color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; transition: all 0.3s; box-shadow: 0 2px 8px rgba(59, 130, 246, 0.2); text-decoration: none;">
                                        <i class="fas fa-edit"></i>
                                        <span class="action-text">Editar</span>
                                    </a>
                                @endcan
                                @can('permissions.destroy')
                                    <button type="button" class="btn-delete-permission" data-id="{{ $permission->id }}" style="width: 40px; height: 40px; border-radius: 8px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border: none; color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; transition: all 0.3s; box-shadow: 0 2px 8px rgba(239, 68, 68, 0.2);">
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
            <div class="cards-view" id="cardsView" style="display: none;">
                <div class="row">
                    @foreach ($permissions as $permission)
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="permission-card" style="background: white; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); overflow: hidden; transition: all 0.3s;">
                                <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1rem;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 font-weight-bold">{{ $permission->name }}</h6>
                                        <span class="badge badge-light">#{{ $loop->iteration }}</span>
                                    </div>
                                </div>
                                <div class="card-body" style="padding: 1.5rem;">
                                    <div class="permission-info" style="margin-bottom: 1rem;">
                                        <div class="info-row" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                            <span style="color: #64748b; font-weight: 600;">Guard:</span>
                                            <span class="badge badge-info">{{ $permission->guard_name }}</span>
                                        </div>
                                        <div class="info-row" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                            <span style="color: #64748b; font-weight: 600;">Roles:</span>
                                            <span class="badge badge-success">
                                                {{ $permission->roles->count() }}
                                                <i class="fas fa-user-shield ml-1"></i>
                                            </span>
                                        </div>
                                        <div class="info-row" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                            <span style="color: #64748b; font-weight: 600;">Usuarios:</span>
                                            <span class="badge badge-info">
                                                {{ $permission->users_count ?? 0 }}
                                                <i class="fas fa-users ml-1"></i>
                                            </span>
                                        </div>
                                        <div class="info-row" style="display: flex; justify-content: space-between; align-items: center;">
                                            <span style="color: #64748b; font-weight: 600;">Creado:</span>
                                            <span style="color: #1e293b; font-size: 0.875rem;">{{ $permission->created_at->format('d/m/Y') }}</span>
                                        </div>
                                    </div>
                                    <div class="card-actions" style="display: flex; gap: 0.5rem; justify-content: center;">
                                        @can('permissions.show')
                                            <button type="button" class="btn btn-success btn-sm show-permission"
                                                data-id="{{ $permission->id }}" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        @endcan
                                        @can('permissions.edit')
                                            <a href="{{ route('admin.permissions.edit', $permission->id) }}" 
                                               class="btn btn-info btn-sm" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan
                                        @can('permissions.destroy')
                                            <button type="button" class="btn btn-danger btn-sm delete-permission"
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
    
    <!-- Paginación -->
    <div class="pagination-container" style="display: flex; justify-content: space-between; align-items: center; margin-top: 2rem; padding: 1rem; background: white; border-radius: 12px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
        <div class="pagination-info" style="color: #64748b; font-weight: 500;">
            <span class="pagination-text-desktop">Mostrando {{ $permissions->firstItem() ?? 0 }} a {{ $permissions->lastItem() ?? 0 }} de {{ $permissions->total() }} permisos</span>
            <span class="pagination-text-mobile" style="display: none;">Página {{ $permissions->currentPage() }} de {{ $permissions->lastPage() }}</span>
        </div>
        <div class="pagination-links" style="display: flex; gap: 0.5rem;">
            @if ($permissions->hasPages())
                <!-- Botón Anterior -->
                @if ($permissions->onFirstPage())
                    <span style="padding: 0.5rem 1rem; background: #f1f5f9; color: #94a3b8; border-radius: 8px; cursor: not-allowed; font-weight: 500;">
                        <i class="fas fa-chevron-left" style="margin-right: 0.5rem;"></i>
                        <span class="pagination-text-desktop">Anterior</span>
                        <span class="pagination-text-mobile" style="display: none;">Ant</span>
                    </span>
                @else
                    <a href="{{ $permissions->previousPageUrl() }}" style="padding: 0.5rem 1rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 8px; text-decoration: none; font-weight: 500; transition: all 0.3s; box-shadow: 0 2px 4px rgba(102, 126, 234, 0.2);">
                        <i class="fas fa-chevron-left" style="margin-right: 0.5rem;"></i>
                        <span class="pagination-text-desktop">Anterior</span>
                        <span class="pagination-text-mobile" style="display: none;">Ant</span>
                    </a>
                @endif

                <!-- Números de página -->
                @foreach ($permissions->getUrlRange(1, $permissions->lastPage()) as $page => $url)
                    @if ($page == $permissions->currentPage())
                        <span style="padding: 0.5rem 1rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 8px; font-weight: 700; box-shadow: 0 2px 4px rgba(102, 126, 234, 0.2);">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}" style="padding: 0.5rem 1rem; background: white; color: #64748b; border: 2px solid #e2e8f0; border-radius: 8px; text-decoration: none; font-weight: 500; transition: all 0.3s;">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach

                <!-- Botón Siguiente -->
                @if ($permissions->hasMorePages())
                    <a href="{{ $permissions->nextPageUrl() }}" style="padding: 0.5rem 1rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 8px; text-decoration: none; font-weight: 500; transition: all 0.3s; box-shadow: 0 2px 4px rgba(102, 126, 234, 0.2);">
                        <span class="pagination-text-desktop">Siguiente</span>
                        <span class="pagination-text-mobile" style="display: none;">Sig</span>
                        <i class="fas fa-chevron-right" style="margin-left: 0.5rem;"></i>
                    </a>
                @else
                    <span style="padding: 0.5rem 1rem; background: #f1f5f9; color: #94a3b8; border-radius: 8px; cursor: not-allowed; font-weight: 500;">
                        <span class="pagination-text-desktop">Siguiente</span>
                        <span class="pagination-text-mobile" style="display: none;">Sig</span>
                        <i class="fas fa-chevron-right" style="margin-left: 0.5rem;"></i>
                    </span>
                @endif
            @endif
        </div>
    </div>
</div>
</div>

<!-- Modal para Ver Detalles del Permiso -->
<div class="modal fade" id="showPermissionModal" tabindex="-1" aria-labelledby="showPermissionModalLabel" role="dialog" inert>
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title" id="showPermissionModalLabel">
                    <i class="fas fa-key me-2"></i>
                    Detalles del Permiso
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" tabindex="0"></button>
            </div>
            <div class="modal-body" style="padding: 2rem; background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);">
                <!-- Información Principal -->
                <div class="permission-main-info" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 16px; padding: 2rem; margin-bottom: 2rem; color: white; text-align: center; position: relative; overflow: hidden;">
                    <div style="position: absolute; top: -50%; right: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);"></div>
                    <div style="position: relative; z-index: 2;">
                        <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.8;">
                            <i class="fas fa-key"></i>
                        </div>
                        <h3 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem; text-shadow: 0 2px 4px rgba(0,0,0,0.1);" id="permissionName"></h3>
                        <div style="font-size: 0.9rem; opacity: 0.9; font-weight: 500;" id="permissionGuard"></div>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="permission-stats" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                    <div class="stat-card" style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-left: 4px solid #10b981; transition: all 0.3s ease;">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.2rem;">
                                <i class="fas fa-users"></i>
                            </div>
                            <div>
                                <div style="font-size: 0.8rem; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Roles Asignados</div>
                                <div style="font-size: 1.5rem; font-weight: 700; color: #10b981;" id="permissionRoles"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stat-card" style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-left: 4px solid #8b5cf6; transition: all 0.3s ease;">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.2rem;">
                                <i class="fas fa-user-friends"></i>
                            </div>
                            <div>
                                <div style="font-size: 0.8rem; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Usuarios con Permiso</div>
                                <div style="font-size: 1.5rem; font-weight: 700; color: #8b5cf6;" id="permissionUsers"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información de Fechas -->
                <div class="permission-dates" style="background: white; border-radius: 16px; padding: 2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                    <h4 style="font-size: 1.1rem; font-weight: 700; color: #374151; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-calendar-alt" style="color: #667eea;"></i>
                        Información Temporal
                    </h4>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                        <div class="date-info" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-radius: 12px; border-left: 4px solid #0ea5e9;">
                            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1rem;">
                                <i class="fas fa-plus"></i>
                            </div>
                            <div>
                                <div style="font-size: 0.8rem; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Fecha de Creación</div>
                                <div style="font-size: 1rem; font-weight: 600; color: #0ea5e9;" id="permissionCreated"></div>
                            </div>
                        </div>
                        
                        <div class="date-info" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-radius: 12px; border-left: 4px solid #f59e0b;">
                            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1rem;">
                                <i class="fas fa-edit"></i>
                            </div>
                            <div>
                                <div style="font-size: 0.8rem; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Última Actualización</div>
                                <div style="font-size: 1rem; font-weight: 600; color: #f59e0b;" id="permissionUpdated"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%); border-top: 1px solid #e5e7eb; padding: 1.5rem 2rem;">
                <div style="display: flex; justify-content: center; width: 100%;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" tabindex="0" style="background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%); border: none; border-radius: 12px; padding: 0.75rem 2rem; font-weight: 600; color: white; transition: all 0.3s ease; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                        <i class="fas fa-times me-2"></i>Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('css')
<link rel="stylesheet" href="{{ asset('css/admin/sales/index.css') }}">
<style>
    /* Estilos adicionales para la tabla */
    .table-row-hover:hover {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%) !important;
        transform: scale(1.01) !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05) !important;
    }
    
    /* Asegurar que la tabla ocupe todo el ancho */
    #tableView {
        width: 100% !important;
        max-width: 100% !important;
    }
    
    #permissionsTable {
        width: 100% !important;
        table-layout: fixed !important;
    }
    
    /* Estilos para la paginación */
    .pagination-container {
        width: 100% !important;
    }
    
    .pagination-links a:hover {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        color: white !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3) !important;
    }
    
    .pagination-links a {
        transition: all 0.3s ease !important;
    }
    
    /* Responsividad para la paginación */
    @media (max-width: 768px) {
        .pagination-container {
            flex-direction: column !important;
            gap: 1rem !important;
            text-align: center !important;
        }
        
        .pagination-text-desktop {
            display: none !important;
        }
        
        .pagination-text-mobile {
            display: inline !important;
        }
        
        .pagination-links {
            justify-content: center !important;
            flex-wrap: wrap !important;
        }
        
        .pagination-links a,
        .pagination-links span {
            padding: 0.5rem 0.75rem !important;
            font-size: 0.875rem !important;
        }
        
        /* Ocultar texto de botones de acción en móviles */
        .action-text {
            display: none !important;
        }
        
        /* Ajustar botones de acción para móviles */
        .action-buttons {
            display: flex !important;
            gap: 0.5rem !important;
            justify-content: center !important;
            align-items: center !important;
            flex-direction: row !important;
        }
        
        .action-buttons button,
        .action-buttons a {
            width: 40px !important;
            height: 40px !important;
            min-width: 40px !important;
            min-height: 40px !important;
            border-radius: 8px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        
        /* Botones de acción en tarjetas móviles */
        .card-actions {
            flex-direction: row !important;
            gap: 0.5rem !important;
            justify-content: center !important;
            align-items: center !important;
        }
        
        .card-actions button,
        .card-actions a {
            flex: none !important;
            width: 40px !important;
            height: 40px !important;
            min-width: 40px !important;
            min-height: 40px !important;
            padding: 0 !important;
            border-radius: 8px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            margin: 0 !important;
        }
        
        .card-actions .action-text {
            display: none !important;
        }
    }
    
    @media (max-width: 576px) {
        .pagination-container {
            padding: 0.75rem !important;
        }
        
        .pagination-links {
            gap: 0.25rem !important;
        }
        
        .pagination-links a,
        .pagination-links span {
            padding: 0.4rem 0.6rem !important;
            font-size: 0.8rem !important;
        }
    }
    
    /* Efectos hover para las tarjetas del modal */
    .stat-card:hover {
        transform: translateY(-5px) !important;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1) !important;
    }
    
    .date-info:hover {
        transform: translateY(-3px) !important;
        box-shadow: 0 6px 20px rgba(0,0,0,0.08) !important;
    }
    
    /* Animación de entrada para el modal */
    .modal.fade .modal-dialog {
        transition: transform 0.3s ease-out !important;
        transform: translate(0, -50px) scale(0.95) !important;
    }
    
    .modal.show .modal-dialog {
        transform: translate(0, 0) scale(1) !important;
    }
    
    /* Efectos de brillo en las tarjetas */
    .permission-main-info::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }
    
    .permission-main-info:hover::before {
        left: 100%;
    }
    
    /* Responsividad para pantallas pequeñas */
    @media (max-width: 768px) {
        /* Estilos responsivos para el modal */
        .permission-stats {
            grid-template-columns: 1fr !important;
            gap: 1rem !important;
        }
        
        .permission-dates > div {
            grid-template-columns: 1fr !important;
            gap: 1rem !important;
        }
        
        .permission-main-info {
            padding: 1.5rem !important;
        }
        
        .permission-main-info h3 {
            font-size: 1.2rem !important;
        }
        
        .stat-card {
            padding: 1rem !important;
        }
        
        .date-info {
            padding: 0.75rem !important;
        }
        /* Ocultar tabla en móviles */
        #tableView {
            display: none !important;
        }
        
        /* Mostrar tarjetas móviles */
        #mobileCardsView {
            display: block !important;
        }
        
        /* Ocultar vista de tarjetas desktop */
        #cardsView {
            display: none !important;
        }
        
        /* Ajustar header de la tarjeta principal */
        .modern-card-header {
            flex-direction: column !important;
            gap: 1rem !important;
        }
        
        .title-content {
            flex-direction: column !important;
            text-align: center !important;
            gap: 0.75rem !important;
        }
        
        .title-icon {
            width: 50px !important;
            height: 50px !important;
            font-size: 1.25rem !important;
        }
        
        .modern-title {
            font-size: 1.25rem !important;
        }
        
        .modern-subtitle {
            font-size: 0.875rem !important;
        }
        
        .modern-card-actions {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 1rem !important;
        }
        
        .search-container {
            max-width: 100% !important;
        }
        
        .view-toggles {
            justify-content: center !important;
        }
        
        /* Efectos hover para tarjetas móviles */
        .mobile-permission-card:hover {
            transform: translateY(-4px) !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
        }
        
        /* Ajustar botones en tarjetas móviles */
        .card-actions {
            flex-direction: row !important;
            gap: 0.5rem !important;
            justify-content: center !important;
        }
        
        .card-actions button,
        .card-actions a {
            width: 40px !important;
            height: 40px !important;
            min-width: 40px !important;
            min-height: 40px !important;
            justify-content: center !important;
        }
    }
    
    @media (max-width: 576px) {
        /* Ajustes para pantallas muy pequeñas */
        .modern-card-header {
            padding: 1rem !important;
        }
        
        .modern-card-body {
            padding: 1rem !important;
        }
        
        .mobile-permission-card {
            margin-bottom: 0.75rem !important;
        }
        
        .card-header {
            padding: 0.75rem !important;
        }
        
        .card-body {
            padding: 1rem !important;
        }
        
        .info-row {
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 0.5rem !important;
        }
        
        .info-row span:last-child {
            align-self: flex-end !important;
        }
        
        /* Ajustar estadísticas */
        .stats-card {
            min-height: 100px !important;
        }
        
        .stats-card-body {
            padding: 1rem !important;
            gap: 0.75rem !important;
        }
        
        .stats-icon {
            width: 40px !important;
            height: 40px !important;
            font-size: 1rem !important;
        }
        
        .stats-value {
            font-size: 1.25rem !important;
        }
        
        .stats-label {
            font-size: 0.8rem !important;
        }
        
        .stats-trend {
            font-size: 0.7rem !important;
        }
        
        /* Asegurar que los botones de acción estén horizontales en pantallas muy pequeñas */
        .card-actions {
            flex-direction: row !important;
            gap: 0.5rem !important;
            justify-content: center !important;
            align-items: center !important;
        }
        
        .card-actions button,
        .card-actions a {
            width: 40px !important;
            height: 40px !important;
            min-width: 40px !important;
            min-height: 40px !important;
            padding: 0 !important;
            border-radius: 8px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            margin: 0 !important;
        }
        
        .card-actions .action-text {
            display: none !important;
        }
    }
    
    /* Debugbar styles */
    #debugbar {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(0, 0, 0, 0.9);
        color: white;
        padding: 10px;
        font-family: monospace;
        font-size: 12px;
        z-index: 9999;
        display: none;
    }
    
    #debugbar.show {
        display: block;
    }
    
    .debug-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .debug-metric {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .debug-metric i {
        color: #10b981;
    }
    
    /* Estilos para botones de acción en desktop */
    .action-buttons {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
        align-items: center;
    }
    
    .action-buttons button,
    .action-buttons a {
        position: relative;
        transition: all 0.3s ease;
    }
    
    .action-buttons button:hover,
    .action-buttons a:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    /* Tooltips para botones en desktop */
    .action-buttons button:hover .action-text,
    .action-buttons a:hover .action-text {
        opacity: 1;
        visibility: visible;
    }
    
    .action-text {
        position: absolute;
        top: -35px;
        left: 50%;
        transform: translateX(-50%);
        background: #374151;
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        pointer-events: none;
        z-index: 10;
    }
    
    .action-text::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        border: 4px solid transparent;
        border-top-color: #374151;
    }
</style>
@endpush

@push('js')
<script>
// JavaScript específico para la vista de permisos
document.addEventListener('DOMContentLoaded', function() {
    // Efectos hover para las filas de la tabla
    const tableRows = document.querySelectorAll('.table-row-hover');
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.background = 'linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%)';
            this.style.transform = 'scale(1.01)';
            this.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.05)';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.background = 'transparent';
            this.style.transform = 'scale(1)';
            this.style.boxShadow = 'none';
        });
    });
    
    // Efectos hover para los botones de acción
    const actionButtons = document.querySelectorAll('.btn-show-permission, .btn-delete-permission, a[href*="edit"]');
    actionButtons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px) scale(1.1)';
            this.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.2)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
            this.style.boxShadow = this.style.boxShadow.replace('0 4px 12px rgba(0, 0, 0, 0.2)', '0 2px 8px rgba(0, 0, 0, 0.1)');
        });
    });
    // Funcionalidad de toggle de vista con efectos
    const viewToggles = document.querySelectorAll('.view-toggle');
    const tableView = document.getElementById('tableView');
    const cardsView = document.getElementById('cardsView');
    const mobileCardsView = document.getElementById('mobileCardsView');
    
    // Función para manejar la responsividad
    function handleResponsiveView() {
        const isMobile = window.innerWidth <= 768;
        
        if (isMobile) {
            // En móviles, siempre mostrar tarjetas móviles
            tableView.style.display = 'none';
            cardsView.style.display = 'none';
            mobileCardsView.style.display = 'block';
            
            // Desactivar toggles en móviles
            viewToggles.forEach(toggle => {
                toggle.style.opacity = '0.5';
                toggle.style.pointerEvents = 'none';
            });
        } else {
            // En desktop, permitir cambio de vista
            mobileCardsView.style.display = 'none';
            viewToggles.forEach(toggle => {
                toggle.style.opacity = '1';
                toggle.style.pointerEvents = 'auto';
            });
            
            // Mantener la vista seleccionada
            const activeToggle = document.querySelector('.view-toggle.active');
            if (activeToggle) {
                const viewType = activeToggle.dataset.view;
                if (viewType === 'table') {
                    tableView.style.display = 'block';
                    cardsView.style.display = 'none';
                } else {
                    tableView.style.display = 'none';
                    cardsView.style.display = 'block';
                }
            }
        }
    }
    
    // Ejecutar al cargar y al cambiar tamaño de ventana
    handleResponsiveView();
    window.addEventListener('resize', handleResponsiveView);
    
    viewToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            // Solo permitir en desktop
            if (window.innerWidth <= 768) return;
            
            // Remover clase active de todos los toggles
            viewToggles.forEach(t => {
                t.classList.remove('active');
                t.style.background = 'white';
                t.style.color = '#64748b';
                t.style.border = '2px solid #e2e8f0';
            });
            
            // Agregar clase active al toggle clickeado
            this.classList.add('active');
            this.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
            this.style.color = 'white';
            this.style.border = 'none';
            
            // Cambiar vista
            const viewType = this.dataset.view;
            if (viewType === 'table') {
                tableView.style.display = 'block';
                cardsView.style.display = 'none';
            } else {
                tableView.style.display = 'none';
                cardsView.style.display = 'block';
            }
        });
        
        // Efectos hover para toggles
        toggle.addEventListener('mouseenter', function() {
            if (!this.classList.contains('active')) {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 8px 16px rgba(0, 0, 0, 0.1)';
            }
        });
        
        toggle.addEventListener('mouseleave', function() {
            if (!this.classList.contains('active')) {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
            }
        });
    });
    
    // Efecto de hover para las tarjetas de estadísticas
    const statCards = document.querySelectorAll('.stats-card');
    statCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 20px 40px rgba(0, 0, 0, 0.1)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)';
        });
    });
    
    // Efectos para inputs
    const inputs = document.querySelectorAll('input[type="text"]');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.style.borderColor = '#667eea';
            this.style.boxShadow = '0 0 0 3px rgba(102, 126, 234, 0.1)';
        });
        
        input.addEventListener('blur', function() {
            this.style.borderColor = '#e2e8f0';
            this.style.boxShadow = 'none';
        });
    });
    
    // Efectos para tarjetas de permisos
    const permissionCards = document.querySelectorAll('.permission-card');
    permissionCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-4px)';
            this.style.boxShadow = '0 10px 25px rgba(0, 0, 0, 0.15)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 4px 6px -1px rgba(0, 0, 0, 0.1)';
        });
    });
    
    // Efectos para tarjetas móviles
    const mobilePermissionCards = document.querySelectorAll('.mobile-permission-card');
    mobilePermissionCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-4px)';
            this.style.boxShadow = '0 10px 25px rgba(0, 0, 0, 0.15)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 4px 6px -1px rgba(0, 0, 0, 0.1)';
        });
    });
    
    // Efectos para botones en tarjetas móviles
    const mobileCardButtons = document.querySelectorAll('.mobile-permission-card .card-actions button, .mobile-permission-card .card-actions a');
    mobileCardButtons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px) scale(1.02)';
            this.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.2)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
            this.style.boxShadow = '0 2px 8px rgba(0, 0, 0, 0.1)';
        });
    });
    
    // Debugbar functionality
    let debugbar = null;
    let performanceStart = performance.now();
    
    // Crear debugbar
    function createDebugbar() {
        debugbar = document.createElement('div');
        debugbar.id = 'debugbar';
        debugbar.innerHTML = `
            <div class="debug-info">
                <div class="debug-metric">
                    <i class="fas fa-clock"></i>
                    <span id="loadTime">0ms</span>
                </div>
                <div class="debug-metric">
                    <i class="fas fa-memory"></i>
                    <span id="memoryUsage">0MB</span>
                </div>
                <div class="debug-metric">
                    <i class="fas fa-database"></i>
                    <span id="dbQueries">0 queries</span>
                </div>
                <div class="debug-metric">
                    <i class="fas fa-code"></i>
                    <span id="phpTime">0ms</span>
                </div>
                <div class="debug-metric">
                    <i class="fas fa-eye"></i>
                    <span id="permissionsCount">{{ $permissions->count() }} permisos</span>
                </div>
                <button onclick="toggleDebugbar()" style="background: #10b981; border: none; color: white; padding: 5px 10px; border-radius: 4px; cursor: pointer;">
                    <i class="fas fa-cog"></i> Debug
                </button>
            </div>
        `;
        document.body.appendChild(debugbar);
    }
    
    // Toggle debugbar
    window.toggleDebugbar = function() {
        debugbar.classList.toggle('show');
        if (debugbar.classList.contains('show')) {
            updateDebugbar();
        }
    };
    
    // Actualizar debugbar
    function updateDebugbar() {
        const loadTime = performance.now() - performanceStart;
        document.getElementById('loadTime').textContent = Math.round(loadTime) + 'ms';
        
        // Simular métricas de memoria (en un entorno real, esto vendría del servidor)
        const memoryUsage = Math.round(Math.random() * 50 + 20);
        document.getElementById('memoryUsage').textContent = memoryUsage + 'MB';
        
        // Simular queries de base de datos
        const dbQueries = Math.round(Math.random() * 10 + 5);
        document.getElementById('dbQueries').textContent = dbQueries + ' queries';
        
        // Simular tiempo de PHP
        const phpTime = Math.round(Math.random() * 100 + 50);
        document.getElementById('phpTime').textContent = phpTime + 'ms';
    }
    
    // Eliminar permiso
    $(document).on('click', '.btn-delete-permission', function() {
        const permissionId = $(this).data('id');

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
                    url: `/permissions/delete/${permissionId}`,
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
                        let errorMessage = 'No se pudo eliminar el permiso';
                        
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
                            title: 'Error al Eliminar',
                            text: errorMessage,
                            confirmButtonText: 'Entendido'
                        });
                    }
                });
            }
        });
    });

    // Ver detalles del permiso
    $(document).on('click', '.btn-show-permission', function() {
        const permissionId = $(this).data('id');
        const button = $(this);
        
        // Guardar referencia del botón que abrió el modal
        window.lastModalTrigger = this;

        // Deshabilitar botón y mostrar loading
        button.prop('disabled', true);
        button.html('<i class="fas fa-spinner fa-spin"></i>');
        
        // Mostrar loading global
        Swal.fire({
            title: 'Cargando detalles...',
            text: 'Obteniendo información del permiso',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Obtener datos del permiso
        $.ajax({
            url: `/permissions/${permissionId}`,
            type: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                console.log('Respuesta del servidor:', response);
                
                if (response.status === 'success' && response.permission) {
                    // Llenar datos en el modal
                    $('#permissionName').text(response.permission.name);
                    $('#permissionGuard').html(`<span style="background: rgba(255,255,255,0.2); color: white; padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.3);">${response.permission.guard_name}</span>`);
                    $('#permissionRoles').text(response.permission.roles_count + ' rol(es)');
                    $('#permissionUsers').text(response.permission.users_count + ' usuario(s)');
                    $('#permissionCreated').text(response.permission.created_at);
                    $('#permissionUpdated').text(response.permission.updated_at);

                    // Cerrar loading y mostrar modal
                    Swal.close();
                    
                    // Mostrar modal usando Bootstrap 5 API
                    const modal = new bootstrap.Modal(document.getElementById('showPermissionModal'));
                    modal.show();
                    
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al Obtener Datos',
                        text: response.message || 'No se pudieron obtener los datos del permiso',
                        confirmButtonText: 'Entendido'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX:', xhr, status, error);
                
                let errorMessage = 'No se pudieron obtener los datos del permiso';
                
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
                    confirmButtonText: 'Entendido'
                });
            },
            complete: function() {
                // Restaurar botón
                button.prop('disabled', false);
                button.html('<i class="fas fa-eye"></i>');
            }
        });
    });

    // Event listeners para el modal (Bootstrap 5)
    document.getElementById('showPermissionModal').addEventListener('show.bs.modal', function () {
        console.log('Modal abriéndose...');
        // Remover el atributo inert cuando se abre el modal
        this.removeAttribute('inert');
    });

    document.getElementById('showPermissionModal').addEventListener('shown.bs.modal', function () {
        console.log('Modal abierto completamente');
        // Enfocar el primer elemento interactivo del modal
        const firstFocusableElement = this.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
        if (firstFocusableElement) {
            firstFocusableElement.focus();
        }
    });

    document.getElementById('showPermissionModal').addEventListener('hide.bs.modal', function () {
        console.log('Modal cerrando...');
        // Remover el foco antes de cerrar para evitar el warning
        if (document.activeElement && this.contains(document.activeElement)) {
            document.activeElement.blur();
        }
    });

    document.getElementById('showPermissionModal').addEventListener('hidden.bs.modal', function () {
        console.log('Modal cerrado completamente');
        // Agregar el atributo inert cuando se cierra el modal
        this.setAttribute('inert', '');
        // Restaurar el foco al elemento que abrió el modal
        if (window.lastModalTrigger) {
            window.lastModalTrigger.focus();
            window.lastModalTrigger = null;
        }
    });
    
    // Inicializar debugbar
    createDebugbar();
    
    // Mostrar debugbar con Ctrl+Shift+D
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.shiftKey && e.key === 'D') {
            e.preventDefault();
            toggleDebugbar();
        }
    });
    
    // Actualizar debugbar cada 5 segundos si está visible
    setInterval(function() {
        if (debugbar && debugbar.classList.contains('show')) {
            updateDebugbar();
        }
    }, 5000);
    
    // Efectos hover para enlaces de paginación
    $(document).on('mouseenter', '.pagination-links a', function() {
        $(this).css({
            'transform': 'translateY(-2px)',
            'box-shadow': '0 4px 8px rgba(102, 126, 234, 0.3)'
        });
    });
    
    $(document).on('mouseleave', '.pagination-links a', function() {
        $(this).css({
            'transform': 'translateY(0)',
            'box-shadow': '0 2px 4px rgba(102, 126, 234, 0.2)'
        });
    });
    
    // Asegurar que la tabla ocupe todo el ancho disponible
    function adjustTableWidth() {
        const container = document.querySelector('.modern-card-body');
        const table = document.getElementById('tableView');
        if (container && table) {
            const containerWidth = container.offsetWidth;
            table.style.width = containerWidth + 'px';
            table.style.maxWidth = '100%';
        }
    }
    
    // Ajustar ancho al cargar y al cambiar tamaño de ventana
    adjustTableWidth();
    window.addEventListener('resize', adjustTableWidth);
});
</script>
@endpush
@endsection
