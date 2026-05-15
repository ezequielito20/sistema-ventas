<div class="mx-auto max-w-dv px-margin-mobile pb-16 sm:px-6 lg:px-margin-desktop">
    @if ($submitted && $createdOrderId)
        @php($ord = \App\Models\Order::query()->find($createdOrderId))
        <div class="rounded-xl border border-dv-secondary/30 bg-dv-secondary-container/10 p-6 text-center">
            <h1 class="font-dv-display text-dv-headline-md text-dv-on-surface">{{ __('¡Pedido recibido!') }}</h1>
            <p class="mt-2 text-dv-body-sm text-dv-on-surface-variant">{{ __('Gracias. La tienda revisará tu pedido y te contactará.') }}</p>
            @if ($ord)
                <p class="mt-4 text-sm text-dv-outline">{{ __('Tu número de pedido:') }} <strong class="text-dv-primary">#{{ $ord->id }}</strong></p>
                <a href="{{ $ord->summaryUrl() }}" target="_blank" rel="noopener"
                   class="mt-6 inline-flex items-center justify-center rounded-xl bg-dv-primary px-6 py-3 text-sm font-bold text-dv-on-primary">
                    {{ __('Ver resumen / monto a pagar') }}
                </a>
            @endif
            <div class="mt-6">
                <a href="{{ $catalogHomeUrl }}" class="text-sm text-dv-secondary underline">{{ __('Volver al catálogo') }}</a>
            </div>
        </div>
    @elseif (count($cartItems) === 0)
        <div class="rounded-xl border border-dv-outline-variant/30 bg-dv-surface-container-low p-8 text-center">
            <p class="text-dv-on-surface">{{ __('Tu carrito está vacío.') }}</p>
            <a href="{{ $catalogHomeUrl }}" class="mt-4 inline-block text-dv-primary underline">{{ __('Ir al catálogo') }}</a>
        </div>
    @else
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
                <div class="mt-4 flex justify-between border-t border-dv-outline-variant/30 pt-4 font-semibold text-dv-on-surface">
                    <span>{{ __('Total estimado (USD)') }}</span>
                    <span>${{ $cartTotalUsd }}</span>
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
                    <input type="text" wire:model="customer_phone" class="mt-1 w-full rounded-lg border border-dv-outline-variant bg-dv-surface px-3 py-2 text-sm text-dv-on-surface" required>
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

                <div>
                    <label class="block text-xs font-semibold uppercase text-dv-outline">{{ __('Método de entrega') }}</label>
                    <select wire:model.live="company_delivery_method_id" class="mt-1 w-full rounded-lg border border-dv-outline-variant bg-dv-surface px-3 py-2 text-sm text-dv-on-surface">
                        <option value="">{{ __('Seleccionar…') }}</option>
                        @foreach ($this->deliveryMethods as $m)
                            <option value="{{ $m->id }}">{{ $m->name }}</option>
                        @endforeach
                    </select>
                    @error('company_delivery_method_id') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                @if ($this->zones->isNotEmpty())
                    <div>
                        <label class="block text-xs font-semibold uppercase text-dv-outline">{{ __('Zona') }}</label>
                        <select wire:model.live="delivery_zone_id" class="mt-1 w-full rounded-lg border border-dv-outline-variant bg-dv-surface px-3 py-2 text-sm text-dv-on-surface">
                            <option value="">{{ __('Seleccionar…') }}</option>
                            @foreach ($this->zones as $z)
                                <option value="{{ $z->id }}">{{ $z->name }} (+${{ number_format($z->extra_fee_usd, 2) }})</option>
                            @endforeach
                        </select>
                        @error('delivery_zone_id') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                @endif

                @if ($this->deliverySlots->isNotEmpty())
                    <div>
                        <label class="block text-xs font-semibold uppercase text-dv-outline">{{ __('Horario') }}</label>
                        <select wire:model="delivery_slot_id" class="mt-1 w-full rounded-lg border border-dv-outline-variant bg-dv-surface px-3 py-2 text-sm text-dv-on-surface">
                            <option value="">{{ __('Seleccionar…') }}</option>
                            @foreach ($this->deliverySlots as $s)
                                <option value="{{ $s->id }}">{{ $s->starts_at->format('d/m H:i') }} – {{ $s->ends_at->format('H:i') }}</option>
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
    @endif
</div>
