<div class="space-y-6" wire:key="permission-form-{{ $permissionId ?? 'create' }}">
    <div class="ui-panel">
        <div class="ui-panel__header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="ui-panel__title">{{ $headingTitle }}</h1>
                <p class="ui-panel__subtitle">{{ $headingSubtitle }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a
                    href="{{ route('admin.permissions.index') }}"
                    class="ui-btn ui-btn-ghost text-sm md:py-2.5 md:px-5 md:text-[0.95rem]"
                    wire:navigate
                >
                    <i class="fas fa-arrow-left"></i> Volver al listado
                </a>
            </div>
        </div>
    </div>

    <div class="ui-panel">
        <div class="ui-panel__body">
            <form wire:submit="saveAndBack" class="space-y-6">
                <div>
                    <label for="permission-name" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">
                        Nombre del permiso <span class="text-rose-400">*</span>
                    </label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                            <i class="fas fa-key"></i>
                        </span>
                        <input
                            id="permission-name"
                            type="text"
                            wire:model.blur="name"
                            autocomplete="off"
                            placeholder="ej.: users.index"
                            class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2.5 pl-10 pr-3 font-mono text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500 @error('name') border-rose-500/80 @enderror"
                        />
                    </div>
                    @error('name')
                        <p class="mt-1.5 text-sm text-rose-300">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-xs text-slate-500">
                        Formato <span class="font-mono">modulo.accion</span> en minúsculas (solo letras y un punto). El prefijo del módulo debe estar autorizado.
                    </p>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-700/60 pt-6">
                    @unless ($isEdit)
                        <button
                            type="button"
                            wire:click="saveAndCreateAnother"
                            class="ui-btn ui-btn-ghost text-sm md:py-2.5 md:px-5 md:text-[0.95rem]"
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

                    <button type="submit" class="ui-btn ui-btn-primary text-sm md:py-2.5 md:px-5 md:text-[0.95rem]" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="saveAndBack">
                            <i class="fas fa-save"></i>
                            {{ $isEdit ? 'Guardar cambios' : 'Crear permiso' }}
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
