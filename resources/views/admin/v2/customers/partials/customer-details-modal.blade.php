<div
    x-show="showCustomerModalV2"
    x-cloak
    class="fixed inset-0 z-[70] overflow-y-auto customer-detail-modal-v2"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
>
    <div class="fixed inset-0 bg-[#020617]/90 backdrop-blur-sm" @click="closeCustomerDetailsModal()"></div>

    <div class="flex min-h-screen items-center justify-center p-2 sm:p-4">
        <div
            class="relative w-full max-w-6xl overflow-hidden rounded-xl sm:rounded-2xl border border-slate-600/80 bg-slate-900 text-slate-100 shadow-[0_25px_80px_rgba(0,0,0,0.75)]"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
        >
            <div class="flex items-center justify-between border-b border-slate-700 bg-slate-900/95 p-4 sm:p-6">
                <div class="flex items-center gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-cyan-400/40 bg-cyan-500/15 sm:h-12 sm:w-12 sm:rounded-xl">
                        <i class="fas fa-user-tie text-cyan-200 text-lg"></i>
                    </div>
                    <div>
                        <h5 class="text-base font-bold text-slate-100 sm:text-xl">Detalles del Cliente</h5>
                        <p class="text-xs text-slate-400 sm:text-sm">Informacion completa e historial de ventas</p>
                    </div>
                </div>
                <button
                    type="button"
                    @click="closeCustomerDetailsModal()"
                    class="flex h-10 w-10 items-center justify-center rounded-lg border border-slate-600 bg-slate-800 text-slate-200 transition hover:bg-slate-700"
                    aria-label="Cerrar"
                >
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="max-h-[80vh] overflow-y-auto p-3 sm:max-h-[72vh] sm:p-6">
                <section class="mb-4 rounded-xl border border-slate-700/70 bg-slate-900/75 p-4 sm:mb-6 sm:p-6">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-cyan-500/85 sm:h-8 sm:w-8">
                            <i class="fas fa-user text-white text-sm"></i>
                        </div>
                        <h6 class="text-base font-semibold text-slate-100 sm:text-lg">Informacion del Cliente</h6>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div class="space-y-2">
                            <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Cliente</label>
                            <input id="v2_customer_name_details" type="text" readonly class="customer-detail-modal-v2__input" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Telefono</label>
                            <input id="v2_customer_phone_details" type="text" readonly class="customer-detail-modal-v2__input" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Ultimo Pago</label>
                            <input
                                id="v2_customer_last_payment_details"
                                type="text"
                                readonly
                                placeholder="Sin pagos registrados"
                                class="customer-detail-modal-v2__input"
                            />
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <span class="text-sm font-semibold text-slate-300">Estado:</span>
                        <span id="v2_customer_status_details" class="ui-badge"></span>
                    </div>
                </section>

                <section class="overflow-hidden rounded-xl border border-slate-700/70 bg-slate-900/80">
                    <div class="flex items-center gap-3 border-b border-slate-700 bg-slate-900/95 p-4 sm:gap-4 sm:p-6">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-violet-400/40 bg-violet-500/15 sm:h-10 sm:w-10">
                            <i class="fas fa-shopping-cart text-violet-200"></i>
                        </div>
                        <div>
                            <h6 class="text-base font-semibold text-slate-100 sm:text-lg">Historial de Ventas</h6>
                            <p class="text-xs text-slate-400 sm:text-sm">Cliente: <span id="v2_customerName" class="font-semibold text-slate-200"></span></p>
                        </div>
                    </div>

                    <div class="border-b border-slate-700 bg-slate-900/70 p-4 sm:p-6">
                        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                            <div class="space-y-2">
                                <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Rango de Fechas</label>
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-3">
                                    <input type="date" id="v2_dateFrom" class="customer-detail-modal-v2__input w-full sm:flex-1" />
                                    <span class="hidden text-sm text-slate-500 sm:inline">hasta</span>
                                    <input type="date" id="v2_dateTo" class="customer-detail-modal-v2__input w-full sm:flex-1" />
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Rango de Monto</label>
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-3">
                                    <div class="relative w-full sm:flex-1">
                                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-500">{{ $currency->symbol }}</span>
                                        <input type="number" id="v2_amountFrom" step="0.01" min="0" placeholder="Minimo" class="customer-detail-modal-v2__input customer-detail-modal-v2__input--with-prefix w-full" />
                                    </div>
                                    <span class="hidden text-sm text-slate-500 sm:inline">-</span>
                                    <div class="relative w-full sm:flex-1">
                                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-500">{{ $currency->symbol }}</span>
                                        <input type="number" id="v2_amountTo" step="0.01" min="0" placeholder="Maximo" class="customer-detail-modal-v2__input customer-detail-modal-v2__input--with-prefix w-full" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 flex justify-start sm:justify-end">
                            <button type="button" id="v2_clearFilters" class="ui-btn ui-btn-ghost w-full justify-center text-sm sm:w-auto">
                                <i class="fas fa-times"></i> Limpiar Filtros
                            </button>
                        </div>
                    </div>

                    <div class="p-4 sm:p-6">
                        <div class="max-h-96 overflow-y-auto rounded-lg border border-slate-700/80">
                            <table class="w-full sales-history-table customer-detail-modal-v2__table">
                                <thead class="sticky top-0">
                                    <tr>
                                        <th class="border-b border-slate-600 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-200">Fecha</th>
                                        <th class="border-b border-slate-600 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-200">Productos</th>
                                        <th class="border-b border-slate-600 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-200">Total</th>
                                        <th class="border-b border-slate-600 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-200">Estado de Pago</th>
                                    </tr>
                                </thead>
                                <tbody id="v2_salesHistoryTable">
                                    <tr>
                                        <td colspan="4" class="px-4 py-12 text-center">
                                            <div class="flex flex-col items-center space-y-3">
                                                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-800">
                                                    <i class="fas fa-info-circle text-2xl text-slate-500"></i>
                                                </div>
                                                <p class="text-slate-400">No hay ventas registradas</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 border-t border-slate-700 pt-4 text-center">
                            <div class="text-sm text-slate-400">
                                <span id="v2_salesCount" class="font-semibold">0</span> ventas mostradas
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
