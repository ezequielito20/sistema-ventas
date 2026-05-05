<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Nueva contraseña') }} — {{ str_replace('_', ' ', config('app.name', 'Sistema')) }}</title>

    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    @vite(['resources/sass/app.scss'])
</head>

<body class="font-sans antialiased">
    <div class="relative flex min-h-screen items-center justify-center bg-slate-950 px-4 py-12 sm:px-6 lg:px-8">
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 h-[500px] w-[500px] rounded-full bg-gradient-to-br from-emerald-500/10 to-teal-600/10 blur-3xl"></div>
        </div>

        <div class="relative z-10 w-full max-w-md space-y-8">
            <div class="text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-emerald-500/30 bg-gradient-to-br from-emerald-500/20 to-teal-600/20 shadow-lg shadow-emerald-500/10 backdrop-blur-sm">
                    <i class="fas fa-lock-open text-xl text-emerald-400"></i>
                </div>
                <h1 class="mt-4 text-xl font-bold tracking-tight text-slate-100">
                    {{ __('Nueva contraseña') }}
                </h1>
                <p class="mt-1.5 text-sm text-slate-400">
                    {{ __('Elige una contraseña segura para tu cuenta.') }}
                </p>
            </div>

            <form method="POST" action="{{ route('password.recovery.update') }}" class="space-y-5">
                @csrf
                <div class="rounded-2xl border border-slate-700/60 bg-slate-900/70 p-6 shadow-2xl backdrop-blur-xl sm:p-8">
                    <div class="space-y-1.5">
                        <label for="password" class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            {{ __('Nueva contraseña') }}
                        </label>
                        <div class="relative" x-data="{ show: false }">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex w-10 items-center justify-center text-slate-500">
                                <i class="fas fa-lock text-sm"></i>
                            </span>
                            <input id="password" name="password" :type="show ? 'text' : 'password'" required
                                class="block w-full rounded-xl border border-slate-600 bg-slate-950/60 py-2.5 pl-10 pr-10 text-sm text-slate-100 placeholder:text-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 transition"
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

                    <div class="mt-4 space-y-1.5">
                        <label for="password_confirmation" class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            {{ __('Confirmar contraseña') }}
                        </label>
                        <div class="relative" x-data="{ show: false }">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex w-10 items-center justify-center text-slate-500">
                                <i class="fas fa-lock text-sm"></i>
                            </span>
                            <input id="password_confirmation" name="password_confirmation" :type="show ? 'text' : 'password'" required
                                class="block w-full rounded-xl border border-slate-600 bg-slate-950/60 py-2.5 pl-10 pr-10 text-sm text-slate-100 placeholder:text-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 transition"
                                placeholder="••••••••">
                            <button type="button" @click="show = !show"
                                class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-slate-500 hover:text-slate-300 transition">
                                <i class="fas text-sm" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit"
                        class="mt-6 flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-500/25 transition hover:brightness-110 active:scale-[0.98]">
                        <i class="fas fa-save"></i>
                        {{ __('Guardar nueva contraseña') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
