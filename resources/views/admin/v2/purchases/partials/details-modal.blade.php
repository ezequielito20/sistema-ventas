<div id="purchaseDetailsModalV2" class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-950/80 px-4 py-8 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="purchaseDetailsTitleV2">
    <div class="relative max-h-[90vh] w-full max-w-5xl overflow-hidden rounded-2xl border border-cyan-500/20 bg-slate-900/95 shadow-[0_0_40px_rgba(34,211,238,0.12)]">
        <div class="flex items-center justify-between border-b border-slate-700/80 bg-gradient-to-r from-cyan-500/10 to-indigo-600/10 px-5 py-4">
            <h3 id="purchaseDetailsTitleV2" class="text-lg font-semibold text-white">
                <i class="fas fa-receipt mr-2 text-cyan-300"></i> Detalle de la compra
            </h3>
            <button type="button" onclick="window.closePurchaseModalV2 && window.closePurchaseModalV2()" class="rounded-lg border border-slate-600 bg-slate-800/80 px-3 py-1.5 text-slate-300 transition hover:bg-slate-700 hover:text-white" aria-label="Cerrar">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="max-h-[calc(90vh-8rem)] overflow-y-auto p-5">
            <div class="ui-table-wrap rounded-xl border border-slate-700/60">
                <table class="ui-table text-sm">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th class="text-center">Cant.</th>
                            <th class="text-right">P. unit.</th>
                            <th class="text-right">Desc.</th>
                            <th class="text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="purchaseDetailsTableBodyV2">
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">Cargando…</td>
                        </tr>
                    </tbody>
                    <tfoot class="border-t border-slate-700/80 bg-slate-950/40">
                        <tr>
                            <td colspan="6" class="px-4 py-2 text-right text-xs text-slate-400">Subtotal antes de desc. general</td>
                            <td class="px-4 py-2 text-right font-medium tabular-nums text-slate-200" id="modalSubtotalBeforeV2">0.00</td>
                        </tr>
                        <tr>
                            <td colspan="6" class="px-4 py-2 text-right text-xs text-cyan-300/90">Descuento general</td>
                            <td class="px-4 py-2 text-right font-medium tabular-nums text-cyan-200" id="modalGeneralDiscountV2">—</td>
                        </tr>
                        <tr>
                            <td colspan="6" class="px-4 py-3 text-right text-sm font-semibold text-white">Total</td>
                            <td class="px-4 py-3 text-right">
                                <span class="text-cyan-300" id="modalCurrencyV2">{{ $currency->symbol }}</span>
                                <span class="text-xl font-bold tabular-nums text-white" id="modalTotalV2">0.00</span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="border-t border-slate-700/80 bg-slate-950/50 px-5 py-3 text-right">
            <button type="button" onclick="window.closePurchaseModalV2 && window.closePurchaseModalV2()" class="ui-btn ui-btn-primary text-sm">
                Cerrar
            </button>
        </div>
    </div>
</div>
