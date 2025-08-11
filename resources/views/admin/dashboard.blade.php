@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Dashboard Ejecutivo</h1>
            <p class="text-gray-600">Panel de control y análisis en tiempo real</p>
        </div>
        <div class="flex items-center space-x-3">
            <span class="text-sm text-gray-500">{{ now()->format('d/m/Y H:i') }}</span>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Ventas -->
        <div class="card">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-dollar-sign text-white text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Ventas</p>
                    <p class="text-2xl font-bold text-gray-900">$15,420</p>
                    <p class="text-sm text-green-600">
                        <i class="fas fa-arrow-up mr-1"></i>
                        +12.5%
                    </p>
                </div>
            </div>
        </div>

        <!-- Pedidos Pendientes -->
        <div class="card">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-yellow-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-white text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Pedidos Pendientes</p>
                    <p class="text-2xl font-bold text-gray-900">8</p>
                    <p class="text-sm text-yellow-600">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Requieren atención
                    </p>
                </div>
            </div>
        </div>

        <!-- Productos Vendidos -->
        <div class="card">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-box text-white text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Productos Vendidos</p>
                    <p class="text-2xl font-bold text-gray-900">156</p>
                    <p class="text-sm text-green-600">
                        <i class="fas fa-arrow-up mr-1"></i>
                        +8.2%
                    </p>
                </div>
            </div>
        </div>

        <!-- Clientes Nuevos -->
        <div class="card">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-white text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Clientes Nuevos</p>
                    <p class="text-2xl font-bold text-gray-900">23</p>
                    <p class="text-sm text-purple-600">
                        <i class="fas fa-arrow-up mr-1"></i>
                        +15.3%
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Orders -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Pedidos Recientes</h3>
                <p class="card-subtitle">Últimos pedidos recibidos</p>
            </div>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-shopping-cart text-blue-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Pedido #1234</p>
                            <p class="text-sm text-gray-500">Juan Pérez - $150.00</p>
                        </div>
                    </div>
                    <span class="badge badge-warning">Pendiente</span>
                </div>

                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-check text-green-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Pedido #1233</p>
                            <p class="text-sm text-gray-500">María García - $89.50</p>
                        </div>
                    </div>
                    <span class="badge badge-success">Procesado</span>
                </div>

                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-times text-red-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Pedido #1232</p>
                            <p class="text-sm text-gray-500">Carlos López - $200.00</p>
                        </div>
                    </div>
                    <span class="badge badge-danger">Cancelado</span>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Acciones Rápidas</h3>
                <p class="card-subtitle">Acciones frecuentes del sistema</p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <a href="{{ route('admin.orders.index') }}" class="flex flex-col items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors duration-200">
                    <i class="fas fa-shopping-cart text-blue-600 text-2xl mb-2"></i>
                    <span class="text-sm font-medium text-blue-900">Gestionar Pedidos</span>
                </a>

                <a href="{{ route('admin.products.index') }}" class="flex flex-col items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors duration-200">
                    <i class="fas fa-box text-green-600 text-2xl mb-2"></i>
                    <span class="text-sm font-medium text-green-900">Productos</span>
                </a>

                <a href="{{ route('admin.customers.index') }}" class="flex flex-col items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors duration-200">
                    <i class="fas fa-users text-purple-600 text-2xl mb-2"></i>
                    <span class="text-sm font-medium text-purple-900">Clientes</span>
                </a>

                <a href="{{ route('admin.sales.index') }}" class="flex flex-col items-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors duration-200">
                    <i class="fas fa-chart-line text-orange-600 text-2xl mb-2"></i>
                    <span class="text-sm font-medium text-orange-900">Ventas</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
