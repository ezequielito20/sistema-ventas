<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-100">Lista de mercado</h1>
            <p class="text-sm text-slate-400 mt-1">Generá tu lista y llevala al supermercado</p>
        </div>
        @if($canCreate)
            <button wire:click="generate" wire:loading.attr="disabled"
                class="px-4 py-2 text-sm font-medium rounded-lg bg-emerald-600 text-white hover:bg-emerald-500 disabled:opacity-50 transition-colors">
                <i class="fas fa-sync-alt mr-1"></i> Generar lista
            </button>
        @endif
    </div>

    @if($showGenerateConfirm)
    <div class="mb-6 bg-amber-500/10 border border-amber-500/30 rounded-xl p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <i class="fas fa-info-circle text-amber-400"></i>
                <p class="text-sm text-slate-200">Ya hay una lista activa. ¿Generar una nueva sumando cantidades?</p>
            </div>
            <div class="flex gap-2">
                <button wire:click="$set('showGenerateConfirm', false)"
                    class="px-3 py-1.5 text-xs font-medium rounded-lg bg-slate-700 text-slate-300 hover:bg-slate-600">
                    Cancelar
                </button>
                <button wire:click="generate"
                    class="px-3 py-1.5 text-xs font-medium rounded-lg bg-amber-600 text-white hover:bg-amber-500">
                    Sí, generar sumando
                </button>
            </div>
        </div>
    </div>
    @endif

    @if($activeList)
    <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl overflow-hidden mb-6">
        <div class="p-4 border-b border-slate-700/50 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-medium text-slate-200">Lista activa</h2>
                <p class="text-xs text-slate-500">Generada {{ $activeList->generated_at->diffForHumans() }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.home.shopping-list.mobile') }}"
                    class="px-3 py-1.5 text-xs font-medium rounded-lg bg-emerald-600 text-white hover:bg-emerald-500 transition-colors">
                    <i class="fas fa-mobile-alt mr-1"></i> Modo supermercado
                </a>
                <button wire:click="complete"
                    class="px-3 py-1.5 text-xs font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-500 transition-colors">
                    <i class="fas fa-check mr-1"></i> Completar lista
                </button>
            </div>
        </div>

        @if($activeList->items->count() > 0)
            <div class="divide-y divide-slate-700/30">
                @foreach($activeList->items as $item)
                    <div class="flex items-center justify-between p-3 hover:bg-slate-700/20 transition-colors">
                        <div class="flex items-center gap-3 flex-1">
                            <input type="checkbox" wire:click="toggleItem({{ $item->id }})" {{ $item->is_purchased ? 'checked' : '' }}
                                class="rounded border-slate-600 bg-slate-700 text-emerald-500 focus:ring-emerald-500">
                            <div class="{{ $item->is_purchased ? 'line-through text-slate-500' : 'text-slate-200' }}">
                                <p class="text-sm font-medium">{{ $item->name_snapshot }}</p>
                                <p class="text-xs text-slate-500">
                                    Tenés: {{ $item->product?->quantity ?? '—' }} ·
                                    Óptimo: {{ $item->product?->min_quantity ?? '—' }} ·
                                    A comprar: {{ $item->suggested_quantity }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="number" value="{{ $item->actual_purchased_quantity ?? $item->suggested_quantity }}" min="0"
                                wire:change="updatePurchasedQuantity({{ $item->id }}, $event.target.value)"
                                class="w-16 rounded border-slate-600 bg-slate-700 text-slate-200 text-xs text-center">
                            @if($item->product && $item->product->purchase_price > 0)
                                <span class="text-xs text-slate-500 w-16 text-right">
                                    ${{ number_format($item->product->purchase_price * $item->suggested_quantity, 2) }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            @php
                $total = $activeList->items->sum(fn($i) => ($i->product?->purchase_price ?? 0) * $i->suggested_quantity);
            @endphp
            <div class="p-3 border-t border-slate-700/50 flex justify-between items-center">
                <span class="text-sm text-slate-400">Total estimado</span>
                <span class="text-lg font-semibold text-slate-100">${{ number_format($total, 2) }}</span>
            </div>
        @else
            <div class="p-6 text-center">
                <p class="text-sm text-slate-500">No hay productos con stock bajo. ¡Todo en orden!</p>
            </div>
        @endif
    </div>
    @endif

    <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl overflow-hidden">
        <div class="p-4 border-b border-slate-700/50">
            <h2 class="text-sm font-medium text-slate-200">Listas anteriores</h2>
        </div>
        @if($lists->count() > 0)
            <div class="divide-y divide-slate-700/30">
                @foreach($lists as $list)
                    <div class="flex items-center justify-between p-3">
                        <div>
                            <p class="text-sm text-slate-200">{{ $list->generated_at->format('d/m/Y H:i') }}</p>
                            <p class="text-xs text-slate-500">{{ $list->items_count }} items · {{ $list->is_completed ? 'Completada' : 'Pendiente' }}</p>
                        </div>
                        <span class="text-xs {{ $list->is_completed ? 'text-emerald-400' : 'text-amber-400' }}">
                            {{ $list->is_completed ? '✓ Completada' : '○ Pendiente' }}
                        </span>
                    </div>
                @endforeach
            </div>
            {{ $lists->links() }}
        @else
            <div class="p-6 text-center">
                <p class="text-sm text-slate-500">No hay listas generadas aún.</p>
            </div>
        @endif
    </div>
</div>
