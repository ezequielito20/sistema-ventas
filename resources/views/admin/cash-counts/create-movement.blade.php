@extends('layouts.app')

@section('title', 'Nuevo Movimiento de Caja')

@section('content')
<div class="space-y-6">
    <!-- Hero Section -->
    <div class="relative overflow-hidden bg-gradient-to-br from-blue-600 via-purple-600 to-indigo-700 rounded-2xl shadow-2xl">
        <div class="absolute inset-0 bg-black opacity-10"></div>
        <div class="relative px-6 py-8 sm:px-8 sm:py-12">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div class="flex-1">
                    <div class="flex items-center space-x-4 mb-4">
                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                            <i class="fas fa-money-bill-wave text-white text-2xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl sm:text-4xl font-bold text-white mb-2">
                                Nuevo Movimiento de Caja
                            </h1>
                            <p class="text-blue-100 text-lg">
                                Registra un nuevo ingreso o egreso en la caja actual
                            </p>
                            @if ($currentCashCount)
                                <div class="mt-4 flex items-center space-x-2 text-blue-100">
                                    <i class="fas fa-cash-register"></i>
                                    <span>Caja abierta desde: {{ \Carbon\Carbon::parse($currentCashCount->opening_date)->format('d/m/Y H:i') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="mt-6 lg:mt-0">
                    <a href="{{ route('admin.cash-counts.index') }}" 
                       class="inline-flex items-center justify-center px-6 py-3 bg-white bg-opacity-20 hover:bg-opacity-30 text-white font-semibold rounded-xl transition-all duration-200 border border-white border-opacity-30">
                        <i class="fas fa-arrow-left mr-2"></i>
                        <span class="hidden sm:inline">Volver</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="bg-gradient-to-r from-blue-50 to-purple-50 px-6 py-6 border-b border-gray-200">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-edit text-white text-xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Información del Movimiento</h2>
                    <p class="text-gray-600">Completa los datos del nuevo movimiento de caja</p>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.cash-counts.store-movement') }}" method="POST" class="p-6 space-y-6">
            @csrf
            
            <!-- Tipo de Movimiento -->
            <div>
                <label for="type" class="block text-sm font-semibold text-gray-700 mb-2">
                    Tipo de Movimiento <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <label class="relative cursor-pointer">
                        <input type="radio" name="type" value="income" class="sr-only" required>
                        <div class="border-2 border-gray-200 rounded-xl p-4 transition-all duration-200 hover:border-blue-300 hover:bg-blue-50">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-arrow-up text-green-600"></i>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900">Ingreso</div>
                                    <div class="text-sm text-gray-600">Dinero que entra a la caja</div>
                                </div>
                            </div>
                        </div>
                    </label>
                    
                    <label class="relative cursor-pointer">
                        <input type="radio" name="type" value="expense" class="sr-only" required>
                        <div class="border-2 border-gray-200 rounded-xl p-4 transition-all duration-200 hover:border-red-300 hover:bg-red-50">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-arrow-down text-red-600"></i>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900">Egreso</div>
                                    <div class="text-sm text-gray-600">Dinero que sale de la caja</div>
                                </div>
                            </div>
                        </div>
                    </label>
                </div>
                @error('type')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Monto -->
            <div>
                <label for="amount" class="block text-sm font-semibold text-gray-700 mb-2">
                    Monto <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 text-lg font-semibold">{{ $currency->symbol }}</span>
                    </div>
                    <input type="number" 
                           id="amount" 
                           name="amount" 
                           step="0.01" 
                           min="0" 
                           required
                           class="block w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 text-lg font-semibold"
                           placeholder="0.00">
                </div>
                @error('amount')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Descripción -->
            <div>
                <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                    Descripción
                </label>
                <textarea id="description" 
                          name="description" 
                          rows="3"
                          maxlength="255"
                          class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 resize-none"
                          placeholder="Describe el motivo del movimiento (opcional)"></textarea>
                <div class="mt-1 text-sm text-gray-500 text-right">
                    <span id="char-count">0</span>/255 caracteres
                </div>
                @error('description')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Botones -->
            <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-gray-200">
                <button type="submit" 
                        class="flex-1 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <i class="fas fa-save mr-2"></i>
                    Registrar Movimiento
                </button>
                <a href="{{ route('admin.cash-counts.index') }}" 
                   class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 px-6 rounded-xl transition-all duration-200 text-center">
                    <i class="fas fa-times mr-2"></i>
                    Cancelar
                </a>
            </div>
        </form>
    </div>

    <!-- Información de la Caja Actual -->
    @if ($currentCashCount)
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-6 border-b border-gray-200">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-info-circle text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Información de la Caja</h2>
                        <p class="text-gray-600">Detalles del arqueo actual</p>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-blue-50 rounded-xl p-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-cash-register text-blue-600"></i>
                            </div>
                            <div>
                                <div class="text-sm text-blue-600 font-medium">Monto Inicial</div>
                                <div class="text-lg font-bold text-blue-900">{{ $currency->symbol }} {{ number_format($currentCashCount->initial_amount, 2) }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-green-50 rounded-xl p-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-calendar-alt text-green-600"></i>
                            </div>
                            <div>
                                <div class="text-sm text-green-600 font-medium">Fecha de Apertura</div>
                                <div class="text-lg font-bold text-green-900">{{ \Carbon\Carbon::parse($currentCashCount->opening_date)->format('d/m/Y') }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-purple-50 rounded-xl p-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-clock text-purple-600"></i>
                            </div>
                            <div>
                                <div class="text-sm text-purple-600 font-medium">Hora de Apertura</div>
                                <div class="text-lg font-bold text-purple-900">{{ \Carbon\Carbon::parse($currentCashCount->opening_date)->format('H:i') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Contador de caracteres para la descripción
    const descriptionTextarea = document.getElementById('description');
    const charCount = document.getElementById('char-count');
    
    if (descriptionTextarea && charCount) {
        descriptionTextarea.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
    }

    // Estilos para los radio buttons personalizados
    const radioButtons = document.querySelectorAll('input[type="radio"]');
    radioButtons.forEach(radio => {
        radio.addEventListener('change', function() {
            // Remover estilos activos de todos los contenedores
            document.querySelectorAll('input[type="radio"]').forEach(rb => {
                const container = rb.closest('label').querySelector('div');
                container.classList.remove('border-blue-500', 'bg-blue-50', 'border-red-500', 'bg-red-50');
                container.classList.add('border-gray-200');
            });
            
            // Aplicar estilos al seleccionado
            const container = this.closest('label').querySelector('div');
            if (this.value === 'income') {
                container.classList.remove('border-gray-200');
                container.classList.add('border-blue-500', 'bg-blue-50');
            } else if (this.value === 'expense') {
                container.classList.remove('border-gray-200');
                container.classList.add('border-red-500', 'bg-red-50');
            }
        });
    });

    // Formateo automático del monto
    const amountInput = document.getElementById('amount');
    if (amountInput) {
        amountInput.addEventListener('input', function() {
            // Remover caracteres no numéricos excepto punto decimal
            let value = this.value.replace(/[^\d.]/g, '');
            
            // Asegurar que solo haya un punto decimal
            const parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts.slice(1).join('');
            }
            
            // Limitar a 2 decimales
            if (parts.length === 2 && parts[1].length > 2) {
                value = parts[0] + '.' + parts[1].substring(0, 2);
            }
            
            this.value = value;
        });
    }
});
</script>
@endpush
