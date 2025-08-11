@extends('layouts.app')

@section('title', 'Gestión de Pedidos')

@section('content')
<div class="space-y-6">
    <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
            <h1 class="text-2xl font-bold text-gray-900">Gestión de Pedidos</h1>
            <p class="text-gray-600">Panel de control y administración de pedidos</p>
        </div>
        <div class="flex items-center space-x-3">
            @can('orders.report')
                <a href="{{ route('admin.orders.report') }}" class="btn-outline" target="_blank">
                    <i class="fas fa-file-pdf mr-2"></i>
                    Reporte
                </a>
            @endcan
            @can('orders.create')
                <a href="{{ route('admin.orders.create') }}" class="btn-primary">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Nuevo Pedido
                </a>
            @endcan
        </div>
    </div>

    {{-- Dashboard de Estadísticas Moderno --}}
    <div class="stats-dashboard">
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="stats-card stats-card-primary" title="Total de pedidos en el sistema">
                    <div class="stats-card-body">
                        <div class="stats-icon">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <div class="stats-content">
                            <h3 class="stats-value">0</h3>
                            <p class="stats-label">Total de Pedidos</p>
                            <div class="stats-trend">
                                <i class="fas fa-arrow-up"></i>
                                <span>+0%</span>
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
                <div class="stats-card stats-card-success" title="Pedidos completados exitosamente">
                    <div class="stats-card-body">
                        <div class="stats-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stats-content">
                            <h3 class="stats-value">0</h3>
                            <p class="stats-label">Completados</p>
                            <div class="stats-trend">
                                <i class="fas fa-arrow-up"></i>
                                <span>+0%</span>
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
                <div class="stats-card stats-card-warning" title="Pedidos pendientes de procesar">
                    <div class="stats-card-body">
                        <div class="stats-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stats-content">
                            <h3 class="stats-value">0</h3>
                            <p class="stats-label">Pendientes</p>
                            <div class="stats-trend">
                                <i class="fas fa-arrow-up"></i>
                                <span>+0%</span>
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
                <div class="stats-card stats-card-info" title="Pedidos en proceso de entrega">
                    <div class="stats-card-body">
                        <div class="stats-icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div class="stats-content">
                            <h3 class="stats-value">0</h3>
                            <p class="stats-label">En Proceso</p>
                            <div class="stats-trend">
                                <i class="fas fa-arrow-up"></i>
                                <span>+0%</span>
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

    {{-- Filtros y Búsqueda --}}
    <div class="filters-section" style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        <div class="filters-header" id="filtersHeader" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 16px 16px 0 0; padding: 1.5rem;">
            <div class="filters-title" style="color: white;">
                <i class="fas fa-filter" style="color: #fbbf24;"></i>
                <span style="font-weight: 600;">Filtros Avanzados</span>
            </div>
            <button class="filters-toggle" id="filtersToggle" style="color: white; background: rgba(255,255,255,0.2); border-radius: 8px; padding: 0.5rem;">
                <i class="fas fa-chevron-down"></i>
            </button>
        </div>
        
        <div class="filters-content" id="filtersContent" style="padding: 2rem; background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border-radius: 0 0 16px 16px;">
            <div class="filters-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
                <div class="filter-group" style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); padding: 1.5rem; border-radius: 12px; border: 1px solid #bae6fd;">
                    <label class="filter-label" style="color: #0369a1; font-weight: 600; margin-bottom: 1rem; display: block;">
                        <i class="fas fa-calendar" style="color: #0ea5e9; margin-right: 0.5rem;"></i>
                        Rango de Fechas
                    </label>
                    <div class="date-range" style="display: flex; gap: 1rem;">
                        <div class="date-input" style="flex: 1;">
                            <label style="color: #64748b; font-size: 0.875rem; margin-bottom: 0.5rem; display: block;">Desde</label>
                            <input type="date" class="filter-input" id="dateFrom" style="width: 100%; padding: 0.75rem; border: 2px solid #e2e8f0; border-radius: 8px; background: white; transition: all 0.3s;">
                        </div>
                        <div class="date-input" style="flex: 1;">
                            <label style="color: #64748b; font-size: 0.875rem; margin-bottom: 0.5rem; display: block;">Hasta</label>
                            <input type="date" class="filter-input" id="dateTo" style="width: 100%; padding: 0.75rem; border: 2px solid #e2e8f0; border-radius: 8px; background: white; transition: all 0.3s;">
                        </div>
                    </div>
                </div>
                
                <div class="filter-group" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); padding: 1.5rem; border-radius: 12px; border: 1px solid #bbf7d0;">
                    <label class="filter-label" style="color: #166534; font-weight: 600; margin-bottom: 1rem; display: block;">
                        <i class="fas fa-dollar-sign" style="color: #22c55e; margin-right: 0.5rem;"></i>
                        Rango de Monto
                    </label>
                    <div class="amount-range" style="display: flex; gap: 1rem;">
                        <div class="amount-input" style="flex: 1;">
                            <label style="color: #64748b; font-size: 0.875rem; margin-bottom: 0.5rem; display: block;">Mínimo</label>
                            <div class="input-with-symbol" style="position: relative;">
                                <span class="currency-symbol" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: #22c55e; font-weight: 600;">$</span>
                                <input type="number" class="filter-input" id="amountMin" placeholder="0.00" step="0.01" min="0" style="width: 100%; padding: 0.75rem 0.75rem 0.75rem 2rem; border: 2px solid #e2e8f0; border-radius: 8px; background: white; transition: all 0.3s;">
                            </div>
                        </div>
                        <div class="amount-input" style="flex: 1;">
                            <label style="color: #64748b; font-size: 0.875rem; margin-bottom: 0.5rem; display: block;">Máximo</label>
                            <div class="input-with-symbol" style="position: relative;">
                                <span class="currency-symbol" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: #22c55e; font-weight: 600;">$</span>
                                <input type="number" class="filter-input" id="amountMax" placeholder="0.00" step="0.01" min="0" style="width: 100%; padding: 0.75rem 0.75rem 0.75rem 2rem; border: 2px solid #e2e8f0; border-radius: 8px; background: white; transition: all 0.3s;">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="filter-group" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); padding: 1.5rem; border-radius: 12px; border: 1px solid #fcd34d;">
                    <label class="filter-label" style="color: #92400e; font-weight: 600; margin-bottom: 1rem; display: block;">
                        <i class="fas fa-tag" style="color: #f59e0b; margin-right: 0.5rem;"></i>
                        Estado del Pedido
                    </label>
                    <div class="filters-buttons" style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                        <button class="btn-filter active" data-filter="all" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600; transition: all 0.3s;">
                            <i class="fas fa-list"></i>
                            Todos
                        </button>
                        <button class="btn-filter" data-filter="pending" style="background: white; color: #f59e0b; border: 2px solid #fbbf24; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600; transition: all 0.3s;">
                            <i class="fas fa-clock"></i>
                            Pendientes
                        </button>
                        <button class="btn-filter" data-filter="processing" style="background: white; color: #0ea5e9; border: 2px solid #38bdf8; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600; transition: all 0.3s;">
                            <i class="fas fa-truck"></i>
                            En Proceso
                        </button>
                        <button class="btn-filter" data-filter="completed" style="background: white; color: #22c55e; border: 2px solid #4ade80; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600; transition: all 0.3s;">
                            <i class="fas fa-check"></i>
                            Completados
                        </button>
                        <button class="btn-filter" data-filter="cancelled" style="background: white; color: #ef4444; border: 2px solid #f87171; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600; transition: all 0.3s;">
                            <i class="fas fa-times"></i>
                            Cancelados
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="filters-actions" style="display: flex; justify-content: space-between; align-items: center; padding-top: 1.5rem; border-top: 2px solid #e2e8f0;">
                <div class="filters-status" style="display: flex; align-items: center; gap: 1rem;">
                    <span class="status-text" style="color: #64748b; font-weight: 600;">Filtros activos:</span>
                    <div class="active-filters" id="activeFilters">
                        <span class="filter-badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 0.5rem 1rem; border-radius: 20px; font-weight: 600;">Todos los pedidos</span>
                    </div>
                </div>
                <div class="filters-buttons" style="display: flex; gap: 1rem;">
                    <button class="btn-apply" id="applyFilters" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; transition: all 0.3s;">
                        <i class="fas fa-filter"></i>
                        Aplicar Filtros
                    </button>
                    <button class="btn-clear" id="clearFilters" style="background: white; color: #64748b; border: 2px solid #e2e8f0; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; transition: all 0.3s;">
                        <i class="fas fa-times"></i>
                        Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Contenido Principal --}}
    <div class="modern-card" style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        <div class="modern-card-header" style="background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%); padding: 2rem; border-radius: 16px 16px 0 0; border-bottom: 2px solid #e2e8f0;">
            <div class="title-content" style="display: flex; align-items: center; gap: 1rem;">
                <div class="title-icon" style="width: 60px; height: 60px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                    <i class="fas fa-list"></i>
                </div>
                <div>
                    <h3 style="color: #1e293b; font-size: 1.5rem; font-weight: 700; margin: 0;">Lista de Pedidos</h3>
                    <p style="color: #64748b; margin: 0.5rem 0 0 0;">Gestiona y visualiza todos los pedidos del sistema</p>
                </div>
            </div>
            <div class="modern-card-actions" style="display: flex; align-items: center; gap: 1.5rem; margin-top: 1.5rem;">
                <div class="search-container" style="flex: 1; max-width: 400px;">
                    <div class="search-box" style="position: relative;">
                        <i class="fas fa-search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #64748b;"></i>
                        <input type="text" placeholder="Buscar pedidos..." id="searchInput" style="width: 100%; padding: 0.75rem 1rem 0.75rem 2.5rem; border: 2px solid #e2e8f0; border-radius: 8px; background: white; transition: all 0.3s;">
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
    <!-- Livewire Component -->
    <livewire:orders-table />
        </div>
    </div>
