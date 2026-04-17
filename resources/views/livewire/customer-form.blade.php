@php
    /** Clases Tailwind en variable PHP no siempre entran en el purge; el aspecto real lo define .customer-form-v2__input en app.scss */
    $inputBase = 'customer-form-v2__input';
    $labelBase = 'mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400';
@endphp

<div class="customer-form-v2 space-y-6" wire:key="customer-form-{{ $customerId ?? 'create' }}">
    <div class="ui-panel">
        <div class="ui-panel__header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="ui-panel__title">{{ $headingTitle }}</h1>
                <p class="ui-panel__subtitle">{{ $headingSubtitle }}</p>
            </div>
            <a
                href="{{ route('admin.customers.index') }}"
                class="ui-btn ui-btn-ghost shrink-0 text-sm md:py-2.5 md:px-5 md:text-[0.95rem]"
                wire:navigate
            >
                <i class="fas fa-arrow-left"></i> Volver al listado
            </a>
        </div>
    </div>

    <div class="ui-panel">
        <div class="ui-panel__body">
            <form wire:submit="saveAndBack" class="space-y-6">
                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-slate-200">Datos del cliente</h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <div class="min-w-0">
                            <label for="customer_name" class="{{ $labelBase }}">Nombre <span class="text-rose-400">*</span></label>
                            <div class="relative">
                                <span class="customer-form-v2__input-icon" aria-hidden="true">
                                    <i class="fas fa-user"></i>
                                </span>
                                <input
                                    id="customer_name"
                                    type="text"
                                    wire:model.blur="name"
                                    class="{{ $inputBase }} customer-form-v2__input--icon-start @error('name') customer-form-v2__input--error @enderror"
                                    autocomplete="off"
                                >
                            </div>
                            @error('name')
                                <p class="mt-1.5 text-sm text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="min-w-0">
                            <label for="nit_number" class="{{ $labelBase }}">Cédula / NIT</label>
                            <input
                                id="nit_number"
                                type="text"
                                wire:model.blur="nit_number"
                                class="{{ $inputBase }} @error('nit_number') customer-form-v2__input--error @enderror"
                                autocomplete="off"
                            >
                            @error('nit_number')
                                <p class="mt-1.5 text-sm text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="min-w-0">
                            <label for="phone" class="{{ $labelBase }}">Teléfono</label>
                            <input
                                id="phone"
                                type="tel"
                                wire:model.blur="phone"
                                maxlength="20"
                                class="{{ $inputBase }} @error('phone') customer-form-v2__input--error @enderror"
                                autocomplete="off"
                            >
                            @error('phone')
                                <p class="mt-1.5 text-sm text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="min-w-0">
                            <label for="email" class="{{ $labelBase }}">Correo electrónico</label>
                            <input
                                id="email"
                                type="email"
                                wire:model.blur="email"
                                class="{{ $inputBase }} @error('email') customer-form-v2__input--error @enderror"
                                autocomplete="off"
                            >
                            @error('email')
                                <p class="mt-1.5 text-sm text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        @if ($customerId !== null)
                            <div class="min-w-0 md:col-span-2 xl:col-span-4">
                                <label for="total_debt" class="{{ $labelBase }}">Deuda total registrada</label>
                                <div class="relative">
                                    <span class="customer-form-v2__input-icon" aria-hidden="true">
                                        <i class="fas fa-dollar-sign"></i>
                                    </span>
                                    <input
                                        id="total_debt"
                                        type="text"
                                        inputmode="decimal"
                                        wire:model.blur="total_debt"
                                        class="{{ $inputBase }} customer-form-v2__input--icon-start @error('total_debt') customer-form-v2__input--error @enderror"
                                    >
                                </div>
                                @error('total_debt')
                                    <p class="mt-1.5 text-sm text-rose-300">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-slate-500">
                                    Refleja la deuda operativa del cliente; los pagos y ventas siguen las reglas del sistema.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex flex-col gap-3 border-t border-slate-700/50 pt-6 sm:flex-row sm:justify-end">
                    <button
                        type="submit"
                        class="ui-btn ui-btn-primary justify-center text-sm sm:min-w-[10rem]"
                        wire:loading.attr="disabled"
                        wire:target="saveAndBack,saveAndCreateAnother"
                    >
                        <span wire:loading.remove wire:target="saveAndBack,saveAndCreateAnother">
                            <i class="fas fa-save"></i> {{ $customerId !== null ? 'Guardar cambios' : 'Guardar' }}
                        </span>
                        <span wire:loading wire:target="saveAndBack,saveAndCreateAnother" class="inline-flex items-center gap-2">
                            <i class="fas fa-spinner fa-spin"></i> Guardando…
                        </span>
                    </button>

                    @if ($customerId === null)
                        <button
                            type="button"
                            class="ui-btn ui-btn-ghost justify-center text-sm sm:min-w-[12rem]"
                            wire:click="saveAndCreateAnother"
                            wire:loading.attr="disabled"
                            wire:target="saveAndBack,saveAndCreateAnother"
                        >
                            <span wire:loading.remove wire:target="saveAndCreateAnother">
                                <i class="fas fa-plus-circle"></i> Guardar y crear otro
                            </span>
                            <span wire:loading wire:target="saveAndCreateAnother" class="inline-flex items-center gap-2">
                                <i class="fas fa-spinner fa-spin"></i> Guardando…
                            </span>
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
