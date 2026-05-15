<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark overflow-x-hidden">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('Catálogo'))</title>
    @stack('meta')
    @vite(['resources/js/catalog-public.js'])
    @livewireStyles
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    @stack('head')
</head>
<body class="min-h-screen overflow-x-hidden bg-dv-background text-dv-on-background font-dv-body antialiased [padding-left:env(safe-area-inset-left)] [padding-right:env(safe-area-inset-right)]">

@yield('content')

{{-- Scripts inline antes del footer por si el pie usa datos preparados aquí --}}
@stack('scripts')
@livewireScripts

@isset($company)
    <footer class="relative z-10 mt-12 w-full border-t border-dv-outline-variant bg-dv-surface-container-lowest py-stack-lg @yield('footer_width', 'lg:ml-56 xl:ml-64')">
        <div class="mx-auto flex w-full max-w-dv flex-col items-center justify-between gap-6 px-margin-mobile md:flex-row md:px-margin-desktop">
            <div class="text-center md:text-start">
                <span class="font-dv-display text-dv-headline-md text-dv-primary">{{ $company->name }}</span>
                <p class="mt-stack-sm font-dv-body text-dv-body-sm text-dv-outline">
                    &copy; {{ date('Y') }} {{ $company->name }}. {{ __('Todos los derechos reservados.') }}
                </p>
            </div>
            <div class="flex flex-wrap items-center justify-center gap-6 md:gap-8">
                @if($company->phone)
                    <span class="font-dv-body text-dv-body-sm text-dv-on-surface-variant">
                        <i class="fas fa-phone mr-1.5 text-[10px] text-dv-secondary"></i>{{ $company->phone }}
                    </span>
                @endif
                @if($company->email)
                    <span class="font-dv-body text-dv-body-sm text-dv-on-surface-variant">
                        <i class="fas fa-envelope mr-1.5 text-[10px] text-dv-secondary"></i>{{ $company->email }}
                    </span>
                @endif
            </div>
        </div>
    </footer>
@endisset

</body>
</html>
