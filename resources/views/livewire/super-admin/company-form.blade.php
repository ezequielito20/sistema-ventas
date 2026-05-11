<div class="space-y-6">
    <div class="ui-panel">
        <div class="ui-panel__header">
            <h1 class="ui-panel__title">Crear nuevo cliente</h1>
            <p class="ui-panel__subtitle">Registra una empresa y su usuario administrador.</p>
        </div>
        <div class="ui-panel__body">
            <form wire:submit="save" class="space-y-8">
                {{-- Datos de la Empresa --}}
                <div>
                    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-slate-300">Datos de la Empresa</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div class="sm:col-span-2">
                            <label for="companyName" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Nombre de la empresa *</label>
                            <input type="text" id="companyName" wire:model="companyName" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2.5 px-4 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500" placeholder="Nombre comercial" />
                            @error('companyName') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="companyNit" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">NIT *</label>
                            <input type="text" id="companyNit" wire:model="companyNit" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2.5 px-4 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500" placeholder="Número de identificación" />
                            @error('companyNit') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="mt-4">
                        <label for="email" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Correo de la empresa *</label>
                        <input type="email" id="email" wire:model="email" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2.5 px-4 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500" placeholder="correo@empresa.com" />
                        @error('email') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Datos del Administrador --}}
                <div>
                    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-slate-300">Datos del Usuario Administrador</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="adminName" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Nombre del administrador *</label>
                            <input type="text" id="adminName" wire:model="adminName" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2.5 px-4 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500" placeholder="Nombre completo" />
                            @error('adminName') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="adminEmail" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Correo del administrador *</label>
                            <input type="email" id="adminEmail" wire:model="adminEmail" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2.5 px-4 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500" placeholder="admin@empresa.com" />
                            @error('adminEmail') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="adminPassword" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Contraseña *</label>
                            <input type="password" id="adminPassword" wire:model="adminPassword" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2.5 px-4 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500" placeholder="Mínimo 8 caracteres" />
                            @error('adminPassword') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="adminPasswordConfirmation" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Confirmar contraseña *</label>
                            <input type="password" id="adminPasswordConfirmation" wire:model="adminPasswordConfirmation" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2.5 px-4 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500" placeholder="Repetí la contraseña" />
                        </div>
                    </div>
                </div>

                {{-- Plan y Fecha de Cobro --}}
                <div>
                    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-slate-300">Plan y Cobro</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <label for="planId" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Plan *</label>
                            <select id="planId" wire:model="planId" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2.5 px-3 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500">
                                <option value="">Seleccionar plan</option>
                                @foreach ($plans as $plan)
                                    <option value="{{ $plan->id }}">{{ $plan->name }} (${{ number_format($plan->base_price, 2) }})</option>
                                @endforeach
                            </select>
                            @error('planId') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="billingDay" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Día de cobro (1-28) *</label>
                            <input type="number" id="billingDay" wire:model="billingDay" min="1" max="28" class="w-full rounded-lg border border-slate-600 bg-slate-950/60 py-2.5 px-4 text-sm text-slate-100 focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500" />
                            @error('billingDay') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Botones --}}
                <div class="flex items-center justify-between border-t border-slate-700/50 pt-6">
                    <a href="{{ route('super-admin.companies.index') }}" class="ui-btn ui-btn-ghost" wire:navigate>
                        <i class="fas fa-arrow-left mr-1.5"></i> Cancelar
                    </a>
                    <div class="flex gap-2">
                        <button type="button" wire:click="saveAndCreateAnother" class="ui-btn ui-btn-ghost">
                            <i class="fas fa-plus-circle mr-1.5"></i> Guardar y crear otro
                        </button>
                        <button type="submit" class="ui-btn ui-btn-primary">
                            <i class="fas fa-save mr-1.5"></i> Guardar cliente
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
