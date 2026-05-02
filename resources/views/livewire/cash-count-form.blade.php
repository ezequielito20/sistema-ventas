@php
    $inputBase = 'w-full rounded-lg border border-slate-600 bg-slate-950/60 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500';
    $labelBase = 'mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400';
@endphp

<div class="space-y-6" wire:key="cash-count-form">
    {{-- ================================================================ --}}
    {{-- HEADER                                                           --}}
    {{-- ================================================================ --}}
    <div class="ui-panel overflow-hidden">
        <div class="ui-panel__header flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="ui-panel__title">{{ $title }}</h1>
                <p class="ui-panel__subtitle">
                    @if ($isEdit)
                        Modificá los datos del arqueo seleccionado
                    @else
                        Registrá la apertura de una nueva caja
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.cash-counts.index') }}"
                    class="ui-btn ui-btn-ghost text-sm md:py-2.5 md:px-5 md:text-[0.95rem]"
                    wire:navigate>
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- FORM                                                             --}}
    {{-- ================================================================ --}}
    <div class="ui-panel">
        <div class="ui-panel__header">
            <h2 class="ui-panel__title">Datos de Apertura</h2>
            <p class="ui-panel__subtitle">Completá los campos para {{ $isEdit ? 'actualizar' : 'abrir' }} la caja.</p>
        </div>

        <div class="ui-panel__body">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                {{-- Fecha --}}
                <div>
                    <label for="openingDate" class="{{ $labelBase }}">Fecha de apertura <span class="text-red-400">*</span></label>
                    <input type="date" id="openingDate" wire:model="openingDate"
                        class="{{ $inputBase }}" style="color-scheme: dark;" required>
                    @error('openingDate') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                {{-- Hora --}}
                <div>
                    <label for="openingTime" class="{{ $labelBase }}">Hora de apertura <span class="text-red-400">*</span></label>
                    <input type="time" id="openingTime" wire:model="openingTime"
                        class="{{ $inputBase }}" style="color-scheme: dark;" required>
                    @error('openingTime') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                {{-- Monto inicial --}}
                <div>
                    <label for="initialAmount" class="{{ $labelBase }}">Monto inicial ({{ $currencySymbol }}) <span class="text-red-400">*</span></label>
                    <input type="number" id="initialAmount" wire:model="initialAmount"
                        step="0.01" min="0"
                        class="{{ $inputBase }}" required>
                    @error('initialAmount') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Observaciones --}}
            <div class="mt-4">
                <label for="observations" class="{{ $labelBase }}">Observaciones</label>
                <textarea id="observations" wire:model="observations"
                    rows="3" maxlength="1000"
                    class="{{ $inputBase }}"
                    placeholder="Notas adicionales (opcional)..."></textarea>
                @error('observations') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>

            {{-- Monto final (solo en edición) --}}
            @if ($isEdit)
                <div class="mt-6 rounded-lg border border-slate-700 bg-slate-900/60 p-4">
                    <div class="mb-3 flex items-center gap-2">
                        <i class="fas fa-lock text-sm text-slate-400"></i>
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-300">Cerrar Caja</h3>
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="finalAmount" class="{{ $labelBase }}">Monto final ({{ $currencySymbol }})</label>
                            <input type="number" id="finalAmount" wire:model="finalAmount"
                                step="0.01" min="0"
                                class="{{ $inputBase }}"
                                placeholder="Ingresá el monto final para cerrar...">
                            @error('finalAmount') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex items-end">
                            <button type="button" wire:click="openCloseModal"
                                class="ui-btn ui-btn-success w-full text-sm md:py-2.5 md:px-5 md:text-[0.95rem]">
                                <i class="fas fa-lock"></i> Cerrar caja
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Actions --}}
        <div class="ui-panel__footer flex items-center justify-end gap-3">
            <a href="{{ route('admin.cash-counts.index') }}"
                class="ui-btn ui-btn-ghost text-sm md:py-2.5 md:px-5 md:text-[0.95rem]"
                wire:navigate>
                Cancelar
            </a>
            <button type="button" wire:click="save"
                class="ui-btn ui-btn-primary text-sm md:py-2.5 md:px-5 md:text-[0.95rem]">
                <i class="fas fa-save"></i> {{ $isEdit ? 'Actualizar' : 'Abrir caja' }}
            </button>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- MODAL: CONFIRMAR CIERRE                                          --}}
    {{-- ================================================================ --}}
    @if ($showCloseModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-[#020617]/90 p-4 backdrop-blur-md"
            wire:click.self="$set('showCloseModal', false)" x-data x-on:keydown.escape.window="$wire.set('showCloseModal', false)">
            <div class="relative w-full max-w-lg overflow-hidden rounded-2xl border border-slate-600 bg-slate-900 text-slate-100 shadow-[0_25px_80px_rgba(0,0,0,0.75),inset_0_1px_0_rgba(255,255,255,0.06)]">
                <div class="border-b border-slate-700 bg-slate-900 px-5 pb-4 pt-5">
                    <div class="flex items-start gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-amber-500/40 bg-amber-950 text-amber-200">
                            <i class="fas fa-lock text-lg"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-base font-semibold text-white">¿Cerrar caja?</h3>
                            <p class="mt-1.5 text-sm leading-relaxed text-slate-300">
                                El monto final será <span class="font-medium text-white">{{ $currencySymbol }} {{ number_format((float) $finalAmount, 2) }}</span>.
                                No se podrán registrar más movimientos en esta caja.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap justify-end gap-2 border-t border-slate-700 bg-slate-950 px-4 py-3">
                    <button type="button" wire:click="$set('showCloseModal', false)" class="ui-btn ui-btn-ghost text-sm">Cancelar</button>
                    <button type="button" wire:click="confirmClose" class="ui-btn ui-btn-success text-sm">
                        <i class="fas fa-lock mr-1.5"></i> Sí, cerrar caja
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
