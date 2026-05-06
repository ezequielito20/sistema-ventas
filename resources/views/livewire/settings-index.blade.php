@php
    $inputBase = 'w-full rounded-lg border border-slate-600 bg-slate-950/60 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500';
    $labelBase = 'mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400';
    $selectBase = 'w-full rounded-lg border border-slate-600 bg-slate-950/60 px-3 py-2 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500';
@endphp

<div class="space-y-6" wire:key="settings-index">

    {{-- ================================================================ --}}
    {{-- HEADER                                                           --}}
    {{-- ================================================================ --}}
    <div class="ui-panel overflow-hidden">
        <div class="ui-panel__header flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="ui-panel__title">Configuración</h1>
                <p class="ui-panel__subtitle">Datos de la empresa, impuestos y ubicación.</p>
            </div>
        </div>
    </div>

    <form>

        {{-- ================================================================ --}}
        {{-- INFORMACIÓN BÁSICA                                             --}}
        {{-- ================================================================ --}}
        <div class="ui-panel overflow-hidden">
            <div class="ui-panel__header">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-cyan-500/20 text-cyan-300">
                        <i class="fas fa-building text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-white">Información Básica</h2>
                        <p class="text-xs text-slate-400">Nombre, contacto y datos fiscales</p>
                    </div>
                </div>
            </div>
            <div class="p-5">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label class="{{ $labelBase }}" for="name">Nombre de la empresa</label>
                        <input type="text" id="name" wire:model="name" class="{{ $inputBase }}" placeholder="Nombre de la empresa">
                        @error('name') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $labelBase }}" for="business_type">Tipo de negocio</label>
                        <input type="text" id="business_type" wire:model="business_type" class="{{ $inputBase }}" placeholder="Ej: Comercio, Servicios">
                        @error('business_type') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $labelBase }}" for="nit">Cédula / NIT</label>
                        <input type="text" id="nit" wire:model="nit" class="{{ $inputBase }}" placeholder="Número de identificación">
                        @error('nit') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $labelBase }}" for="phone">Teléfono</label>
                        <input type="text" id="phone" wire:model="phone" class="{{ $inputBase }}" placeholder="Número de teléfono">
                        @error('phone') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $labelBase }}" for="email">Correo electrónico</label>
                        <input type="email" id="email" wire:model="email" class="{{ $inputBase }}" placeholder="correo@empresa.com">
                        @error('email') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $labelBase }}" for="ig">Instagram</label>
                        <input type="text" id="ig" wire:model="ig" class="{{ $inputBase }}" placeholder="@usuario">
                        @error('ig') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================================ --}}
        {{-- IMPUESTOS                                                       --}}
        {{-- ================================================================ --}}
        <div class="ui-panel mt-4 overflow-hidden">
            <div class="ui-panel__header">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-500/20 text-indigo-300">
                        <i class="fas fa-file-invoice-dollar text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-white">Impuestos</h2>
                        <p class="text-xs text-slate-400">Nombre y porcentaje del impuesto aplicado</p>
                    </div>
                </div>
            </div>
            <div class="p-5">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label class="{{ $labelBase }}" for="tax_name">Nombre del impuesto</label>
                        <input type="text" id="tax_name" wire:model="tax_name" class="{{ $inputBase }}" placeholder="Ej: IVA, GST">
                        @error('tax_name') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $labelBase }}" for="tax_amount">Porcentaje (%)</label>
                        <input type="number" id="tax_amount" wire:model="tax_amount" class="{{ $inputBase }}" placeholder="Ej: 19" step="1">
                        @error('tax_amount') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $labelBase }}" for="currency">Moneda</label>
                        <select id="currency" wire:model="currency" class="{{ $selectBase }}">
                            <option value="">Seleccionar moneda</option>
                            @foreach ($currencies as $c)
                                <option value="{{ $c->code }}" {{ $currency === $c->code ? 'selected' : '' }}>
                                    {{ $c->code }} — {{ $c->symbol }} ({{ $c->name }})
                                </option>
                            @endforeach
                        </select>
                        @error('currency') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================================ --}}
        {{-- UBICACIÓN                                                       --}}
        {{-- ================================================================ --}}
        <div class="ui-panel mt-4 overflow-hidden">
            <div class="ui-panel__header">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-500/20 text-emerald-300">
                        <i class="fas fa-map-marker-alt text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-white">Ubicación</h2>
                        <p class="text-xs text-slate-400">País, dirección y datos de localización</p>
                    </div>
                </div>
            </div>
            <div class="p-5">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label class="{{ $labelBase }}" for="country_id">País</label>
                        <select id="country_id" wire:model.live="country_id" class="{{ $selectBase }}">
                            <option value="">Seleccionar país</option>
                            @foreach ($countries as $c)
                                <option value="{{ $c->id }}" {{ $country_id == (string) $c->id ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('country_id') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $labelBase }}" for="state_id">Estado / Provincia</label>
                        <select id="state_id" wire:model.live="state_id" class="{{ $selectBase }}">
                            <option value="">Seleccionar estado</option>
                            @foreach ($states as $s)
                                <option value="{{ $s->id }}" {{ $state_id == (string) $s->id ? 'selected' : '' }}>
                                    {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('state_id') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $labelBase }}" for="city_id">Ciudad</label>
                        <select id="city_id" wire:model="city_id" class="{{ $selectBase }}">
                            <option value="">Seleccionar ciudad</option>
                            @foreach ($cities as $ci)
                                <option value="{{ $ci->id }}" {{ $city_id == (string) $ci->id ? 'selected' : '' }}>
                                    {{ $ci->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('city_id') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $labelBase }}" for="postal_code">Código postal</label>
                        <input type="text" id="postal_code" wire:model="postal_code" class="{{ $inputBase }}" placeholder="Código postal">
                        @error('postal_code') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="mt-4">
                    <label class="{{ $labelBase }}" for="address">Dirección completa</label>
                    <textarea id="address" wire:model="address" rows="2" class="{{ $inputBase }}" placeholder="Dirección de la empresa"></textarea>
                    @error('address') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- ================================================================ --}}
        {{-- LOGO                                                            --}}
        {{-- ================================================================ --}}
        <div class="ui-panel mt-4 overflow-hidden">
            <div class="ui-panel__header">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-purple-500/20 text-purple-300">
                        <i class="fas fa-image text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-white">Logo de la empresa</h2>
                        <p class="text-xs text-slate-400">Imagen visible en reportes PDF (máx. 2MB, JPEG/PNG)</p>
                    </div>
                </div>
            </div>
            <div class="p-5">
                <div class="flex flex-col items-center gap-4 sm:flex-row sm:items-start">
                    {{-- Logo preview — new upload takes priority over saved logo --}}
                    <div class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-600/50 bg-slate-950/60">
                        @if ($logo)
                            <img src="{{ $logo->temporaryUrl() }}" alt="Nuevo logo" class="h-full w-full object-contain">
                        @elseif ($current_logo_url)
                            <img src="{{ $current_logo_url }}" alt="Logo actual" class="h-full w-full object-contain"
                                onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                            <div class="hidden h-full w-full flex-col items-center justify-center text-slate-500">
                                <i class="fas fa-image text-xl"></i>
                                <span class="mt-0.5 text-[9px]">Sin logo</span>
                            </div>
                        @else
                            <div class="flex h-full w-full flex-col items-center justify-center text-slate-500">
                                <i class="fas fa-image text-xl"></i>
                                <span class="mt-0.5 text-[9px]">Sin logo</span>
                            </div>
                        @endif
                    </div>

                    {{-- Upload area --}}
                    <div class="flex-1">
                        <label for="logo" class="group relative flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-600/60 bg-slate-950/40 px-6 py-5 text-center transition hover:border-cyan-500/40 hover:bg-cyan-500/5">
                            <i class="fas fa-cloud-upload-alt mb-2 text-xl text-slate-400 group-hover:text-cyan-400"></i>
                            <span class="text-sm font-medium text-slate-300 group-hover:text-cyan-300">Arrastrá o hacé clic para subir</span>
                            <span class="mt-1 text-xs text-slate-500">JPEG, PNG — máx 2MB</span>
                        </label>
                        <input type="file" id="logo" wire:model="logo" accept="image/jpeg,image/png,image/jpg" class="hidden">
                        @error('logo')
                            <p class="mt-2 text-xs text-rose-400">{{ $message }}</p>
                        @enderror

                        @if ($logo)
                            <div class="mt-2 flex items-center gap-2">
                                <span class="text-xs text-slate-400">{{ $logo->getClientOriginalName() }}</span>
                                <span class="text-xs text-emerald-400">✓ Nueva imagen</span>
                                <button type="button" wire:click="$set('logo', null)" class="text-xs text-rose-400 hover:text-rose-300 transition">
                                    <i class="fas fa-times-circle mr-0.5"></i>Quitar
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================================ --}}
        {{-- SAVE BUTTON                                                     --}}
        {{-- ================================================================ --}}
        <div class="mt-6 flex items-center justify-end gap-3">
            <a href="{{ route('admin.index') }}" class="ui-btn ui-btn-ghost">
                <i class="fas fa-arrow-left mr-1.5"></i> Volver
            </a>
            <button
                type="button"
                class="ui-btn ui-btn-primary"
                wire:loading.attr="disabled"
                x-on:click="
                    Swal.fire({
                        title: '¿Guardar cambios?',
                        text: 'Se actualizarán los datos de la empresa. ¿Está seguro?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#10b981',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Sí, guardar',
                        cancelButtonText: 'Cancelar',
                        background: '#0f172a',
                        color: '#e2e8f0',
                        customClass: { popup: 'border border-slate-700 rounded-xl' }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $wire.save();
                        }
                    });
                "
            >
                <span wire:loading.remove><i class="fas fa-save mr-1.5"></i> Guardar cambios</span>
                <span wire:loading><i class="fas fa-spinner fa-spin mr-1.5"></i> Guardando...</span>
            </button>
        </div>

    </form>
</div>