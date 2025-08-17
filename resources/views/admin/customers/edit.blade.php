@extends('layouts.app')

@section('title', 'Editar Cliente')

@section('content')
<div x-data="customerEditForm()" class="space-y-6">
    <!-- Hero Section -->
    <div class="relative overflow-hidden bg-gradient-to-br from-blue-600 via-purple-600 to-indigo-700 rounded-2xl shadow-2xl">
        <div class="absolute inset-0 bg-black opacity-10"></div>
        <div class="relative px-6 py-8 sm:px-8 sm:py-12">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div class="flex-1">
                    <div class="flex items-center space-x-4 mb-4">
                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                            <i class="fas fa-user-edit text-white text-2xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl sm:text-4xl font-bold text-white mb-2">
                                Editar Cliente
                            </h1>
                            <p class="text-blue-100 text-lg">
                                Actualice la información del cliente: <strong>{{ $customer->name }}</strong>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-6 lg:mt-0 flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('admin.customers.index') }}" 
                       class="inline-flex items-center justify-center px-6 py-3 bg-white bg-opacity-20 hover:bg-opacity-30 text-white font-semibold rounded-xl transition-all duration-200 border border-white border-opacity-30">
                        <i class="fas fa-arrow-left mr-2"></i>
                        <span class="hidden sm:inline">Volver</span>
                    </a>
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
                        <i class="fas fa-user-edit text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Información del Cliente</h2>
                        <p class="text-gray-600">Actualice los campos que desee modificar para el cliente: <strong class="text-blue-600">{{ $customer->name }}</strong></p>
                    </div>
                </div>
            </div>
        </div>

        <form @submit.prevent="submitForm()" class="p-6 space-y-8">
            @csrf
            @method('PUT')

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
                               @input="validateField('phone'); formatPhone()"
                               @blur="validateField('phone')"
                               :class="getFieldClasses('phone')"
                               placeholder="(123) 456-7890"
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

                <!-- Deuda Total -->
                <div class="space-y-2">
                    <label for="total_debt" class="flex items-center space-x-2 text-sm font-semibold text-gray-700">
                        <i class="fas fa-money-bill-wave text-blue-500"></i>
                        <span>Deuda Total</span>
                    </label>
                    <div class="relative">
                        <div class="flex items-center space-x-3">
                            <input type="number" 
                                   step="0.01" 
                                   min="0"
                                   id="total_debt"
                                   name="total_debt"
                                   x-model="form.total_debt"
                                   @input="validateField('total_debt')"
                                   @blur="validateField('total_debt')"
                                   :class="getFieldClasses('total_debt')"
                                   :disabled="!debtEditable"
                                   :aria-describedby="debtEditable ? 'debt-edit-enabled' : 'debt-edit-disabled'"
                                   placeholder="0.00"
                                   class="flex-1">
                            <button type="button" 
                                    @click="toggleDebtEdit()"
                                    :class="debtEditable ? 'bg-gradient-to-r from-green-600 to-emerald-600' : 'bg-gradient-to-r from-yellow-600 to-orange-600'"
                                    class="w-12 h-12 rounded-xl flex items-center justify-center text-white transition-all duration-200 hover:scale-105">
                                <i :class="debtEditable ? 'fas fa-unlock' : 'fas fa-lock'" class="text-lg"></i>
                            </button>
                        </div>
                        <div x-show="errors.total_debt" x-cloak class="mt-2 flex items-center space-x-2 text-sm text-red-600">
                            <i class="fas fa-exclamation-circle"></i>
                            <span x-text="errors.total_debt"></span>
                        </div>
                        <div class="mt-2 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <div class="flex items-start space-x-2">
                                <i class="fas fa-exclamation-triangle text-yellow-600 mt-0.5"></i>
                                <div class="text-sm text-yellow-800">
                                    <p class="font-medium">La deuda se calcula automáticamente según las ventas y pagos.</p>
                                    <p class="text-xs mt-1">Solo edite manualmente si es necesario.</p>
                                </div>
                            </div>
                        </div>
                        <!-- Estados de accesibilidad -->
                        <div id="debt-edit-disabled" x-show="!debtEditable" x-cloak class="sr-only">
                            Campo de deuda deshabilitado. Haga clic en el botón de candado para habilitar la edición manual.
                        </div>
                        <div id="debt-edit-enabled" x-show="debtEditable" x-cloak class="sr-only">
                            Campo de deuda habilitado para edición manual. Haga clic en el botón de candado para deshabilitar.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end pt-6 border-t border-gray-200">
                <button type="submit" 
                        :disabled="isSubmitting || !isFormValid"
                        class="w-32 inline-flex items-center justify-center px-4 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold rounded-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl"
                        title="Actualizar Cliente">
                    <i class="fas fa-save"></i>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('css')
