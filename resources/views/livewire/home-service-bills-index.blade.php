<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-100">Facturas de servicios</h1>
            <p class="text-sm text-slate-400 mt-1">Control de pagos de servicios básicos</p>
        </div>
        <button wire:click="openCreate"
            class="px-4 py-2 text-sm font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-500 transition-colors">
            <i class="fas fa-plus mr-1"></i> Nueva factura
        </button>
    </div>

    <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-3">
            <select wire:model.live="service_id"
                class="rounded-lg border-slate-600 bg-slate-700 text-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Todos los servicios</option>
                @foreach($services as $service)
                    <option value="{{ $service->id }}">{{ $service->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="status"
                class="rounded-lg border-slate-600 bg-slate-700 text-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Todos los estados</option>
                <option value="unpaid">Sin pagar</option>
                <option value="paid">Pagadas</option>
                <option value="due_soon">Vencen pronto</option>
            </select>
            @if($filtersOpen)
                <button wire:click="$set('service_id', ''); $set('status', '')"
                    class="px-3 py-2 text-xs font-medium rounded-lg bg-slate-700 text-slate-300 hover:bg-slate-600">
                    <i class="fas fa-times mr-1"></i> Limpiar
                </button>
            @endif
        </div>
    </div>

    <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl overflow-hidden">
        @if($bills->count() > 0)
            <div class="divide-y divide-slate-700/30">
                @foreach($bills as $bill)
                    <div class="flex items-center justify-between p-4 hover:bg-slate-700/20 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-lg bg-blue-500/20 flex items-center justify-center">
                                <i class="fas fa-file-invoice text-blue-400"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-200">{{ $bill->service->name }} · {{ $bill->period }}</p>
                                <p class="text-xs text-slate-500">
                                    Vence: {{ $bill->due_date->format('d/m/Y') }}
                                    @if($bill->cutoff_date) · Corte: {{ $bill->cutoff_date->format('d/m/Y') }} @endif
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="text-right">
                                <p class="text-sm font-medium text-slate-100">${{ number_format($bill->amount, 2) }}</p>
                                @if($bill->is_paid)
                                    <span class="text-xs text-emerald-400">Pagada {{ $bill->paid_at->format('d/m/Y') }}</span>
                                @else
                                    <span class="text-xs text-rose-400">Pendiente</span>
                                @endif
                            </div>
                            <div class="flex gap-1">
                                @if(!$bill->is_paid)
                                    <button wire:click="markAsPaid({{ $bill->id }})"
                                        wire:confirm="¿Marcar como pagada?"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-emerald-400 hover:bg-slate-700">
                                        <i class="fas fa-check text-xs"></i>
                                    </button>
                                @endif
                                <button wire:click="delete({{ $bill->id }})"
                                    wire:confirm="¿Eliminar esta factura?"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-slate-700">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="p-4 border-t border-slate-700/50">
                {{ $bills->links() }}
            </div>
        @else
            <div class="p-6 text-center">
                <p class="text-sm text-slate-500">Sin facturas registradas.</p>
            </div>
        @endif
    </div>

    @if($showForm)
    <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/60" wire:click="closeForm"></div>
            <div class="relative bg-slate-800 border border-slate-700/50 rounded-xl shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-medium text-slate-100 mb-4">{{ $editingId ? 'Editar' : 'Nueva' }} factura</h3>
                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Servicio</label>
                        <select wire:model="home_service_id"
                            class="mt-1 block w-full rounded-lg border-slate-600 bg-slate-700 text-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Seleccionar...</option>
                            @foreach($services as $service)
                                <option value="{{ $service->id }}">{{ $service->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300">Período</label>
                            <input type="text" wire:model="period" placeholder="YYYY-MM"
                                class="mt-1 block w-full rounded-lg border-slate-600 bg-slate-700 text-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300">Monto</label>
                            <input type="number" wire:model="amount" step="0.01" min="0"
                                class="mt-1 block w-full rounded-lg border-slate-600 bg-slate-700 text-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300">Vence</label>
                            <input type="date" wire:model="due_date"
                                class="mt-1 block w-full rounded-lg border-slate-600 bg-slate-700 text-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300">Corte</label>
                            <input type="date" wire:model="cutoff_date"
                                class="mt-1 block w-full rounded-lg border-slate-600 bg-slate-700 text-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Imagen</label>
                        <input type="file" wire:model="bill_image" accept="image/*"
                            class="mt-1 block w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:bg-blue-500/20 file:text-blue-400 hover:file:bg-blue-500/30">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Notas</label>
                        <textarea wire:model="notes" rows="2"
                            class="mt-1 block w-full rounded-lg border-slate-600 bg-slate-700 text-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
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
