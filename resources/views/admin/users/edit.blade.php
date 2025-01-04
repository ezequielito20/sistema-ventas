@extends('adminlte::page')

@section('title', 'Editar Usuario')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-dark font-weight-bold">Editar Usuario</h1>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i>Volver al listado
        </a>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-edit mr-2"></i>
                    Información del Usuario
                </h3>
            </div>

            <form action="{{ route('admin.users.update', $user->id) }}" method="POST" id="editUserForm">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row">
                        {{-- Nombre del Usuario --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name" class="font-weight-bold required">
                                    Nombre Completo
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-user"></i>
                                        </span>
                                    </div>
                                    <input type="text" 
                                           name="name" 
                                           id="name" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name', $user->name) }}" 
                                           required>
                                    @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Email --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email" class="font-weight-bold required">
                                    Correo Electrónico
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-envelope"></i>
                                        </span>
                                    </div>
                                    <input type="email" 
                                           name="email" 
                                           id="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           value="{{ old('email', $user->email) }}" 
                                           required>
                                    @error('email')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        {{-- Contraseña actual (si se va a cambiar la contraseña) --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="current_password" class="font-weight-bold">
                                    Contraseña Actual
                                    <small class="text-muted">(requerida solo para cambiar la contraseña)</small>
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-key"></i>
                                        </span>
                                    </div>
                                    <input type="password" 
                                           name="current_password" 
                                           id="current_password" 
                                           class="form-control @error('current_password') is-invalid @enderror">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" id="toggleCurrentPassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    @error('current_password')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Nueva Contraseña (opcional) --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="password" class="font-weight-bold">
                                    Nueva Contraseña
                                    <small class="text-muted">(dejar en blanco para mantener la actual)</small>
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                    </div>
                                    <input type="password" 
                                           name="password" 
                                           id="password" 
                                           class="form-control @error('password') is-invalid @enderror">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    @error('password')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div id="passwordStrength" class="mt-2"></div>
                            </div>
                        </div>
                        {{-- Confirmar Contraseña --}}
                        <div class="col-md-4">
                           <div class="form-group">
                               <label for="password_confirmation" class="font-weight-bold">
                                   Confirmar Nueva Contraseña
                               </label>
                               <div class="input-group">
                                   <div class="input-group-prepend">
                                       <span class="input-group-text">
                                           <i class="fas fa-lock"></i>
                                       </span>
                                   </div>
                                   <input type="password" 
                                          name="password_confirmation" 
                                          id="password_confirmation" 
                                          class="form-control">
                               </div>
                           </div>
                       </div>
                    </div>


                    <div class="row">
                        {{-- Empresa --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="company_id" class="font-weight-bold required">
                                    Empresa
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-building"></i>
                                        </span>
                                    </div>
                                    <select name="company_id" 
                                            id="company_id" 
                                            class="form-control @error('company_id') is-invalid @enderror"
                                            required>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}" 
                                                    {{ old('company_id', $user->company_id) == $company->id ? 'selected' : '' }}>
                                                {{ $company->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('company_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Rol --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="role" class="font-weight-bold required">
                                    Rol del Usuario
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-user-tag"></i>
                                        </span>
                                    </div>
                                    <select name="role" 
                                            id="role" 
                                            class="form-control @error('role') is-invalid @enderror"
                                            required>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id }}" 
                                                    {{ old('role', $user->roles->first()->id ?? '') == $role->id ? 'selected' : '' }}>
                                                {{ $role->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('role')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="text-right">
                        <button type="button" 
                                onclick="window.history.back()" 
                                class="btn btn-secondary">
                            <i class="fas fa-times mr-2"></i>
                            Cancelar
                        </button>
                        <button type="submit" 
                                class="btn btn-primary" 
                                id="submitButton">
                            <i class="fas fa-save mr-2"></i>
                            Actualizar Usuario
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .required::after {
        content: " *";
        color: red;
    }
    .card {
        border-radius: 0.75rem;
    }
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid rgba(0,0,0,.125);
    }
    .input-group-text {
        background-color: #f8f9fa;
    }
    .btn {
        border-radius: 0.5rem;
    }
    .form-control {
        border-radius: 0.5rem;
    }
    .input-group > .form-control {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }
    .password-strength-meter {
        height: 0.25rem;
        background-color: #e9ecef;
        border-radius: 0.25rem;
        margin-top: 0.5rem;
    }
    .strength-weak { background-color: #dc3545; }
    .strength-medium { background-color: #ffc107; }
    .strength-strong { background-color: #28a745; }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    let formChanged = false;

    // Toggle password visibility
    $('#togglePassword').click(function() {
        const passwordInput = $('#password');
        const icon = $(this).find('i');
        
        if (passwordInput.attr('type') === 'password') {
            passwordInput.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordInput.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Password strength meter
    function checkPasswordStrength(password) {
        if (!password) return 0;
        let strength = 0;
        if (password.length >= 8) strength += 1;
        if (password.match(/[a-z]/)) strength += 1;
        if (password.match(/[A-Z]/)) strength += 1;
        if (password.match(/[0-9]/)) strength += 1;
        if (password.match(/[^a-zA-Z0-9]/)) strength += 1;
        return strength;
    }

    $('#password').on('input', function() {
        const password = $(this).val();
        if (!password) {
            $('#passwordStrength').html('');
            return;
        }

        const strength = checkPasswordStrength(password);
        let strengthHtml = '<div class="password-strength-meter ';
        
        if (strength < 2) {
            strengthHtml += 'strength-weak" style="width: 33%"></div>';
            $('#passwordStrength').html(strengthHtml + '<small class="text-danger">Contraseña débil</small>');
        } else if (strength < 4) {
            strengthHtml += 'strength-medium" style="width: 66%"></div>';
            $('#passwordStrength').html(strengthHtml + '<small class="text-warning">Contraseña media</small>');
        } else {
            strengthHtml += 'strength-strong" style="width: 100%"></div>';
            $('#passwordStrength').html(strengthHtml + '<small class="text-success">Contraseña fuerte</small>');
        }
    });

    // Form validation
    $('#editUserForm').on('submit', function(e) {
        const password = $('#password').val();
        const confirmPassword = $('#password_confirmation').val();

        if (password) {
            if (password !== confirmPassword) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Error de validación',
                    text: 'Las contraseñas no coinciden'
                });
                return false;
            }

            if (checkPasswordStrength(password) < 2) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Contraseña débil',
                    text: 'Por favor, use una contraseña más segura'
                });
                return false;
            }
        }

        // Show loading state
        const submitButton = $('#submitButton');
        const originalContent = submitButton.html();
        submitButton.html('<i class="fas fa-spinner fa-spin mr-2"></i>Actualizando...').prop('disabled', true);
    });

    // Track form changes
    $('form :input').on('change input', function() {
        formChanged = true;
    });

    // Warn about unsaved changes
    window.onbeforeunload = function() {
        if (formChanged) {
            return "¿Estás seguro de que quieres salir? Los cambios no guardados se perderán.";
        }
    };

    // Don't warn if submitting the form
    $('form').on('submit', function() {
        window.onbeforeunload = null;
    });

    // Select2 initialization (if you're using it)
    if ($.fn.select2) {
        $('#company_id, #role').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
    }
});
</script>
@stop