</div>
    
    <!-- Notificaciones -->
    <div x-data="{ notifications: [] }" 
         @showNotification.window="notifications.push({ id: Date.now(), type: $event.detail.type, message: $event.detail.message }); setTimeout(() => { notifications = notifications.filter(n => n.id !== notifications[notifications.length - 1].id) }, 3000)"
         class="fixed top-4 right-4 z-50 space-y-2">
        <template x-for="notification in notifications" :key="notification.id">
            <div x-show="true" 
                 x-transition:enter="transform ease-out duration-300 transition"
                 x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                 x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden">
                <div class="p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i x-show="notification.type === 'success'" class="fas fa-check-circle text-green-400"></i>
                            <i x-show="notification.type === 'error'" class="fas fa-exclamation-circle text-red-400"></i>
                            <i x-show="notification.type === 'warning'" class="fas fa-exclamation-triangle text-yellow-400"></i>
                            <i x-show="notification.type === 'info'" class="fas fa-info-circle text-blue-400"></i>
                        </div>
                        <div class="ml-3 w-0 flex-1 pt-0.5">
                            <p x-text="notification.message" class="text-sm font-medium text-gray-900"></p>
                        </div>
                        <div class="ml-4 flex-shrink-0 flex">
                            <button @click="notifications = notifications.filter(n => n.id !== notification.id)" 
                                    class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

