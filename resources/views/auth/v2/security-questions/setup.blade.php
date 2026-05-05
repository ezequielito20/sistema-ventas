<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Preguntas de seguridad') }} — {{ str_replace('_', ' ', config('app.name', 'Sistema')) }}</title>

    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    @vite(['resources/sass/app.scss'])
</head>

<body class="font-sans antialiased">
    <div class="relative flex min-h-screen items-center justify-center bg-slate-950 px-4 py-12 sm:px-6 lg:px-8">
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 h-[500px] w-[500px] rounded-full bg-gradient-to-br from-cyan-500/10 to-purple-600/10 blur-3xl"></div>
        </div>

        <div class="relative z-10 w-full max-w-lg space-y-8">
            <div class="text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-amber-500/30 bg-gradient-to-br from-amber-500/20 to-orange-600/20 shadow-lg shadow-amber-500/10 backdrop-blur-sm">
                    <i class="fas fa-shield-alt text-xl text-amber-400"></i>
                </div>
                <h1 class="mt-4 text-xl font-bold tracking-tight text-slate-100">
                    {{ __('Configura tus preguntas de seguridad') }}
                </h1>
                <p class="mt-1.5 text-sm text-slate-400">
                    {{ __('Son obligatorias para recuperar tu contraseña en el futuro. Elige preguntas que solo tú puedas responder.') }}
                </p>
            </div>

            <form method="POST" action="{{ route('security-questions.store') }}" class="space-y-5">
                @csrf

                <div class="rounded-2xl border border-slate-700/60 bg-slate-900/70 p-6 shadow-2xl backdrop-blur-xl sm:p-8 space-y-6">
                    @for ($i = 0; $i < 3; $i++)
                        <div class="space-y-3 rounded-xl border border-slate-700/40 bg-slate-800/30 p-4">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-amber-500/20 text-xs font-bold text-amber-400">
                                    {{ $i + 1 }}
                                </span>
                                <span class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    {{ __('Pregunta') }} {{ $i + 1 }}
                                </span>
                            </div>

                            <div>
                                <input name="questions[]" type="text" required
                                    class="block w-full rounded-xl border border-slate-600 bg-slate-950/60 py-2.5 px-3.5 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500 transition"
                                    placeholder="{{ __('Ej: ¿Cuál es el nombre de mi primera mascota?') }}">
                                @error("questions.{$i}")
                                    <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <input name="answers[]" type="text" required
                                    class="block w-full rounded-xl border border-slate-600 bg-slate-950/60 py-2.5 px-3.5 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500 transition"
                                    placeholder="{{ __('Tu respuesta...') }}">
                                @error("answers.{$i}")
                                    <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    @endfor

                    <button type="submit"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-indigo-600 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-500/25 transition hover:brightness-110 active:scale-[0.98]">
                        <i class="fas fa-save"></i>
                        {{ __('Guardar preguntas') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
