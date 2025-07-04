@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@section('adminlte_css_pre')
    <link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
@stop

@section('adminlte_js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Evitar que el navegador complete automáticamente las contraseñas guardadas
    const passwordField = document.querySelector('input[name="password"]');
    const emailField = document.querySelector('input[name="email"]');
    
    // Limpiar el campo de contraseña al cargar la página
    if (passwordField) {
        passwordField.value = '';
        
        // Evitar el autocompletado después de un breve delay
        setTimeout(function() {
            passwordField.value = '';
        }, 100);
        
        // Limpiar cuando el campo obtiene el foco
        passwordField.addEventListener('focus', function() {
            this.value = '';
        });
    }
    
    // Prevenir el pegado de contraseñas en texto plano
    if (passwordField) {
        passwordField.addEventListener('paste', function(e) {
            // Permitir el pegado pero asegurar que se mantenga oculto
            setTimeout(function() {
                passwordField.type = 'password';
            }, 1);
        });
    }
    
    // Evitar la inspección del campo de contraseña
    if (passwordField) {
        passwordField.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            return false;
        });
    }
});
</script>
@stop

@php( $login_url = View::getSection('login_url') ?? config('adminlte.login_url', 'login') )
@php( $register_url = View::getSection('register_url') ?? config('adminlte.register_url', 'register') )
@php( $password_reset_url = View::getSection('password_reset_url') ?? config('adminlte.password_reset_url', 'password/reset') )

@if (config('adminlte.use_route_url', false))
    @php( $login_url = $login_url ? route($login_url) : '' )
    @php( $register_url = $register_url ? route($register_url) : '' )
    @php( $password_reset_url = $password_reset_url ? route($password_reset_url) : '' )
@else
    @php( $login_url = $login_url ? url($login_url) : '' )
    @php( $register_url = $register_url ? url($register_url) : '' )
    @php( $password_reset_url = $password_reset_url ? url($password_reset_url) : '' )
@endif

@section('auth_header', 'Iniciar sesión')

@section('auth_body')
    <form action="{{ $login_url }}" method="post" autocomplete="on" novalidate>
        @csrf

        {{-- Email field --}}
        <div class="input-group mb-3">
            <input type="email" 
                   name="email" 
                   class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email') }}" 
                   placeholder="Correo electrónico" 
                   autocomplete="username"
                   autocapitalize="none"
                   spellcheck="false"
                   autofocus>

            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-envelope {{ config('adminlte.classes_auth_icon', '') }}"></span>
                </div>
            </div>

            @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>Correo electrónico incorrecto</strong>
                </span>
            @enderror
        </div>

        {{-- Password field --}}
        <div class="input-group mb-3">
            <input type="password" 
                   name="password" 
                   class="form-control @error('password') is-invalid @enderror"
                   placeholder="Contraseña"
                   autocomplete="current-password"
                   autocapitalize="none"
                   spellcheck="false"
                   data-lpignore="true">

            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-lock {{ config('adminlte.classes_auth_icon', '') }}"></span>
                </div>
            </div>

            @error('password')
                <span class="invalid-feedback" role="alert">
                    <strong>Contraseña incorrecta</strong>
                </span>
            @enderror
        </div>

        {{-- Login field --}}
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="icheck-primary" title="Recordarme">
                        <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label for="remember">
                            Recordarme
                        </label>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary shadow-sm">
                            <span class="fas fa-sign-in-alt mr-2"></span>
                            Iniciar sesión
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </form>
@stop

@section('auth_footer')
    {{-- Password reset link --}}
    @if($password_reset_url)
        <p class="my-0">
            <a href="{{ $password_reset_url }}">
                Recuperar contraseña
            </a>
        </p>
    @endif

    {{-- Register link --}}
    @if($register_url)
        <p class="my-0">
            <a href="{{ route('admin.company.create') }}">
                Crear una Empresa
            </a>
        </p>
    @endif
@stop