<style>
    /* Input styles */
    input[type="text"], input[type="tel"], input[type="email"], input[type="number"] {
        @apply w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent;
    }

    input:focus {
        @apply transform -translate-y-0.5;
    }

    input:disabled {
        @apply bg-gray-100 text-gray-500 cursor-not-allowed opacity-70;
    }

    /* Validation states */
    .input-valid {
        @apply border-green-300 bg-green-50;
    }

    .input-invalid {
        @apply border-red-300 bg-red-50;
    }

    /* Loading states */
    .loading {
        @apply opacity-75 cursor-not-allowed;
    }

    /* Responsive adjustments */
    @media (max-width: 640px) {
        .hero-section {
            @apply px-4 py-6;
        }
        
        .hero-title {
            @apply text-2xl;
        }
    }
</style>
@endpush

@push('js')
<script>
function customerEditForm() {
    return {
        form: {
            name: '{{ old('name', $customer->name) }}',
            nit_number: '{{ old('nit_number', $customer->nit_number) }}',
            phone: '{{ old('phone', $customer->phone) }}',
            email: '{{ old('email', $customer->email) }}',
            total_debt: '{{ old('total_debt', $customer->total_debt) }}'
        },
        errors: {},
        isSubmitting: false,
        debtEditable: false,

        init() {
            // Formatear teléfono al cargar
            if (this.form.phone) {
                this.formatPhone();
            }

            // Capitalizar automáticamente el nombre
            this.$watch('form.name', (value) => {
                if (value) {
                    let words = value.split(' ');
                    words = words.map(word => {
                        return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
                    });
                    this.form.name = words.join(' ');
                }
            });

            // Validar campos iniciales
            this.validateAllFields();
        },

        validateField(fieldName) {
            const value = this.form[fieldName];
            
            switch(fieldName) {
                case 'name':
                    if (!value) {
                        this.errors.name = 'El nombre es requerido';
                    } else if (value.length < 2) {
                        this.errors.name = 'El nombre debe tener al menos 2 caracteres';
                    } else {
                        delete this.errors.name;
                    }
                    break;

                case 'nit_number':
                    if (value && !/^\d{7,11}$/.test(value.replace(/\D/g, ''))) {
                        this.errors.nit_number = 'La cédula debe tener entre 7 y 11 dígitos';
                    } else {
                        delete this.errors.nit_number;
                    }
                    break;

                case 'phone':
                    if (value) {
                        const cleanPhone = value.replace(/\D/g, '');
                        if (cleanPhone.length < 10) {
                            this.errors.phone = 'El teléfono debe tener al menos 10 dígitos';
                        } else {
                            delete this.errors.phone;
                        }
                    } else {
                        delete this.errors.phone;
                    }
                    break;

                case 'email':
                    if (value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                        this.errors.email = 'Ingrese un email válido';
                    } else {
                        delete this.errors.email;
                    }
                    break;

                case 'total_debt':
                    if (value && (isNaN(value) || parseFloat(value) < 0)) {
                        this.errors.total_debt = 'La deuda debe ser un número válido mayor o igual a 0';
                    } else {
                        delete this.errors.total_debt;
                    }
                    break;
            }
        },

        validateAllFields() {
            ['name', 'nit_number', 'phone', 'email', 'total_debt'].forEach(field => {
                this.validateField(field);
            });
        },

        getFieldClasses(fieldName) {
            const baseClasses = 'w-full px-4 py-3 border-2 rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent';
            
            if (this.errors[fieldName]) {
                return baseClasses + ' border-red-300 bg-red-50';
            } else if (this.form[fieldName] && !this.errors[fieldName]) {
                return baseClasses + ' border-green-300 bg-green-50';
            } else {
                return baseClasses + ' border-gray-200';
            }
        },

        formatPhone() {
            let value = this.form.phone.replace(/\D/g, '');
            if (value.length >= 6) {
                value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
            } else if (value.length >= 3) {
                value = value.replace(/(\d{3})(\d{0,3})/, '($1) $2');
            }
            this.form.phone = value;
        },

        async toggleDebtEdit() {
            if (!this.debtEditable) {
                // Mostrar confirmación para habilitar edición
                const result = await this.showConfirmAlert(
                    '¿Habilitar edición de deuda?',
                    'La deuda se calcula automáticamente según las ventas y pagos. ¿Está seguro de que desea editarla manualmente?',
                    'warning'
                );

                if (result.isConfirmed) {
                    this.debtEditable = true;
                    // Usar requestAnimationFrame para evitar conflictos de accesibilidad
                    requestAnimationFrame(() => {
                        const debtInput = document.getElementById('total_debt');
                        if (debtInput) {
                            // Remover temporalmente cualquier aria-hidden del ancestro
                            const ancestors = this.getAncestorsWithAriaHidden(debtInput);
                            ancestors.forEach(el => {
                                el.setAttribute('data-aria-hidden-backup', el.getAttribute('aria-hidden'));
                                el.removeAttribute('aria-hidden');
                            });
                            
                            // Enfocar y seleccionar
                            debtInput.focus();
                            debtInput.select();
                            
                            // Restaurar aria-hidden después de un breve delay
                            setTimeout(() => {
                                ancestors.forEach(el => {
                                    const backup = el.getAttribute('data-aria-hidden-backup');
                                    if (backup) {
                                        el.setAttribute('aria-hidden', backup);
                                        el.removeAttribute('data-aria-hidden-backup');
                                    }
                                });
                            }, 500);
                        }
                    });
                    this.showAlert('Campo habilitado. Ahora puede editar la deuda manualmente.', 'success');
                }
            } else {
                // Deshabilitar edición
                this.debtEditable = false;
                // Remover el foco del campo al deshabilitarlo
                const debtInput = document.getElementById('total_debt');
                if (debtInput) {
                    debtInput.blur();
                }
                this.showAlert('Campo deshabilitado. La deuda volverá a calcularse automáticamente.', 'info');
            }
        },

        getAncestorsWithAriaHidden(element) {
            const ancestors = [];
            let current = element.parentElement;
            
            while (current) {
                if (current.hasAttribute('aria-hidden')) {
                    ancestors.push(current);
                }
                current = current.parentElement;
            }
            
            return ancestors;
        },

        get isFormValid() {
            return !this.errors.name && this.form.name;
        },

        async submitForm() {
            // Validar todos los campos
            this.validateAllFields();
            
            if (!this.isFormValid) {
                this.showAlert('Por favor, complete todos los campos requeridos', 'error');
                return;
            }

            this.isSubmitting = true;

            try {
                // Preparar datos del formulario
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('_method', 'PUT');
                formData.append('name', this.form.name);
                formData.append('nit_number', this.form.nit_number);
                formData.append('phone', this.form.phone.replace(/\D/g, '')); // Guardar solo números
                formData.append('email', this.form.email);
                formData.append('total_debt', this.form.total_debt);

                const response = await fetch('{{ route('admin.customers.update', $customer->id) }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();

                if (response.ok) {
                    this.showAlert(result.message || 'Cliente actualizado exitosamente', 'success');
                    
                    // Redirigir a la lista de clientes después de un breve delay
                    setTimeout(() => {
                        window.location.href = '{{ route('admin.customers.index') }}';
                    }, 1500);
                } else {
                    // Manejar errores de validación del servidor
                    if (result.errors) {
                        this.errors = result.errors;
                        this.showAlert('Por favor, corrija los errores en el formulario', 'error');
                    } else {
                        this.showAlert(result.message || 'Error al actualizar el cliente', 'error');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                this.showAlert('Error de conexión. Intente nuevamente.', 'error');
            } finally {
                this.isSubmitting = false;
            }
        },

        showAlert(message, type = 'info') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: type === 'success' ? '¡Éxito!' : type === 'error' ? 'Error' : 'Información',
                    text: message,
                    icon: type,
                    confirmButtonText: 'Entendido',
                    timer: type === 'success' ? 3000 : undefined,
                    timerProgressBar: type === 'success'
                });
            } else {
                alert(message);
            }
        },

        async showConfirmAlert(title, text, icon = 'warning') {
            if (typeof Swal !== 'undefined') {
                const result = await Swal.fire({
                    title: title,
                    text: text,
                    icon: icon,
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, continuar',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true
                });
                return result;
            } else {
                return { isConfirmed: confirm(text) };
            }
        }
    }
}
</script>
@endpush
