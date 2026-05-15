<div class="space-y-8" wire:key="catalog-delivery-method-form-{{ $deliveryMethodId ?? 'new' }}">
    <div class="ui-panel">
        <div class="ui-panel__header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="ui-panel__title">{{ $headingTitle }}</h1>
                <p class="ui-panel__subtitle">Entrega, delivery, zonas y franjas visibles para el cliente al cerrar el pedido.</p>
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
                            <option value="pickup">Entrega</option>
                            <option value="delivery">Delivery</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Nombre <span class="text-rose-400">*</span></label>
                        <input type="text" wire:model.blur="delName" class="w-full rounded-lg border border-slate-600 bg-slate-950 px-3 py-2 text-sm text-slate-100" />
                        @error('delName')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>
                    @if ($delType === 'pickup')
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Dirección de Entrega <span class="text-rose-400">*</span></label>
                            <input type="text" wire:model.blur="delPickupAddress" class="w-full rounded-lg border border-slate-600 bg-slate-950 px-3 py-2 text-sm text-slate-100" />
                            @error('delPickupAddress')
                                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>
                    @else
                        <div class="hidden md:block" aria-hidden="true"></div>
                    @endif
                    <div class="flex items-end pb-2 md:self-end">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" wire:model="delActive" id="dm-active" class="rounded border-slate-600 bg-slate-900" />
                            <label for="dm-active" class="text-sm text-slate-300">Activo</label>
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Instrucciones</label>
                        <textarea wire:model.blur="delInstructions" rows="3" class="w-full rounded-lg border border-slate-600 bg-slate-950 px-3 py-2 text-sm text-slate-100"></textarea>
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
                <p class="ui-panel__subtitle">Horario recurrente por semana: marcá uno o más días y la ventana de entrega desde / hasta (misma ventana para todos los del mismo alta). El cupo cuenta por día concreto al confirmar el pedido. Podés dar varios altas (ej.: sábado 12–17 y lun–vie 17–20).</p>
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
                        @if ($deliverySlotZoneFilterActive ?? false)
                            <p class="mt-2 text-xs text-amber-200/90">Estás viendo sólo franjas de esa zona. Si no aparece lo que cargaste en el alta (otra zona), pasá a &quot;Todas las zonas&quot; o pulsá:</p>
                            <button type="button" wire:click="clearSlotZoneFilter" class="mt-2 text-xs font-semibold text-cyan-400 hover:underline">
                                Ver franjas de todas las zonas
                            </button>
                        @endif
                    </div>
                @endif

                <form wire:submit="saveSlot" class="space-y-5">
                    @if ($methodModel->isDelivery())
                        <div class="max-w-xl">
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

                    @if ($slId)
                        <div class="grid gap-4 md:grid-cols-3">
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-400">Día</label>
                                <select wire:model="slWeekdayIso" class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-950 px-3 py-2 text-sm text-slate-100">
                                    @foreach (\App\Models\DeliverySlot::isoWeekdaysLabelsEs() as $iso => $lbl)
                                        <option value="{{ $iso }}">{{ $lbl }}</option>
                                    @endforeach
                                </select>
                                @error('slWeekdayIso')
                                    <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-400">Hora desde</label>
                                <input type="time" wire:model.live="slDeliveryFrom" class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-950 px-3 py-2 text-sm text-slate-100" />
                                @error('slDeliveryFrom')
                                    <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-400">Hora hasta</label>
                                <input type="time" wire:model.live="slDeliveryTo" class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-950 px-3 py-2 text-sm text-slate-100" />
                                @error('slDeliveryTo')
                                    <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    @else
                        <div>
                            <p class="mb-2 text-xs font-semibold text-slate-400">Días de la semana (uno o más)</p>
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4">
                                @foreach (\App\Models\DeliverySlot::isoWeekdaysLabelsEs() as $iso => $lbl)
                                    <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-700/80 bg-slate-950/50 px-3 py-2 text-sm text-slate-200 hover:border-cyan-600/50">
                                        <input type="checkbox" wire:model.live="slSelectedWeekdays" value="{{ $iso }}" wire:key="wd-new-{{ $iso }}" class="rounded border-slate-600" />
                                        <span>{{ $lbl }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('slSelectedWeekdays')
                                <p class="mt-2 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                            @error('slSelectedWeekdays.*')
                                <p class="mt-2 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2 max-w-xl">
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-400">Hora desde (compartida)</label>
                                <input type="time" wire:model.live="slDeliveryFrom" class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-950 px-3 py-2 text-sm text-slate-100" />
                                @error('slDeliveryFrom')
                                    <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-slate-400">Hora hasta (compartida)</label>
                                <input type="time" wire:model.live="slDeliveryTo" class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-950 px-3 py-2 text-sm text-slate-100" />
                                @error('slDeliveryTo')
                                    <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <p class="mt-1 max-w-xl text-xs text-slate-500">Para otro rango horario sobre otros días (u otra zona), repetí este alta después de borrar selección.</p>
                    @endif

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-400">Máx. pedidos</label>
                            <input type="number" wire:model="slMax" min="1" class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-950 px-3 py-2 text-sm text-slate-100" />
                            @error('slMax')
                                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-slate-500">Por fecha concreta de entrega (próxima ocurrencia de ese día dentro de la ventana).</p>
                        </div>
                        <div class="flex items-end pb-2">
                            <div class="flex items-center gap-2">
                                <input type="checkbox" wire:model="slActive" id="sl-active-dm" class="rounded border-slate-600" />
                                <label for="sl-active-dm" class="text-sm text-slate-300">Activo</label>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button type="submit" wire:loading.attr="disabled" wire:target="saveSlot" class="rounded-lg bg-cyan-600 px-4 py-2 text-sm font-medium text-white hover:bg-cyan-500 disabled:opacity-60">
                            <span wire:loading.remove wire:target="saveSlot">{{ $slId ? 'Guardar franja' : 'Agregar franja(s)' }}</span>
                            <span wire:loading wire:target="saveSlot">Guardando…</span>
                        </button>
                        @if ($slId)
                            <button type="button" wire:click="resetSlotForm" class="rounded-lg border border-slate-600 px-4 py-2 text-sm text-slate-300 hover:bg-slate-800">Cancelar</button>
                        @endif
                    </div>
                </form>

                @if ($deliverySlots->isEmpty())
                    <div class="rounded-lg border border-slate-600/60 bg-slate-950/60 p-4 text-xs leading-relaxed text-slate-400">
                        <p class="font-medium text-slate-300">La tabla muestra sólo las franjas ya guardadas en base de datos.</p>
                        <ul class="mt-2 list-inside list-disc space-y-1">
                            <li>Marcá al menos un día, completá «desde» y «hasta», y pulsá «Agregar franja(s)». Si algo falta, verás avisos encima.</li>
                            <li>Si el método es <strong class="font-semibold text-slate-300">delivery</strong>, elegí zona en el alta y revisá «Filtrar listado por zona»: si ese filtro apunta a otra zona que la del alta, no verás ninguna fila.</li>
                            <li>En el servidor ejecutá migraciones (<code class="rounded bg-slate-900 px-1 py-0.5 font-mono text-[0.65rem] text-cyan-200/90">php artisan migrate</code>) por la columna de hora hasta.</li>
                        </ul>
                    </div>
                @endif

                <div class="overflow-x-auto rounded-lg border border-slate-700/60">
                    <table class="min-w-full text-left text-sm text-slate-300">
                        <thead class="border-b border-slate-700 text-xs uppercase text-slate-500">
                            <tr>
                                <th class="py-3 px-4">Día</th>
                                <th class="py-3 px-4">Desde</th>
                                <th class="py-3 px-4">Hasta</th>
                                <th class="py-3 px-4">Máx.</th>
                                <th class="py-3 px-4">Activo</th>
                                <th class="py-3 px-4"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($deliverySlots as $s)
                                <tr wire:key="sl-{{ $s->id }}" class="border-b border-slate-800/80">
                                    <td class="py-2 px-4 whitespace-nowrap">{{ $s->weekdayLabelEs() }}</td>
                                    <td class="py-2 px-4 whitespace-nowrap">{{ $s->timeShort() }}</td>
                                    <td class="py-2 px-4 whitespace-nowrap">{{ $s->timeEndShort() }}</td>
                                    <td class="py-2 px-4">{{ $s->max_orders }}</td>
                                    <td class="py-2 px-4">{{ $s->is_active ? 'Sí' : 'No' }}</td>
                                    <td class="py-2 px-4 text-right whitespace-nowrap">
                                        <button type="button" wire:click="editSlot({{ $s->id }})" class="text-cyan-400 hover:underline">Editar</button>
                                        <button type="button" wire:click="deleteSlot({{ $s->id }})" wire:confirm="¿Eliminar franja?" class="ml-3 text-rose-400 hover:underline">Eliminar</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-6 px-4 space-y-3">
                                        @if (($deliverySlotZoneFilterActive ?? false) && ($deliverySlotsTotalForMethod ?? 0) > 0)
                                            <p class="text-center text-sm text-slate-300">Hay {{ $deliverySlotsTotalForMethod }} franja(s) en este método para otras zonas, pero ninguna coincide con la zona seleccionada en el filtro.</p>
                                            <p class="text-center">
                                                <button type="button" wire:click="clearSlotZoneFilter" class="text-cyan-400 hover:underline">Mostrar todas las zonas</button>
                                            </p>
                                        @elseif (($deliverySlotZoneFilterActive ?? false) && ($deliverySlotsTotalForMethod ?? 0) === 0)
                                            <p class="text-center text-sm text-slate-400">Hay un filtro de zona activo y aún no hay franjas guardadas para este método. Podés tener el filtro en una zona donde no cargaste datos.</p>
                                            <p class="text-center">
                                                <button type="button" wire:click="clearSlotZoneFilter" class="text-cyan-400 hover:underline">Quitar filtro y ver todas</button>
                                            </p>
                                        @else
                                            <p class="text-center text-sm text-slate-500">Sin franjas en el listado. Si pulsaste «Agregar franja(s)» y esto sigue igual, revisá días marcados, horarios y avisos de error arriba.</p>
                                        @endif
                                    </td>
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
