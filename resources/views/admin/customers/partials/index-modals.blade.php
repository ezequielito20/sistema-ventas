        {{-- Modal de Detalles del Cliente Rediseñado con Alpine.js --}}
        <div x-show="showCustomerModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black bg-opacity-50" @click="closeModal('showCustomerModal')"></div>

            <!-- Modal Content -->
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-6xl max-h-[90vh] overflow-hidden"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 transform scale-100"
                    x-transition:leave-end="opacity-0 transform scale-95">

                    <!-- Header del Modal -->
                    <div
                        class="flex items-center justify-between p-6 border-b border-gray-200 bg-gradient-to-r from-blue-500 to-purple-600 rounded-t-2xl">
                        <div class="flex items-center space-x-4">
                            <div
                                class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                <i class="fas fa-user-tie text-white text-lg"></i>
                            </div>
                            <div>
                                <h5 class="text-xl font-bold text-white">Detalles del Cliente</h5>
                                <p class="text-sm text-blue-100">Información completa y historial de ventas</p>
                            </div>
                        </div>
                        <button type="button" @click="closeModal('showCustomerModal')"
                            class="w-10 h-10 bg-white/20 hover:bg-white/30 text-white hover:text-white rounded-lg flex items-center justify-center transition-all duration-200 backdrop-blur-sm">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <!-- Body del Modal -->
                    <div class="p-6 max-h-[70vh] overflow-y-auto">
                        <!-- Información del Cliente -->
                        <div
                            class="bg-gradient-to-br from-blue-50/90 via-indigo-50/75 to-purple-50/90 rounded-xl shadow-sm border border-blue-200/60 p-6 mb-6 backdrop-blur-sm">
                            <div class="flex items-center space-x-3 mb-4">
                                <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-user text-white text-sm"></i>
                                </div>
                                <h6 class="text-lg font-semibold text-gray-900">Información del Cliente</h6>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-gray-700">Cliente</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        </div>
                                        <input type="text" id="customer_name_details" readonly
                                            class="w-full pl-10 pr-3 py-2.5 bg-gray-100 border border-gray-300 rounded-lg text-gray-700 text-sm">
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-gray-700">Teléfono</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        </div>
                                        <input type="text" id="customer_phone_details" readonly
                                            class="w-full pl-10 pr-3 py-2.5 bg-gray-100 border border-gray-300 rounded-lg text-gray-700 text-sm">
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-gray-700">Último Pago</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        </div>
                                        <input type="text" id="customer_last_payment_details" readonly
                                            class="w-full pl-10 pr-3 py-2.5 bg-gray-100 border border-gray-300 rounded-lg text-gray-700 text-sm"
                                            placeholder="Sin pagos registrados">
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm font-semibold text-gray-700">Estado:</span>
                                    <span id="customer_status_details"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"></span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                            <!-- Header de la Sección -->
                            <div
                                class="flex items-center space-x-4 p-6 bg-gradient-to-r from-blue-500 to-purple-600 border-b border-gray-200">
                                <div
                                    class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center backdrop-blur-sm">
                                    <i class="fas fa-shopping-cart text-white"></i>
                                </div>
                                <div>
                                    <h6 class="text-lg font-semibold text-white">Historial de Ventas</h6>
                                    <p class="text-sm text-blue-100">Cliente: <span id="customerName"
                                            class="font-semibold text-white"></span></p>
                                </div>
                            </div>

                            <!-- Filtros -->
                            <div
                                class="p-6 border-b border-gray-100 bg-gradient-to-br from-purple-50/90 via-pink-50/75 to-rose-50/90 backdrop-blur-sm">
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    <!-- Rango de Fechas -->
                                    <div class="space-y-2">
                                        <label class="text-sm font-semibold text-gray-700">Rango de Fechas</label>
                                        <div class="flex items-center space-x-3">
                                            <div class="relative flex-1">
                                                <div
                                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                </div>
                                                <input type="date" id="dateFrom" placeholder="Desde"
                                                    class="w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm">
                                            </div>
                                            <span class="text-sm text-gray-500 font-medium">hasta</span>
                                            <div class="relative flex-1">
                                                <div
                                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                </div>
                                                <input type="date" id="dateTo" placeholder="Hasta"
                                                    class="w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Rango de Monto -->
                                    <div class="space-y-2">
                                        <label class="text-sm font-semibold text-gray-700">Rango de Monto</label>
                                        <div class="flex items-center space-x-3">
                                            <div class="relative flex-1">
                                                <div
                                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <span class="text-gray-500 font-medium">{{ $currency->symbol }}</span>
                                                </div>
                                                <input type="number" id="amountFrom" placeholder="Mínimo"
                                                    step="0.01" min="0"
                                                    class="w-full pl-8 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm">
                                            </div>
                                            <span class="text-sm text-gray-500 font-medium">-</span>
                                            <div class="relative flex-1">
                                                <div
                                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <span class="text-gray-500 font-medium">{{ $currency->symbol }}</span>
                                                </div>
                                                <input type="number" id="amountTo" placeholder="Máximo" step="0.01"
                                                    min="0"
                                                    class="w-full pl-8 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Botón Limpiar Filtros -->
                                <div class="flex justify-end mt-4">
                                    <button type="button" id="clearFilters"
                                        class="flex items-center space-x-2 px-4 py-2.5 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                        <i class="fas fa-times text-sm"></i>
                                        <span class="text-sm font-medium">Limpiar Filtros</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Tabla de Ventas -->
                            <div class="p-6">
                                <div class="max-h-96 overflow-y-auto border border-gray-200 rounded-lg">
                                    <table class="w-full sales-history-table">
                                        <thead class="bg-gradient-to-r from-blue-500 to-purple-600 sticky top-0">
                                            <tr>
                                                <th
                                                    class="px-4 py-3 text-left text-sm font-semibold text-white border-b border-blue-400">
                                                    Fecha</th>
                                                <th
                                                    class="px-4 py-3 text-left text-sm font-semibold text-white border-b border-blue-400">
                                                    Productos</th>
                                                <th
                                                    class="px-4 py-3 text-left text-sm font-semibold text-white border-b border-blue-400">
                                                    Total</th>
                                                <th
                                                    class="px-4 py-3 text-left text-sm font-semibold text-white border-b border-blue-400">
                                                    Estado de Pago</th>
                                            </tr>
                                        </thead>
                                        <tbody id="salesHistoryTable">
                                            <tr>
                                                <td colspan="3" class="px-4 py-12 text-center">
                                                    <div class="flex flex-col items-center space-y-3">
                                                        <div
                                                            class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center">
                                                            <i class="fas fa-info-circle text-2xl text-gray-400"></i>
                                                        </div>
                                                        <p class="text-gray-500">No hay ventas registradas</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Footer de la Tabla -->
                                <div class="mt-4 pt-4 border-t border-gray-200 text-center">
                                    <div class="text-sm text-gray-600">
                                        <span id="salesCount" class="font-semibold">0</span> ventas mostradas
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal para el reporte de deudas rediseñado con Alpine.js --}}
        <div id="debtReportModal" x-show="debtReportModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black bg-opacity-50" @click="closeModal('debtReportModal')"></div>

            <!-- Modal Content -->
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="modal-content relative bg-white rounded-2xl shadow-2xl w-full max-w-6xl max-h-[90vh] overflow-hidden"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 transform scale-100"
                    x-transition:leave-end="opacity-0 transform scale-95">

                    <!-- Header del Modal -->
                    <div
                        class="flex items-center justify-between p-6 border-b border-gray-200 bg-gradient-to-r from-red-50 to-pink-50 rounded-t-2xl">
                        <div class="flex items-center space-x-4">
                            <div
                                class="w-12 h-12 bg-gradient-to-br from-red-500 to-pink-600 rounded-xl flex items-center justify-center">
                                <i class="fas fa-file-invoice-dollar text-white text-lg"></i>
                            </div>
                            <div>
                                <h5 class="text-xl font-bold text-gray-900">Reporte de Deudas</h5>
                                <p class="text-sm text-gray-600">Análisis detallado de deudas por cliente</p>
                            </div>
                        </div>
                        <button type="button" @click="closeModal('debtReportModal')"
                            class="w-10 h-10 bg-gray-100 hover:bg-gray-200 text-gray-600 hover:text-gray-800 rounded-lg flex items-center justify-center transition-all duration-200">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <!-- Body del Modal -->
                    <div class="modal-body p-8">
                        <div class="flex flex-col items-center justify-center py-12">
                            <!-- Spinner de Carga -->
                            <div
                                class="w-16 h-16 border-4 border-gray-200 border-t-blue-500 rounded-full animate-spin mb-6">
                            </div>

                            <!-- Texto de Carga -->
                            <div class="text-center">
                                <h5 class="text-xl font-semibold text-gray-900 mb-2">Cargando reporte de deudas</h5>
                                <p class="text-gray-600">Preparando información detallada...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal para registrar pagos de deuda --}}
        <div id="debtPaymentModal" class="fixed inset-0 z-50 overflow-y-auto hidden" style="display: none;">

            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black bg-opacity-50" onclick="spaPaymentHandler.closePaymentModal()"></div>

            <!-- Modal Content -->
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden">

                    <!-- Header del Modal -->
                    <div
                        class="flex items-center justify-between p-6 border-b border-gray-200 bg-gradient-to-r from-green-50 to-emerald-50 rounded-t-2xl">
                        <div class="flex items-center space-x-4">
                            <div
                                class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
                                <i class="fas fa-money-bill-wave text-white text-lg"></i>
                            </div>
                            <div>
                                <h5 class="text-xl font-bold text-gray-900">Registrar Pago de Deuda</h5>
                            </div>
                        </div>
                        <button type="button" onclick="spaPaymentHandler.closePaymentModal()"
                            class="w-10 h-10 bg-gray-100 hover:bg-gray-200 text-gray-600 hover:text-gray-800 rounded-lg flex items-center justify-center transition-all duration-200">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <form id="debtPaymentForm" method="POST">
                        <div class="p-6 max-h-[70vh] overflow-y-auto">
                            @csrf
                            <input type="hidden" id="payment_customer_id" name="customer_id">

                            <div class="space-y-6">
                                <!-- Información del Cliente -->
                                <div class="bg-gray-50 rounded-xl p-6">
                                    <div class="flex items-center space-x-3 mb-4">
                                        <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-user text-white text-sm"></i>
                                        </div>
                                        <h6 class="text-lg font-semibold text-gray-900">Información del Cliente</h6>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-2">
                                            <label for="customer_name"
                                                class="text-sm font-semibold text-gray-700">Cliente</label>
                                            <div class="relative">
                                                <div
                                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                </div>
                                                <input type="text" id="customer_name" readonly
                                                    class="w-full pl-10 pr-3 py-2.5 bg-gray-100 border border-gray-300 rounded-lg text-gray-700 text-sm">
                                            </div>
                                        </div>
                                        <div class="space-y-2">
                                            <label for="customer_phone"
                                                class="text-sm font-semibold text-gray-700">Teléfono</label>
                                            <div class="relative">
                                                <div
                                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                </div>
                                                <input type="text" id="customer_phone" readonly
                                                    class="w-full pl-10 pr-3 py-2.5 bg-gray-100 border border-gray-300 rounded-lg text-gray-700 text-sm">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <div class="flex items-center space-x-2">
                                            <span class="text-sm font-semibold text-gray-700">Estado:</span>
                                            <span id="customer_status"
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Estado de Deuda -->
                                <div class="bg-gray-50 rounded-xl p-6">
                                    <div class="flex items-center space-x-3 mb-4">
                                        <div class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-chart-line text-white text-sm"></i>
                                        </div>
                                        <h6 class="text-lg font-semibold text-gray-900">Estado de Deuda</h6>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-2">
                                            <label for="current_debt" class="text-sm font-semibold text-gray-700">Deuda
                                                Actual</label>
                                            <div class="relative">
                                                <div
                                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-20">
                                                    <i class="fas fa-dollar-sign text-gray-400 text-sm"></i>
                                                </div>
                                                <div id="current_debt"
                                                    class="w-full pl-12 pr-12 py-2.5 bg-red-50 border border-red-200 rounded-lg text-red-700 font-semibold text-sm flex items-center">
                                                    <span class="text-red-700 font-semibold">$0.00</span>
                                                </div>
                                                <button type="button" id="current_debt_btn"
                                                    class="absolute inset-y-0 right-0 px-3 bg-red-500 hover:bg-red-600 text-white rounded-r-lg transition-colors duration-200"
                                                    title="Deuda actual">
                                                    <i class="fas fa-info text-sm"></i>
                                                </button>
                                            </div>
                                            <small class="text-xs text-gray-500">Deuda total del cliente</small>
                                        </div>
                                        <div class="space-y-2">
                                            <label for="remaining_debt" class="text-sm font-semibold text-gray-700">Deuda
                                                Restante</label>
                                            <div class="relative">
                                                <div
                                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-20">
                                                    <i class="fas fa-dollar-sign text-gray-400 text-sm"></i>
                                                </div>
                                                <div id="remaining_debt"
                                                    class="w-full pl-12 pr-12 py-2.5 bg-orange-50 border border-orange-200 rounded-lg text-orange-700 font-semibold text-sm flex items-center">
                                                    <span class="text-orange-700 font-semibold">$0.00</span>
                                                </div>
                                                <button type="button" id="remaining_debt_btn"
                                                    class="absolute inset-y-0 right-0 px-3 bg-orange-500 hover:bg-orange-600 text-white rounded-r-lg transition-colors duration-200"
                                                    title="Deuda restante">
                                                    <i class="fas fa-calculator text-sm"></i>
                                                </button>
                                            </div>
                                            <small class="text-xs text-gray-500">Deuda después del pago</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Detalles del Pago -->
                                <div class="bg-gray-50 rounded-xl p-6">
                                    <div class="flex items-center space-x-3 mb-4">
                                        <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-credit-card text-white text-sm"></i>
                                        </div>
                                        <h6 class="text-lg font-semibold text-gray-900">Detalles del Pago</h6>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="space-y-2">
                                            <label for="payment_amount" class="text-sm font-semibold text-gray-700">Monto
                                                del Pago</label>
                                            <div class="relative">
                                                <div
                                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-20">
                                                    <i class="fas fa-dollar-sign text-gray-400 text-sm"></i>
                                                </div>
                                                <input type="number" id="payment_amount" name="payment_amount"
                                                    step="0.01" min="0.01" required
                                                    class="w-full pl-12 pr-12 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">
                                                <button type="button" id="max_payment_btn"
                                                    class="absolute inset-y-0 right-0 px-3 bg-green-500 hover:bg-green-600 text-white rounded-r-lg transition-colors duration-200"
                                                    title="Pagar deuda completa">
                                                    <i class="fas fa-plus text-sm"></i>
                                                </button>
                                            </div>
                                            <small class="text-xs text-gray-500">El monto no puede ser mayor que la deuda
                                                actual</small>
                                        </div>

                                        <div class="space-y-2">
                                            <label for="payment_date" class="text-sm font-semibold text-gray-700">Fecha
                                                del Pago</label>
                                            <div class="relative">
                                                <div
                                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-20">
                                                </div>
                                                <input type="date" id="payment_date" name="payment_date" required
                                                    class="w-full pl-12 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">
                                            </div>
                                            <small class="text-xs text-gray-500">La fecha no puede ser mayor a hoy</small>
                                        </div>

                                        <div class="space-y-2">
                                            <label for="payment_time" class="text-sm font-semibold text-gray-700">Hora del
                                                Pago</label>
                                            <div class="relative">
                                                <div
                                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-20">
                                                </div>
                                                <input type="time" id="payment_time" name="payment_time" required
                                                    class="w-full pl-12 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">
                                            </div>
                                            <small class="text-xs text-gray-500">Hora en que se realizó el pago</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Notas Adicionales -->
                                <div class="bg-gray-50 rounded-xl p-6">
                                    <div class="flex items-center space-x-3 mb-4">
                                        <div class="w-8 h-8 bg-yellow-500 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-sticky-note text-white text-sm"></i>
                                        </div>
                                        <h6 class="text-lg font-semibold text-gray-900">Notas Adicionales</h6>
                                    </div>
                                    <div class="space-y-2">
                                        <label for="payment_notes"
                                            class="text-sm font-semibold text-gray-700">Notas</label>
                                        <textarea id="payment_notes" name="notes" rows="3" placeholder="Detalles adicionales sobre este pago..."
                                            class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 text-sm resize-vertical"></textarea>
                                    </div>
                                </div>
                            </div>
                            <!-- Footer del Modal (sticky dentro del área scrollable) -->
                            <div
                                class="flex items-center justify-end space-x-3 p-6 border-t border-gray-200 bg-gray-50 rounded-b-2xl sticky bottom-0">
                                <button type="submit"
                                    class="flex items-center space-x-2 px-6 py-2.5 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 shadow-md hover:shadow-lg">
                                    <i class="fas fa-save text-sm"></i>
                                    <span class="text-sm font-medium">Registrar Pago</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
