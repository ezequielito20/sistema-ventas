<div class="py-6 px-4 sm:px-6 lg:px-8">
    @php $exchangeRate = \App\Models\ExchangeRate::current(); @endphp

    {{-- Encabezado --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-100">Lista de mercado</h1>
            <p class="text-sm text-slate-400 mt-1">
                Tasa BCV: <span class="text-blue-400 font-bold">Bs. {{ number_format($exchangeRate, 2) }}</span>
                @if($lowStockCount > 0)
                    · <span class="text-amber-400 font-medium">{{ $lowStockCount }} productos</span> necesitan compra
                    @if($outOfStockCount > 0)
                        · <span class="text-rose-400 font-medium">{{ $outOfStockCount }} agotados</span>
                    @endif
                @else
                    · Todo en orden
                @endif
            </p>
        </div>
        @if($canCreate && !$hasActiveList && $lowStockCount > 0)
            <button wire:click="generate" wire:loading.attr="disabled"
                class="px-5 py-2.5 text-sm font-medium rounded-lg bg-emerald-600 text-white hover:bg-emerald-500 disabled:opacity-50 transition-colors shadow-lg shadow-emerald-900/30">
                <i class="fas fa-shopping-cart mr-2"></i> Generar lista de mercado
            </button>
        @endif
    </div>

    {{-- Confirmación --}}
    @if($showGenerateConfirm)
    <div class="mb-6 bg-amber-500/10 border border-amber-500/30 rounded-xl p-4">
        <div class="flex items-center gap-3 flex-wrap">
            <i class="fas fa-info-circle text-amber-400 text-lg"></i>
            <p class="text-sm text-slate-200 flex-1">Ya hay una lista activa. ¿Generar una nueva sumando?</p>
            <div class="flex gap-2">
                <button wire:click="$set('showGenerateConfirm', false)" class="px-3 py-1.5 text-xs font-medium rounded-lg bg-slate-700 text-slate-300 hover:bg-slate-600">Cancelar</button>
                <button wire:click="generate" class="px-3 py-1.5 text-xs font-medium rounded-lg bg-amber-600 text-white hover:bg-amber-500">Sí, sumar</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Productos que necesitan compra (vista previa) --}}
    @if(!$hasActiveList && $productsToBuy->count() > 0)
    <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl overflow-hidden mb-6">
        <div class="p-4 border-b border-slate-700/50">
            <h2 class="text-sm font-medium text-slate-200">Productos que necesitan compra</h2>
        </div>
        <div class="divide-y divide-slate-700/30">
            @foreach($productsToBuy as $product)
                @php $rowTotal = $product->purchase_price * $product->to_buy; @endphp
                <div class="flex items-center justify-between p-3 hover:bg-slate-700/20 transition-colors">
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <div class="h-9 w-9 rounded-lg bg-slate-700 flex items-center justify-center flex-shrink-0 overflow-hidden">
                            @if($product->image)
                                <img src="{{ $product->image_url }}" class="h-full w-full object-cover">
                            @else
                                <i class="fas fa-box text-slate-400 text-xs"></i>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-200 truncate">{{ $product->name }}</p>
                            <p class="text-xs text-slate-500">
                                Tenés: <span class="{{ $product->quantity <= 0 ? 'text-rose-400 font-medium' : 'text-slate-400' }}">{{ $product->quantity }}</span>
                                · Mín: {{ $product->min_quantity }}
                                · Faltan: <span class="text-amber-400 font-medium">{{ $product->to_buy }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        @if($product->quantity <= 0)
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-rose-500/20 text-rose-300 border border-rose-500/30">Agotado</span>
                        @elseif($product->quantity < $product->min_quantity / 2)
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30">Muy bajo</span>
                        @else
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-orange-500/20 text-orange-300 border border-orange-500/30">Bajo</span>
                        @endif
                        <div class="text-right">
                            <p class="text-sm text-slate-200 font-medium">${{ number_format($rowTotal, 2) }}</p>
                            <p class="text-xs text-amber-400 font-medium">Bs. {{ number_format($rowTotal * $exchangeRate, 2) }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @php $totalEst = $productsToBuy->sum(fn($p) => $p->purchase_price * $p->to_buy); @endphp
        <div class="p-3 border-t border-slate-700/50 flex justify-between items-center bg-slate-700/20">
            <span class="text-sm text-slate-400">Total estimado para stock óptimo</span>
            <div class="text-right">
                <span class="text-lg font-semibold text-slate-100">${{ number_format($totalEst, 2) }}</span>
                <br><span class="text-xs text-amber-400 font-medium">Bs. {{ number_format($totalEst * $exchangeRate, 2) }}</span>
            </div>
        </div>
    </div>
    @endif

    {{-- Lista activa --}}
    @if($activeList)
    <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl overflow-hidden mb-6">
        {{-- Header --}}
        <div class="p-4 border-b border-slate-700/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-lg bg-emerald-500/20 flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-emerald-400"></i>
                </div>
                <div>
                    <h2 class="text-sm font-medium text-slate-200">Lista activa</h2>
                    <p class="text-xs text-slate-500">
                        Generada {{ $activeList->generated_at->diffForHumans() }}
                        · Tasa: <span class="text-blue-400">Bs. {{ number_format($exchangeRate, 2) }}</span>
                    </p>
                </div>
            </div>
            <div class="flex gap-2 flex-wrap">
                <button wire:click="$toggle('showPriceEditor')"
                    class="px-3 py-1.5 text-xs font-medium rounded-lg {{ $showPriceEditor ? 'bg-blue-600 text-white' : 'bg-slate-700 text-slate-300 hover:bg-slate-600' }} transition-colors">
                    <i class="fas fa-tag mr-1"></i> Precios
                </button>
                <button wire:click="cancelList" wire:confirm="¿Cancelar esta lista?"
                    class="px-3 py-1.5 text-xs font-medium rounded-lg bg-slate-700 text-slate-300 hover:bg-slate-600">Cancelar</button>
                <a href="{{ route('admin.home.shopping-list.mobile') }}"
                    class="px-3 py-1.5 text-xs font-medium rounded-lg bg-emerald-600 text-white hover:bg-emerald-500">
                    <i class="fas fa-mobile-alt mr-1"></i> Modo supermercado
                </a>
                <button wire:click="complete"
                    class="px-3 py-1.5 text-xs font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-500">
                    <i class="fas fa-check mr-1"></i> Completar
                </button>
            </div>
        </div>

        {{-- Items --}}
        @if($activeList->items->count() > 0)
            @php
                $totalLista = 0;
                $purchasedCount = collect($this->checkedItems)->filter()->count();
            @endphp
            <div class="divide-y divide-slate-700/30">
                @foreach($activeList->items as $item)
                    @php
                        $isChecked = $this->checkedItems[$item->id] ?? false;
                        $qty = $this->quantities[$item->id] ?? $item->suggested_quantity;
                        $unitPrice = $item->product?->purchase_price ?? 0;
                        $itemTotal = $unitPrice * $qty;
                        $totalLista += $itemTotal;
                    @endphp
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 hover:bg-slate-700/20 transition-colors {{ $isChecked ? 'bg-emerald-500/5' : '' }}">
                        <div class="flex items-center gap-3 flex-1 min-w-0 mb-2 sm:mb-0">
                            <button wire:click="toggleItem({{ $item->id }})"
                                class="h-6 w-6 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-all duration-200
                                {{ $isChecked ? 'bg-emerald-500 border-emerald-500 scale-110' : 'border-slate-500 hover:border-emerald-400' }}">
                                @if($isChecked) <i class="fas fa-check text-white text-xs"></i> @endif
                            </button>
                            <div class="{{ $isChecked ? 'opacity-60' : '' }}">
                                <p class="text-sm font-medium {{ $isChecked ? 'line-through text-slate-500' : 'text-slate-200' }}">
                                    {{ $item->name_snapshot }}
                                    @if($item->product && $item->product->quantity <= 0 && !$isChecked)
                                        <span class="text-[10px] font-bold uppercase ml-1.5 px-1.5 py-0.5 rounded bg-rose-500/20 text-rose-300">Urgente</span>
                                    @endif
                                </p>
                                <p class="text-xs text-slate-500">
                                    Tenés: {{ $item->product?->quantity ?? '—' }} · Mín: {{ $item->product?->min_quantity ?? '—' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-1">
                                <button wire:click="changeQuantity({{ max(0, $qty - 1) }}, {{ $item->id }})"
                                    class="h-7 w-7 rounded-lg bg-slate-700 text-slate-300 hover:bg-slate-600 flex items-center justify-center text-sm">−</button>
                                <span class="w-8 text-center text-sm font-bold text-slate-200">{{ $qty }}</span>
                                <button wire:click="changeQuantity({{ $qty + 1 }}, {{ $item->id }})"
                                    class="h-7 w-7 rounded-lg bg-slate-700 text-slate-300 hover:bg-slate-600 flex items-center justify-center text-sm">+</button>
                            </div>
                            @if($showPriceEditor)
                                <div class="relative">
                                    <span class="absolute left-2 top-1/2 -translate-y-1/2 text-slate-400 text-xs">$</span>
                                    <input type="number" step="0.01" min="0"
                                        value="{{ $this->marketPrices[$item->id] ?? $unitPrice }}"
                                        wire:model.blur="marketPrices.{{ $item->id }}"
                                        class="w-20 pl-5 pr-2 py-1.5 rounded-lg border-slate-600 bg-slate-700 text-slate-200 text-sm font-bold text-center focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            @endif
                            <div class="text-right min-w-[90px]">
                                <p class="text-sm font-bold text-slate-200">
                                    ${{ number_format(($this->marketPrices[$item->id] ?? $unitPrice) * $qty, 2) }}
                                </p>
                                <p class="text-xs text-amber-400 font-medium">
                                    Bs. {{ number_format(($this->marketPrices[$item->id] ?? $unitPrice) * $qty * $exchangeRate, 2) }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($showPriceEditor)
                <div class="p-3 border-t border-slate-700/30 bg-blue-500/5 flex items-center justify-between">
                    <span class="text-xs text-slate-400">Editando precios de mercado — los cambios se guardan al confirmar</span>
                    <button wire:click="saveMarketPrices"
                        class="px-4 py-2 text-xs font-bold rounded-lg bg-blue-600 text-white hover:bg-blue-500 transition-colors">
                        <i class="fas fa-save mr-1"></i> Guardar precios
                    </button>
                </div>
            @endif

            {{-- Totales --}}
            <div class="p-3 border-t border-slate-700/50 bg-slate-700/20">
                <div class="flex flex-col sm:flex-row justify-between gap-2">
                    <div class="text-sm text-slate-400">
                        {{ $purchasedCount }}/{{ $activeList->items->count() }} comprados ·
                        <span class="text-emerald-400">${{ number_format(collect($this->checkedItems)->filter()->keys()->sum(fn($id) => ($activeList->items->firstWhere('id', $id)?->product?->purchase_price ?? 0) * ($this->quantities[$id] ?? $activeList->items->firstWhere('id', $id)?->suggested_quantity ?? 0)), 2) }}</span> gastados
                    </div>
                    <div class="text-right">
                        <span class="text-slate-400 text-sm">Total estimado: </span>
                        <span class="text-lg font-bold text-slate-100">${{ number_format($totalLista, 2) }}</span>
                        <br>
                        <span class="text-xs text-amber-400 font-medium">Bs. {{ number_format($totalLista * $exchangeRate, 2) }}</span>
                    </div>
                </div>
            </div>
        @else
            <div class="p-8 text-center">
                <div class="h-12 w-12 rounded-full bg-emerald-500/10 flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-check text-emerald-400 text-xl"></i>
                </div>
                <p class="text-sm text-slate-300 font-medium">¡Todo en stock óptimo!</p>
                <p class="text-xs text-slate-500 mt-1">No hay productos que necesiten compra.</p>
            </div>
        @endif
    </div>
    @endif

    {{-- Empty state --}}
    @if(!$hasActiveList && $productsToBuy->count() === 0)
    <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-8 text-center mb-6">
        <div class="h-14 w-14 rounded-full bg-emerald-500/10 flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-check-circle text-emerald-400 text-2xl"></i>
        </div>
        <h2 class="text-lg font-medium text-slate-200 mb-1">Todo al día</h2>
        <p class="text-sm text-slate-500 mb-4">Todos los productos están en nivel óptimo.</p>
        <button wire:click="generate" class="px-4 py-2 text-sm font-medium rounded-lg bg-slate-700 text-slate-300 hover:bg-slate-600">
            <i class="fas fa-sync-alt mr-1"></i> Generar lista igualmente
        </button>
    </div>
    @endif

    {{-- Historial --}}
    @if($lists->count() > 0)
    <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl overflow-hidden">
        <div class="p-4 border-b border-slate-700/50 flex items-center justify-between">
            <h2 class="text-sm font-medium text-slate-200">Historial de listas</h2>
            <span class="text-xs text-slate-500">{{ $lists->total() }} listas</span>
        </div>
        <div class="divide-y divide-slate-700/30">
            @foreach($lists as $list)
                <div class="flex items-center justify-between p-3 hover:bg-slate-700/20 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="h-8 w-8 rounded-lg {{ $list->is_completed ? 'bg-emerald-500/20' : 'bg-amber-500/20' }} flex items-center justify-center">
                            <i class="fas {{ $list->is_completed ? 'fa-check text-emerald-400' : 'fa-clock text-amber-400' }} text-xs"></i>
                        </div>
                        <div>
                            <p class="text-sm text-slate-200">{{ $list->generated_at->format('d/m/Y') }}</p>
                            <p class="text-xs text-slate-500">{{ $list->items_count }} items · {{ $list->is_completed ? 'Completada' : 'Pendiente' }}</p>
                        </div>
                    </div>
                    @if(!$list->is_completed && !$hasActiveList)
                        <button wire:click="$set('activeListId', {{ $list->id }}); $set('hasActiveList', true)"
                            class="text-xs text-blue-400 hover:text-blue-300">Reanudar</button>
                    @endif
                </div>
            @endforeach
        </div>
        @if($lists->hasPages())
            <div class="p-3 border-t border-slate-700/50">{{ $lists->links() }}</div>
        @endif
    </div>
    @else
    <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-6 text-center">
        <p class="text-sm text-slate-500">Aún no se generaron listas.</p>
    </div>
    @endif
</div>
