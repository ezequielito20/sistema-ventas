<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Recuperar contraseña') }} — {{ str_replace('_', ' ', config('app.name', 'Sistema')) }}</title>

    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    @vite(['resources/sass/app.scss'])
</head>

<body class="font-sans antialiased">
    <div class="relative flex min-h-screen items-center justify-center bg-slate-950 px-4 py-12 sm:px-6 lg:px-8">
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 h-[500px] w-[500px] rounded-full bg-gradient-to-br from-cyan-500/10 to-purple-600/10 blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 h-[400px] w-[400px] rounded-full bg-gradient-to-tr from-purple-500/8 to-cyan-400/8 blur-3xl"></div>
        </div>

        <div class="relative z-10 w-full max-w-md space-y-8">
            <div class="text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-cyan-500/30 bg-gradient-to-br from-cyan-500/20 to-purple-600/20 shadow-lg shadow-cyan-500/10 backdrop-blur-sm">
                    <i class="fas fa-key text-xl text-cyan-400"></i>
                </div>
                <h1 class="mt-4 text-xl font-bold tracking-tight text-slate-100">
                    {{ __('Recuperar contraseña') }}
                </h1>
                <p class="mt-1.5 text-sm text-slate-400">
                    {{ __('Ingresá tu correo electrónico para continuar.') }}
                </p>
            </div>

            <form method="POST" action="{{ route('password.recovery.find') }}" class="space-y-5">
                @csrf
                <div class="rounded-2xl border border-slate-700/60 bg-slate-900/70 p-6 shadow-2xl backdrop-blur-xl sm:p-8">
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
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                        class="mt-6 flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-purple-600 py-2.5 text-sm font-semibold text-white shadow-lg shadow-cyan-500/25 transition hover:brightness-110 active:scale-[0.98]">
                        <i class="fas fa-arrow-right"></i>
                        {{ __('Continuar') }}
                    </button>
                </div>
            </form>

            <p class="text-center">
                <a href="{{ route('login') }}" class="text-xs font-medium text-cyan-400 hover:text-cyan-300 transition">
                    <i class="fas fa-arrow-left mr-1"></i>{{ __('Volver al inicio de sesión') }}
                </a>
            </p>
        </div>
    </div>
</body>
</html>
