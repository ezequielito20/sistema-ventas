<div
    x-show="debtReportModalV2"
    x-cloak
    class="fixed inset-0 z-[75] debt-report-modal-v2"
    style="overflow: hidden;"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
>
    <div class="fixed inset-0 bg-[#020617]/90 backdrop-blur-sm" @click="closeDebtReportV2()"></div>

    <div
        class="flex h-full items-center justify-center p-3 sm:p-4"
        style="padding-top: max(1rem, env(safe-area-inset-top)); padding-bottom: max(1rem, env(safe-area-inset-bottom));"
    >
        <div
            class="relative my-0 flex w-full max-w-7xl flex-col overflow-hidden rounded-xl border border-slate-600/80 bg-slate-900 text-slate-100 shadow-[0_25px_80px_rgba(0,0,0,0.75)] sm:rounded-2xl"
            style="height: min(940px, calc(100dvh - 2rem));"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
        >
            <div class="flex items-center justify-between border-b border-slate-700 bg-slate-900/95 p-4 sm:p-6">
                <div class="flex items-center gap-3 sm:gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-violet-400/40 bg-violet-500/15 sm:h-12 sm:w-12 sm:rounded-xl">
                        <i class="fas fa-file-invoice-dollar text-violet-200 text-base sm:text-lg"></i>
                    </div>
                    <div>
                        <h5 class="text-base font-bold text-slate-100 sm:text-xl">Reporte de Deudas</h5>
                        <p class="text-xs text-slate-400 sm:text-sm">Visualiza y filtra clientes con deudas pendientes</p>
                    </div>
                </div>
                <button
                    type="button"
                    @click="closeDebtReportV2()"
                    class="flex h-10 w-10 items-center justify-center rounded-lg border border-slate-600 bg-slate-800 text-slate-200 transition hover:bg-slate-700"
                    aria-label="Cerrar"
                >
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto p-2.5 sm:p-6">
                <section class="mb-3 rounded-xl border border-slate-700/70 bg-slate-900/75 p-3 sm:mb-6 sm:p-6">
                    <div class="grid grid-cols-1 gap-3 xl:grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)]">
                        <div class="rounded-xl border border-slate-700/70 bg-slate-900/60 p-3.5 sm:p-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-cyan-500/15 text-cyan-200">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div class="min-w-0">
                                    <p id="v2DebtCompanyName" class="truncate font-semibold text-slate-100">Empresa</p>
                                    <p id="v2DebtReportTimestamp" class="text-xs text-slate-400">--/--/---- --:--</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-xl border border-slate-700/70 bg-slate-900/60 p-3.5 sm:p-4">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-sm text-slate-400">Conversion:</span>
                                <span class="text-sm font-semibold text-slate-200">1 USD</span>
                                <input
                                    type="number"
                                    id="v2ModalExchangeRate"
                                    step="0.01"
                                    min="0.01"
                                    readonly
                                    class="debt-report-modal-v2__input w-24 text-center font-semibold"
                                />
                            </div>
                            <div class="mt-3 flex flex-wrap items-center gap-1.5 sm:gap-2">
                                <button type="button" @click="v2FiltersOpen = !v2FiltersOpen" class="ui-btn ui-btn-ghost text-sm">
                                    <i class="fas fa-filter"></i>
                                    <span x-text="v2FiltersOpen ? 'Ocultar filtros' : 'Filtros'"></span>
                                </button>
                                <button type="button" id="v2UpdateModalExchangeRate" class="ui-btn ui-btn-ghost text-sm" title="Actualizar conversion">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                                <button type="button" id="v2DownloadPdfBtn" class="ui-btn ui-btn-danger text-sm" title="Descargar PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                <section
                    x-show="v2FiltersOpen"
                    x-transition
                    class="mb-3 rounded-xl border border-slate-700/70 bg-slate-900/70 p-3 sm:mb-6 sm:p-6"
                >
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-6">
                        <div class="space-y-1 xl:col-span-2">
                            <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Buscar</label>
                            <input type="text" id="v2SearchFilter" class="debt-report-modal-v2__input" placeholder="Cliente..." />
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Vista</label>
                            <select id="v2OrderFilter" class="debt-report-modal-v2__input">
                                <option value="debt_desc">Deuda Mayor</option>
                                <option value="debt_asc">Deuda Menor</option>
                                <option value="debt_date_asc">Mas antiguos</option>
                                <option value="debt_date_desc">Mas recientes</option>
                                <option value="name_asc">Nombre (A-Z)</option>
                                <option value="name_desc">Nombre (Z-A)</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Estado</label>
                            <select id="v2DebtTypeFilter" class="debt-report-modal-v2__input">
                                <option value="">Todos</option>
                                <option value="defaulters">Morosos</option>
                                <option value="current">Actuales</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Desde</label>
                            <input type="date" id="v2DateFromFilter" class="debt-report-modal-v2__input" />
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Hasta</label>
                            <input type="date" id="v2DateToFilter" class="debt-report-modal-v2__input" />
                        </div>
                    </div>
                    <div class="mt-3 flex justify-start sm:mt-4 sm:justify-end">
                        <button type="button" id="v2ClearFiltersBtn" class="ui-btn ui-btn-ghost w-full justify-center text-sm sm:w-auto">
                            <i class="fas fa-trash-alt"></i> Limpiar
                        </button>
                    </div>
                </section>

                <section id="v2DebtReportStats" class="mb-3 rounded-xl border border-slate-700/70 bg-slate-900/70 p-3 sm:mb-6 sm:p-6">
                    <div class="py-10 text-center text-slate-400">
                        <i class="fas fa-spinner fa-spin mr-2"></i> Cargando resumen...
                    </div>
                </section>

                <section id="v2DebtReportTable" class="overflow-hidden rounded-xl border border-slate-700/70 bg-slate-900/75">
                    <div class="py-12 text-center text-slate-400">
                        <i class="fas fa-spinner fa-spin mr-2"></i> Cargando tabla...
                    </div>
                </section>

                <section id="v2DebtReportPagination" class="mt-3"></section>
            </div>

            <div class="flex justify-end border-t border-slate-700 bg-slate-900/95 px-4 py-3 sm:px-6">
                <button type="button" @click="closeDebtReportV2()" class="ui-btn ui-btn-ghost text-sm">
                    <i class="fas fa-times"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
