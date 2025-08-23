@extends('layouts.app')

@section('title', 'Editar Usuario')

@section('content')
<div class="space-y-6">
    <!-- Header de la página -->
    <div class="page-header">
        <div class="header-content">
            <div class="header-left">
                <div class="header-icon">
                    <i class="fas fa-user-edit"></i>
                </div>
                <div class="header-text">
                    <h1 class="page-title">Editar Usuario</h1>
                    <p class="page-subtitle">Modifica la información del usuario {{ $user->name }}</p>
                </div>
            </div>
            <a href="{{ route('admin.users.index') }}" class="btn-back">
                <i class="fas fa-arrow-left mr-2"></i>
                Volver
            </a>
        </div>
    </div>

    <!-- Formulario -->
    <div class="form-container">
        <div class="form-card">
            <div class="form-card-header">
                <div class="header-icon-container">
                    <div class="header-icon-bg">
                        <i class="fas fa-user-edit"></i>
                    </div>
                </div>
                <div class="header-content">
                    <h3 class="form-card-title">Editar Usuario</h3>
                    <p class="form-card-subtitle">Modifica la información del usuario</p>
                </div>
            </div>
            
            <div class="form-card-body">
                <form action="{{ route('admin.users.update', $user->id) }}" method="POST" x-data="userEditForm()" x-cloak>
                    @csrf
                    @method('PUT')

                    <!-- Información del Usuario -->
                    <div class="form-section">
                        <h4 class="section-title">
                            <i class="fas fa-user label-icon"></i>
                            Información del Usuario
                        </h4>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="name" class="form-label">
                                    <i class="fas fa-user label-icon"></i>
                                    Nombre Completo <span class="required">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-icon">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <input type="text" name="name" id="name"
                                        class="form-input {{ $errors->has('name') ? 'is-invalid' : '' }}"
                                        value="{{ old('name', $user->name) }}" 
                                        required>
                                </div>
                                @if ($errors->has('name'))
                                    <div class="error-message">
                                        <i class="fas fa-exclamation-circle"></i>
                                        {{ $errors->first('name') }}
                                    </div>
                                @endif
                            </div>

                            <div class="form-group">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope label-icon"></i>
                                    Correo Electrónico <span class="required">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-icon">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                    <input type="email" name="email" id="email"
                                        class="form-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
                                        value="{{ old('email', $user->email) }}" 
                                        required>
                                </div>
                                @if ($errors->has('email'))
                                    <div class="error-message">
                                        <i class="fas fa-exclamation-circle"></i>
                                        {{ $errors->first('email') }}
                                    </div>
                                @endif
                            </div>

                            <div class="form-group">
                                <label for="current_password" class="form-label">
                                    <i class="fas fa-key label-icon"></i>
                                    Contraseña Actual
                                </label>
                                <div class="input-group">
                                    <span class="input-group-icon">
                                        <i class="fas fa-key"></i>
                                    </span>
                                    <input type="password" name="current_password" id="current_password"
                                        class="form-input {{ $errors->has('current_password') ? 'is-invalid' : '' }}"
                                        x-model="currentPassword">
                                    <button type="button" class="password-toggle-btn" 
                                        @click="togglePassword('current_password')" 
                                        :title="showCurrentPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'">
                                        <i class="fas" :class="showCurrentPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                                    </button>
                                </div>
                                @if ($errors->has('current_password'))
                                    <div class="error-message">
                                        <i class="fas fa-exclamation-circle"></i>
                                        {{ $errors->first('current_password') }}
                                    </div>
                                @endif
                            </div>

                            <div class="form-group">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock label-icon"></i>
                                    Nueva Contraseña
                                </label>
                                <div class="input-group">
                                    <span class="input-group-icon">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" name="password" id="password"
                                        class="form-input {{ $errors->has('password') ? 'is-invalid' : '' }}"
                                        x-model="password"
                                        @input="checkPasswordStrength()">
                                    <button type="button" class="password-toggle-btn" 
                                        @click="togglePassword('password')" 
                                        :title="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'">
                                        <i class="fas" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                                    </button>
                                </div>
                                @if ($errors->has('password'))
                                    <div class="error-message">
                                        <i class="fas fa-exclamation-circle"></i>
                                        {{ $errors->first('password') }}
                                    </div>
                                @endif
                                <div x-show="passwordStrength.show" class="password-strength">
                                    <div class="strength-bar">
                                        <div class="strength-fill" :class="passwordStrength.class" :style="'width: ' + passwordStrength.width + '%'"></div>
                                    </div>
                                    <span class="strength-text" :class="passwordStrength.textClass" x-text="passwordStrength.text"></span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="password_confirmation" class="form-label">
                                    <i class="fas fa-lock label-icon"></i>
                                    Confirmar Nueva Contraseña
                                </label>
                                <div class="input-group">
                                    <span class="input-group-icon">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" name="password_confirmation" id="password_confirmation"
                                        class="form-input"
                                        x-model="passwordConfirmation"
                                        @input="checkPasswordMatch()">
                                    <button type="button" class="password-toggle-btn" 
                                        @click="togglePassword('password_confirmation')" 
                                        :title="showPasswordConfirmation ? 'Ocultar confirmación' : 'Mostrar confirmación'">
                                        <i class="fas" :class="showPasswordConfirmation ? 'fa-eye-slash' : 'fa-eye'"></i>
                                    </button>
                                </div>
                                <div x-show="passwordMatch.show" class="password-match" :class="passwordMatch.class">
                                    <i class="fas" :class="passwordMatch.icon"></i>
                                    <span x-text="passwordMatch.text"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Asignación y Configuración -->
                    <div class="form-section">
                        <h4 class="section-title">
                            <i class="fas fa-users-cog label-icon"></i>
                            Asignación y Configuración
                        </h4>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="company_id" class="form-label">
                                    <i class="fas fa-building label-icon"></i>
                                    Empresa <span class="required">*</span>
                                </label>
                                <div class="relative" 
                                     x-data="{ 
                                         isOpen: false, 
                                         searchTerm: '', 
                                         filteredCompanies: @js($companies),
                                         selectedCompanyName: '{{ $user->company->name ?? 'Seleccione una empresa' }}',
                                         filterCompanies() {
                                             if (!this.searchTerm) {
                                                 this.filteredCompanies = @js($companies);
                                                 return;
                                             }
                                             const term = this.searchTerm.toLowerCase();
                                             this.filteredCompanies = @js($companies).filter(company => 
                                                 company.name.toLowerCase().includes(term)
                                             );
                                         },
                                         selectCompany(company) {
                                             $refs.companyInput.value = company.id;
                                             this.selectedCompanyName = company.name;
                                             this.isOpen = false;
                                             this.searchTerm = '';
                                         }
                                     }" 
                                     @click.away="isOpen = false">
                                    
                                    <!-- Hidden input for form submission -->
                                    <input type="hidden" name="company_id" x-ref="companyInput" value="{{ $user->company_id }}" required>
                                    
                                    <!-- Select Button -->
                                    <button type="button" 
                                            @click="isOpen = !isOpen; if (isOpen) { $nextTick(() => $refs.companySearch.focus()) }"
                                            class="relative w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-3 py-2.5 pr-10 text-left cursor-pointer focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-300 hover:bg-white hover:border-gray-300 h-11">
                                        <span class="block truncate text-gray-700 text-sm" x-text="selectedCompanyName"></span>
                                        <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                                            <svg class="h-5 w-5 text-gray-400 transition-transform duration-200" 
                                                 :class="{ 'rotate-180': isOpen }" 
                                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </span>
                                    </button>

                                    <!-- Dropdown -->
                                    <div x-show="isOpen" 
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 translate-y-1"
                                         x-transition:enter-end="opacity-1 translate-y-0"
                                         x-transition:leave="transition ease-in duration-150"
                                         x-transition:leave-start="opacity-1 translate-y-0"
                                         x-transition:leave-end="opacity-0 translate-y-1"
                                         class="absolute z-50 mt-1 w-full bg-white shadow-lg max-h-60 rounded-xl border border-gray-200 overflow-auto">
                                        
                                        <!-- Search Input -->
                                        <div class="px-3 py-2 border-b border-gray-100">
                                            <input type="text"
                                                   x-ref="companySearch"
                                                   x-model="searchTerm"
                                                   @input="filterCompanies()"
                                                   @keydown.escape="isOpen = false"
                                                   placeholder="Buscar empresa..."
                                                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                        </div>

                                        <!-- Options List -->
                                        <div class="max-h-48 overflow-y-auto">
                                            <template x-for="company in filteredCompanies" :key="company.id">
                                                <div @click="selectCompany(company)"
                                                     class="cursor-pointer select-none relative py-3 pl-3 pr-3 hover:bg-gray-50 transition-colors duration-150">
                                                    <div class="flex items-center">
                                                        <i class="fas fa-building text-blue-500 mr-2"></i>
                                                        <span class="block text-sm text-gray-900 font-medium" x-text="company.name"></span>
                                                    </div>
                                                </div>
                                            </template>
                                            
                                            <!-- No results -->
                                            <div x-show="filteredCompanies.length === 0" 
                                                 class="px-3 py-4 text-sm text-gray-500 text-center">
                                                No se encontraron empresas
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if ($errors->has('company_id'))
                                    <div class="error-message">
                                        <i class="fas fa-exclamation-circle"></i>
                                        {{ $errors->first('company_id') }}
                                    </div>
                                @endif
                            </div>

                            <div class="form-group">
                                <label for="role" class="form-label">
                                    <i class="fas fa-user-tag label-icon"></i>
                                    Rol del Usuario <span class="required">*</span>
                                </label>
                                <div class="relative" 
                                     x-data="{ 
                                         isOpen: false, 
                                         searchTerm: '', 
                                         filteredRoles: @js($roles),
                                         selectedRoleName: '{{ $user->roles->first()->display_name ?? $user->roles->first()->name ?? 'Seleccione un rol' }}',
                                         filterRoles() {
                                             if (!this.searchTerm) {
                                                 this.filteredRoles = @js($roles);
                                                 return;
                                             }
                                             const term = this.searchTerm.toLowerCase();
                                             this.filteredRoles = @js($roles).filter(role => 
                                                 role.name.toLowerCase().includes(term) || 
                                                 (role.display_name && role.display_name.toLowerCase().includes(term))
                                             );
                                         },
                                         selectRole(role) {
                                             $refs.roleInput.value = role.id;
                                             this.selectedRoleName = role.display_name || role.name;
                                             this.isOpen = false;
                                             this.searchTerm = '';
                                         }
                                     }" 
                                     @click.away="isOpen = false">
                                    
                                    <!-- Hidden input for form submission -->
                                    <input type="hidden" name="role" x-ref="roleInput" value="{{ $user->roles->first()->id ?? '' }}" required>
                                    
                                    <!-- Select Button -->
                                    <button type="button" 
                                            @click="isOpen = !isOpen; if (isOpen) { $nextTick(() => $refs.roleSearch.focus()) }"
                                            class="relative w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-3 py-2.5 pr-10 text-left cursor-pointer focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-300 hover:bg-white hover:border-gray-300 h-11">
                                        <span class="block truncate text-gray-700 text-sm" x-text="selectedRoleName"></span>
                                        <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                                            <svg class="h-5 w-5 text-gray-400 transition-transform duration-200" 
                                                 :class="{ 'rotate-180': isOpen }" 
                                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </span>
                                    </button>

                                    <!-- Dropdown -->
                                    <div x-show="isOpen" 
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 translate-y-1"
                                         x-transition:enter-end="opacity-1 translate-y-0"
                                         x-transition:leave="transition ease-in duration-150"
                                         x-transition:leave-start="opacity-1 translate-y-0"
                                         x-transition:leave-end="opacity-0 translate-y-1"
                                         class="absolute z-50 mt-1 w-full bg-white shadow-lg max-h-60 rounded-xl border border-gray-200 overflow-auto">
                                        
                                        <!-- Search Input -->
                                        <div class="px-3 py-2 border-b border-gray-100">
                                            <input type="text"
                                                   x-ref="roleSearch"
                                                   x-model="searchTerm"
                                                   @input="filterRoles()"
                                                   @keydown.escape="isOpen = false"
                                                   placeholder="Buscar rol..."
                                                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                        </div>

                                        <!-- Options List -->
                                        <div class="max-h-48 overflow-y-auto">
                                            <template x-for="role in filteredRoles" :key="role.id">
                                                <div @click="selectRole(role)"
                                                     class="cursor-pointer select-none relative py-3 pl-3 pr-3 hover:bg-gray-50 transition-colors duration-150">
                                                    <div class="flex items-center">
                                                        <i class="fas fa-user-shield text-purple-500 mr-2"></i>
                                                        <div>
                                                            <span class="block text-sm text-gray-900 font-medium" x-text="role.display_name || role.name"></span>
                                                            <span class="block text-xs text-gray-500" x-text="role.name"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                            
                                            <!-- No results -->
                                            <div x-show="filteredRoles.length === 0" 
                                                 class="px-3 py-4 text-sm text-gray-500 text-center">
                                                No se encontraron roles
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if ($errors->has('role'))
                                    <div class="error-message">
                                        <i class="fas fa-exclamation-circle"></i>
                                        {{ $errors->first('role') }}
                                    </div>
                                @endif
                            </div>

                            <div class="form-group">
                                <div class="checkbox-group">
                                    <label class="checkbox-label">
                                        <input type="checkbox" x-model="syncPasswordVisibility">
                                        <span class="checkmark"></span>
                                        <i class="fas fa-sync checkbox-icon"></i>
                                        Sincronizar visibilidad de contraseñas
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="checkbox-group">
                                    <label class="checkbox-label">
                                        <input type="checkbox" x-model="showPasswordHints">
                                        <span class="checkmark"></span>
                                        <i class="fas fa-info-circle checkbox-icon"></i>
                                        Mostrar indicadores de fortaleza
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('admin.users.index') }}" class="btn-secondary">
                            <i class="fas fa-times mr-2"></i>
                            Cancelar
                        </a>
                        <button type="submit" class="btn-primary" x-ref="submitBtn">
                            <i class="fas fa-save mr-2"></i>
                            Actualizar Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin/users/edit.css') }}">
@endpush
    

@push('js')
    <script src="{{ asset('js/admin/users/edit.js') }}" defer></script>
@endpush
@endsection