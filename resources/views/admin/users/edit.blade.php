@extends('layouts.app')

@section('title', 'Editar Usuario')

@section('content')
<div class="space-y-6">
    <!-- Header con gradiente -->
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
                <form action="{{ route('admin.users.update', $user->id) }}" method="POST" x-data="userEditForm()">
                    @csrf
                    @method('PUT')

                    <!-- Información básica -->
                    <div class="form-section">
                        <h4 class="section-title">
                            <i class="fas fa-user label-icon"></i>
                            Información Básica
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
                        </div>
                    </div>

                    <!-- Contraseñas -->
                    <div class="form-section">
                        <h4 class="section-title">
                            <i class="fas fa-lock label-icon"></i>
                            Cambio de Contraseña
                        </h4>
                        <p class="section-hint">Deja los campos de contraseña en blanco si no deseas cambiarla</p>
                        
                        <div class="form-grid">
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

                    <!-- Asignación -->
                    <div class="form-section">
                        <h4 class="section-title">
                            <i class="fas fa-users-cog label-icon"></i>
                            Asignación
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
                        </div>
                    </div>

                    <!-- Opciones adicionales -->
                    <div class="form-section">
                        <h4 class="section-title">
                            <i class="fas fa-cog label-icon"></i>
                            Opciones Adicionales
                        </h4>
                        
                        <div class="form-grid">
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
<style>
    /* Estilos mejorados con más color */
    .space-y-6 > * + * {
        margin-top: 2rem;
    }
    
    /* Header de la página */
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 2rem;
        border-radius: 16px;
        color: white;
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        margin-bottom: 2rem;
    }
    
    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .header-left {
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }
    
    .header-icon {
        width: 60px;
        height: 60px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }
    
    .page-title {
        font-size: 2rem;
        font-weight: 800;
        margin: 0;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .page-subtitle {
        font-size: 1rem;
        opacity: 0.9;
        margin: 0.5rem 0 0 0;
    }
    
    .btn-back {
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
    }
    
    .btn-back:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-2px);
        color: white;
    }
    
    /* Contenedor del formulario */
    .form-container {
        max-width: 1000px;
        margin: 0 auto;
    }
    
    .form-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
        overflow: hidden;
    }
    
    /* Header del formulario */
    .form-card-header {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        padding: 2rem;
        display: flex;
        align-items: center;
        gap: 1.5rem;
        border-bottom: 2px solid #e5e7eb;
    }
    
    .header-icon-container {
        display: flex;
        align-items: center;
    }
    
    .header-icon-bg {
        width: 70px;
        height: 70px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.75rem;
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
    }
    
    .form-card-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }
    
    .form-card-subtitle {
        color: #64748b;
        margin: 0.5rem 0 0 0;
        font-size: 0.95rem;
    }
    
    /* Cuerpo del formulario */
    .form-card-body {
        padding: 2.5rem;
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    }
    
    /* Secciones del formulario */
    .form-section {
        margin-bottom: 2.5rem;
        padding: 1.5rem;
        background: white;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }
    
    .section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #374151;
        margin: 0 0 1.5rem 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #f1f5f9;
    }
    
    .section-hint {
        color: #6b7280;
        font-size: 0.875rem;
        margin: 0 0 1rem 0;
        font-style: italic;
    }
    
    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }
    
    /* Grupos de formulario */
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.75rem;
        font-size: 0.95rem;
    }
    
    .label-icon {
        color: #667eea;
        font-size: 0.875rem;
    }
    
    .required {
        color: #ef4444;
        font-weight: 700;
    }
    
    /* Input group */
    .input-group {
        display: flex;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        border: 2px solid #e5e7eb;
        transition: all 0.3s ease;
    }
    
    .input-group:focus-within {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .input-group-icon {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1rem 1.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 60px;
        font-size: 1.1rem;
    }
    
    .form-input {
        flex: 1;
        padding: 1rem 1.25rem;
        border: none;
        font-size: 1rem;
        background: white;
        color: #374151;
    }
    
    .form-input:focus {
        outline: none;
        background: #fafbfc;
    }
    
    .form-input.is-invalid {
        background: #fef2f2;
    }
    
    .form-input::placeholder {
        color: #9ca3af;
        font-style: italic;
    }
    
    /* Password toggle button */
    .password-toggle-btn {
        background: #f8fafc;
        border: none;
        color: #6b7280;
        padding: 1rem 1.25rem;
        cursor: pointer;
        transition: all 0.3s ease;
        border-left: 1px solid #e5e7eb;
    }
    
    .password-toggle-btn:hover {
        background: #e5e7eb;
        color: #374151;
    }
    
    /* Password strength */
    .password-strength {
        margin-top: 0.75rem;
    }
    
    .strength-bar {
        height: 4px;
        background: #e5e7eb;
        border-radius: 2px;
        overflow: hidden;
        margin-bottom: 0.5rem;
    }
    
    .strength-fill {
        height: 100%;
        transition: all 0.3s ease;
    }
    
    .strength-fill.weak {
        background: linear-gradient(90deg, #ef4444, #f87171);
    }
    
    .strength-fill.medium {
        background: linear-gradient(90deg, #f59e0b, #fbbf24);
    }
    
    .strength-fill.strong {
        background: linear-gradient(90deg, #10b981, #34d399);
    }
    
    .strength-text {
        font-size: 0.875rem;
        font-weight: 500;
    }
    
    .strength-text.weak {
        color: #ef4444;
    }
    
    .strength-text.medium {
        color: #f59e0b;
    }
    
    .strength-text.strong {
        color: #10b981;
    }
    
    /* Password match */
    .password-match {
        margin-top: 0.75rem;
        padding: 0.75rem;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
    }
    
    .password-match.match {
        background: #f0fdf4;
        color: #166534;
        border-left: 4px solid #10b981;
    }
    
    .password-match.no-match {
        background: #fef2f2;
        color: #dc2626;
        border-left: 4px solid #ef4444;
    }
    
    /* Checkbox group */
    .checkbox-group {
        margin-top: 0.5rem;
    }
    
    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        cursor: pointer;
        padding: 0.75rem;
        border-radius: 8px;
        transition: all 0.3s ease;
        font-size: 0.95rem;
        color: #374151;
    }
    
    .checkbox-label:hover {
        background: #f8fafc;
    }
    
    .checkbox-label input[type="checkbox"] {
        display: none;
    }
    
    .checkmark {
        width: 20px;
        height: 20px;
        border: 2px solid #d1d5db;
        border-radius: 4px;
        position: relative;
        transition: all 0.3s ease;
    }
    
    .checkbox-label input[type="checkbox"]:checked + .checkmark {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-color: #667eea;
    }
    
    .checkbox-label input[type="checkbox"]:checked + .checkmark::after {
        content: '✓';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-size: 0.75rem;
        font-weight: bold;
    }
    
    .checkbox-icon {
        color: #667eea;
        font-size: 0.875rem;
    }
    
    /* Mensajes de error */
    .error-message {
        color: #ef4444;
        font-size: 0.875rem;
        margin-top: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem;
        background: #fef2f2;
        border-radius: 8px;
        border-left: 4px solid #ef4444;
    }
    
    /* Acciones del formulario */
    .form-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        padding-top: 2rem;
        border-top: 2px solid #f1f5f9;
        margin-top: 2rem;
    }
    
    /* Botones */
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        padding: 1rem 2rem;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
    }
    
    .btn-secondary {
        background: white;
        border: 2px solid #e5e7eb;
        color: #64748b;
        padding: 1rem 2rem;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.95rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }
    
    .btn-secondary:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #475569;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    /* Responsividad */
    @media (max-width: 768px) {
        .page-header {
            padding: 1.5rem;
            margin: 0 1rem 2rem 1rem;
        }
        
        .header-content {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }
        
        .header-left {
            flex-direction: column;
            gap: 1rem;
        }
        
        .page-title {
            font-size: 1.5rem;
        }
        
        .header-icon {
            width: 50px;
            height: 50px;
            font-size: 1.25rem;
        }
        
        .form-container {
            margin: 0 1rem;
        }
        
        .form-card-header {
            padding: 1.5rem;
            flex-direction: column;
            text-align: center;
            gap: 1rem;
        }
        
        .header-icon-bg {
            width: 60px;
            height: 60px;
            font-size: 1.5rem;
        }
        
        .form-card-body {
            padding: 1.5rem;
        }
        
        .form-section {
            padding: 1rem;
        }
        
        .form-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .form-actions {
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .btn-primary,
        .btn-secondary {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@push('js')
<script>
function userEditForm() {
    return {
        currentPassword: '',
        password: '',
        passwordConfirmation: '',
        showCurrentPassword: false,
        showPassword: false,
        showPasswordConfirmation: false,
        syncPasswordVisibility: false,
        showPasswordHints: false,
        passwordStrength: {
            show: false,
            width: 0,
            class: '',
            text: '',
            textClass: ''
        },
        passwordMatch: {
            show: false,
            class: '',
            icon: '',
            text: ''
        },
        
        init() {
            this.$watch('syncPasswordVisibility', (value) => {
                if (value) {
                    this.syncPasswords();
                }
            });
            
            this.$watch('showPasswordHints', (value) => {
                if (value && this.password) {
                    this.checkPasswordStrength();
                } else if (!value) {
                    this.passwordStrength.show = false;
                }
            });
        },
        
        togglePassword(field) {
            if (field === 'current_password') {
                this.showCurrentPassword = !this.showCurrentPassword;
                const input = document.getElementById('current_password');
                input.type = this.showCurrentPassword ? 'text' : 'password';
            } else if (field === 'password') {
                this.showPassword = !this.showPassword;
                const input = document.getElementById('password');
                input.type = this.showPassword ? 'text' : 'password';
                
                if (this.syncPasswordVisibility) {
                    this.showPasswordConfirmation = this.showPassword;
                    const confirmInput = document.getElementById('password_confirmation');
                    confirmInput.type = this.showPassword ? 'text' : 'password';
                }
            } else if (field === 'password_confirmation') {
                this.showPasswordConfirmation = !this.showPasswordConfirmation;
                const input = document.getElementById('password_confirmation');
                input.type = this.showPasswordConfirmation ? 'text' : 'password';
                
                if (this.syncPasswordVisibility) {
                    this.showPassword = this.showPasswordConfirmation;
                    const passwordInput = document.getElementById('password');
                    passwordInput.type = this.showPasswordConfirmation ? 'text' : 'password';
                }
            }
        },
        
        syncPasswords() {
            this.showPasswordConfirmation = this.showPassword;
            const confirmInput = document.getElementById('password_confirmation');
            confirmInput.type = this.showPassword ? 'text' : 'password';
        },
        
        checkPasswordStrength() {
            if (!this.password) {
                this.passwordStrength.show = false;
                return;
            }
            
            if (!this.showPasswordHints) {
                this.passwordStrength.show = false;
                return;
            }
            
            let strength = 0;
            
            // Length check
            if (this.password.length >= 8) strength += 1;
            
            // Character type checks
            if (this.password.match(/[a-z]/)) strength += 1;
            if (this.password.match(/[A-Z]/)) strength += 1;
            if (this.password.match(/[0-9]/)) strength += 1;
            if (this.password.match(/[^a-zA-Z0-9]/)) strength += 1;
            
            this.passwordStrength.show = true;
            
            if (strength < 2) {
                this.passwordStrength.width = 25;
                this.passwordStrength.class = 'weak';
                this.passwordStrength.text = 'Contraseña débil';
                this.passwordStrength.textClass = 'weak';
            } else if (strength < 4) {
                this.passwordStrength.width = 50;
                this.passwordStrength.class = 'medium';
                this.passwordStrength.text = 'Contraseña media';
                this.passwordStrength.textClass = 'medium';
            } else {
                this.passwordStrength.width = 100;
                this.passwordStrength.class = 'strong';
                this.passwordStrength.text = 'Contraseña fuerte';
                this.passwordStrength.textClass = 'strong';
            }
        },
        
        checkPasswordMatch() {
            if (!this.passwordConfirmation) {
                this.passwordMatch.show = false;
                return;
            }
            
            this.passwordMatch.show = true;
            
            if (this.password === this.passwordConfirmation) {
                this.passwordMatch.class = 'match';
                this.passwordMatch.icon = 'fa-check-circle';
                this.passwordMatch.text = 'Las contraseñas coinciden';
            } else {
                this.passwordMatch.class = 'no-match';
                this.passwordMatch.icon = 'fa-times-circle';
                this.passwordMatch.text = 'Las contraseñas no coinciden';
            }
        },
        
        submitForm() {
            // Validaciones para cambio de contraseña
            if (this.password) {
                // Verificar si se proporciona la contraseña actual
                if (!this.currentPassword) {
                    this.showAlert('Contraseña actual requerida', 'Debe proporcionar su contraseña actual para cambiarla', 'warning');
                    document.getElementById('current_password').focus();
                    return false;
                }

                // Verificar confirmación de contraseña
                if (this.password !== this.passwordConfirmation) {
                    this.showAlert('Error de validación', 'Las contraseñas no coinciden', 'error');
                    document.getElementById('password_confirmation').focus();
                    return false;
                }

                // Verificar fortaleza de contraseña
                if (this.passwordStrength.width < 50) {
                    this.showAlert('Contraseña débil', 'Por favor, use una contraseña más segura', 'warning');
                    document.getElementById('password').focus();
                    return false;
                }
            }
            
            // Mostrar estado de carga
            const submitBtn = this.$refs.submitBtn;
            const originalContent = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Actualizando...';
            submitBtn.disabled = true;
            
            // Enviar formulario
            return true;
        },
        
        showAlert(title, text, icon) {
            // Usar SweetAlert2 si está disponible, sino alert nativo
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: icon,
                    title: title,
                    text: text,
                    confirmButtonText: 'Entendido'
                });
            } else {
                alert(`${title}: ${text}`);
            }
        }
    }
}
</script>
@endpush
