@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                🚀 Prueba de Integración: Tailwind + Alpine.js + Livewire
            </h1>
            <p class="text-gray-600">
                Esta página demuestra la integración completa de las tres tecnologías en tu sistema Laravel.
            </p>
        </div>

        <!-- Información de las tecnologías -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Tailwind CSS -->
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-6 rounded-lg border border-blue-200">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-blue-900">Tailwind CSS</h3>
                        <p class="text-blue-700 text-sm">Framework CSS Utility-First</p>
                    </div>
                </div>
                <p class="text-blue-800 text-sm">
                    Clases utilitarias para diseño rápido y responsive. Totalmente integrado con Alpine.js y Livewire.
                </p>
            </div>

            <!-- Alpine.js -->
            <div class="bg-gradient-to-br from-green-50 to-green-100 p-6 rounded-lg border border-green-200">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-green-900">Alpine.js</h3>
                        <p class="text-green-700 text-sm">Reactividad Ligera</p>
                    </div>
                </div>
                <p class="text-green-800 text-sm">
                    JavaScript reactivo ligero para interactividad en el frontend. Perfecto para modales, dropdowns y estados.
                </p>
            </div>

            <!-- Livewire -->
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-6 rounded-lg border border-purple-200">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-purple-900">Livewire</h3>
                        <p class="text-purple-700 text-sm">Componentes Dinámicos</p>
                    </div>
                </div>
                <p class="text-purple-800 text-sm">
                    Componentes dinámicos en PHP sin escribir JavaScript. Actualizaciones en tiempo real del servidor.
                </p>
            </div>
        </div>

        <!-- Demo de Alpine.js -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8" x-data="{ 
            count: 0,
            showAlert: false,
            message: '',
            items: ['Item 1', 'Item 2', 'Item 3'],
            newItem: '',
            
            addItem() {
                if (this.newItem.trim()) {
                    this.items.push(this.newItem);
                    this.newItem = '';
                }
            },
            
            removeItem(index) {
                this.items.splice(index, 1);
            },
            
            showMessage(msg) {
                this.message = msg;
                this.showAlert = true;
                setTimeout(() => this.showAlert = false, 3000);
            }
        }">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">🎯 Demo Alpine.js</h2>
            
            <!-- Contador -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Contador Interactivo</h3>
                <div class="flex items-center space-x-4">
                    <button 
                        @click="count--"
                        class="btn-danger"
                    >
                        -
                    </button>
                    <span class="text-2xl font-bold text-gray-900" x-text="count"></span>
                    <button 
                        @click="count++"
                        class="btn-success"
                    >
                        +
                    </button>
                    <button 
                        @click="count = 0"
                        class="btn-secondary"
                    >
                        Reset
                    </button>
                </div>
            </div>

            <!-- Lista dinámica -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Lista Dinámica</h3>
                <div class="flex space-x-2 mb-4">
                    <input 
                        x-model="newItem"
                        @keyup.enter="addItem()"
                        type="text" 
                        placeholder="Agregar item..."
                        class="form-input flex-1"
                    >
                    <button 
                        @click="addItem()"
                        class="btn-primary"
                    >
                        Agregar
                    </button>
                </div>
                <ul class="space-y-2">
                    <template x-for="(item, index) in items" :key="index">
                        <li class="flex items-center justify-between p-2 bg-white rounded border">
                            <span x-text="item"></span>
                            <button 
                                @click="removeItem(index)"
                                class="text-red-600 hover:text-red-800"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </li>
                    </template>
                </ul>
            </div>

            <!-- Botones de acción -->
            <div class="flex space-x-2">
                <button 
                    @click="showMessage('¡Hola desde Alpine.js!')"
                    class="btn-primary"
                >
                    Mostrar Mensaje
                </button>
                <button 
                    @click="showMessage('Contador actual: ' + count)"
                    class="btn-secondary"
                >
                    Estado Actual
                </button>
            </div>

            <!-- Alert dinámico -->
            <div 
                x-show="showAlert"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95"
                class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50"
            >
                <span x-text="message"></span>
            </div>
        </div>

        <!-- Componente Livewire -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">🔥 Componente Livewire</h2>
            <p class="text-gray-600 mb-6">
                Este componente demuestra la integración de Livewire con Tailwind CSS y Alpine.js. 
                Incluye búsqueda en tiempo real, ordenamiento, selección múltiple y paginación.
            </p>
            
            @livewire('sales-table')
        </div>

        <!-- Información adicional -->
        <div class="mt-8 p-6 bg-blue-50 rounded-lg border border-blue-200">
            <h3 class="text-lg font-semibold text-blue-900 mb-2">✅ Integración Completada</h3>
            <ul class="text-blue-800 space-y-1">
                <li>• <strong>Tailwind CSS:</strong> Configurado con plugins de formularios y tipografía</li>
                <li>• <strong>Alpine.js:</strong> Inicializado y funcionando con transiciones</li>
                <li>• <strong>Livewire:</strong> Instalado y configurado con componentes dinámicos</li>
                <li>• <strong>AdminLTE:</strong> Mantenido para compatibilidad del panel administrativo</li>
                <li>• <strong>Vite:</strong> Configurado para compilar todos los assets</li>
            </ul>
        </div>
    </div>
</div>
@endsection 