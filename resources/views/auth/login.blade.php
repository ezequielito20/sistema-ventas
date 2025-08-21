<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Iniciar Sesión - Sistema de Ventas</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="{{ asset('vendor/sweetalert2/sweetalert2.min.css') }}">
    
    <!-- Tailwind CSS -->
    @vite(['resources/sass/app.scss'])
    
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
    </style>
</head>
<body>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <div class="mx-auto h-16 w-16 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-sm border border-white/20">
                    <i class="fas fa-user-lock text-2xl text-white"></i>
                </div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-white">
                    Iniciar Sesión
                </h2>
                <p class="mt-2 text-center text-sm text-white text-opacity-90">
                    Accede a tu cuenta del sistema
                </p>
            </div>
            
            <form class="mt-8 space-y-6" method="POST" action="{{ route('login') }}">
                @csrf
                
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-8 border border-white/20">
                    <div class="space-y-6">
                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-white mb-2">
                                <i class="fas fa-envelope mr-2"></i>Correo Electrónico
                            </label>
                            <input id="email" name="email" type="email" required 
                                   class="appearance-none relative block w-full px-3 py-3 border border-white/30 placeholder-white/60 text-white bg-white/10 rounded-xl focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent transition-all duration-300"
                                   placeholder="tu@email.com"
                                   value="{{ old('email') }}">
                            @error('email')
                                <p class="mt-1 text-sm text-red-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-white mb-2">
                                <i class="fas fa-lock mr-2"></i>Contraseña
                            </label>
                            <input id="password" name="password" type="password" required 
                                   class="appearance-none relative block w-full px-3 py-3 border border-white/30 placeholder-white/60 text-white bg-white/10 rounded-xl focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent transition-all duration-300"
                                   placeholder="Tu contraseña">
                            @error('password')
                                <p class="mt-1 text-sm text-red-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Remember Me -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <input id="remember" name="remember" type="checkbox" 
                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label for="remember" class="ml-2 block text-sm text-white">
                                    Recordarme
                                </label>
                            </div>

                            @if (Route::has('password.request'))
                                <div class="text-sm">
                                    <a href="{{ route('password.request') }}" class="font-medium text-white hover:text-white/80 transition-colors duration-300">
                                        ¿Olvidaste tu contraseña?
                                    </a>
                                </div>
                            @endif
                        </div>

                        <!-- Submit Button -->
                        <div>
                            <button type="submit" 
                                    class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-xl text-indigo-600 bg-white hover:bg-white/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white transition-all duration-300 transform hover:scale-105">
                                <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                    <i class="fas fa-sign-in-alt text-indigo-600 group-hover:text-indigo-700"></i>
                                </span>
                                Iniciar Sesión
                            </button>
                        </div>

                        <!-- Register Link -->
                        @if (Route::has('register'))
                            <div class="text-center">
                                <p class="text-sm text-white">
                                    ¿No tienes una cuenta? 
                                    <a href="{{ route('register') }}" class="font-medium text-white hover:text-white/80 transition-colors duration-300">
                                        Regístrate aquí
                                    </a>
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- SweetAlert2 -->
    <script src="{{ asset('vendor/sweetalert2/sweetalert2.min.js') }}"></script>
    
    <!-- Manejo de errores de sesión -->
    @if(session('message'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const message = '{{ session('message') }}';
                const icon = '{{ session('icons', 'info') }}';
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: icon === 'success' ? '¡Éxito!' : icon === 'error' ? 'Error' : 'Información',
                        text: message,
                        icon: icon,
                        confirmButtonText: 'Entendido',
                        timer: icon === 'success' ? 3000 : undefined,
                        timerProgressBar: icon === 'success'
                    });
                } else {
                    alert(message);
                }
            });
        </script>
    @endif
</body>
</html>