<div class="space-y-10">
    @if (session('status'))
        <div class="rounded-lg border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
            {{ session('status') }}
        </div>
    @endif
    @if (session('error'))
        <div class="rounded-lg border border-rose-500/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
            {{ session('error') }}
        </div>
    @endif

    {{-- Pagos --}}
    <section class="rounded-xl border border-slate-700/80 bg-slate-900/40 p-5">
        <h2 class="text-lg font-semibold text-slate-100">Métodos de pago</h2>
        <p class="mt-1 text-sm text-slate-400">Aparecen en el checkout del catálogo. El % de descuento se aplica al total en USD antes de convertir a Bs.</p>

        <form wire:submit="savePayment" class="mt-4 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-slate-400">Nombre</label>
                <input type="text" wire:model="payName" class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-950 px-3 py-2 text-sm text-slate-100" />
                @error('payName') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400">Orden</label>
                <input type="number" wire:model="paySort" class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-950 px-3 py-2 text-sm text-slate-100" />
                @error('paySort') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400">Descuento %</label>
                <input type="text" wire:model="payDiscount" class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-950 px-3 py-2 text-sm text-slate-100" />
                @error('payDiscount') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2 flex items-center gap-2 pt-6">
                <input type="checkbox" wire:model="payActive" id="payActive" class="rounded border-slate-600" />
                <label for="payActive" class="text-sm text-slate-300">Activo</label>
            </div>
            <div class="md:col-span-3">
                <label class="block text-xs font-medium text-slate-400">Datos para el cliente (texto / instrucciones)</label>
                <textarea wire:model="payInstructions" rows="3" class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-950 px-3 py-2 text-sm text-slate-100"></textarea>
                @error('payInstructions') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-3 flex flex-wrap gap-2">
                <button type="submit" class="rounded-lg bg-cyan-600 px-4 py-2 text-sm font-medium text-white hover:bg-cyan-500">
                    {{ $payId ? 'Guardar cambios' : 'Crear método' }}
                </button>
                @if ($payId)
                    <button type="button" wire:click="resetPaymentForm" class="rounded-lg border border-slate-600 px-4 py-2 text-sm text-slate-300 hover:bg-slate-800">Cancelar edición</button>
                @endif
            </div>
        </form>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full text-left text-sm text-slate-300">
                <thead class="border-b border-slate-700 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="py-2 pr-4">Nombre</th>
                        <th class="py-2 pr-4">%</th>
                        <th class="py-2 pr-4">Orden</th>
                        <th class="py-2 pr-4">Activo</th>
                        <th class="py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $p)
                        <tr class="border-b border-slate-800/80" wire:key="pay-{{ $p->id }}">
                            <td class="py-2 pr-4">{{ $p->name }}</td>
                            <td class="py-2 pr-4">{{ $p->discount_percent }}</td>
                            <td class="py-2 pr-4">{{ $p->sort_order }}</td>
                            <td class="py-2 pr-4">{{ $p->is_active ? 'Sí' : 'No' }}</td>
                            <td class="py-2 text-right whitespace-nowrap">
                                <button type="button" wire:click="editPayment({{ $p->id }})" class="text-cyan-400 hover:underline">Editar</button>
                                <button type="button" wire:click="deletePayment({{ $p->id }})" wire:confirm="¿Eliminar este método de pago?" class="ml-3 text-rose-400 hover:underline">Eliminar</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-4 text-slate-500">No hay métodos de pago.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- Entrega --}}
    <section class="rounded-xl border border-slate-700/80 bg-slate-900/40 p-5">
        <h2 class="text-lg font-semibold text-slate-100">Métodos de entrega</h2>
        <p class="mt-1 text-sm text-slate-400">Retiro en local o delivery. Si hay pedidos asociados, no se puede eliminar el método.</p>

        <form wire:submit="saveDelivery" class="mt-4 grid gap-4 md:grid-cols-2">
            <div>
                <label class="block text-xs font-medium text-slate-400">Tipo</label>
                <select wire:model.live="delType" class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-950 px-3 py-2 text-sm text-slate-100">
                    <option value="pickup">Retiro</option>
                    <option value="delivery">Delivery</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400">Orden</label>
                <input type="number" wire:model="delSort" class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-950 px-3 py-2 text-sm text-slate-100" />
                @error('delSort') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-slate-400">Nombre</label>
                <input type="text" wire:model="delName" class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-950 px-3 py-2 text-sm text-slate-100" />
                @error('delName') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
            </div>
            @if ($delType === 'pickup')
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-slate-400">Dirección de retiro</label>
                    <input type="text" wire:model="delPickupAddress" class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-950 px-3 py-2 text-sm text-slate-100" />
                    @error('delPickupAddress') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>
            @endif
            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-slate-400">Instrucciones</label>
                <textarea wire:model="delInstructions" rows="2" class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-950 px-3 py-2 text-sm text-slate-100"></textarea>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" wire:model="delActive" id="delActive" class="rounded border-slate-600" />
                <label for="delActive" class="text-sm text-slate-300">Activo</label>
            </div>
            <div class="md:col-span-2 flex flex-wrap gap-2">
                <button type="submit" class="rounded-lg bg-cyan-600 px-4 py-2 text-sm font-medium text-white hover:bg-cyan-500">
                    {{ $delId ? 'Guardar cambios' : 'Crear método' }}
                </button>
                @if ($delId)
                    <button type="button" wire:click="resetDeliveryForm" class="rounded-lg border border-slate-600 px-4 py-2 text-sm text-slate-300 hover:bg-slate-800">Cancelar edición</button>
                @endif
            </div>
        </form>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full text-left text-sm text-slate-300">
                <thead class="border-b border-slate-700 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="py-2 pr-4">Nombre</th>
                        <th class="py-2 pr-4">Tipo</th>
                        <th class="py-2 pr-4">Activo</th>
                        <th class="py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($deliveries as $d)
                        <tr class="border-b border-slate-800/80" wire:key="del-{{ $d->id }}">
                            <td class="py-2 pr-4">{{ $d->name }}</td>
                            <td class="py-2 pr-4">{{ $d->type === 'delivery' ? 'Delivery' : 'Retiro' }}</td>
                            <td class="py-2 pr-4">{{ $d->is_active ? 'Sí' : 'No' }}</td>
                            <td class="py-2 text-right whitespace-nowrap">
                                <button type="button" wire:click="editDelivery({{ $d->id }})" class="text-cyan-400 hover:underline">Editar</button>
                                <button type="button" wire:click="deleteDelivery({{ $d->id }})" wire:confirm="¿Eliminar este método?" class="ml-3 text-rose-400 hover:underline">Eliminar</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-4 text-slate-500">No hay métodos de entrega.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- Zonas --}}
    <section class="rounded-xl border border-slate-700/80 bg-slate-900/40 p-5">
        <h2 class="text-lg font-semibold text-slate-100">Zonas de delivery</h2>
        <p class="mt-1 text-sm text-slate-400">Solo aplica a métodos de tipo delivery. El costo extra en USD se suma al pedido.</p>

        @php $deliveryChoices = $deliveries->filter(fn ($d) => $d->type === 'delivery'); @endphp
        @if ($deliveryChoices->isEmpty())
            <p class="mt-4 text-sm text-slate-500">Creá al menos un método delivery para administrar zonas.</p>
        @else
            <div class="mt-4 max-w-md">
                <label class="block text-xs font-medium text-slate-400">Método delivery</label>
                <select wire:model.live="zoneFilterMethodId" class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-950 px-3 py-2 text-sm text-slate-100">
                    @foreach ($deliveryChoices as $d)
                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>

            @if ($zoneMethod)
                <form wire:submit="saveZone" class="mt-4 grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-xs font-medium text-slate-400">Nombre zona</label>
                        <input type="text" wire:model="zoneName" class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-950 px-3 py-2 text-sm text-slate-100" />
                        @error('zoneName') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400">Costo extra (USD)</label>
                        <input type="text" wire:model="zoneFee" class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-950 px-3 py-2 text-sm text-slate-100" />
                        @error('zoneFee') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-center gap-2 md:col-span-2">
                        <input type="checkbox" wire:model="zoneActive" id="zoneActive" class="rounded border-slate-600" />
                        <label for="zoneActive" class="text-sm text-slate-300">Activo</label>
                    </div>
                    <div class="md:col-span-2 flex flex-wrap gap-2">
                        <button type="submit" class="rounded-lg bg-cyan-600 px-4 py-2 text-sm font-medium text-white hover:bg-cyan-500">
                            {{ $zoneId ? 'Guardar zona' : 'Crear zona' }}
                        </button>
                        @if ($zoneId)
                            <button type="button" wire:click="resetZoneForm" class="rounded-lg border border-slate-600 px-4 py-2 text-sm text-slate-300 hover:bg-slate-800">Cancelar</button>
                        @endif
                    </div>
                </form>

                <div class="mt-6 overflow-x-auto">
                    <table class="min-w-full text-left text-sm text-slate-300">
                        <thead class="border-b border-slate-700 text-xs uppercase text-slate-500">
                            <tr>
                                <th class="py-2 pr-4">Zona</th>
                                <th class="py-2 pr-4">Extra USD</th>
                                <th class="py-2 pr-4">Activo</th>
                                <th class="py-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($zones as $z)
                                <tr class="border-b border-slate-800/80" wire:key="zone-{{ $z->id }}">
                                    <td class="py-2 pr-4">{{ $z->name }}</td>
                                    <td class="py-2 pr-4">{{ $z->extra_fee_usd }}</td>
                                    <td class="py-2 pr-4">{{ $z->is_active ? 'Sí' : 'No' }}</td>
                                    <td class="py-2 text-right whitespace-nowrap">
                                        <button type="button" wire:click="editZone({{ $z->id }})" class="text-cyan-400 hover:underline">Editar</button>
                                        <button type="button" wire:click="deleteZone({{ $z->id }})" wire:confirm="¿Eliminar esta zona?" class="ml-3 text-rose-400 hover:underline">Eliminar</button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-4 text-slate-500">No hay zonas para este método.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        @endif
    </section>

    {{-- Franjas --}}
    <section class="rounded-xl border border-slate-700/80 bg-slate-900/40 p-5">
        <h2 class="text-lg font-semibold text-slate-100">Franjas horarias</h2>
        <p class="mt-1 text-sm text-slate-400">Capacidad por franja (por defecto 1 pedido). Solo se listan franjas futuras en el checkout.</p>

        @if ($deliveries->isEmpty())
            <p class="mt-4 text-sm text-slate-500">Creá métodos de entrega primero.</p>
        @else
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-xs font-medium text-slate-400">Método</label>
                    <select wire:model.live="slotFilterMethodId" class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-950 px-3 py-2 text-sm text-slate-100">
                        @foreach ($deliveries as $d)
                            <option value="{{ $d->id }}">{{ $d->name }} ({{ $d->type === 'delivery' ? 'Delivery' : 'Retiro' }})</option>
                        @endforeach
                    </select>
                </div>
                @if ($slotMethod && $slotMethod->isDelivery() && $slotZones->isNotEmpty())
                    <div>
                        <label class="block text-xs font-medium text-slate-400">Filtrar listado por zona</label>
                        <select wire:model.live="slotListZoneId" class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-950 px-3 py-2 text-sm text-slate-100">
                            <option value="0">Todas las zonas</option>
                            @foreach ($slotZones as $z)
                                <option value="{{ $z->id }}">{{ $z->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>

            @if ($slotMethod)
                <form wire:submit="saveSlot" class="mt-4 grid gap-4 md:grid-cols-2">
                    @if ($slotMethod->isDelivery())
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-slate-400">Zona (obligatorio para delivery)</label>
                            <select wire:model="slZoneId" class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-950 px-3 py-2 text-sm text-slate-100">
                                <option value="">— Elegir —</option>
                                @foreach ($slotZones as $z)
                                    <option value="{{ $z->id }}">{{ $z->name }}</option>
                                @endforeach
                            </select>
                            @error('slZoneId') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                        </div>
                    @endif
                    <div>
                        <label class="block text-xs font-medium text-slate-400">Inicio</label>
                        <input type="datetime-local" wire:model="slStarts" class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-950 px-3 py-2 text-sm text-slate-100" />
                        @error('slStarts') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400">Fin</label>
                        <input type="datetime-local" wire:model="slEnds" class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-950 px-3 py-2 text-sm text-slate-100" />
                        @error('slEnds') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400">Máx. pedidos</label>
                        <input type="number" wire:model="slMax" min="1" class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-950 px-3 py-2 text-sm text-slate-100" />
                        @error('slMax') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-center gap-2 pt-6">
                        <input type="checkbox" wire:model="slActive" id="slActive" class="rounded border-slate-600" />
                        <label for="slActive" class="text-sm text-slate-300">Activo</label>
                    </div>
                    <div class="md:col-span-2 flex flex-wrap gap-2">
                        <button type="submit" class="rounded-lg bg-cyan-600 px-4 py-2 text-sm font-medium text-white hover:bg-cyan-500">
                            {{ $slId ? 'Guardar franja' : 'Crear franja' }}
                        </button>
                        @if ($slId)
                            <button type="button" wire:click="resetSlotForm" class="rounded-lg border border-slate-600 px-4 py-2 text-sm text-slate-300 hover:bg-slate-800">Cancelar</button>
                        @endif
                    </div>
                </form>

                <div class="mt-6 overflow-x-auto">
                    <table class="min-w-full text-left text-sm text-slate-300">
                        <thead class="border-b border-slate-700 text-xs uppercase text-slate-500">
                            <tr>
                                <th class="py-2 pr-4">Inicio</th>
                                <th class="py-2 pr-4">Fin</th>
                                <th class="py-2 pr-4">Zona</th>
                                <th class="py-2 pr-4">Cap.</th>
                                <th class="py-2 pr-4">Reserv.</th>
                                <th class="py-2 pr-4">Activo</th>
                                <th class="py-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($slots as $s)
                                <tr class="border-b border-slate-800/80" wire:key="slot-{{ $s->id }}">
                                    <td class="py-2 pr-4 whitespace-nowrap">{{ $s->starts_at->format('d/m/Y H:i') }}</td>
                                    <td class="py-2 pr-4 whitespace-nowrap">{{ $s->ends_at->format('d/m/Y H:i') }}</td>
                                    <td class="py-2 pr-4">{{ $s->delivery_zone_id ? ($s->zone?->name ?? '—') : '—' }}</td>
                                    <td class="py-2 pr-4">{{ $s->max_orders }}</td>
                                    <td class="py-2 pr-4">{{ $s->booked_count }}</td>
                                    <td class="py-2 pr-4">{{ $s->is_active ? 'Sí' : 'No' }}</td>
                                    <td class="py-2 text-right whitespace-nowrap">
                                        <button type="button" wire:click="editSlot({{ $s->id }})" class="text-cyan-400 hover:underline">Editar</button>
                                        <button type="button" wire:click="deleteSlot({{ $s->id }})" wire:confirm="¿Eliminar esta franja?" class="ml-3 text-rose-400 hover:underline">Eliminar</button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="py-4 text-slate-500">No hay franjas para este filtro.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        @endif
    </section>
</div>
