<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-100">Servicios</h1>
            <p class="text-sm text-slate-400 mt-1">Agua, luz, internet y más</p>
        </div>
        <button wire:click="openCreate"
            class="px-4 py-2 text-sm font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-500 transition-colors">
            <i class="fas fa-plus mr-1"></i> Nuevo servicio
        </button>
    </div>

    <div class="mb-4">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar servicio..."
            class="w-full max-w-sm rounded-lg border-slate-600 bg-slate-700 text-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
    </div>

    <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl overflow-hidden">
        @if($services->count() > 0)
            <div class="divide-y divide-slate-700/30">
                @foreach($services as $service)
                    <div class="flex items-center justify-between p-4 hover:bg-slate-700/20 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-lg bg-yellow-500/20 flex items-center justify-center">
                                <i class="fas fa-bolt text-yellow-400"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-200">{{ $service->name }}</p>
                                @if($service->provider)
                                    <p class="text-xs text-slate-500">{{ $service->provider }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button wire:click="openEdit({{ $service->id }})"
                                class="p-1.5 rounded-lg text-slate-400 hover:text-blue-400 hover:bg-slate-700">
                                <i class="fas fa-edit text-xs"></i>
                            </button>
                            <button wire:click="delete({{ $service->id }})"
                                wire:confirm="¿Eliminar este servicio?"
                                class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-slate-700">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-6 text-center">
                <p class="text-sm text-slate-500">Sin servicios registrados.</p>
            </div>
        @endif
    </div>

    @if($showForm)
    <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/60" wire:click="closeForm"></div>
            <div class="relative bg-slate-800 border border-slate-700/50 rounded-xl shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-medium text-slate-100 mb-4">{{ $editingId ? 'Editar' : 'Nuevo' }} servicio</h3>
                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Nombre</label>
                        <input type="text" wire:model="name"
                            class="mt-1 block w-full rounded-lg border-slate-600 bg-slate-700 text-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('name') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Proveedor</label>
                        <input type="text" wire:model="provider"
                            class="mt-1 block w-full rounded-lg border-slate-600 bg-slate-700 text-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">N° de contrato</label>
                        <input type="text" wire:model="contract_number"
                            class="mt-1 block w-full rounded-lg border-slate-600 bg-slate-700 text-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="closeForm"
                            class="px-4 py-2 text-sm rounded-lg bg-slate-700 text-slate-300 hover:bg-slate-600">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="px-4 py-2 text-sm rounded-lg bg-blue-600 text-white hover:bg-blue-500">
                            {{ $editingId ? 'Actualizar' : 'Crear' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
