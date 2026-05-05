<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Verificar identidad') }} — {{ str_replace('_', ' ', config('app.name', 'Sistema')) }}</title>

    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    @vite(['resources/sass/app.scss'])
</head>

<body class="font-sans antialiased">
    <div class="relative flex min-h-screen items-center justify-center bg-slate-950 px-4 py-12 sm:px-6 lg:px-8">
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 h-[500px] w-[500px] rounded-full bg-gradient-to-br from-amber-500/10 to-orange-600/10 blur-3xl"></div>
        </div>

        <div class="relative z-10 w-full max-w-lg space-y-8">
            <div class="text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-amber-500/30 bg-gradient-to-br from-amber-500/20 to-orange-600/20 shadow-lg shadow-amber-500/10 backdrop-blur-sm">
                    <i class="fas fa-shield-alt text-xl text-amber-400"></i>
                </div>
                <h1 class="mt-4 text-xl font-bold tracking-tight text-slate-100">
                    {{ __('Verifica tu identidad') }}
                </h1>
                <p class="mt-1.5 text-sm text-slate-400">
                    {{ __('Responde las siguientes preguntas de seguridad.') }}
                </p>
            </div>

            <form method="POST" action="{{ route('password.recovery.verify') }}" class="space-y-5">
                @csrf
                <div class="rounded-2xl border border-slate-700/60 bg-slate-900/70 p-6 shadow-2xl backdrop-blur-xl sm:p-8 space-y-5">
                    @foreach ($questions as $i => $q)
                        <div class="space-y-2">
                            <label class="flex items-start gap-2 text-sm font-medium text-slate-200">
                                <span class="mt-0.5 inline-flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-amber-500/20 text-[10px] font-bold text-amber-400">
                                    {{ $i + 1 }}
                                </span>
                                {{ $q['question'] }}
                            </label>
                            <input name="answers[{{ $q['id'] }}]" type="text" required
                                class="block w-full rounded-xl border border-slate-600 bg-slate-950/60 py-2.5 px-3.5 text-sm text-slate-100 placeholder:text-slate-500 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 transition"
                                placeholder="{{ __('Tu respuesta...') }}">
                        </div>
                    @endforeach

                    @error('answers')
                        <p class="text-xs text-rose-400">{{ $message }}</p>
                    @enderror

                    <button type="submit"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 py-2.5 text-sm font-semibold text-white shadow-lg shadow-amber-500/25 transition hover:brightness-110 active:scale-[0.98]">
                        <i class="fas fa-check-circle"></i>
                        {{ __('Verificar respuestas') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
