<div class="space-y-8" wire:key="catalog-delivery-method-form-{{ $deliveryMethodId ?? 'new' }}">
    <div class="ui-panel">
        <div class="ui-panel__header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="ui-panel__title">{{ $headingTitle }}</h1>
                <p class="ui-panel__subtitle">Retiro, delivery, zonas y franjas visibles para el cliente al cerrar el pedido.</p>
            </div>
            <a href="{{ route('admin.catalog-delivery-methods.index') }}" class="ui-btn ui-btn-ghost shrink-0 text-sm md:py-2.5 md:px-5 md:text-[0.95rem]" wire:navigate>
                <i class="fas fa-arrow-left"></i> Volver al listado
            </a>
        </div>
    </div>

    {{-- Datos principales del método --}}
    <div class="ui-panel">
        <div class="ui-panel__header border-b border-slate-700/50">
            <h2 class="text-base font-semibold text-slate-100">Datos del método</h2>
            <p class="ui-panel__subtitle">{{ $deliveryMethodId ? 'Los cambios se guardan desde este formulario.' : 'Creá primero el método; luego configurá zonas y franjas en la vista de edición.' }}</p>
        </div>
        <div class="ui-panel__body">
            <form wire:submit="saveDeliveryMethod" class="space-y-6">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Tipo</label>
                        <select wire:model.live="delType" class="w-full rounded-lg border border-slate-600 bg-slate-950 px-3 py-2 text-sm text-slate-100">
                            <option value="pickup">Retiro</option>
                            <option value="delivery">Delivery</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Orden</label>
                        <input type="number" wire:model.blur="delSort" class="w-full rounded-lg border border-slate-600 bg-slate-950 px-3 py-2 text-sm text-slate-100" />
                        @error('delSort')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Nombre <span class="text-rose-400">*</span></label>
                        <input type="text" wire:model.blur="delName" class="w-full rounded-lg border border-slate-600 bg-slate-950 px-3 py-2 text-sm text-slate-100" />
                        @error('delName')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>
                    @if ($delType === 'pickup')
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Dirección de retiro <span class="text-rose-400">*</span></label>
                            <input type="text" wire:model.blur="delPickupAddress" class="w-full rounded-lg border border-slate-600 bg-slate-950 px-3 py-2 text-sm text-slate-100" />
                            @error('delPickupAddress')
                                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Instrucciones</label>
                        <textarea wire:model.blur="delInstructions" rows="3" class="w-full rounded-lg border border-slate-600 bg-slate-950 px-3 py-2 text-sm text-slate-100"></textarea>
                    </div>
                    <div class="flex items-center gap-2 md:col-span-2">
                        <input type="checkbox" wire:model="delActive" id="dm-active" class="rounded border-slate-600 bg-slate-900" />
                        <label for="dm-active" class="text-sm text-slate-300">Activo</label>
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" class="ui-btn ui-btn-primary text-sm">
                            <i class="fas fa-save mr-2"></i>{{ $deliveryMethodId ? 'Guardar método' : 'Crear y continuar' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if ($deliveryMethodId && $methodModel)
        {{-- Zonas (solo delivery) --}}
        @if ($methodModel->isDelivery())
            <div class="ui-panel">
                <div class="ui-panel__header border-b border-slate-700/50">
                    <h2 class="text-base font-semibold text-slate-100">Zonas de delivery</h2>
                    <p class="ui-panel__subtitle">Costo extra en USD por zona.</p>
                </div>
                <div class="ui-panel__body space-y-6">
                    <form wire:submit="saveZone" class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-400">Nombre zona</label>
                            <input type="text" wire:model.blur="zoneName" class="w-full rounded-lg border border-slate-600 bg-slate-950 px-3 py-2 text-sm text-slate-100" />
                            @error('zoneName')
                                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-400">Costo extra (USD)</label>
                            <input type="text" wire:model.blur="zoneFee" class="w-full rounded-lg border border-slate-600 bg-slate-950 px-3 py-2 text-sm text-slate-100" />
                            @error('zoneFee')
                                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex items-center gap-2 md:col-span-2">
                            <input type="checkbox" wire:model="zoneActive" id="zone-active-dm" class="rounded border-slate-600" />
                            <label for="zone-active-dm" class="text-sm text-slate-300">Activo</label>
                        </div>
                        <div class="flex flex-wrap gap-2 md:col-span-2">
                            <button type="submit" class="rounded-lg bg-cyan-600 px-4 py-2 text-sm font-medium text-white hover:bg-cyan-500">{{ $zoneId ? 'Guardar zona' : 'Agregar zona' }}</button>
                            @if ($zoneId)
                                <button type="button" wire:click="resetZoneForm" class="rounded-lg border border-slate-600 px-4 py-2 text-sm text-slate-300 hover:bg-slate-800">Cancelar</button>
                            @endif
                        </div>
                    </form>

                    <div class="overflow-x-auto rounded-lg border border-slate-700/60">
                        <table class="min-w-full text-left text-sm text-slate-300">
                            <thead class="border-b border-slate-700 text-xs uppercase text-slate-500">
                                <tr>
                                    <th class="py-3 px-4">Zona</th>
                                    <th class="py-3 px-4">Extra USD</th>
                                    <th class="py-3 px-4">Activo</th>
                                    <th class="py-3 px-4"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($zones as $z)
                                    <tr class="border-b border-slate-800/80" wire:key="z-{{ $z->id }}">
                                        <td class="py-2 px-4">{{ $z->name }}</td>
                                        <td class="py-2 px-4">{{ $z->extra_fee_usd }}</td>
                                        <td class="py-2 px-4">{{ $z->is_active ? 'Sí' : 'No' }}</td>
                                        <td class="py-2 px-4 text-right whitespace-nowrap">
                                            <button type="button" wire:click="editZone({{ $z->id }})" class="text-cyan-400 hover:underline">Editar</button>
                                            <button type="button" wire:click="deleteZone({{ $z->id }})" wire:confirm="¿Eliminar zona?" class="ml-3 text-rose-400 hover:underline">Eliminar</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-6 px-4 text-center text-slate-500">No hay zonas aún.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- Franjas horarias --}}
        <div class="ui-panel">
            <div class="ui-panel__header border-b border-slate-700/50">
                <h2 class="text-base font-semibold text-slate-100">Franjas horarias</h2>
                <p class="ui-panel__subtitle">Capacidad por franja para este método.</p>
            </div>
            <div class="ui-panel__body space-y-6">
                @if ($methodModel->isDelivery() && $slotZones->isNotEmpty())
                    <div class="max-w-md">
                        <label class="mb-1 block text-xs font-semibold text-slate-400">Filtrar listado por zona</label>
                        <select wire:model.live="slotListZoneId" class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-950 px-3 py-2 text-sm text-slate-100">
                            <option value="0">Todas las zonas</option>
                            @foreach ($slotZones as $z)
                                <option value="{{ $z->id }}">{{ $z->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <form wire:submit="saveSlot" class="grid gap-4 md:grid-cols-2">
                    @if ($methodModel->isDelivery())
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-semibold text-slate-400">Zona (obligatorio para delivery)</label>
                            <select wire:model="slZoneId" class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-950 px-3 py-2 text-sm text-slate-100">
                                <option value="">— Elegir —</option>
                                @foreach ($slotZones as $z)
                                    <option value="{{ $z->id }}">{{ $z->name }}</option>
                                @endforeach
                            </select>
                            @error('slZoneId')
                                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-400">Inicio</label>
                        <input type="datetime-local" wire:model="slStarts" class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-950 px-3 py-2 text-sm text-slate-100" />
                        @error('slStarts')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-400">Fin</label>
                        <input type="datetime-local" wire:model="slEnds" class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-950 px-3 py-2 text-sm text-slate-100" />
                        @error('slEnds')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-400">Máx. pedidos</label>
                        <input type="number" wire:model="slMax" min="1" class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-950 px-3 py-2 text-sm text-slate-100" />
                        @error('slMax')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex items-center gap-2 pt-6 md:col-span-2">
                        <input type="checkbox" wire:model="slActive" id="sl-active-dm" class="rounded border-slate-600" />
                        <label for="sl-active-dm" class="text-sm text-slate-300">Activo</label>
                    </div>
                    <div class="flex flex-wrap gap-2 md:col-span-2">
                        <button type="submit" class="rounded-lg bg-cyan-600 px-4 py-2 text-sm font-medium text-white hover:bg-cyan-500">{{ $slId ? 'Guardar franja' : 'Agregar franja' }}</button>
                        @if ($slId)
                            <button type="button" wire:click="resetSlotForm" class="rounded-lg border border-slate-600 px-4 py-2 text-sm text-slate-300 hover:bg-slate-800">Cancelar</button>
                        @endif
                    </div>
                </form>

                <div class="overflow-x-auto rounded-lg border border-slate-700/60">
                    <table class="min-w-full text-left text-sm text-slate-300">
                        <thead class="border-b border-slate-700 text-xs uppercase text-slate-500">
                            <tr>
                                <th class="py-3 px-4">Inicio</th>
                                <th class="py-3 px-4">Fin</th>
                                <th class="py-3 px-4">Zona</th>
                                <th class="py-3 px-4">Cap.</th>
                                <th class="py-3 px-4">Reserv.</th>
                                <th class="py-3 px-4">Activo</th>
                                <th class="py-3 px-4"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($slots as $s)
                                <tr wire:key="sl-{{ $s->id }}" class="border-b border-slate-800/80">
                                    <td class="py-2 px-4 whitespace-nowrap">{{ $s->starts_at->format('d/m/Y H:i') }}</td>
                                    <td class="py-2 px-4 whitespace-nowrap">{{ $s->ends_at->format('d/m/Y H:i') }}</td>
                                    <td class="py-2 px-4">{{ $s->delivery_zone_id ? ($s->zone?->name ?? '—') : '—' }}</td>
                                    <td class="py-2 px-4">{{ $s->max_orders }}</td>
                                    <td class="py-2 px-4">{{ $s->booked_count }}</td>
                                    <td class="py-2 px-4">{{ $s->is_active ? 'Sí' : 'No' }}</td>
                                    <td class="py-2 px-4 text-right whitespace-nowrap">
                                        <button type="button" wire:click="editSlot({{ $s->id }})" class="text-cyan-400 hover:underline">Editar</button>
                                        <button type="button" wire:click="deleteSlot({{ $s->id }})" wire:confirm="¿Eliminar franja?" class="ml-3 text-rose-400 hover:underline">Eliminar</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-6 px-4 text-center text-slate-500">Sin franjas para el filtro actual.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @elseif (! $deliveryMethodId)
        <div class="rounded-xl border border-slate-700/70 bg-slate-900/30 p-6 text-center text-sm text-slate-400">
            Después de guardar vas a poder administrar zonas (si es delivery) y franjas en esta pantalla de edición.
        </div>
    @endif
</div>
