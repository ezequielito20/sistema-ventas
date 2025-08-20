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
                                <div class="input-group">
                                    <span class="input-group-icon">
                                        <i class="fas fa-building"></i>
                                    </span>
                                    <select name="company_id" id="company_id"
                                        class="form-input {{ $errors->has('company_id') ? 'is-invalid' : '' }}"
                                        required>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}" 
                                                {{ old('company_id', $user->company_id) == $company->id ? 'selected' : '' }}>
                                                {{ $company->name }}
                                            </option>
                                        @endforeach
                                    </select>
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
                                <div class="input-group">
                                    <span class="input-group-icon">
                                        <i class="fas fa-user-tag"></i>
                                    </span>
                                    <select name="role" id="role"
                                        class="form-input {{ $errors->has('role') ? 'is-invalid' : '' }}"
                                        required>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id }}" 
                                                {{ old('role', $user->roles->first()->id ?? '') == $role->id ? 'selected' : '' }}>
                                                {{ $role->name }}
                                            </option>
                                        @endforeach
                                    </select>
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