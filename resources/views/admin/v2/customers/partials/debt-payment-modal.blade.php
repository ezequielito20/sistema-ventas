<template x-teleport="body">
<div id="debtPaymentModalV2" class="debt-payment-modal-v2-alt fixed inset-0 z-[75]" style="display: none;">
    <div class="fixed inset-0 bg-[#020617]/92 backdrop-blur-sm" onclick="spaPaymentHandlerV2.closePaymentModal()"></div>

    <div
        class="relative flex h-full items-center justify-center p-3 sm:p-4"
        style="padding-top: max(4.2rem, calc(env(safe-area-inset-top) + 0.7rem)); padding-bottom: max(0.85rem, env(safe-area-inset-bottom));"
    >
        <div
            class="relative flex w-full max-w-4xl flex-col overflow-hidden rounded-xl border border-slate-600/80 bg-slate-900 text-slate-100 shadow-[0_28px_80px_rgba(0,0,0,0.76)]"
            style="height: min(760px, calc(100dvh - 6.2rem));"
        >
            <div class="flex items-center justify-between border-b border-slate-700 bg-slate-900/95 px-4 py-3 sm:px-5">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-emerald-400/35 bg-emerald-500/15">
                        <i class="fas fa-money-bill-wave text-sm text-emerald-100"></i>
                    </div>
                    <div>
                        <h5 class="text-base font-bold text-slate-100 sm:text-lg">Registrar Pago de Deuda</h5>
                        <p class="text-xs text-slate-400">Formulario compacto y trazable</p>
                    </div>
                </div>
                <button
                    type="button"
                    onclick="spaPaymentHandlerV2.closePaymentModal()"
                    class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-600 bg-slate-800 text-slate-200 transition hover:bg-slate-700"
                >
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="debtPaymentFormV2" class="flex min-h-0 flex-1 flex-col">
                @csrf
                <input type="hidden" id="v2_payment_customer_id" name="customer_id">

                <div class="min-h-0 flex-1 space-y-3 overflow-y-auto p-3 sm:p-4">
                    <section class="rounded-xl border border-slate-700/70 bg-slate-900/60 p-3">
                        <h6 class="mb-2 flex items-center gap-2 text-sm font-semibold text-slate-100">
                            <i class="fas fa-user text-cyan-300"></i> Información del Cliente
                        </h6>
                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                            <div class="space-y-1.5">
                                <label for="v2_customer_name" class="text-xs font-semibold uppercase tracking-wide text-slate-400">Cliente</label>
                                <input type="text" id="v2_customer_name" readonly class="debt-payment-modal-v2-alt__input">
                            </div>
                            <div class="space-y-1.5">
                                <label for="v2_customer_phone" class="text-xs font-semibold uppercase tracking-wide text-slate-400">Teléfono</label>
                                <input type="text" id="v2_customer_phone" readonly class="debt-payment-modal-v2-alt__input">
                            </div>
                        </div>
                        <div class="mt-3">
                            <span class="text-xs font-semibold uppercase tracking-wide text-slate-400">Estado</span>
                            <span id="v2_customer_status" class="ml-2 inline-flex"></span>
                        </div>
                    </section>

                    <section class="rounded-xl border border-slate-700/70 bg-slate-900/60 p-3">
                        <h6 class="mb-2 flex items-center gap-2 text-sm font-semibold text-slate-100">
                            <i class="fas fa-chart-line text-violet-300"></i> Estado de Deuda
                        </h6>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div class="rounded-lg border border-rose-500/30 bg-rose-950/20 px-3 py-2">
                                <p class="text-[0.7rem] uppercase tracking-wide text-rose-200/90">Deuda Actual</p>
                                <p id="v2_current_debt" class="mt-1 text-base font-bold tabular-nums text-rose-100">$0.00</p>
                            </div>
                            <div class="rounded-lg border border-amber-500/30 bg-amber-950/20 px-3 py-2">
                                <p class="text-[0.7rem] uppercase tracking-wide text-amber-200/90">Deuda Restante</p>
                                <p id="v2_remaining_debt" class="mt-1 text-base font-bold tabular-nums text-amber-100">$0.00</p>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-xl border border-slate-700/70 bg-slate-900/60 p-3">
                        <h6 class="mb-2 flex items-center gap-2 text-sm font-semibold text-slate-100">
                            <i class="fas fa-credit-card text-emerald-300"></i> Detalles del Pago
                        </h6>
                        <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                            <div class="space-y-1.5">
                                <label for="v2_payment_amount" class="text-xs font-semibold uppercase tracking-wide text-slate-400">Monto</label>
                                <div class="flex rounded-lg border border-slate-600 bg-slate-950/70">
                                    <span class="flex items-center px-3 text-slate-400">$</span>
                                    <input
                                        type="number"
                                        id="v2_payment_amount"
                                        name="payment_amount"
                                        step="0.01"
                                        min="0.01"
                                        required
                                        class="debt-payment-modal-v2-alt__input debt-payment-modal-v2-alt__input--compact border-0"
                                    >
                                    <button type="button" id="v2_max_payment_btn" class="rounded-r-lg bg-emerald-500 px-3 text-white transition hover:bg-emerald-600">
                                        <i class="fas fa-plus text-xs"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <label for="v2_payment_date" class="text-xs font-semibold uppercase tracking-wide text-slate-400">Fecha</label>
                                <input
                                    type="date"
                                    id="v2_payment_date"
                                    name="payment_date"
                                    required
                                    class="debt-payment-modal-v2-alt__input debt-payment-modal-v2-alt__input--compact"
                                >
                            </div>

                            <div class="space-y-1.5">
                                <label for="v2_payment_time" class="text-xs font-semibold uppercase tracking-wide text-slate-400">Hora</label>
                                <input
                                    type="time"
                                    id="v2_payment_time"
                                    name="payment_time"
                                    required
                                    class="debt-payment-modal-v2-alt__input debt-payment-modal-v2-alt__input--compact"
                                >
                            </div>
                        </div>
                    </section>

                    <section class="rounded-xl border border-slate-700/70 bg-slate-900/60 p-3">
                        <label for="v2_payment_notes" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-400">Notas</label>
                        <textarea
                            id="v2_payment_notes"
                            name="notes"
                            rows="2"
                            placeholder="Detalles adicionales sobre este pago..."
                            class="debt-payment-modal-v2-alt__input resize-y"
                        ></textarea>
                    </section>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-700 bg-slate-900/95 px-4 py-3">
                    <button type="button" onclick="spaPaymentHandlerV2.closePaymentModal()" class="ui-btn ui-btn-ghost text-sm">Cerrar</button>
                    <button type="submit" id="v2_submit_payment_btn" class="ui-btn ui-btn-success text-sm">
                        <i class="fas fa-save"></i> Registrar Pago
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</template>
