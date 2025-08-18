@extends('layouts.app')

@section('title', 'Editar Caja')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/cash-counts/edit.css') }}">
@endpush

@push('js')
    <script>
        // Datos globales para JavaScript
        window.cashCountEditData = {
            cashCountId: {{ $cashCount->id }},
            currencySymbol: '{{ $currency->symbol }}',
            openingDate: '{{ old('opening_date', \Carbon\Carbon::parse($cashCount->opening_date)->format('Y-m-d')) }}',
            openingTime: '{{ old('opening_time', \Carbon\Carbon::parse($cashCount->opening_date)->format('H:i')) }}',
            initialAmount: '{{ old('initial_amount', $cashCount->initial_amount) }}',
            observations: @json(old('observations', $cashCount->observations)),
            movementsSummary: {
                income: {{ $cashCount->movements()->where('type', 'income')->sum('amount') }},
                expense: {{ $cashCount->movements()->where('type', 'expense')->sum('amount') }},
                totalMovements: {{ $cashCount->movements()->count() }}
            }
        };
    </script>
    <script src="{{ asset('js/admin/cash-counts/edit.js') }}" defer></script>
@endpush

@section('content')
<div class="space-y-6" x-data="editCashCountForm()">
    <!-- Hero -->
    <div class="relative overflow-hidden bg-gradient-to-br from-blue-600 via-purple-600 to-indigo-700 rounded-2xl shadow-2xl">
        <div class="absolute inset-0 bg-black opacity-10"></div>
        <div class="relative px-6 py-8 sm:px-8 sm:py-12">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div class="flex-1">
                    <div class="flex items-center space-x-4 mb-4">
                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                            <i class="fas fa-cash-register text-white text-2xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl sm:text-4xl font-bold text-white mb-2">Editar Caja #{{ str_pad($cashCount->id, 4, '0', STR_PAD_LEFT) }}</h1>
                            <p class="text-blue-100 text-lg">Modifica los datos del arqueo seleccionado</p>
                        </div>
                    </div>
                </div>
                <div class="mt-6 lg:mt-0 hero-buttons">
                    <a href="{{ route('admin.cash-counts.index') }}"
                       class="inline-flex items-center justify-center px-4 sm:px-6 py-3 bg-white bg-opacity-20 hover:bg-opacity-30 text-white font-semibold rounded-xl transition-all duration-200 border border-white border-opacity-30 min-w-[120px] sm:min-w-[140px]">
                        <i class="fas fa-arrow-left mr-1 sm:mr-2"></i>
                        <span class="text-xs sm:text-sm">Volver</span>
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
                    <h2 class="text-2xl font-bold text-gray-900">Información General</h2>
                    <p class="text-gray-600">Actualiza la información de la caja</p>
                </div>
            </div>
        </div>

        <form x-ref="form" action="{{ route('admin.cash-counts.update', $cashCount->id) }}" method="POST" @submit.prevent="handleSubmit">
            @csrf
            @method('PUT')

            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 form-grid">
                <!-- Fecha de apertura -->
                <div>
                    <label for="opening_date" class="block text-sm font-medium text-gray-700 mb-1">Fecha de apertura</label>
                    <input type="date" id="opening_date" name="opening_date" x-model="openingDate"
                           :class="errors.opening_date ? 'form-input input-error' : 'form-input'"
                           class="w-full rounded-lg border-gray-300 focus:ring-purple-500 focus:border-purple-500">
                    <p class="error-message" x-text="errors.opening_date" x-show="errors.opening_date"></p>
                </div>

                <!-- Hora de apertura -->
                <div>
                    <label for="opening_time" class="block text-sm font-medium text-gray-700 mb-1">Hora de apertura</label>
                    <input type="time" id="opening_time" name="opening_time" x-model="openingTime"
                           :class="errors.opening_time ? 'form-input input-error' : 'form-input'"
                           class="w-full rounded-lg border-gray-300 focus:ring-purple-500 focus:border-purple-500">
                    <p class="error-message" x-text="errors.opening_time" x-show="errors.opening_time"></p>
                </div>

                <!-- Monto inicial -->
                <div>
                    <label for="initial_amount" class="block text-sm font-medium text-gray-700 mb-1">Monto inicial ({{ $currency->symbol }})</label>
                    <input type="number" step="0.01" id="initial_amount" name="initial_amount" x-model="initialAmount" @blur="formatAmount"
                           :class="errors.initial_amount ? 'form-input input-error' : 'form-input'"
                           class="w-full rounded-lg border-gray-300 focus:ring-purple-500 focus:border-purple-500">
                    <p class="error-message" x-text="errors.initial_amount" x-show="errors.initial_amount"></p>
                </div>

                <!-- Observaciones -->
                <div class="sm:col-span-2 md:col-span-3 lg:col-span-5">
                    <label for="observations" class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                    <textarea id="observations" name="observations" rows="3" x-model="observations"
                              class="w-full rounded-lg border-gray-300 focus:ring-purple-500 focus:border-purple-500 form-input"></textarea>
                </div>
            </div>

            <!-- Acciones -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                <div class="flex items-center justify-end hero-buttons">
                    <a href="{{ url()->previous() }}" class="inline-flex items-center justify-center px-4 sm:px-6 py-2 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-100 transition">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Volver
                    </a>
                    <button type="button" @click="confirmReset()" class="inline-flex items-center justify-center px-4 sm:px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
                        <i class="fas fa-undo mr-2"></i>
                        Restablecer
                    </button>
                    <button type="submit" 
                            :disabled="isSubmitting"
                            class="inline-flex items-center justify-center px-4 sm:px-6 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg hover:opacity-90 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-save mr-2" :class="isSubmitting ? 'loading-spinner' : ''"></i>
                        <span x-text="isSubmitting ? 'Guardando...' : 'Guardar Cambios'"></span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Resumen de Movimientos -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-6 border-b border-gray-200">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-chart-pie text-white text-xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Resumen de Movimientos</h2>
                    <p class="text-gray-600">Ingresos, egresos y total de movimientos</p>
                </div>
            </div>
        </div>
        <div class="p-6">
            <div class="summary-cards">
                <div class="summary-card income">
                    <div class="flex items-center justify-between">
                        <div class="icon">
                            <i class="fas fa-arrow-up"></i>
                        </div>
                        <div class="text-right">
                            <div class="amount">{{ $currency->symbol }} {{ number_format($cashCount->movements()->where('type', 'income')->sum('amount'), 2) }}</div>
                            <div class="label">Total Ingresos</div>
                        </div>
                    </div>
                </div>
                <div class="summary-card expense">
                    <div class="flex items-center justify-between">
                        <div class="icon">
                            <i class="fas fa-arrow-down"></i>
                        </div>
                        <div class="text-right">
                            <div class="amount">{{ $currency->symbol }} {{ number_format($cashCount->movements()->where('type', 'expense')->sum('amount'), 2) }}</div>
                            <div class="label">Total Egresos</div>
                        </div>
                    </div>
                </div>
                <div class="summary-card movements">
                    <div class="flex items-center justify-between">
                        <div class="icon">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                        <div class="text-right">
                            <div class="amount">{{ $cashCount->movements()->count() }}</div>
                            <div class="label">Total Movimientos</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


