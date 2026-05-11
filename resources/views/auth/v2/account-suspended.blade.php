<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Servicio Suspendido — {{ str_replace('_', ' ', config('app.name', 'Sistema')) }}</title>

    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    @vite(['resources/sass/app.scss'])
</head>

<body class="font-sans antialiased">
    <div class="relative flex min-h-screen items-center justify-center bg-slate-950 px-4 py-12 sm:px-6 lg:px-8">
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 h-[500px] w-[500px] rounded-full bg-gradient-to-br from-rose-500/10 to-red-600/10 blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 h-[500px] w-[500px] rounded-full bg-gradient-to-br from-red-500/10 to-rose-500/10 blur-3xl"></div>
        </div>

        <div class="relative w-full max-w-md text-center">
            <div class="mb-8">
                <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-rose-500/20">
                    <i class="fas fa-exclamation-triangle text-3xl text-rose-400"></i>
                </div>
                <h1 class="text-2xl font-bold text-slate-100">Servicio Suspendido</h1>
                <p class="mt-3 text-slate-400 leading-relaxed">
                    Tu acceso al sistema ha sido suspendido por falta de pago.<br>
                    Por favor, contactá al administrador del sistema para regularizar tu situación.
                </p>
            </div>

            <div class="ui-panel">
                <div class="ui-panel__body">
                    <p class="text-sm text-slate-400 mb-4">
                        Si creés que esto es un error o ya realizaste el pago, comunicate con el equipo de soporte.
                    </p>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="ui-btn ui-btn-ghost w-full">
                            <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
