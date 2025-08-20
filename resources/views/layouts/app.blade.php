<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistema de Ventas')</title>
    
    <!-- Fonts locales del sistema -->
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="{{ asset('vendor/sweetalert2/sweetalert2.min.css') }}">
    
    <!-- Bootstrap eliminado: usamos Tailwind -->
    
    <!-- Styles -->
    @vite(['resources/sass/app.scss'])
    @livewireStyles
    @stack('css')
    

    
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
        }
        
        /* Asegurar que todos los elementos usen fuentes del sistema */
        * {
            font-family: inherit;
        }
        
        /* Scrollbar personalizada */
        ::-webkit-scrollbar {
            width: 6px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        
        /* Animaciones */
        .fade-in {
            animation: fadeIn 0.3s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .slide-in {
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from { transform: translateX(-100%); }
            to { transform: translateX(0); }
        }
        
        /* Estilos para botones compatibles con AdminLTE */
        .btn-outline {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            background-color: white;
            color: #374151;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .btn-outline:hover {
            background-color: #f9fafb;
            border-color: #9ca3af;
            color: #111827;
        }
        
        .btn-primary {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border: 1px solid transparent;
            border-radius: 0.375rem;
            background-color: #3b82f6;
            color: white;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .btn-primary:hover {
            background-color: #2563eb;
            color: white;
        }
        
        .btn-danger {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border: 1px solid transparent;
            border-radius: 0.375rem;
            background-color: #dc2626;
            color: white;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .btn-danger:hover {
            background-color: #b91c1c;
            color: white;
        }
        
        /* Estilos para el contenido principal */
        .space-y-6 > * + * {
            margin-top: 1.5rem;
        }
        
        /* Asegurar que el contenido principal no se superponga con el sidebar */
        .lg\:pl-64 {
            padding-left: 16rem;
        }

        /* Transición suave para el contenido principal */
        .flex-1 {
            transition: margin-left 0.3s ease-in-out;
        }

        /* Estilos para el botón de toggle del sidebar */
        .sidebar-toggle-btn {
            transition: all 0.2s ease-in-out;
            display: none !important;
        }

        @media (min-width: 1024px) {
            .sidebar-toggle-btn {
                display: flex !important;
            }
        }

        .sidebar-toggle-btn:hover {
            background-color: #f3f4f6;
            transform: scale(1.05);
        }
        
        /* Prevenir flash del sidebar antes de que Alpine.js se inicialice */
        [x-cloak] {
            display: none !important;
        }
        
        /* Optimización de carga - ocultar sidebar hasta que Alpine.js esté listo */
        .sidebar-hidden {
            transform: translateX(-100%);
        }
        
        .sidebar-visible {
            transform: translateX(0);
        }
        
        /* Estilos para el header de la página */
        .flex.items-center.justify-between {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .text-2xl {
            font-size: 1.5rem;
            line-height: 2rem;
        }
        
        .font-bold {
            font-weight: 700;
        }
        
        .text-gray-900 {
            color: #111827;
        }
        
        .text-gray-600 {
            color: #4b5563;
        }
        
        .space-x-3 > * + * {
            margin-left: 0.75rem;
        }
    </style>
</head>

<body class="bg-gray-50" x-data="appLayout()">
    <div class="flex h-screen">
        <!-- Sidebar para móviles (overlay) -->
        <div x-show="sidebarOpen" 
             x-cloak
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 lg:hidden">
        </div>

        <!-- Sidebar -->
        <div x-show="sidebarOpen" 
             x-cloak
             x-transition:enter="transition ease-in-out duration-300 transform"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in-out duration-300 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
             @click.stop
             class="fixed inset-y-0 left-0 z-50 w-64 bg-gradient-to-br from-blue-500 via-purple-500 to-indigo-600 transform lg:translate-x-0"
             :class="sidebarOpen ? 'sidebar-visible' : 'sidebar-hidden'">
        
        <!-- Logo -->
        <div class="flex items-center justify-between h-16 px-6 border-b border-white/20">
            <a href="{{ route('admin.index') }}" class="flex items-center group">
                <div class="flex-shrink-0">
                    <i class="fas fa-store text-white text-2xl group-hover:scale-110 transition-transform duration-200"></i>
                </div>
                <div class="ml-3">
                    <h1 class="text-white text-lg font-semibold group-hover:text-purple-200 transition-colors duration-200">Test Company</h1>
                </div>
            </a>
            <button @click="sidebarOpen = false" class="lg:hidden text-white hover:text-purple-200 transition-colors duration-200">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="mt-6 px-3">
            <div class="space-y-1">


                <!-- Pedidos Online -->
                <a href="{{ route('admin.orders.index') }}" 
                   class="group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.orders.*') ? 'bg-white/20 text-white shadow-lg' : 'text-white/90 hover:bg-white/10 hover:text-white' }}">
                    <i class="fas fa-shopping-cart mr-3 text-lg"></i>
                    Pedidos Online
                </a>

                <!-- Configuración -->
                <div x-data="{ open: false }">
                    <button @click="open = !open" 
                            class="group w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg text-white/90 hover:bg-white/10 hover:text-white transition-all duration-200">
                        <div class="flex items-center">
                            <i class="fas fa-cog mr-3 text-lg"></i>
                            Config empresa
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                    </button>
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="ml-6 mt-1 space-y-1">
                        <a href="#" class="block px-3 py-2 text-sm text-white/70 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200">
                            Configuración General
                        </a>
                        <a href="#" class="block px-3 py-2 text-sm text-white/70 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200">
                            Configuración Avanzada
                        </a>
                    </div>
                </div>

                <!-- Roles y Permisos -->
                <div x-data="{ open: false }">
                    <button @click="open = !open" 
                            class="group w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg text-white/90 hover:bg-white/10 hover:text-white transition-all duration-200">
                        <div class="flex items-center">
                            <i class="fas fa-users-cog mr-3 text-lg"></i>
                            Roles y Permisos
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                    </button>
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="ml-6 mt-1 space-y-1">
                        <a href="{{ route('admin.roles.index') }}" class="block px-3 py-2 text-sm text-white/70 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200">
                            Lista de Roles
                        </a>
                        <a href="{{ route('admin.permissions.index') }}" class="block px-3 py-2 text-sm text-white/70 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200">
                            Permisos
                        </a>
                    </div>
                </div>

                <!-- Usuarios -->
                <a href="{{ route('admin.users.index') }}" 
                   class="group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.users.*') ? 'bg-white/20 text-white shadow-lg' : 'text-white/90 hover:bg-white/10 hover:text-white' }}">
                    <i class="fas fa-users mr-3 text-lg"></i>
                    Usuarios
                </a>

                <!-- Categorías -->
                <a href="{{ route('admin.categories.index') }}" 
                   class="group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.categories.*') ? 'bg-white/20 text-white shadow-lg' : 'text-white/90 hover:bg-white/10 hover:text-white' }}">
                    <i class="fas fa-tags mr-3 text-lg"></i>
                    Categorías
                </a>

                <!-- Productos -->
                <a href="{{ route('admin.products.index') }}" 
                   class="group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.products.*') ? 'bg-white/20 text-white shadow-lg' : 'text-white/90 hover:bg-white/10 hover:text-white' }}">
                    <i class="fas fa-box mr-3 text-lg"></i>
                    Productos
                </a>

                <!-- Proveedores -->
                <a href="{{ route('admin.suppliers.index') }}" 
                   class="group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.suppliers.*') ? 'bg-white/20 text-white shadow-lg' : 'text-white/90 hover:bg-white/10 hover:text-white' }}">
                    <i class="fas fa-truck mr-3 text-lg"></i>
                    Proveedores
                </a>

                <!-- Compras -->
                <a href="{{ route('admin.purchases.index') }}" 
                   class="group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.purchases.*') ? 'bg-white/20 text-white shadow-lg' : 'text-white/90 hover:bg-white/10 hover:text-white' }}">
                    <i class="fas fa-shopping-bag mr-3 text-lg"></i>
                    Compras
                </a>

                <!-- Ventas -->
                <a href="{{ route('admin.sales.index') }}" 
                   class="group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.sales.*') ? 'bg-white/20 text-white shadow-lg' : 'text-white/90 hover:bg-white/10 hover:text-white' }}">
                    <i class="fas fa-chart-line mr-3 text-lg"></i>
                    Ventas
                </a>

                <!-- Clientes -->
                <a href="{{ route('admin.customers.index') }}" 
                   class="group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.customers.*') ? 'bg-white/20 text-white shadow-lg' : 'text-white/90 hover:bg-white/10 hover:text-white' }}">
                    <i class="fas fa-user-friends mr-3 text-lg"></i>
                    Clientes
                </a>

                <!-- Arqueo de Caja -->
                <a href="{{ route('admin.cash-counts.index') }}" 
                   class="group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('admin.cash-counts.*') ? 'bg-white/20 text-white shadow-lg' : 'text-white/90 hover:bg-white/10 hover:text-white' }}">
                    <i class="fas fa-cash-register mr-3 text-lg"></i>
                    Arqueo de Caja
                </a>
            </div>
        </nav>

        <!-- User Info -->
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-white/20">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-white text-sm"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-white">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-white/70">{{ Auth::user()->email }}</p>
                </div>
            </div>
        </div>
    </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden" :class="sidebarOpen ? 'lg:ml-64' : 'lg:ml-0'">
        <!-- Top Navigation -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
                <!-- Mobile menu button -->
                <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500">
                    <i class="fas fa-bars text-xl"></i>
                </button>

                <!-- Desktop sidebar toggle button -->
                <button @click="sidebarOpen = !sidebarOpen" class="sidebar-toggle-btn p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500 transition-all duration-200" style="display: none;">
                    <i class="fas fa-bars text-xl"></i>
                </button>



                <!-- Right side -->
                <div class="flex items-center space-x-4">
                    <!-- Notifications -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <i class="fas fa-bell text-xl"></i>
                            <span class="absolute top-1 right-1 block h-2 w-2 rounded-full bg-red-400"></span>
                        </button>
                        
                        <!-- Notifications dropdown -->
                        <div x-show="open" 
                             x-cloak
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             @click.away="open = false"
                             class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                            <div class="py-1">
                                <div class="px-4 py-2 border-b border-gray-100">
                                    <h3 class="text-sm font-semibold text-gray-900">Notificaciones</h3>
                                </div>
                                <div class="max-h-64 overflow-y-auto">
                                    <a href="#" class="block px-4 py-3 hover:bg-gray-50 transition-colors duration-200">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-shopping-cart text-blue-500"></i>
                                            </div>
                                            <div class="ml-3 flex-1">
                                                <p class="text-sm font-medium text-gray-900">Nuevo pedido recibido</p>
                                                <p class="text-sm text-gray-500">Pedido #1234 de Juan Pérez</p>
                                                <p class="text-xs text-gray-400 mt-1">Hace 5 minutos</p>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="px-4 py-2 border-t border-gray-100">
                                    <a href="#" class="text-sm text-blue-600 hover:text-blue-800">Ver todas las notificaciones</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- User menu -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center space-x-2 p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-white text-sm"></i>
                            </div>
                            <span class="hidden md:block text-sm font-medium">{{ Auth::user()->name }}</span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        
                        <!-- User dropdown -->
                        <div x-show="open" 
                             x-cloak
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             @click.away="open = false"
                             class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                            <div class="py-1">
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user mr-2"></i>
                                    Mi Perfil
                                </a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-cog mr-2"></i>
                                    Configuración
                                </a>
                                <div class="border-t border-gray-100"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                        <i class="fas fa-sign-out-alt mr-2"></i>
                                        Cerrar Sesión
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50">
                <div class="py-6 px-4 sm:px-6 lg:px-8">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Scripts -->
    @vite(['resources/js/app.js'])
    @livewireScripts
    
    <!-- Chart.js -->
    <script src="{{ asset('vendor/chartjs/chart.min.js') }}"></script>
    
    <!-- SweetAlert2 -->
    <script src="{{ asset('vendor/sweetalert2/sweetalert2.min.js') }}"></script>
    
    <!-- CSS compartido -->
    <link rel="stylesheet" href="{{ asset('css/shared/components.css') }}">
    
    <!-- Utilidades compartidas -->
    <script src="{{ asset('js/shared/utils.js') }}"></script>
    
    <!-- Sistema de carga optimizada -->
    <script src="{{ asset('js/shared/loader.js') }}"></script>
    
    @stack('js')
    
    <!-- Manejo de notificaciones de sesión -->
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
    
    <script>
        function appLayout() {
            return {
                sidebarOpen: false, // Estado inicial cerrado
                
                init() {
                    // Inicializar después de que Alpine.js esté completamente cargado
                    this.$nextTick(() => {
                        // Mostrar/ocultar botón de toggle según el tamaño de pantalla
                        const toggleButton = document.querySelector('.sidebar-toggle-btn');
                        
                        function updateButtonVisibility() {
                            if (window.innerWidth >= 1024) {
                                toggleButton.style.display = 'flex';
                            } else {
                                toggleButton.style.display = 'none';
                            }
                        }
                        
                        // Ejecutar al inicio
                        setTimeout(updateButtonVisibility, 100);
                        
                        // Ejecutar cuando cambie el tamaño de la ventana
                        window.addEventListener('resize', updateButtonVisibility);
                        
                        // Manejar el estado del sidebar en localStorage
                        const savedState = localStorage.getItem('sidebarOpen');
                        if (savedState !== null) {
                            // Solo cargar el estado guardado si estamos en desktop
                            if (window.innerWidth >= 1024) {
                                this.sidebarOpen = JSON.parse(savedState);
                            }
                        } else {
                            // Si no hay estado guardado, establecer según el tamaño de pantalla
                            this.sidebarOpen = window.innerWidth >= 1024;
                        }

                        // Guardar el estado cuando cambie (solo en desktop)
                        this.$watch('sidebarOpen', value => {
                            if (window.innerWidth >= 1024) {
                                localStorage.setItem('sidebarOpen', JSON.stringify(value));
                            }
                        });
                        
                        // Manejar cambios de tamaño de ventana
                        window.addEventListener('resize', () => {
                            if (window.innerWidth < 1024) {
                                // En móviles, siempre cerrar el sidebar
                                this.sidebarOpen = false;
                            } else {
                                // En desktop, restaurar el estado guardado
                                const savedState = localStorage.getItem('sidebarOpen');
                                if (savedState !== null) {
                                    this.sidebarOpen = JSON.parse(savedState);
                                } else {
                                    this.sidebarOpen = true; // Por defecto abierto en desktop
                                }
                            }
                        });
                    });
                }
            }
        }
    </script>
    @include('debugbar-include')
</body>
</html>