@push('css')
<link rel="stylesheet" href="{{ asset('css/admin/sales/index.css') }}">
@endpush

@push('js')
<script>
// JavaScript específico para la vista de pedidos
document.addEventListener('DOMContentLoaded', function() {
    // Funcionalidad de filtros avanzados
    const filtersToggle = document.getElementById('filtersToggle');
    const filtersContent = document.getElementById('filtersContent');
    
    if (filtersToggle && filtersContent) {
        filtersToggle.addEventListener('click', function() {
            filtersContent.classList.toggle('show');
            const icon = this.querySelector('i');
            if (filtersContent.classList.contains('show')) {
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            } else {
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        });
    }
    
    // Funcionalidad de botones de filtro con efectos visuales
    const filterButtons = document.querySelectorAll('.btn-filter');
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remover clase active de todos los botones
            filterButtons.forEach(btn => {
                btn.classList.remove('active');
                btn.style.background = 'white';
                btn.style.color = btn.dataset.filter === 'pending' ? '#f59e0b' : 
                                 btn.dataset.filter === 'processing' ? '#0ea5e9' : 
                                 btn.dataset.filter === 'completed' ? '#22c55e' : 
                                 btn.dataset.filter === 'cancelled' ? '#ef4444' : '#64748b';
            });
            
            // Agregar clase active al botón clickeado
            this.classList.add('active');
            this.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
            this.style.color = 'white';
        });
        
        // Efectos hover para botones de filtro
        button.addEventListener('mouseenter', function() {
            if (!this.classList.contains('active')) {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 8px 16px rgba(0, 0, 0, 0.1)';
            }
        });
        
        button.addEventListener('mouseleave', function() {
            if (!this.classList.contains('active')) {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
            }
        });
    });
    
    // Funcionalidad de toggle de vista con efectos
    const viewToggles = document.querySelectorAll('.view-toggle');
    viewToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
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
    const inputs = document.querySelectorAll('input[type="date"], input[type="number"], input[type="text"]');
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
    
    // Efectos para botones de acción
    const actionButtons = document.querySelectorAll('.btn-apply, .btn-clear');
    actionButtons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 8px 16px rgba(0, 0, 0, 0.15)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'none';
        });
    });
});
</script>
@endpush
@endsection