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
                                        <button class="btn btn-outline-secondary password-toggle-btn" type="button" id="toggleCurrentPassword" title="Mostrar/Ocultar contraseña actual">
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
                                        <button class="btn btn-outline-secondary password-toggle-btn" type="button" id="togglePassword" title="Mostrar/Ocultar nueva contraseña">
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
                                   <div class="input-group-append">
                                       <button class="btn btn-outline-secondary password-toggle-btn" type="button" id="togglePasswordConfirmation" title="Mostrar/Ocultar confirmación">
                                           <i class="fas fa-eye"></i>
                                       </button>
                                   </div>
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

                    {{-- Opciones adicionales --}}
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" 
                                       class="custom-control-input" 
                                       id="syncPasswordVisibility">
                                <label class="custom-control-label" for="syncPasswordVisibility">
                                    <i class="fas fa-sync mr-1"></i>
                                    Sincronizar visibilidad de contraseñas
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" 
                                       class="custom-control-input" 
                                       id="showPasswordHints">
                                <label class="custom-control-label" for="showPasswordHints">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Mostrar indicadores de fortaleza
                                </label>
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
    
    /* Password toggle button styling */
    .password-toggle-btn {
        border: none;
        background: transparent;
        color: #6c757d;
        transition: all 0.3s ease;
        border-radius: 0 0.5rem 0.5rem 0;
    }
    
    .password-toggle-btn:hover {
        background-color: #f8f9fa;
        color: #495057;
        transform: scale(1.05);
    }
    
    .password-toggle-btn:focus {
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        outline: none;
    }
    
    .password-toggle-btn i {
        transition: all 0.3s ease;
    }
    
    .input-group-append .btn {
        border-left: none;
    }
    
    /* Enhanced form styling */
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .custom-control-label {
        font-size: 0.9rem;
        color: #495057;
    }
    
    .custom-control-label:hover {
        color: #007bff;
    }
    
    /* Form loading state */
    .form-loading {
        opacity: 0.7;
        pointer-events: none;
        transition: opacity 0.3s ease;
    }
    
    .form-loading .card-body {
        position: relative;
    }
    
    .form-loading .card-body::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        z-index: 1;
    }
    
    /* Enhanced SweetAlert2 styling */
    .swal2-popup {
        border-radius: 15px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
    }
    
    .swal2-title {
        font-size: 1.5rem;
        font-weight: 600;
    }
    
    .swal2-content {
        font-size: 1rem;
        line-height: 1.5;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    let formChanged = false;

    // Toggle password visibility with smooth animation
    function togglePasswordVisibility(inputId, buttonId) {
        const passwordInput = $(inputId);
        const button = $(buttonId);
        const icon = button.find('i');
        
        // Add animation effect
        icon.addClass('fa-spin');
        
        setTimeout(() => {
            if (passwordInput.attr('type') === 'password') {
                passwordInput.attr('type', 'text');
                icon.removeClass('fa-eye fa-spin').addClass('fa-eye-slash');
                button.attr('title', 'Ocultar contraseña');
                button.removeClass('btn-outline-secondary').addClass('btn-outline-primary');
            } else {
                passwordInput.attr('type', 'password');
                icon.removeClass('fa-eye-slash fa-spin').addClass('fa-eye');
                button.attr('title', 'Mostrar contraseña');
                button.removeClass('btn-outline-primary').addClass('btn-outline-secondary');
            }
        }, 150);
    }

    // Toggle current password visibility
    $('#toggleCurrentPassword').click(function() {
        togglePasswordVisibility('#current_password', '#toggleCurrentPassword');
    });

    // Toggle new password visibility
    $('#togglePassword').click(function() {
        togglePasswordVisibility('#password', '#togglePassword');
        
        // Sync with confirmation if enabled
        if ($('#syncPasswordVisibility').is(':checked')) {
            setTimeout(() => {
                const passwordType = $('#password').attr('type');
                $('#password_confirmation').attr('type', passwordType);
                
                const confirmIcon = $('#togglePasswordConfirmation i');
                const confirmButton = $('#togglePasswordConfirmation');
                
                if (passwordType === 'text') {
                    confirmIcon.removeClass('fa-eye').addClass('fa-eye-slash');
                    confirmButton.removeClass('btn-outline-secondary').addClass('btn-outline-primary');
                    confirmButton.attr('title', 'Ocultar confirmación');
                } else {
                    confirmIcon.removeClass('fa-eye-slash').addClass('fa-eye');
                    confirmButton.removeClass('btn-outline-primary').addClass('btn-outline-secondary');
                    confirmButton.attr('title', 'Mostrar confirmación');
                }
            }, 150);
        }
    });

    // Toggle password confirmation visibility
    $('#togglePasswordConfirmation').click(function() {
        togglePasswordVisibility('#password_confirmation', '#togglePasswordConfirmation');
        
        // Sync with main password if enabled
        if ($('#syncPasswordVisibility').is(':checked')) {
            setTimeout(() => {
                const confirmType = $('#password_confirmation').attr('type');
                $('#password').attr('type', confirmType);
                
                const passwordIcon = $('#togglePassword i');
                const passwordButton = $('#togglePassword');
                
                if (confirmType === 'text') {
                    passwordIcon.removeClass('fa-eye').addClass('fa-eye-slash');
                    passwordButton.removeClass('btn-outline-secondary').addClass('btn-outline-primary');
                    passwordButton.attr('title', 'Ocultar nueva contraseña');
                } else {
                    passwordIcon.removeClass('fa-eye-slash').addClass('fa-eye');
                    passwordButton.removeClass('btn-outline-primary').addClass('btn-outline-secondary');
                    passwordButton.attr('title', 'Mostrar nueva contraseña');
                }
            }, 150);
        }
    });

    // Synchronize password visibility
    $('#syncPasswordVisibility').change(function() {
        if ($(this).is(':checked')) {
            // Show feedback message
            const label = $('label[for="syncPasswordVisibility"]');
            const originalText = label.html();
            label.html('<i class="fas fa-check text-success mr-1"></i>Sincronización activada');
            
            // Sync current state
            const passwordType = $('#password').attr('type');
            $('#password_confirmation').attr('type', passwordType);
            
            const confirmIcon = $('#togglePasswordConfirmation i');
            const confirmButton = $('#togglePasswordConfirmation');
            
            if (passwordType === 'text') {
                confirmIcon.removeClass('fa-eye').addClass('fa-eye-slash');
                confirmButton.removeClass('btn-outline-secondary').addClass('btn-outline-primary');
                confirmButton.attr('title', 'Ocultar confirmación');
            } else {
                confirmIcon.removeClass('fa-eye-slash').addClass('fa-eye');
                confirmButton.removeClass('btn-outline-primary').addClass('btn-outline-secondary');
                confirmButton.attr('title', 'Mostrar confirmación');
            }
            
            // Reset label after 2 seconds
            setTimeout(() => {
                label.html(originalText);
            }, 2000);
        } else {
            // Show deactivation feedback
            const label = $('label[for="syncPasswordVisibility"]');
            const originalText = label.html();
            label.html('<i class="fas fa-times text-warning mr-1"></i>Sincronización desactivada');
            
            setTimeout(() => {
                label.html(originalText);
            }, 2000);
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

        // Only show strength meter if hints are enabled
        if (!$('#showPasswordHints').is(':checked')) {
            $('#passwordStrength').html('');
            return;
        }

        const strength = checkPasswordStrength(password);
        let strengthHtml = '<div class="password-strength-meter ';
        let strengthText = '';
        let strengthIcon = '';
        
        if (strength < 2) {
            strengthHtml += 'strength-weak" style="width: 33%"></div>';
            strengthText = '<small class="text-danger"><i class="fas fa-exclamation-triangle mr-1"></i>Contraseña débil</small>';
            strengthIcon = '<i class="fas fa-shield-alt text-danger"></i>';
        } else if (strength < 4) {
            strengthHtml += 'strength-medium" style="width: 66%"></div>';
            strengthText = '<small class="text-warning"><i class="fas fa-info-circle mr-1"></i>Contraseña media</small>';
            strengthIcon = '<i class="fas fa-shield-alt text-warning"></i>';
        } else {
            strengthHtml += 'strength-strong" style="width: 100%"></div>';
            strengthText = '<small class="text-success"><i class="fas fa-check-circle mr-1"></i>Contraseña fuerte</small>';
            strengthIcon = '<i class="fas fa-shield-alt text-success"></i>';
        }
        
        $('#passwordStrength').html(strengthHtml + strengthText);
        
        // Update the hints toggle icon
        const hintsLabel = $('label[for="showPasswordHints"]');
        const originalIcon = hintsLabel.find('i').first();
        if (originalIcon.hasClass('fa-info-circle')) {
            originalIcon.removeClass('fa-info-circle').addClass('fa-shield-alt');
        }
    });

    // Toggle password hints visibility
    $('#showPasswordHints').change(function() {
        const label = $('label[for="showPasswordHints"]');
        const originalText = label.html();
        
        if ($(this).is(':checked')) {
            label.html('<i class="fas fa-check text-success mr-1"></i>Indicadores activados');
            
            // Trigger password strength check if there's a password
            if ($('#password').val()) {
                $('#password').trigger('input');
            }
        } else {
            label.html('<i class="fas fa-times text-muted mr-1"></i>Indicadores desactivados');
            $('#passwordStrength').html('');
        }
        
        // Reset label after 2 seconds
        setTimeout(() => {
            label.html(originalText);
        }, 2000);
    });

    // Enhanced form validation
    $('#editUserForm').on('submit', function(e) {
        const password = $('#password').val();
        const confirmPassword = $('#password_confirmation').val();
        const currentPassword = $('#current_password').val();

        // Validate password change requirements
        if (password) {
            // Check if current password is provided when changing password
            if (!currentPassword) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Contraseña actual requerida',
                    text: 'Debe proporcionar su contraseña actual para cambiarla',
                    confirmButtonText: 'Entendido',
                    customClass: {
                        confirmButton: 'btn btn-warning'
                    },
                    buttonsStyling: false
                });
                $('#current_password').focus();
                return false;
            }

            // Check password confirmation
            if (password !== confirmPassword) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Error de validación',
                    text: 'Las contraseñas no coinciden',
                    confirmButtonText: 'Entendido',
                    customClass: {
                        confirmButton: 'btn btn-danger'
                    },
                    buttonsStyling: false
                });
                $('#password_confirmation').focus();
                return false;
            }

            // Check password strength
            if (checkPasswordStrength(password) < 2) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Contraseña débil',
                    html: `
                        <p>Por favor, use una contraseña más segura que incluya:</p>
                        <ul class="text-left">
                            <li>Al menos 8 caracteres</li>
                            <li>Letras mayúsculas y minúsculas</li>
                            <li>Números</li>
                            <li>Caracteres especiales</li>
                        </ul>
                    `,
                    confirmButtonText: 'Entendido',
                    customClass: {
                        confirmButton: 'btn btn-warning'
                    },
                    buttonsStyling: false
                });
                $('#password').focus();
                return false;
            }
        }

        // Show loading state with enhanced animation
        const submitButton = $('#submitButton');
        const originalContent = submitButton.html();
        submitButton.html('<i class="fas fa-spinner fa-spin mr-2"></i>Actualizando usuario...').prop('disabled', true);

        // Add a subtle loading effect to the form
        $(this).addClass('form-loading');
        
        // Re-enable button after 10 seconds as fallback
        setTimeout(() => {
            if (submitButton.prop('disabled')) {
                submitButton.html(originalContent).prop('disabled', false);
                $(this).removeClass('form-loading');
            }
        }, 10000);
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
