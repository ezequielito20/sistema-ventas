<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-100">Transacciones</h1>
            <p class="text-sm text-slate-400 mt-1">Ingresos y gastos del hogar</p>
        </div>
        <button wire:click="openCreate"
            class="px-4 py-2 text-sm font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-500 transition-colors">
            <i class="fas fa-plus mr-1"></i> Nueva transacción
        </button>
    </div>

    <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-3 flex-wrap">
            <select wire:model.live="type"
                class="rounded-lg border-slate-600 bg-slate-700 text-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Todos los tipos</option>
                <option value="income">Ingresos</option>
                <option value="expense">Gastos</option>
            </select>
            <select wire:model.live="category"
                class="rounded-lg border-slate-600 bg-slate-700 text-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Todas las categorías</option>
                @foreach($categories as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            <input type="date" wire:model.live="dateFrom"
                class="rounded-lg border-slate-600 bg-slate-700 text-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
            <input type="date" wire:model.live="dateTo"
                class="rounded-lg border-slate-600 bg-slate-700 text-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar..."
                class="flex-1 rounded-lg border-slate-600 bg-slate-700 text-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
            @if($filtersOpen)
                <button wire:click="$set('type', ''); $set('category', ''); $set('dateFrom', ''); $set('dateTo', ''); $set('search', '')"
                    class="px-3 py-2 text-xs font-medium rounded-lg bg-slate-700 text-slate-300 hover:bg-slate-600">
                    <i class="fas fa-times mr-1"></i>
                </button>
            @endif
        </div>
    </div>

    <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl overflow-hidden">
        @if($transactions->count() > 0)
            <div class="divide-y divide-slate-700/30">
                @foreach($transactions as $txn)
                    <div class="flex items-center justify-between p-4 hover:bg-slate-700/20 transition-colors">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <div class="h-10 w-10 rounded-lg {{ $txn->type === 'income' ? 'bg-emerald-500/20' : 'bg-rose-500/20' }} flex items-center justify-center flex-shrink-0">
                                <i class="fas {{ $txn->type === 'income' ? 'fa-arrow-up text-emerald-400' : 'fa-arrow-down text-rose-400' }}"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-slate-200 truncate">{{ $txn->description ?? ucfirst($txn->category) }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ $categories[$txn->category] ?? $txn->category }}
                                    · {{ $txn->transaction_date->format('d/m/Y') }}
                                    @if($txn->bankAccount) · {{ $txn->bankAccount->bank_name }} @endif
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-medium {{ $txn->type === 'income' ? 'text-emerald-400' : 'text-rose-400' }}">
                                {{ $txn->type === 'income' ? '+' : '-' }}${{ number_format($txn->amount, 2) }}
                            </span>
                            <div class="flex gap-1">
                                <button wire:click="edit({{ $txn->id }})"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-blue-400 hover:bg-slate-700">
                                    <i class="fas fa-edit text-xs"></i>
                                </button>
                                <button wire:click="delete({{ $txn->id }})"
                                    wire:confirm="¿Eliminar esta transacción?"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-slate-700">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="p-4 border-t border-slate-700/50">
                {{ $transactions->links() }}
            </div>
        @else
            <div class="p-6 text-center">
                <p class="text-sm text-slate-500">Sin transacciones registradas.</p>
            </div>
        @endif
    </div>

    @if($showForm)
    <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/60" wire:click="closeForm"></div>
            <div class="relative bg-slate-800 border border-slate-700/50 rounded-xl shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-medium text-slate-100 mb-4">{{ $editingId ? 'Editar' : 'Nueva' }} transacción</h3>
                <form wire:submit="save" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300">Tipo</label>
                            <select wire:model="form_type"
                                class="mt-1 block w-full rounded-lg border-slate-600 bg-slate-700 text-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="expense">Gasto</option>
                                <option value="income">Ingreso</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300">Categoría</label>
                            <select wire:model="form_category"
                                class="mt-1 block w-full rounded-lg border-slate-600 bg-slate-700 text-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                                @foreach($categories as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300">Monto</label>
                            <input type="number" wire:model="form_amount" step="0.01" min="0.01"
                                class="mt-1 block w-full rounded-lg border-slate-600 bg-slate-700 text-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300">Fecha</label>
                            <input type="date" wire:model="form_date"
                                class="mt-1 block w-full rounded-lg border-slate-600 bg-slate-700 text-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Descripción</label>
                        <input type="text" wire:model="form_description"
                            class="mt-1 block w-full rounded-lg border-slate-600 bg-slate-700 text-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Cuenta (opcional)</label>
                        <select wire:model="form_bank_account_id"
                            class="mt-1 block w-full rounded-lg border-slate-600 bg-slate-700 text-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Sin cuenta</option>
                            @foreach($bankAccounts as $account)
                                <option value="{{ $account->id }}">{{ $account->bank_name }} ({{ $account->masked_number }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Comprobante</label>
                        <input type="file" wire:model="form_receipt" accept="image/*"
                            class="mt-1 block w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:bg-blue-500/20 file:text-blue-400 hover:file:bg-blue-500/30">
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
