@extends('layouts.app')

@section('title', 'Nuevo Movimiento de Caja')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/cash-counts/create-movement.css') }}">
@endpush

@push('js')
    <script>
        // Datos globales para JavaScript
        window.movementCreateData = {
            currencySymbol: '{{ $currency->symbol }}',
            type: '{{ old('type', '') }}',
            amount: '{{ old('amount', '') }}',
            description: @json(old('description', '')),
            currentCashCount: @json($currentCashCount ? [
                'id' => $currentCashCount->id,
                'opening_date' => $currentCashCount->opening_date,
                'initial_amount' => $currentCashCount->initial_amount
            ] : null)
        };
    </script>
    <script src="{{ asset('js/admin/cash-counts/create-movement.js') }}" defer></script>
@endpush

@section('content')
    <div class="space-y-6">
        <!-- Hero Section -->
        <div
            class="relative overflow-hidden bg-gradient-to-br from-blue-600 via-purple-600 to-indigo-700 rounded-2xl shadow-2xl">
            <div class="absolute inset-0 bg-black opacity-10"></div>
            <div class="relative px-6 py-8 sm:px-8 sm:py-12">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="w-12 h-12 bg-blue-500 bg-opacity-90 rounded-xl flex items-center justify-center shadow-lg">
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
                                        <span>Caja abierta desde:
                                            {{ \Carbon\Carbon::parse($currentCashCount->opening_date)->format('d/m/Y H:i') }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 lg:mt-0">
                        <a href="{{ route('admin.cash-counts.index') }}"
                            class="inline-flex items-center justify-center px-6 py-3 bg-blue-500 bg-opacity-90 hover:bg-opacity-30 text-white font-semibold rounded-xl transition-all duration-200 border border-white border-opacity-30">
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
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-edit text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Información del Movimiento</h2>
                        <p class="text-gray-600">Completa los datos del nuevo movimiento de caja</p>
                    </div>
                </div>
            </div>

            <form x-ref="form" action="{{ route('admin.cash-counts.store-movement') }}" method="POST" class="p-6 space-y-6" x-data="movementForm()" @submit.prevent="handleSubmit">
                @csrf

                <!-- Tipo de Movimiento -->
                <div>
                    <label for="type" class="block text-sm font-semibold text-gray-700 mb-2">
                        Tipo de Movimiento <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 radio-options">
                        <label class="radio-option">
                            <input type="radio" name="type" value="income" x-model="type" class="sr-only" required>
                            <div class="radio-content income">
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

                        <label class="radio-option">
                            <input type="radio" name="type" value="expense" x-model="type" class="sr-only" required>
                            <div class="radio-content expense">
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
                    <p class="error-message" x-text="errors.type" x-show="errors.type"></p>
                    @error('type')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Monto -->
                <div>
                    <label for="amount" class="block text-sm font-semibold text-gray-700 mb-2">
                        Monto <span class="text-red-500">*</span>
                    </label>
                    <div class="relative amount-input-container">
                        <div class="currency-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <input type="number" 
                               id="amount" 
                               name="amount" 
                               x-model="amount"
                               step="0.01" 
                               min="0" 
                               required
                               :class="errors.amount ? 'form-input input-error' : 'form-input'"
                               class="block w-full pl-16 pr-4 py-4 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 text-lg font-semibold bg-white"
                               placeholder="0.00">
                    </div>
                    <p class="error-message" x-text="errors.amount" x-show="errors.amount"></p>
                    @error('amount')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Descripción -->
                <div>
                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                        Descripción
                    </label>
                    <textarea id="description" name="description" rows="3" maxlength="255" x-model="description"
                        class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 resize-none form-input"
                        placeholder="Describe el motivo del movimiento (opcional)"></textarea>
                    <div class="char-counter">
                        <span class="count" :class="getCharCountClass(charCount, 255)" x-text="charCount"></span>
                        <span>/255 caracteres</span>
                    </div>
                    <p class="error-message" x-text="errors.description" x-show="errors.description"></p>
                    @error('description')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Botones -->
                <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-gray-200">
                    <button type="button" @click="confirmReset()" class="flex-1 btn-secondary">
                        <i class="fas fa-undo mr-2"></i>
                        Limpiar
                    </button>
                    <button type="submit" 
                            :disabled="isSubmitting"
                            class="flex-1 btn-primary">
                        <i class="fas fa-save mr-2" :class="isSubmitting ? 'loading-spinner' : ''"></i>
                        <span x-text="isSubmitting ? 'Registrando...' : 'Registrar Movimiento'"></span>
                    </button>
                    <a href="{{ route('admin.cash-counts.index') }}"
                        class="flex-1 btn-secondary text-center">
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
                        <div
                            class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-info-circle text-white text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Información de la Caja</h2>
                            <p class="text-gray-600">Detalles del arqueo actual</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="cash-info-grid">
                        <div class="cash-info-card blue">
                            <div class="flex items-center space-x-3">
                                <div class="icon">
                                    <i class="fas fa-cash-register"></i>
                                </div>
                                <div>
                                    <div class="text-sm text-blue-600 font-medium">Monto Inicial</div>
                                    <div class="text-lg font-bold text-blue-900">{{ $currency->symbol }}
                                        {{ number_format($currentCashCount->initial_amount, 2) }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="cash-info-card green">
                            <div class="flex items-center space-x-3">
                                <div class="icon">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div>
                                    <div class="text-sm text-green-600 font-medium">Fecha de Apertura</div>
                                    <div class="text-lg font-bold text-green-900">
                                        {{ \Carbon\Carbon::parse($currentCashCount->opening_date)->format('d/m/Y') }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="cash-info-card purple">
                            <div class="flex items-center space-x-3">
                                <div class="icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div>
                                    <div class="text-sm text-purple-600 font-medium">Hora de Apertura</div>
                                    <div class="text-lg font-bold text-purple-900">
                                        {{ \Carbon\Carbon::parse($currentCashCount->opening_date)->format('H:i') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection


