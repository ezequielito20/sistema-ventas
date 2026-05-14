<div class="space-y-6" wire:key="category-form-{{ $categoryId ?? 'create' }}">
    <div class="ui-panel">
        <div class="ui-panel__header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="ui-panel__title">{{ $headingTitle }}</h1>
                <p class="ui-panel__subtitle">{{ $headingSubtitle }}</p>
            </div>
            <a
                href="{{ route('admin.categories.index') }}"
                class="ui-btn ui-btn-ghost text-sm md:px-5 md:py-2.5 md:text-[0.95rem]"
                wire:navigate
            >
                <i class="fas fa-arrow-left"></i> Volver al listado
            </a>
        </div>
    </div>

    <div class="ui-panel">
        <div class="ui-panel__body">
            <form wire:submit="saveAndBack" class="space-y-6">
                <x-plan-limit-alert />
                <div>
                    <label for="category-name" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">
                        Nombre <span class="text-rose-400">*</span>
                    </label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                            <i class="fas fa-tag"></i>
                        </span>
                        <input
                            id="category-name"
                            type="text"
                            wire:model.blur="name"
                            autocomplete="off"
                            placeholder="Ej.: Electrónicos, Ropa, Hogar…"
                            class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2.5 pl-10 pr-3 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500 @error('name') border-rose-500/80 @enderror"
                        />
                    </div>
                    @error('name')
                        <p class="mt-1.5 text-sm text-rose-300">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-xs text-slate-500">Solo letras, números, espacios y guiones. Debe ser único dentro de tu empresa.</p>
                </div>

                <div>
                    <label for="category-description" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">
                        Descripción
                    </label>
                    <div class="relative">
                        <span class="pointer-events-none absolute left-0 top-3 flex items-start pl-3 text-slate-500">
                            <i class="fas fa-align-left"></i>
                        </span>
                        <textarea
                            id="category-description"
                            wire:model.blur="description"
                            rows="4"
                            placeholder="Opcional. Ayuda a tu equipo a entender el uso de la categoría."
                            class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2.5 pl-10 pr-3 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500 @error('description') border-rose-500/80 @enderror"
                        ></textarea>
                    </div>
                    @error('description')
                        <p class="mt-1.5 text-sm text-rose-300">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-700/60 pt-6">
                    @unless ($isEdit)
                        <button
                            type="button"
                            wire:click="saveAndCreateAnother"
                            class="ui-btn ui-btn-ghost text-sm md:px-5 md:py-2.5 md:text-[0.95rem]"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove wire:target="saveAndCreateAnother">
                                <i class="fas fa-plus-circle"></i>
                                Crear y crear otro
                            </span>
                            <span wire:loading wire:target="saveAndCreateAnother" class="inline-flex items-center gap-2">
                                <i class="fas fa-spinner fa-spin"></i>
                                Guardando…
                            </span>
                        </button>
                    @endunless

                    <button type="submit" class="ui-btn ui-btn-primary text-sm md:px-5 md:py-2.5 md:text-[0.95rem]" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="saveAndBack">
                            <i class="fas fa-save"></i>
                            {{ $isEdit ? 'Guardar cambios' : 'Crear categoría' }}
                        </span>
                        <span wire:loading wire:target="saveAndBack" class="inline-flex items-center gap-2">
                            <i class="fas fa-spinner fa-spin"></i>
                            Guardando…
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
