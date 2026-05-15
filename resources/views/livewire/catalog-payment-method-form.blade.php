<div class="space-y-6" wire:key="catalog-payment-method-form-{{ $paymentMethodId ?? 'new' }}">
    <div class="ui-panel">
        <div class="ui-panel__header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="ui-panel__title">{{ $headingTitle }}</h1>
                <p class="ui-panel__subtitle">Este método aparece en el checkout del catálogo. El descuento % se aplica al total en USD.</p>
            </div>
            <a
                href="{{ route('admin.catalog-payment-methods.index') }}"
                class="ui-btn ui-btn-ghost shrink-0 text-sm md:py-2.5 md:px-5 md:text-[0.95rem]"
                wire:navigate
            >
                <i class="fas fa-arrow-left"></i> Volver al listado
            </a>
        </div>
    </div>

    <div class="ui-panel">
        <div class="ui-panel__body">
            <form wire:submit="save" class="space-y-6">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Nombre <span class="text-rose-400">*</span></label>
                        <input type="text" wire:model.blur="name" class="w-full rounded-lg border border-slate-600 bg-slate-950 px-3 py-2 text-sm text-slate-100" />
                        @error('name')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Descuento %</label>
                        <input type="text" wire:model.blur="discountPercent" class="w-full rounded-lg border border-slate-600 bg-slate-950 px-3 py-2 text-sm text-slate-100" />
                        @error('discountPercent')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex items-end pb-2 md:self-end">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" wire:model="isActive" id="pm-active" class="rounded border-slate-600 bg-slate-900" />
                            <label for="pm-active" class="text-sm text-slate-300">Activo (visible en checkout)</label>
                        </div>
                    </div>
                    <div class="hidden md:block" aria-hidden="true"></div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Datos para el cliente (instrucciones)</label>
                        <textarea wire:model.blur="instructions" rows="4" class="w-full rounded-lg border border-slate-600 bg-slate-950 px-3 py-2 text-sm text-slate-100"></textarea>
                        @error('instructions')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="submit" class="ui-btn ui-btn-primary text-sm">
                        <i class="fas fa-save mr-2"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
