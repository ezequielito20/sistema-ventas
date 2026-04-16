<div class="space-y-6" wire:key="user-form-{{ $userId ?? 'create' }}">
    <div class="ui-panel">
        <div class="ui-panel__header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="ui-panel__title">{{ $headingTitle }}</h1>
                <p class="ui-panel__subtitle">{{ $headingSubtitle }}</p>
            </div>
            <a
                href="{{ route('admin.users.index') }}"
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
                <section class="space-y-4">
                    <div>
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-300">Datos del usuario</h2>
                        <p class="mt-1 text-sm text-slate-500">Completa la información principal y asigna un rol.</p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label for="user-name" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">
                                Nombre <span class="text-rose-400">*</span>
                            </label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                                    <i class="fas fa-user"></i>
                                </span>
                                <input
                                    id="user-name"
                                    type="text"
                                    wire:model.blur="name"
                                    autocomplete="name"
                                    placeholder="Ej.: María Fernanda López"
                                    class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2.5 pl-10 pr-3 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500 @error('name') border-rose-500/80 @enderror"
                                />
                            </div>
                            @error('name')
                                <p class="mt-1.5 text-sm text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="user-email" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">
                                Correo electrónico <span class="text-rose-400">*</span>
                            </label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input
                                    id="user-email"
                                    type="email"
                                    wire:model.blur="email"
                                    autocomplete="email"
                                    placeholder="usuario@empresa.com"
                                    class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2.5 pl-10 pr-3 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500 @error('email') border-rose-500/80 @enderror"
                                />
                            </div>
                            @error('email')
                                <p class="mt-1.5 text-sm text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="user-role" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">
                                Rol principal <span class="text-rose-400">*</span>
                            </label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                                    <i class="fas fa-user-shield"></i>
                                </span>
                                <select
                                    id="user-role"
                                    wire:model.live="roleId"
                                    class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2.5 pl-10 pr-10 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500 @error('roleId') border-rose-500/80 @enderror"
                                >
                                    <option value="">Selecciona un rol</option>
                                    @foreach ($roleOptions as $roleOption)
                                        <option value="{{ $roleOption['id'] }}">{{ $roleOption['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('roleId')
                                <p class="mt-1.5 text-sm text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </section>

                <section class="space-y-4 border-t border-slate-700/60 pt-6">
                    <div>
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-300">
                            {{ $isEdit ? 'Cambio de contraseña' : 'Credenciales de acceso' }}
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ $isEdit ? 'Déjalo vacío si no deseas modificar la contraseña actual.' : 'La contraseña inicial debe cumplir las reglas de seguridad.' }}
                        </p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="user-password" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">
                                {{ $isEdit ? 'Nueva contraseña' : 'Contraseña' }} <span class="text-rose-400">{{ $isEdit ? '' : '*' }}</span>
                            </label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input
                                    id="user-password"
                                    type="password"
                                    wire:model.blur="password"
                                    autocomplete="new-password"
                                    placeholder="{{ $isEdit ? 'Solo si deseas reemplazarla' : 'Mínimo 8 caracteres' }}"
                                    class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2.5 pl-10 pr-3 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500 @error('password') border-rose-500/80 @enderror"
                                />
                            </div>
                            @error('password')
                                <p class="mt-1.5 text-sm text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="user-password-confirmation" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">
                                Confirmar contraseña <span class="text-rose-400">{{ $isEdit ? '' : '*' }}</span>
                            </label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                                    <i class="fas fa-shield-alt"></i>
                                </span>
                                <input
                                    id="user-password-confirmation"
                                    type="password"
                                    wire:model.blur="password_confirmation"
                                    autocomplete="new-password"
                                    placeholder="Repite la contraseña"
                                    class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2.5 pl-10 pr-3 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500 @error('password_confirmation') border-rose-500/80 @enderror"
                                />
                            </div>
                            @error('password_confirmation')
                                <p class="mt-1.5 text-sm text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <p class="text-xs text-slate-500">Requiere al menos una mayúscula, una minúscula y un número.</p>
                </section>

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
                                Guardando...
                            </span>
                        </button>
                    @endunless

                    <button
                        type="submit"
                        class="ui-btn ui-btn-primary text-sm md:px-5 md:py-2.5 md:text-[0.95rem]"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove wire:target="saveAndBack">
                            <i class="fas fa-save"></i>
                            {{ $isEdit ? 'Guardar cambios' : 'Crear usuario' }}
                        </span>
                        <span wire:loading wire:target="saveAndBack" class="inline-flex items-center gap-2">
                            <i class="fas fa-spinner fa-spin"></i>
                            Guardando...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
