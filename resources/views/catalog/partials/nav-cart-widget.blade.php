<div x-show="cartUrls.sync"
     class="relative z-[60]"
     @keydown.window.escape="cartPanelOpen = false"
     @click.outside="cartPanelOpen = false">
    <button type="button"
            @click.prevent="cartPanelOpen = !cartPanelOpen"
            class="relative flex items-center gap-2 rounded-xl border border-dv-primary/40 bg-dv-primary/10 px-2.5 py-2 text-sm font-semibold text-dv-primary ring-dv-primary/20 transition hover:bg-dv-primary/15 hover:ring-2 sm:px-3"
            :aria-expanded="cartPanelOpen"
            aria-haspopup="listbox"
            aria-label="{{ __('Carrito de pedido') }}"
            id="catalog-cart-trigger"
            aria-controls="catalog-cart-panel">
        <i class="fas fa-shopping-cart text-base" aria-hidden="true"></i>
        <span class="max-w-[5rem] truncate text-left text-[11px] font-bold tabular-nums sm:max-w-none sm:text-sm" x-cloak>
            <span class="text-dv-primary" x-show="cartQtyTotal > 0" x-text="'$' + formatPrice(cartSubtotalUsd)"></span>
            <span x-show="cartQtyTotal === 0" class="font-semibold text-dv-outline/90">{{ __('Vacío') }}</span>
        </span>
        <span x-show="cartQtyTotal > 0" x-cloak
              class="absolute -right-1 -top-1 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-dv-secondary px-1 text-[10px] font-bold text-white shadow-sm ring-2 ring-dv-background"
              x-text="cartQtyTotal"></span>
    </button>

    {{-- Popover tipo “option list”: ventana flotante, sombra marcada y flecha hacia el trigger --}}
    <div x-show="cartPanelOpen"
         x-cloak
         x-transition:enter="transition ease-[cubic-bezier(0.16,1,0.3,1)] duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="catalog-cart-popover isolate flex min-h-0 flex-col outline-none ring-1 ring-transparent"
         id="catalog-cart-panel"
         role="region"
         aria-labelledby="catalog-cart-title"
         aria-modal="false"
         :aria-busy="lineSyncing"
         :style="cartPopoverStyle">
        {{-- Pico alineado bajo el botón carrito (posición desde JS porque el panel es fixed) --}}
        <div class="pointer-events-none absolute -top-[7px] left-0 z-10 h-3 w-3 -translate-x-1/2 rotate-45 rounded-sm border-l border-t border-dv-outline-variant/35 bg-dv-surface-container-highest shadow-[-4px_-4px_8px_-6px_rgba(0,0,0,.4)]"
             :style="{ left: cartPopoverCaretLeft + 'px', right: 'auto' }"></div>

        <div class="catalog-cart-popover__panel relative flex min-h-0 flex-1 flex-col overflow-hidden rounded-2xl border border-dv-outline-variant/35 bg-dv-surface-container-highest/95 shadow-[0_22px_50px_-12px_rgba(0,0,0,.65),0_0_1px_rgba(255,255,255,.08)_inset] ring-1 ring-black/35 backdrop-blur-xl">
            <header class="shrink-0 border-b border-dv-outline-variant/25 bg-dv-surface-container-low/90 px-4 py-3">
                <div class="flex items-center justify-between gap-2">
                    <div>
                        <h2 id="catalog-cart-title" class="font-dv-display text-sm font-bold tracking-tight text-dv-on-surface">{{ __('Tu carrito') }}</h2>
                        <p class="mt-0.5 text-[11px] leading-tight text-dv-on-surface-variant/85">
                            <span x-show="cartQtyTotal > 0" x-cloak><span x-text="cartQtyTotal"></span> {{ __('unidades en el pedido') }}</span>
                            <span x-show="cartQtyTotal === 0">{{ __('Sumá productos desde el catálogo.') }}</span>
                        </p>
                    </div>
                    <button type="button"
                            @click.prevent="cartPanelOpen = false"
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-dv-outline transition hover:bg-dv-surface-container-high hover:text-dv-on-surface"
                            aria-label="{{ __('Cerrar') }}">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                </div>
            </header>

            {{-- Lista estilo opciones de select — scroll interno, sin forzar scrollbar en la barra --}}
            <ul role="listbox"
                class="catalog-cart-popover__list min-h-0 flex-1 divide-y divide-dv-outline-variant/15 overflow-y-auto overscroll-contain"
                aria-labelledby="catalog-cart-title">
                <template x-if="!cartItems.length">
                    <li class="flex flex-col items-center gap-3 px-4 py-10 text-center">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full border border-dv-outline-variant/30 bg-dv-surface-container-high/80 text-dv-outline">
                            <i class="fas fa-shopping-basket text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-dv-on-surface">{{ __('Sin productos') }}</p>
                            <p class="mt-1 max-w-[14rem] text-[11px] leading-relaxed text-dv-on-surface-variant/80">{{ __('Elegí artículos en el catálogo; acá verás cantidades y el total.') }}</p>
                        </div>
                    </li>
                </template>
                <template x-for="line in cartItems" :key="line.product_id">
                    <li role="option"
                        class="group flex gap-3 px-3 py-2.5 transition-colors hover:bg-dv-surface-container-high/85 focus-within:bg-dv-surface-container-high/85">
                        <div class="min-w-0 flex-1 py-0.5">
                            <a :href="productUrl(line.product_id)"
                               class="block truncate text-[13px] font-semibold leading-snug text-dv-on-surface underline-offset-2 transition hover:text-dv-primary hover:underline"
                               @click="cartPanelOpen = false">
                                <span x-text="line.name"></span>
                            </a>
                            <p class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[11px] text-dv-on-surface-variant/90 tabular-nums">
                                <span>
                                    $<span x-text="formatPrice(line.unit_price_usd)"></span>
                                    × <span x-text="line.quantity"></span>
                                </span>
                                <span class="text-dv-outline/70">·</span>
                                <span class="font-semibold text-dv-primary" x-text="'$' + formatPrice(line.line_total_usd)"></span>
                            </p>
                            <p class="mt-1 text-[10px] font-medium text-dv-outline/75 tabular-nums" x-show="line.stock !== undefined">
                                {{ __('Stock disponible') }}: <span x-text="line.stock"></span>
                            </p>
                        </div>

                        <div class="flex shrink-0 flex-col items-end justify-center gap-1.5">
                            <div class="inline-flex overflow-hidden rounded-lg border border-dv-outline-variant/40 bg-dv-surface-container p-0.5 shadow-sm">
                                <button type="button"
                                        class="flex h-8 w-8 items-center justify-center text-dv-on-surface-variant transition hover:bg-dv-primary/15 hover:text-dv-primary disabled:opacity-35"
                                        @click.stop.prevent="decrementLine(line)"
                                        :disabled="lineSyncing"
                                        aria-label="{{ __('Quitar una unidad') }}">
                                    <i class="fas fa-minus text-[10px]"></i>
                                </button>
                                <span class="flex min-w-[1.75rem] items-center justify-center border-x border-dv-outline-variant/25 px-1 text-xs font-bold tabular-nums text-dv-on-surface"
                                      x-text="line.quantity"></span>
                                <button type="button"
                                        class="flex h-8 w-8 items-center justify-center text-dv-on-surface-variant transition hover:bg-dv-primary/15 hover:text-dv-primary disabled:cursor-not-allowed disabled:opacity-35"
                                        @click.stop.prevent="incrementLine(line)"
                                        :disabled="lineSyncing || (typeof line.stock === 'number' && Number(line.quantity) >= line.stock)"
                                        aria-label="{{ __('Agregar una unidad') }}">
                                    <i class="fas fa-plus text-[10px]"></i>
                                </button>
                            </div>
                            <button type="button"
                                    class="text-[11px] font-medium text-dv-outline underline-offset-2 transition hover:text-rose-400 hover:underline disabled:opacity-40"
                                    @click.stop.prevent="removeLineFromCart(line)"
                                    :disabled="lineSyncing"
                                    title="{{ __('Quitar del carrito') }}">
                                <span class="inline-flex items-center gap-1"><i class="fas fa-trash-alt text-[10px] opacity-80"></i>{{ __('Quitar') }}</span>
                            </button>
                        </div>
                    </li>
                </template>
            </ul>

            <footer class="shrink-0 border-t border-dv-outline-variant/25 bg-dv-surface-container-low/95 px-4 py-3">
                <div class="mb-3 flex items-end justify-between gap-3 border-b border-dv-outline-variant/15 pb-3">
                    <span class="text-xs font-semibold uppercase tracking-wide text-dv-outline">{{ __('Subtotal') }}</span>
                    <span class="font-dv-display text-lg font-bold tabular-nums leading-none text-dv-primary" x-text="'$' + formatPrice(cartSubtotalUsd)"></span>
                </div>
                <a x-show="cartQtyTotal > 0" x-cloak
                   :href="cartUrls.checkout"
                   class="flex w-full items-center justify-center gap-2 rounded-xl bg-dv-primary py-3 text-xs font-bold uppercase tracking-wide text-dv-on-primary shadow-lg shadow-dv-primary/25 transition hover:opacity-92 active:scale-[0.99]">
                    {{ __('Continuar pedido') }}
                    <i class="fas fa-arrow-right text-[11px]" aria-hidden="true"></i>
                </a>
                <template x-if="cartQtyTotal === 0">
                    <p class="text-center text-[11px] text-dv-on-surface-variant/75">{{ __('Cerrando este panel también podés seguir navegando el catálogo.') }}</p>
                </template>
            </footer>
        </div>
    </div>
</div>
