@extends('layouts.app')

@section('title', 'Gestión de Pedidos')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-shopping-cart text-blue-600 mr-3"></i>
                    Gestión de Pedidos
                </h1>
                <p class="text-gray-600 mt-1">Administra y visualiza todos los pedidos de tus clientes</p>
            </div>
            <div class="flex items-center space-x-3">
                <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
                    <i class="fas fa-clock mr-1"></i>
                    Última actualización: {{ now()->format('d/m/Y H:i') }}
                </span>
            </div>
        </div>
    </div>

    <!-- Livewire Component -->
    <livewire:orders-table />
    
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
</div>
@endsection