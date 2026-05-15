@php
    $checkoutSuccessVisible = $submitted && $createdOrderId;
    $checkoutCartEmptyVisible = ! $checkoutSuccessVisible && count($cartItems) === 0;
    $checkoutFormVisible = ! $checkoutSuccessVisible && ! $checkoutCartEmptyVisible;
    $showKindStep = $this->hasPickupDeliveryMethods && $this->hasHomeDeliveryMethods;
    $orderForCheckoutSummary = ($checkoutSuccessVisible && $createdOrderId)
        ? \App\Models\Order::query()->find($createdOrderId)
        : null;
@endphp

<div class="mx-auto max-w-dv px-margin-mobile pb-16 sm:px-6 lg:px-margin-desktop">
    <div class="{{ $checkoutSuccessVisible ? '' : 'hidden' }}">
        <div class="rounded-xl border border-dv-secondary/30 bg-dv-secondary-container/10 p-6 text-center">
            <h1 class="font-dv-display text-dv-headline-md text-dv-on-surface">{{ __('¡Pedido recibido!') }}</h1>
            <p class="mt-2 text-dv-body-sm text-dv-on-surface-variant">{{ __('Gracias. La tienda revisará tu pedido y te contactará.') }}</p>
            @if ($orderForCheckoutSummary)
                <p class="mt-4 text-sm text-dv-outline">{{ __('Tu número de pedido:') }} <strong class="text-dv-primary">#{{ $orderForCheckoutSummary->id }}</strong></p>
                <a href="{{ $orderForCheckoutSummary->summaryUrl() }}" target="_blank" rel="noopener"
                   class="mt-6 inline-flex items-center justify-center rounded-xl bg-dv-primary px-6 py-3 text-sm font-bold text-dv-on-primary">
                    {{ __('Ver resumen / monto a pagar') }}
                </a>
            @endif
            <div class="mt-6">
                <a href="{{ $catalogHomeUrl }}" class="text-sm text-dv-secondary underline">{{ __('Volver al catálogo') }}</a>
            </div>
        </div>
    </div>

    <div class="{{ $checkoutCartEmptyVisible ? '' : 'hidden' }}">
        <div class="rounded-xl border border-dv-outline-variant/30 bg-dv-surface-container-low p-8 text-center">
            <p class="text-dv-on-surface">{{ __('Tu carrito está vacío.') }}</p>
            <a href="{{ $catalogHomeUrl }}" class="mt-4 inline-block text-dv-primary underline">{{ __('Ir al catálogo') }}</a>
        </div>
    </div>

    <div class="{{ $checkoutFormVisible ? '' : 'hidden' }}">
        <h1 class="font-dv-display text-dv-headline-md text-dv-on-surface">{{ __('Confirmar pedido') }}</h1>
        <p class="mt-1 text-sm text-dv-outline">{{ $company->name }}</p>

        <div class="mt-6 grid gap-8 lg:grid-cols-2">
            <div class="rounded-xl border border-dv-outline-variant/20 bg-dv-surface-container-low p-5">
                <h2 class="font-dv-label text-xs font-bold uppercase tracking-wider text-dv-outline">{{ __('Tu selección') }}</h2>
                <ul class="mt-4 space-y-3">
                    @foreach ($cartItems as $row)
                        <li class="flex justify-between gap-3 text-sm">
                            <span class="min-w-0 text-dv-on-surface">
                                {{ $row['name'] }} × {{ $row['quantity'] }}
                                @if (($row['quantity'] ?? 1) > 1)
                                    <span class="mt-0.5 block text-xs text-dv-on-surface-variant/90">
                                        {{ __('Por unidad:') }} ${{ number_format($row['unit_usd'], 2) }}
                                    </span>
                                @endif
                            </span>
                            <span class="shrink-0 whitespace-nowrap text-dv-primary">${{ number_format($row['line_usd'], 2) }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-4 border-t border-dv-outline-variant/30 pt-4 font-semibold text-dv-on-surface">
                    <div class="flex justify-between gap-3">
                        <span class="shrink-0">{{ __('Total estimado (USD)') }}</span>
                        <div class="min-w-0 text-right">
                            @if ($this->paymentDiscountPreview['has_payment_discount'])
                                <span class="block text-sm font-normal text-dv-on-surface-variant line-through">${{ number_format($this->paymentDiscountPreview['subtotal'], 2) }}</span>
                                <span class="block text-base font-semibold text-dv-primary">${{ number_format($this->paymentDiscountPreview['final'], 2) }}</span>
                                <span class="mt-1 block text-xs font-normal text-dv-outline">
                                    {{ __('Descuento por pago') }} ({{ number_format($this->paymentDiscountPreview['discount_percent'], 2) }}%): −${{ number_format($this->paymentDiscountPreview['discount_amount'], 2) }}
                                </span>
                            @else
                                <span>${{ number_format($this->paymentDiscountPreview['subtotal'], 2) }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <form wire:submit="submit" class="space-y-4 rounded-xl border border-dv-outline-variant/20 bg-dv-surface-container-low p-5">
                <div>
                    <label class="block text-xs font-semibold uppercase text-dv-outline">{{ __('Nombre') }}</label>
                    <input type="text" wire:model="customer_name" class="mt-1 w-full rounded-lg border border-dv-outline-variant bg-dv-surface px-3 py-2 text-sm text-dv-on-surface" required>
                    @error('customer_name') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-dv-outline">{{ __('Teléfono') }}</label>
                    <input
                        type="text"
                        wire:model.live="customer_phone"
                        maxlength="11"
                        inputmode="numeric"
                        autocomplete="tel"
                        placeholder="04148965789"
                        class="mt-1 w-full rounded-lg border border-dv-outline-variant bg-dv-surface px-3 py-2 text-sm text-dv-on-surface">
                    <p class="mt-1 text-[0.65rem] text-dv-outline">{{ __('11 dígitos numéricos, como en la configuración de la empresa.') }}</p>
                    @error('customer_phone') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-dv-outline">{{ __('Notas (opcional)') }}</label>
                    <textarea wire:model="notes" rows="2" class="mt-1 w-full rounded-lg border border-dv-outline-variant bg-dv-surface px-3 py-2 text-sm text-dv-on-surface"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-dv-outline">{{ __('Método de pago') }}</label>
                    <select wire:model.live="company_payment_method_id" class="mt-1 w-full rounded-lg border border-dv-outline-variant bg-dv-surface px-3 py-2 text-sm text-dv-on-surface">
                        <option value="">{{ __('Seleccionar…') }}</option>
                        @foreach ($this->paymentMethods as $m)
                            <option value="{{ $m->id }}">{{ $m->name }} @if($m->discount_percent > 0) ({{ __('desc.') }} {{ $m->discount_percent }}%) @endif</option>
                        @endforeach
                    </select>
                    @error('company_payment_method_id') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                @if ($showKindStep)
                    <div>
                        <label class="block text-xs font-semibold uppercase text-dv-outline">{{ __('Tipo de logística') }}</label>
                        <select wire:model.live="delivery_kind" class="mt-1 w-full rounded-lg border border-dv-outline-variant bg-dv-surface px-3 py-2 text-sm text-dv-on-surface">
                            <option value="">{{ __('Elegí retiro en punto o envío…') }}</option>
                            @if ($this->hasPickupDeliveryMethods)
                                <option value="pickup">{{ __('Entrega / retiro en punto') }}</option>
                            @endif
                            @if ($this->hasHomeDeliveryMethods)
                                <option value="delivery">{{ __('Delivery a domicilio') }}</option>
                            @endif
                        </select>
                        <p class="mt-1 text-[0.65rem] text-dv-outline">{{ __('Primero indicá cómo querés recibir tu pedido; luego te mostramos las opciones que correspondan.') }}</p>
                        @error('delivery_kind') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                @endif

                @if ($delivery_kind === 'pickup' || $delivery_kind === 'delivery' || ! $showKindStep)
                    <div>
                        <label class="block text-xs font-semibold uppercase text-dv-outline">{{ __('Punto / método específico') }}</label>
                        <select wire:model.live="company_delivery_method_id" class="mt-1 w-full rounded-lg border border-dv-outline-variant bg-dv-surface px-3 py-2 text-sm text-dv-on-surface">
                            <option value="">{{ __('Seleccionar…') }}</option>
                            @foreach ($this->deliveryMethodsFiltered as $m)
                                <option value="{{ $m->id }}">{{ $m->name }}</option>
                            @endforeach
                        </select>
                        @error('company_delivery_method_id') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                @endif

                @if ($delivery_kind === 'pickup' && $company_delivery_method_id && $this->hasHomeDeliveryMethods)
                    <button type="button" wire:click="preferHomeDeliveryInstead"
                            class="w-full rounded-lg border border-dv-secondary/50 bg-dv-secondary-container/10 px-3 py-2 text-left text-xs text-dv-secondary">
                        <strong>{{ __('Ningún punto anterior me sirve') }}</strong> — {{ __('cambiar a delivery a domicilio y elegir zona de envío') }}
                    </button>
                @endif

                @if ($delivery_kind === 'delivery' && $company_delivery_method_id && $this->zones->isNotEmpty())
                    <div>
                        <label class="block text-xs font-semibold uppercase text-dv-outline">{{ __('Zona de envío') }}</label>
                        <select wire:model.live="delivery_zone_selection" class="mt-1 w-full rounded-lg border border-dv-outline-variant bg-dv-surface px-3 py-2 text-sm text-dv-on-surface">
                            <option value="">{{ __('Seleccionar…') }}</option>
                            @foreach ($this->zones as $z)
                                <option value="z:{{ $z->id }}">{{ $z->name }} (+${{ number_format($z->extra_fee_usd, 2) }})</option>
                            @endforeach
                            <option value="custom">{{ __('Otro (costo de envío por confirmar)') }}</option>
                        </select>
                        @error('delivery_zone_id') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                        @error('delivery_zone_selection') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                @endif

                @if ($delivery_kind === 'delivery' && $company_delivery_method_id && ($this->isCustomDeliveryZone))
                    <div>
                        <label class="block text-xs font-semibold uppercase text-dv-outline">{{ __('Zona / dirección (delivery)') }}</label>
                        <textarea wire:model="delivery_custom_zone" rows="3" maxlength="2000"
                                  class="mt-1 w-full rounded-lg border border-dv-outline-variant bg-dv-surface px-3 py-2 text-sm text-dv-on-surface"
                                  placeholder="{{ __('Ej: urbanización, calle principal, punto de referencia…') }}"></textarea>
                        <p class="mt-2 rounded-lg border border-amber-500/25 bg-amber-950/35 px-3 py-2 text-[0.7rem] leading-relaxed text-amber-100/95">
                            {{ __('El cargo extra por envío puede variar.') }}
                            <span class="text-dv-outline">{{ __('No se suma automático al total: la tienda te lo confirmará cuando lea tu pedido.') }}</span>
                        </p>
                        @error('delivery_custom_zone') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                @endif

                @if ($this->deliverySlots->isNotEmpty())
                    <div>
                        <label class="block text-xs font-semibold uppercase text-dv-outline">{{ __('Horario') }}</label>
                        @if ($this->isCustomDeliveryZone && $delivery_kind === 'delivery')
                            <p class="mt-1 text-[0.65rem] leading-relaxed text-dv-outline">{{ __('Las ventanas muestran el horario de reparto configurado por la tienda; si ves varias zonas es para que puedas elegir la franja que te convenga. La entrega será en la dirección que escribiste arriba.') }}</p>
                        @endif
                        <select wire:model="delivery_slot_id" class="mt-1 w-full rounded-lg border border-dv-outline-variant bg-dv-surface px-3 py-2 text-sm text-dv-on-surface">
                            <option value="">{{ __('Seleccionar…') }}</option>
                            @foreach ($this->deliverySlots as $s)
                                <option value="{{ $s->id }}">{{ $s->weekdayLabelEs() }}, {{ $s->deliveryWindowLabelShort() }} · próx. {{ $s->resolveNextScheduledDeliveryDate()->format('d/m') }}
                                    @if ($delivery_kind === 'delivery' && optional($s->zone)->name)
                                        · {{ $s->zone->name }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('delivery_slot_id') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                @endif

                @error('cart') <p class="text-sm text-red-400">{{ $message }}</p> @enderror
                @error('stock') <p class="text-sm text-red-400">{{ $message }}</p> @enderror
                @error('plan') <p class="text-sm text-red-400">{{ $message }}</p> @enderror

                <button type="submit" wire:loading.attr="disabled"
                        class="w-full rounded-xl bg-dv-primary py-3 text-sm font-bold text-dv-on-primary disabled:opacity-50">
                    {{ __('Enviar pedido') }}
                </button>
            </form>
        </div>
    </div>
</div>
