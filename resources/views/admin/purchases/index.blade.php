@extends('layouts.app')

@section('title', 'Gestión de Compras')

@section('content')
    <!-- Background Pattern -->
    <div class="page-background"></div>

    <!-- Main Container -->
    <div class="main-container" id="purchasesRoot" data-currency-symbol="{{ $currency->symbol }}">
        <!-- Page Header with System Gradient -->
        <div class="system-gradient-header">
            <div class="page-header">
                <div class="header-content">
                    <div class="header-left">
                        <div class="header-icon-wrapper">
                            <div class="header-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="icon-glow"></div>
                        </div>
                        <div class="header-text">
                            <h1 class="header-title">Gestión de Compras</h1>
                            <p class="header-subtitle">Administra y controla todas las compras del sistema</p>
                        </div>
                    </div>
                    <div class="header-actions">
                        @if ($permissions['can_report'])
                            <a href="{{ route('admin.purchases.report') }}" class="btn-glass btn-secondary-glass"
                                target="_blank" title="Generar reporte PDF">
                                <i class="fas fa-file-pdf"></i>
                                <span>Reporte</span>
                                <div class="btn-ripple"></div>
                            </a>
                        @endif
                        @if ($cashCount)
                            @if ($permissions['can_create'])
                                <a href="{{ route('admin.purchases.create') }}" class="btn-glass btn-primary-glass"
                                    title="Crear nueva compra">
                                    <i class="fas fa-plus-circle"></i>
                                    <span>Nueva Compra</span>
                                    <div class="btn-ripple"></div>
                                </a>
                            @endif
                        @else
                            <a href="{{ route('admin.cash-counts.create') }}" class="btn-glass btn-danger-glass"
                                title="Abrir caja para realizar compras">
                                <i class="fas fa-cash-register"></i>
                                <span>Abrir Caja</span>
                                <div class="btn-ripple"></div>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <!-- Stats Dashboard -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-4 gap-2 sm:gap-3 mb-6">
            <!-- Productos Únicos -->
            <x-dashboard-widget title="Productos Únicos" value="{{ $totalPurchases }}" valueType="number"
                icon="fas fa-boxes" trend="Comprados" trendIcon="fas fa-shopping-cart" trendColor="text-green-300"
                subtitle="Productos únicos comprados" subtitleIcon="fas fa-box" gradientFrom="from-blue-500"
                gradientTo="to-blue-600" progressWidth="100%" progressGradientFrom="from-blue-400"
                progressGradientTo="to-blue-500" />

            <!-- Total Invertido -->
            <x-dashboard-widget title="Total Invertido" value="{{ $totalAmount }}" valueType="currency"
                currencySymbol="{{ $currency->symbol }}" icon="fas fa-chart-line" trend="Capital"
                trendIcon="fas fa-dollar-sign" trendColor="text-green-300" subtitle="Capital comprometido"
                subtitleIcon="fas fa-chart-bar" gradientFrom="from-green-500" gradientTo="to-emerald-600"
                progressWidth="100%" progressGradientFrom="from-green-400" progressGradientTo="to-emerald-500" />

            <!-- Compras del Mes -->
            <x-dashboard-widget title="Compras del Mes" value="{{ $monthlyPurchases }}" valueType="number"
                icon="fas fa-calendar-check" trend="Recientes" trendIcon="fas fa-calendar-month"
                trendColor="text-yellow-300" subtitle="Actividad reciente" subtitleIcon="fas fa-clock"
                gradientFrom="from-yellow-500" gradientTo="to-orange-500" progressWidth="100%"
                progressGradientFrom="from-yellow-400" progressGradientTo="to-orange-400" />

            <!-- Entregas Pendientes -->
            <x-dashboard-widget title="Entregas Pendientes" value="{{ $pendingDeliveries }}" valueType="number"
                icon="fas fa-hourglass-half" trend="Pendientes" trendIcon="fas fa-clock" trendColor="text-red-300"
                subtitle="Por entregar" subtitleIcon="fas fa-truck" gradientFrom="from-red-500" gradientTo="to-pink-600"
                progressWidth="100%" progressGradientFrom="from-red-400" progressGradientTo="to-pink-500" />
        </div>

        <!-- Contenedor Dinámico para Lista de Compras (Tabla/Tarjetas) y Paginación -->
        <div id="purchases-list-container">
            @include('admin.purchases.partials.list')
        </div>



        <!-- Modal para Detalles -->
        <div class="modal-overlay" id="purchaseDetailsModal" role="dialog" aria-labelledby="purchaseDetailsTitle"
            aria-modal="true">
            <div class="modal-container">
                <div class="modal-header"
                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; color: white !important;">
                    <h3 class="modal-title" id="purchaseDetailsTitle" style="color: white !important;">
                        <i class="fas fa-list-alt mr-2" aria-hidden="true"></i>
                        Detalle de la Compra
                    </h3>
                    <button type="button" class="modal-close" onclick="closePurchaseModal()" aria-label="Cerrar modal"
                        style="color: white !important; background: rgba(255, 255, 255, 0.1) !important; border: 1px solid rgba(255, 255, 255, 0.2) !important; border-radius: 8px !important; padding: 8px 12px !important; transition: all 0.3s ease !important;"
                        onmouseover="this.style.background='rgba(255, 255, 255, 0.2)'; this.style.transform='scale(1.05)'"
                        onmouseout="this.style.background='rgba(255, 255, 255, 0.1)'; this.style.transform='scale(1)'">
                        <i class="fas fa-times" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-wrapper">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Producto</th>
                                    <th>Categoría</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unit.</th>
                                    <th>Descuento</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="purchaseDetailsTableBody">
                                <!-- Los detalles se cargarán aquí dinámicamente -->
                            </tbody>
                            <tfoot>
                                <tr class="subtotal-row border-t border-gray-100">
                                    <td colspan="6" class="px-4 py-2 text-right text-gray-500 font-medium">Subtotal
                                        antes de desc. general:</td>
                                    <td class="px-4 py-2 text-right font-semibold text-gray-700" id="modalSubtotalBefore">
                                        0.00</td>
                                </tr>
                                <tr class="discount-row bg-gray-50/50">
                                    <td colspan="6" class="px-4 py-2 text-right text-purple-600 font-medium">
                                        Descuento
                                        General:</td>
                                    <td class="px-4 py-2 text-right font-semibold text-purple-600"
                                        id="modalGeneralDiscount">
                                        0.00</td>
                                </tr>
                                <tr class="total-row highlight bg-gradient-to-r from-purple-50 to-indigo-50">
                                    <td colspan="6" class="total-label px-4 py-3 text-right">
                                        <div class="total-content inline-flex items-center">
                                            <i class="fas fa-calculator mr-2 text-indigo-600"></i>
                                            <span class="font-bold text-gray-800">Total Final</span>
                                        </div>
                                    </td>
                                    <td class="total-amount px-4 py-3 text-right">
                                        <div class="amount-display flex justify-end items-center">
                                            <span
                                                class="currency text-indigo-600 font-bold mr-1">{{ $currency->symbol }}</span>
                                            <span class="amount text-2xl font-black text-indigo-700"
                                                id="modalTotal">0.00</span>
                                        </div>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modern btn-secondary" onclick="closePurchaseModal()"
                        aria-label="Cerrar modal de detalles"
                        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; 
                               color: white !important; 
                               border: none !important;
                               box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3) !important;
                               transition: all 0.3s ease !important;
                               text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1) !important;"
                        onmouseover="this.style.background='linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%)'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(102, 126, 234, 0.4)'"
                        onmouseout="this.style.background='linear-gradient(135deg, #667eea 0%, #764ba2 100%)'; this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(102, 126, 234, 0.3)'"
                        onmousedown="this.style.transform='translateY(0) scale(0.98)'"
                        onmouseup="this.style.transform='translateY(-2px) scale(1)'">
                        <div class="btn-content">
                            <i class="fas fa-times" aria-hidden="true"></i>
                            <span>Cerrar</span>
                        </div>
                        <div class="btn-bg"></div>
                    </button>
                </div>
            </div>
        </div>
    </div>




    @push('css')
        <link rel="stylesheet" href="{{ asset('css/admin/purchases/index.css') }}">
    @endpush


    @push('js')
        <script src="{{ asset('js/admin/purchases/index.js') }}" defer></script>
    @endpush
@endsection
