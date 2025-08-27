@extends('layouts.app')

@section('title', 'Crear Cliente')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/customers/create.css') }}">
@endpush

@push('js')
    <script>
        // Pasar datos de PHP a JavaScript
        window.oldValues = {
            name: '{{ old('name') }}',
            nit_number: '{{ old('nit_number') }}',
            phone: '{{ old('phone') }}',
            email: '{{ old('email') }}'
        };
    </script>
    <script src="{{ asset('js/admin/customers/create.js') }}" defer></script>
@endpush

@section('content')
<div x-data="customerForm()" class="space-y-6">
    <!-- Hero Section -->
    <div class="relative overflow-hidden bg-gradient-to-br from-blue-600 via-purple-600 to-indigo-700 rounded-2xl shadow-2xl">
        <div class="absolute inset-0 bg-black opacity-10"></div>
        <div class="relative px-6 py-8 sm:px-8 sm:py-12">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div class="flex-1">
                    <div class="flex items-center space-x-4 mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 via-purple-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-user-plus text-white text-2xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl sm:text-4xl font-bold text-white mb-2">
                                Crear Nuevo Cliente
                            </h1>
                            <p class="text-blue-100 text-lg">
                                Ingrese la información del cliente para registrarlo en el sistema
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-6 lg:mt-0 flex flex-col sm:flex-row gap-3">
                    <button @click="goBack()" 
                            class="inline-flex items-center justify-center px-6 py-3 bg-gray-400 bg-opacity-80 hover:bg-gray-500 hover:bg-opacity-90 text-white font-semibold rounded-xl transition-all duration-200 border border-gray-500 border-opacity-30">
                        <i class="fas fa-arrow-left mr-2"></i>
                        <span class="hidden sm:inline">Volver</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="bg-gradient-to-r from-blue-50 to-purple-50 px-6 py-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-user-plus text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Información del Cliente</h2>
                    </div>
                </div>
            </div>
        </div>

        <form @submit.prevent="submitForm('save')" class="p-6 space-y-8">
            @csrf
            @if(request('return_to'))
                <input type="hidden" name="return_to" value="{{ request('return_to') }}">
            @endif

            <!-- Form Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Nombre Completo -->
                <div class="space-y-2">
                    <label for="name" class="flex items-center space-x-2 text-sm font-semibold text-gray-700">
                        <i class="fas fa-user text-blue-500"></i>
                        <span>Nombre Completo</span>
                        <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="text"
                               id="name"
                               name="name"
                               x-model="form.name"
                               @input="validateField('name')"
                               @blur="validateField('name')"
                               :class="getFieldClasses('name')"
                               placeholder="Ingrese el nombre completo"
                               required
                               autofocus>
                        <div x-show="errors.name" x-cloak class="mt-2 flex items-center space-x-2 text-sm text-red-600">
                            <i class="fas fa-exclamation-circle"></i>
                            <span x-text="errors.name"></span>
                        </div>
                        <div x-show="!errors.name && form.name" class="mt-2 flex items-center space-x-2 text-sm text-green-600">
                            <i class="fas fa-check-circle"></i>
                            <span>¡Se ve bien!</span>
                        </div>
                    </div>
                </div>

                <!-- Número de Cédula -->
                <div class="space-y-2">
                    <label for="nit_number" class="flex items-center space-x-2 text-sm font-semibold text-gray-700">
                        <i class="fas fa-id-card text-blue-500"></i>
                        <span>Número de Cédula</span>
                    </label>
                    <div class="relative">
                        <input type="text"
                               id="nit_number"
                               name="nit_number"
                               x-model="form.nit_number"
                               @input="validateField('nit_number')"
                               @blur="validateField('nit_number')"
                               :class="getFieldClasses('nit_number')"
                               placeholder="Ingrese la Cédula">
                        <div x-show="errors.nit_number" x-cloak class="mt-2 flex items-center space-x-2 text-sm text-red-600">
                            <i class="fas fa-exclamation-circle"></i>
                            <span x-text="errors.nit_number"></span>
                        </div>
                    </div>
                </div>

                <!-- Teléfono -->
                <div class="space-y-2">
                    <label for="phone" class="flex items-center space-x-2 text-sm font-semibold text-gray-700">
                        <i class="fas fa-phone text-blue-500"></i>
                        <span>Teléfono</span>
                    </label>
                    <div class="relative">
                        <input type="tel"
                               id="phone"
                               name="phone"
                               x-model="form.phone"
                               @input="validateField('phone')"
                               @blur="validateField('phone'); formatPhone()"
                               :class="getFieldClasses('phone')"
                               placeholder="(123) 456-7890"
                               maxlength="10"
                               autocomplete="off">
                        <div x-show="errors.phone" x-cloak class="mt-2 flex items-center space-x-2 text-sm text-red-600">
                            <i class="fas fa-exclamation-circle"></i>
                            <span x-text="errors.phone"></span>
                        </div>
                    </div>
                </div>

                <!-- Email -->
                <div class="space-y-2">
                    <label for="email" class="flex items-center space-x-2 text-sm font-semibold text-gray-700">
                        <i class="fas fa-envelope text-blue-500"></i>
                        <span>Correo Electrónico</span>
                    </label>
                    <div class="relative">
                        <input type="email"
                               id="email"
                               name="email"
                               x-model="form.email"
                               @input="validateField('email')"
                               @blur="validateField('email')"
                               :class="getFieldClasses('email')"
                               placeholder="ejemplo@correo.com">
                        <div x-show="errors.email" x-cloak class="mt-2 flex items-center space-x-2 text-sm text-red-600">
                            <i class="fas fa-exclamation-circle"></i>
                            <span x-text="errors.email"></span>
                        </div>
                        <div x-show="!errors.email && form.email" class="mt-2 flex items-center space-x-2 text-sm text-green-600">
                            <i class="fas fa-check-circle"></i>
                            <span>Email válido</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="space-y-4 pt-6 border-t border-gray-200">
                <div class="flex flex-row gap-4 justify-end">
                    <button type="submit" 
                            @click="submitForm('save')"
                            :disabled="isSubmitting || !isFormValid"
                            class="w-32 inline-flex items-center justify-center px-4 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold rounded-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl"
                            title="Guardar Cliente">
                        <i class="fas fa-save"></i>
                    </button>
                    <button type="button" 
                            @click="submitForm('save_and_new')"
                            :disabled="isSubmitting || !isFormValid"
                            class="w-32 inline-flex items-center justify-center px-4 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-semibold rounded-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl"
                            title="Guardar y Crear Otro">
                        <i class="fas fa-plus-circle"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
