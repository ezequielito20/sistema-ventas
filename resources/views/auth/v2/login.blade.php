<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Iniciar sesión') }} — {{ str_replace('_', ' ', config('app.name', 'Sistema')) }}</title>

    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/sweetalert2/sweetalert2.min.css') }}">
    @vite(['resources/sass/app.scss'])
</head>

<body class="font-sans antialiased">

    <div class="relative flex min-h-screen items-center justify-center bg-slate-950 px-4 py-12 sm:px-6 lg:px-8">
        {{-- Fondo decorativo --}}
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 h-[500px] w-[500px] rounded-full bg-gradient-to-br from-cyan-500/10 to-purple-600/10 blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 h-[400px] w-[400px] rounded-full bg-gradient-to-tr from-purple-500/8 to-cyan-400/8 blur-3xl"></div>
        </div>

        <div class="relative z-10 w-full max-w-md space-y-8">
            {{-- Logo / Icono --}}
            <div class="text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-cyan-500/30 bg-gradient-to-br from-cyan-500/20 to-purple-600/20 shadow-lg shadow-cyan-500/10 backdrop-blur-sm">
                    <i class="fas fa-rocket text-2xl text-cyan-400"></i>
                </div>
                <h1 class="mt-5 text-2xl font-bold tracking-tight text-slate-100">
                    {{ str_replace('_', ' ', config('app.name', 'Sistema')) }}
                </h1>
                <p class="mt-1.5 text-sm text-slate-400">
                    {{ __('Ingresá tus credenciales para acceder') }}
                </p>
            </div>

            {{-- Formulario --}}
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div class="rounded-2xl border border-slate-700/60 bg-slate-900/70 p-6 shadow-2xl backdrop-blur-xl sm:p-8">
                    {{-- Email --}}
                    <div class="space-y-1.5">
                        <label for="email" class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            {{ __('Correo electrónico') }}
                        </label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex w-10 items-center justify-center text-slate-500">
                                <i class="fas fa-envelope text-sm"></i>
                            </span>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                                class="block w-full rounded-xl border border-slate-600 bg-slate-950/60 py-2.5 pl-10 pr-3.5 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500 transition"
                                placeholder="tu@correo.com">
                        </div>
                        @error('email')
                            <p class="text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="mt-4 space-y-1.5">
                        <label for="password" class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            {{ __('Contraseña') }}
                        </label>
                        <div class="relative" x-data="{ show: false }">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex w-10 items-center justify-center text-slate-500">
                                <i class="fas fa-lock text-sm"></i>
                            </span>
                            <input id="password" name="password" :type="show ? 'text' : 'password'" required
                                class="block w-full rounded-xl border border-slate-600 bg-slate-950/60 py-2.5 pl-10 pr-10 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500 transition"
                                placeholder="••••••••">
                            <button type="button" @click="show = !show"
                                class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-slate-500 hover:text-slate-300 transition">
                                <i class="fas text-sm" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Recordarme + Olvidé contraseña --}}
                    <div class="mt-8 flex items-center justify-between">
                        <label for="remember" class="flex items-center gap-2 cursor-pointer">
                            <input id="remember" name="remember" type="checkbox"
                                class="h-4 w-4 rounded border-slate-600 bg-slate-800 text-cyan-500 focus:ring-cyan-500 focus:ring-offset-0">
                            <span class="text-xs text-slate-400 select-none">{{ __('Recordarme') }}</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                                class="text-xs font-medium text-cyan-400 hover:text-cyan-300 transition">
                                {{ __('¿Olvidaste tu contraseña?') }}
                            </a>
                        @endif
                    </div>

                    {{-- Botón --}}
                    <button type="submit"
                        class="mt-6 flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-purple-600 py-2.5 text-sm font-semibold text-white shadow-lg shadow-cyan-500/25 transition hover:brightness-110 active:scale-[0.98]">
                        <i class="fas fa-sign-in-alt"></i>
                        {{ __('Iniciar sesión') }}
                    </button>
                </div>
            </form>

            {{-- Footer --}}
            <p class="text-center text-xs text-slate-600">
                &copy; {{ date('Y') }} {{ str_replace('_', ' ', config('app.name', 'Sistema')) }}. {{ __('Todos los derechos reservados.') }}
            </p>
        </div>
    </div>

    {{-- SweetAlert2 --}}
    <script src="{{ asset('vendor/sweetalert2/sweetalert2.min.js') }}"></script>

    {{-- Alpine.js para toggle de password --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @if (session('message'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const msg = @js(session('message'));
            const icon = '{{ session('icons', 'info') }}';
            const titles = {
                success: '{{ __('¡Listo!') }}',
                error: '{{ __('Error') }}',
                info: '{{ __('Información') }}'
            };

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: titles[icon] || titles.info,
                    text: msg,
                    icon: icon,
                    confirmButtonText: '{{ __('Entendido') }}',
                    confirmButtonColor: '#06b6d4',
                    timer: icon === 'success' ? 3000 : undefined,
                    timerProgressBar: icon === 'success',
                    background: '#1e293b',
                    color: '#e2e8f0'
                });
            } else {
                alert(msg);
            }
        });
    </script>
    @endif

    @if ($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Swal === 'undefined') return;
            const errorMsg = @js($errors->first());
            if (errorMsg) {
                Swal.fire({
                    title: '{{ __('Error') }}',
                    text: errorMsg,
                    icon: 'error',
                    confirmButtonText: '{{ __('Entendido') }}',
                    confirmButtonColor: '#06b6d4',
                    background: '#1e293b',
                    color: '#e2e8f0'
                });
            }
        });
    </script>
    @endif
</body>
</html>
