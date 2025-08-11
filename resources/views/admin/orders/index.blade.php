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
    <div class="filters-section">
        <div class="filters-header" id="filtersHeader">
            <div class="filters-title">
                <i class="fas fa-filter"></i>
                <span>Filtros Avanzados</span>
            </div>
            <button class="filters-toggle" id="filtersToggle">
                <i class="fas fa-chevron-down"></i>
            </button>
        </div>
        
        <div class="filters-content" id="filtersContent">
            <div class="filters-grid">
                <div class="filter-group">
                    <label class="filter-label">
                        <i class="fas fa-calendar"></i>
                        Rango de Fechas
                    </label>
                    <div class="date-range">
                        <div class="date-input">
                            <label>Desde</label>
                            <input type="date" class="filter-input" id="dateFrom">
                        </div>
                        <div class="date-input">
                            <label>Hasta</label>
                            <input type="date" class="filter-input" id="dateTo">
                        </div>
                    </div>
                </div>
                
                <div class="filter-group">
                    <label class="filter-label">
                        <i class="fas fa-dollar-sign"></i>
                        Rango de Monto
                    </label>
                    <div class="amount-range">
                        <div class="amount-input">
                            <label>Mínimo</label>
                            <div class="input-with-symbol">
                                <span class="currency-symbol">$</span>
                                <input type="number" class="filter-input" id="amountMin" placeholder="0.00" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="amount-input">
                            <label>Máximo</label>
                            <div class="input-with-symbol">
                                <span class="currency-symbol">$</span>
                                <input type="number" class="filter-input" id="amountMax" placeholder="0.00" step="0.01" min="0">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="filter-group">
                    <label class="filter-label">
                        <i class="fas fa-tag"></i>
                        Estado del Pedido
                    </label>
                    <div class="filters-buttons">
                        <button class="btn-filter active" data-filter="all">
                            <i class="fas fa-list"></i>
                            Todos
                        </button>
                        <button class="btn-filter" data-filter="pending">
                            <i class="fas fa-clock"></i>
                            Pendientes
                        </button>
                        <button class="btn-filter" data-filter="processing">
                            <i class="fas fa-truck"></i>
                            En Proceso
                        </button>
                        <button class="btn-filter" data-filter="completed">
                            <i class="fas fa-check"></i>
                            Completados
                        </button>
                        <button class="btn-filter" data-filter="cancelled">
                            <i class="fas fa-times"></i>
                            Cancelados
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="filters-actions">
                <div class="filters-status">
                    <span class="status-text">Filtros activos:</span>
                    <div class="active-filters" id="activeFilters">
                        <span class="filter-badge">Todos los pedidos</span>
                    </div>
                </div>
                <div class="filters-buttons">
                    <button class="btn-apply" id="applyFilters">
                        <i class="fas fa-filter"></i>
                        Aplicar Filtros
                    </button>
                    <button class="btn-clear" id="clearFilters">
                        <i class="fas fa-times"></i>
                        Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Contenido Principal --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <div class="title-content">
                <div class="title-icon">
                    <i class="fas fa-list"></i>
                </div>
                <div>
                    <h3>Lista de Pedidos</h3>
                    <p>Gestiona y visualiza todos los pedidos del sistema</p>
                </div>
            </div>
            <div class="modern-card-actions">
                <div class="search-container">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Buscar pedidos..." id="searchInput">
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
    
    // Funcionalidad de botones de filtro
    const filterButtons = document.querySelectorAll('.btn-filter');
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remover clase active de todos los botones
            filterButtons.forEach(btn => btn.classList.remove('active'));
            // Agregar clase active al botón clickeado
            this.classList.add('active');
        });
    });
    
    // Funcionalidad de toggle de vista
    const viewToggles = document.querySelectorAll('.view-toggle');
    viewToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            // Remover clase active de todos los toggles
            viewToggles.forEach(t => t.classList.remove('active'));
            // Agregar clase active al toggle clickeado
            this.classList.add('active');
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
});
</script>
@endpush
@endsection