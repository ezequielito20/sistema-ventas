<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $company->name . ' - Catálogo')</title>

    {{-- Tailwind + Alpine via Vite (NO CDN) --}}
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    {{-- Font Awesome local (NO CDN) --}}
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">

    <style>
        body {
            background-color: #15121b;
            color: #e7e0ed;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        .glass-card {
            background: rgba(33, 30, 39, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 0.5px solid rgba(255, 255, 255, 0.05);
        }
        .glow-hover:hover {
            box-shadow: 0 0 40px 0 rgba(208, 188, 255, 0.15);
            transform: translateY(-2px);
            border-color: rgba(208, 188, 255, 0.25);
        }
        .category-scroll { scrollbar-width: none; -ms-overflow-style: none; }
        .category-scroll::-webkit-scrollbar { height: 0px; }
        [x-cloak] { display: none !important; }
    </style>

    @stack('head')
</head>
<body class="bg-dv-surface text-dv-on-surface min-h-screen flex flex-col antialiased">

    {{-- Top Navigation Bar — Glassmorphism --}}
    <nav class="sticky top-0 z-50 bg-dv-surface/70 backdrop-blur-xl border-b border-white/5">
        <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-10 h-16 flex items-center justify-between">
            {{-- Logo + Company name --}}
            <div class="flex items-center gap-3">
                @if($company->logo)
                    <img src="{{ $company->logo_url }}" alt="{{ $company->name }}"
                         class="h-8 w-8 rounded-lg object-contain bg-dv-surface-container border border-dv-outline-variant/30">
                @else
                    <div class="h-8 w-8 rounded-lg bg-dv-primary/20 flex items-center justify-center">
                        <i class="fas fa-store text-sm text-dv-primary"></i>
                    </div>
                @endif
                <span class="font-bold text-sm text-dv-on-surface tracking-tight hidden sm:block truncate max-w-[160px]">
                    {{ $company->name }}
                </span>
            </div>

            <div class="flex items-center gap-3">
                @if($company->ig)
                    <a href="https://instagram.com/{{ $company->ig }}" target="_blank" rel="noopener"
                       class="text-dv-on-surface-variant hover:text-dv-secondary transition-colors text-sm flex items-center gap-1.5">
                        <i class="fab fa-instagram"></i>
                        <span class="hidden sm:inline">@ {{ $company->ig }}</span>
                    </a>
                @endif
                @if($company->phone)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company->phone) }}?text=Hola!+Vi+tu+catálogo+y+me+interesa+info"
                       target="_blank" rel="noopener"
                       class="bg-dv-secondary-container/20 border border-dv-secondary/20 text-dv-secondary px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-dv-secondary/10 transition flex items-center gap-1.5">
                        <i class="fab fa-whatsapp"></i>
                        <span class="hidden sm:inline">WhatsApp</span>
                    </a>
                @endif
            </div>
        </div>
    </nav>

    {{-- Main Content --}}
    <main class="flex-1">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="w-full py-8 border-t border-dv-outline-variant/30 bg-dv-surface-container-lowest mt-auto">
        <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-10 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div>
                <span class="text-dv-primary font-bold text-sm">{{ $company->name }}</span>
                <p class="text-xs text-dv-outline mt-1">&copy; {{ date('Y') }} — Todos los derechos reservados</p>
            </div>
            <div class="flex gap-6 text-xs text-dv-on-surface-variant">
                @if($company->phone)
                    <span class="flex items-center gap-1"><i class="fas fa-phone text-[10px]"></i> {{ $company->phone }}</span>
                @endif
                @if($company->email)
                    <span class="flex items-center gap-1"><i class="fas fa-envelope text-[10px]"></i> {{ $company->email }}</span>
                @endif
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
