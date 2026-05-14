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
                <x-plan-limit-alert />
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
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">
                                Roles <span class="text-rose-400">*</span>
                            </label>

                            <div class="relative" x-data="{
                                search: '',
                                open: false,
                                allRoles: {{ Js::from($roleOptions) }},
                                get selectedIds() {
                                    return $wire.get('roleIds') || [];
                                },
                                get selectedRoles() {
                                    return this.allRoles.filter(r => this.selectedIds.includes(r.id));
                                },
                                get availableRoles() {
                                    const s = this.search.toLowerCase();
                                    return this.allRoles.filter(r =>
                                        !this.selectedIds.includes(r.id) &&
                                        r.name.toLowerCase().includes(s)
                                    );
                                },
                                addRole(id) {
                                    const current = [...this.selectedIds];
                                    if (!current.includes(id)) {
                                        current.push(id);
                                        $wire.set('roleIds', current);
                                    }
                                    this.search = '';
                                    this.open = false;
                                },
                                removeRole(id) {
                                    $wire.set('roleIds', this.selectedIds.filter(i => i !== id));
                                },
                                toggleDropdown() {
                                    this.open = !this.open;
                                    if (this.open) { this.search = ''; $nextTick(() => $refs.roleSearch?.focus()); }
                                }
                            }" @click.away="open = false">
                                {{-- Selected tags + trigger --}}
                                <div class="flex min-h-[42px] flex-wrap items-center gap-1.5 rounded-lg border border-slate-600 bg-slate-950/60 p-2 cursor-text"
                                    @click="toggleDropdown()">
                                    <template x-for="role in selectedRoles" :key="role.id">
                                        <span class="inline-flex items-center gap-1 rounded-md bg-cyan-500/15 px-2 py-0.5 text-xs font-medium text-cyan-300 border border-cyan-500/25">
                                            <span x-text="role.name"></span>
                                            <button type="button" @click.stop="removeRole(role.id)"
                                                class="ml-0.5 text-cyan-400 hover:text-rose-400 transition">
                                                <i class="fas fa-times text-[10px]"></i>
                                            </button>
                                        </span>
                                    </template>
                                    <span x-show="selectedRoles.length === 0" class="text-xs text-slate-500 px-1">Seleccionar roles...</span>
                                </div>

                                {{-- Dropdown --}}
                                <div x-show="open" x-cloak x-transition
                                    class="absolute z-50 mt-1 w-full rounded-xl border border-slate-600/50 bg-slate-900/95 shadow-2xl backdrop-blur-xl overflow-hidden"
                                    style="display: none;">
                                    <div class="border-b border-slate-700/50 p-2">
                                        <input type="text" x-ref="roleSearch" x-model="search"
                                            placeholder="Buscar rol..."
                                            class="w-full rounded-lg border border-slate-600 bg-slate-950/60 px-3 py-2 text-xs text-slate-100 placeholder:text-slate-500 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500">
                                    </div>
                                    <div class="max-h-44 overflow-y-auto py-1">
                                        <template x-for="role in availableRoles" :key="role.id">
                                            <button type="button" @click="addRole(role.id)"
                                                class="w-full px-4 py-2.5 text-left text-sm text-slate-200 hover:bg-slate-700/60 hover:text-white transition-all duration-150 border-b border-slate-700/30 last:border-b-0">
                                                <span x-text="role.name"></span>
                                            </button>
                                        </template>
                                        <div x-show="availableRoles.length === 0" class="px-4 py-6 text-center text-sm text-slate-500">
                                            <span x-show="search">No se encontraron roles.</span>
                                            <span x-show="!search">Todos los roles fueron seleccionados.</span>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            @error('roleIds')
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
