<div x-show="cartUrls.sync"
     class="relative z-[60]"
     @keydown.window.escape="cartPanelOpen = false">
    <button type="button"
            @click.prevent="cartPanelOpen = !cartPanelOpen"
            class="relative flex items-center gap-2 rounded-xl border border-dv-primary/40 bg-dv-primary/10 px-2.5 py-2 text-sm font-semibold text-dv-primary transition hover:bg-dv-primary/20 sm:px-3"
            :aria-expanded="cartPanelOpen"
            aria-haspopup="true"
            aria-label="{{ __('Carrito de pedido') }}">
        <i class="fas fa-shopping-cart text-base"></i>
        <span class="max-w-[5rem] truncate text-left text-[11px] font-bold tabular-nums sm:max-w-none sm:text-sm" x-cloak>
            <span class="text-dv-primary" x-show="cartQtyTotal > 0" x-text="'$' + formatPrice(cartSubtotalUsd)"></span>
            <span x-show="cartQtyTotal === 0" class="font-semibold text-dv-outline/90">{{ __('Vacío') }}</span>
        </span>
        <span x-show="cartQtyTotal > 0" x-cloak
              class="absolute -right-1 -top-1 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-dv-secondary px-1 text-[10px] font-bold text-white"
              x-text="cartQtyTotal"></span>
    </button>

    <div x-show="cartPanelOpen"
         x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         @click.outside="cartPanelOpen = false"
         class="absolute right-0 mt-2 w-[min(100vw-1.5rem,20rem)] max-h-[70vh] overflow-hidden rounded-xl border border-dv-outline-variant/35 bg-dv-surface-container-lowest shadow-xl ring-1 ring-black/25">
        <div class="border-b border-dv-outline-variant/25 px-3 py-2.5">
            <p class="font-dv-display text-sm font-semibold text-dv-on-surface">{{ __('Tu pedido') }}</p>
            <p class="text-[11px] text-dv-on-surface-variant/80">{{ __('Hasta el máximo de stock disponible por producto.') }}</p>
        </div>
        <div class="max-h-56 overflow-y-auto">
            <template x-if="!cartItems.length">
                <p class="px-3 py-6 text-center text-xs text-dv-outline">{{ __('Sin productos en el carrito.') }}</p>
            </template>
            <template x-for="line in cartItems" :key="line.product_id">
                <div class="flex gap-2 border-b border-dv-outline-variant/15 px-3 py-2.5 last:border-b-0">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-xs font-semibold leading-snug text-dv-on-surface" x-text="line.name"></p>
                        <p class="mt-0.5 text-[10px] text-dv-outline tabular-nums">
                            $<span x-text="formatPrice(line.unit_price_usd)"></span> × <span x-text="line.quantity"></span>
                            <span class="text-dv-primary"> · $<span x-text="formatPrice(line.line_total_usd)"></span></span>
                        </p>
                    </div>
                    <div class="flex shrink-0 flex-col items-end justify-center gap-1">
                        <div class="flex items-center gap-0.5 rounded-lg border border-dv-outline-variant/30 bg-dv-surface-container-high p-0.5">
                            <button type="button"
                                    class="flex h-7 w-7 items-center justify-center rounded-md text-dv-on-surface-variant transition hover:bg-dv-surface-container hover:text-dv-on-surface disabled:opacity-35"
                                    @click.stop="decrementLine(line)"
                                    :disabled="lineSyncing"
                                    aria-label="{{ __('Quitar una unidad') }}">
                                <i class="fas fa-minus text-[9px]"></i>
                            </button>
                            <span class="min-w-[1.25rem] text-center text-[11px] font-bold tabular-nums text-dv-on-surface" x-text="line.quantity"></span>
                            <button type="button"
                                    class="flex h-7 w-7 items-center justify-center rounded-md text-dv-on-surface-variant transition hover:bg-dv-surface-container hover:text-dv-on-surface disabled:cursor-not-allowed disabled:opacity-35"
                                    @click.stop="incrementLine(line)"
                                    :disabled="lineSyncing || line.quantity >= (line.stock ?? 0)"
                                    aria-label="{{ __('Agregar una unidad') }}">
                                <i class="fas fa-plus text-[9px]"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
        <div class="border-t border-dv-outline-variant/25 bg-dv-surface-container-high/50 px-3 py-2.5">
            <div class="mb-2 flex items-center justify-between text-xs">
                <span class="text-dv-on-surface-variant">{{ __('Subtotal') }}</span>
                <span class="font-dv-display font-bold tabular-nums text-dv-primary" x-text="'$' + formatPrice(cartSubtotalUsd)"></span>
            </div>
            <a x-show="cartQtyTotal > 0" x-cloak
               :href="cartUrls.checkout"
               class="flex w-full items-center justify-center gap-2 rounded-lg bg-dv-primary py-2.5 text-center text-xs font-bold text-dv-on-primary transition hover:opacity-90">
                {{ __('Continuar pedido') }}
                <i class="fas fa-arrow-right text-[10px]"></i>
            </a>
        </div>
    </div>
</div>
