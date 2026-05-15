@extends('layouts.app')

@section('title', 'Lista de mercado — Modo supermercado')

@php
    $exchangeRate = \App\Models\ExchangeRate::current();
@endphp

@section('content')
<div class="min-h-screen pb-44" x-data="supermarketList()" x-init="init()">
    {{-- Header --}}
    <div class="sticky top-0 z-20 bg-slate-900/95 backdrop-blur-sm border-b border-slate-700/50 px-4 py-3">
        <div class="flex items-center justify-between mb-2">
            <div>
                <h1 class="text-base font-semibold text-slate-100">🛒 Factura de mercado</h1>
                <p class="text-xs text-slate-400">
                    Tasa BCV: <span class="text-blue-400 font-bold">Bs. {{ number_format($exchangeRate, 2) }}</span>
                    · <span x-text="'Gasto: Bs. ' + bsFormat(checkedTotal)"></span>
                </p>
            </div>
            <button @click="resetAll()"
                class="px-3 py-1.5 text-xs font-medium rounded-lg bg-slate-700 text-slate-300 hover:bg-slate-600 transition-colors">
                <i class="fas fa-redo mr-1"></i> Reiniciar
            </button>
        </div>
        {{-- Barra de progreso --}}
        <div class="flex items-center gap-3 text-xs">
            <div class="flex-1 h-1.5 bg-slate-700 rounded-full overflow-hidden">
                <div class="h-full bg-emerald-500 rounded-full transition-all duration-500"
                    :style="'width: ' + progressPercent + '%'"></div>
            </div>
            <span class="text-slate-400 font-medium whitespace-nowrap" x-text="checkedCount + '/' + totalCount + ' comprados'"></span>
        </div>
    </div>

    @php
        $activeList = \App\Models\Home\HomeShoppingList::where('company_id', auth()->user()->company_id)
            ->active()
            ->with(['items.product:id,name,brand,purchase_price,unit,image,quantity,min_quantity'])
            ->first();
    @endphp

    @if($activeList && $activeList->items->count() > 0)
        {{-- Items --}}
        <div class="px-3 py-4 space-y-2">
            @foreach($activeList->items as $item)
                @php
                    $defaultPrice = $item->product?->purchase_price ?? 0;
                    $isUrgent = $item->product && $item->product->quantity <= 0;
                    $isLow = $item->product && $item->product->quantity > 0 && $item->product->quantity < $item->product->min_quantity / 2;
                @endphp
                <div class="relative bg-slate-800/80 border rounded-xl overflow-hidden transition-all duration-200"
                    :class="checked_{{ $item->id }}
                        ? 'border-emerald-500/40 bg-emerald-500/[0.04]'
                        : 'border-slate-700/50'">

                    {{-- Top: nombre + check + total --}}
                    <div @click="checked_{{ $item->id }} = !checked_{{ $item->id }}"
                        class="flex items-start gap-3 p-3 cursor-pointer select-none active:bg-slate-700/20">
                        {{-- Checkbox --}}
                        <div class="mt-0.5 h-7 w-7 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-all duration-300"
                            :class="checked_{{ $item->id }}
                                ? 'bg-emerald-500 border-emerald-500 scale-110 shadow-lg shadow-emerald-500/30'
                                : 'border-slate-500'">
                            <template x-if="checked_{{ $item->id }}">
                                <i class="fas fa-check text-white text-sm"></i>
                            </template>
                        </div>

                        {{-- Info del producto --}}
                        <div class="flex-1 min-w-0" :class="checked_{{ $item->id }} ? 'opacity-60' : ''">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-semibold text-slate-100 truncate"
                                    :class="checked_{{ $item->id }} ? 'line-through text-slate-500' : ''">
                                    {{ $item->name_snapshot }}
                                </p>
                                @if($isUrgent)
                                    <span class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded-full bg-rose-500/20 text-rose-300 border border-rose-500/30 flex-shrink-0">Urgente</span>
                                @elseif($isLow)
                                    <span class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30 flex-shrink-0">Bajo</span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5" :class="checked_{{ $item->id }} ? 'text-slate-600' : ''">
                                Tenés: <span class="{{ $item->product && $item->product->quantity <= 0 ? 'text-rose-400 font-medium' : 'text-slate-400' }}">{{ $item->product?->quantity ?? '—' }}</span>
                                · Mín: {{ $item->product?->min_quantity ?? '—' }}
                            </p>
                        </div>

                        {{-- Total producto USD + Bs --}}
                        <div class="text-right flex-shrink-0" :class="checked_{{ $item->id }} ? 'opacity-60' : ''">
                            <p class="text-sm font-bold text-slate-200"
                                x-text="'$' + (price_{{ $item->id }} * qty_{{ $item->id }}).toFixed(2)"
                                :class="checked_{{ $item->id }} ? 'line-through text-slate-600' : ''">
                            </p>
                            <p class="text-[10px] text-amber-400 font-medium"
                                x-text="bsFormat(price_{{ $item->id }} * qty_{{ $item->id }})">
                            </p>
                        </div>
                    </div>

                    {{-- Controles: cantidad + precio --}}
                    <div class="border-t border-slate-700/30 px-3 py-2.5 bg-slate-900/30 space-y-2">
                        <div class="grid grid-cols-5 gap-2">
                            {{-- Cantidad (3 columnas) --}}
                            <div class="col-span-3">
                                <label class="text-[10px] uppercase tracking-wider text-slate-500 font-medium mb-1.5 block">
                                    <i class="fas fa-sort-amount-up-alt mr-1"></i> Cantidad
                                </label>
                                <div class="flex items-center gap-1">
                                    <button @click="qty_{{ $item->id }} = Math.max(0, qty_{{ $item->id }} - 1)"
                                        class="h-10 w-10 rounded-lg bg-slate-700 text-slate-300 hover:bg-slate-600 flex items-center justify-center transition-colors active:scale-90">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" x-model.number="qty_{{ $item->id }}" min="0"
                                        class="flex-1 text-center rounded-lg border-slate-600 bg-slate-700 text-slate-200 text-base font-bold h-10 focus:border-emerald-500 focus:ring-emerald-500">
                                    <button @click="qty_{{ $item->id }}++"
                                        class="h-10 w-10 rounded-lg bg-slate-700 text-slate-300 hover:bg-slate-600 flex items-center justify-center transition-colors active:scale-90">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            {{-- Precio (2 columnas) --}}
                            <div class="col-span-2">
                                <label class="text-[10px] uppercase tracking-wider text-slate-500 font-medium mb-1.5 block">
                                    <i class="fas fa-tag mr-1"></i> Precio
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-bold">$</span>
                                    <input type="number" x-model.number="price_{{ $item->id }}" min="0" step="0.01"
                                        class="w-full h-10 pl-7 pr-3 rounded-lg border-slate-600 bg-slate-700 text-slate-200 text-base font-bold focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>

                        {{-- Desglose: unidades + subtotal en USD y Bs --}}
                        <div class="flex justify-between items-center pt-1">
                            <span class="text-xs text-slate-500" x-text="qty_{{ $item->id }} + ' unid. × $' + price_{{ $item->id }}.toFixed(2)"></span>
                            <div class="text-right">
                                <span class="text-sm font-bold text-slate-200" x-text="'$' + (price_{{ $item->id }} * qty_{{ $item->id }}).toFixed(2)"></span>
                                <span class="text-xs text-amber-400 font-medium ml-2" x-text="'Bs. ' + bsFormat(price_{{ $item->id }} * qty_{{ $item->id }})"></span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- FACTURA TOTAL --}}
        <div class="fixed bottom-0 left-0 right-0 z-20 bg-slate-900/98 backdrop-blur-sm border-t border-slate-700/50 shadow-2xl">
            {{-- Detalle factura --}}
            <div class="px-4 pt-3 pb-2 space-y-1.5 text-sm border-b border-slate-700/30">
                <div class="flex justify-between text-slate-400">
                    <span>Productos a comprar</span>
                    <span class="text-slate-200 font-medium" x-text="totalCount"></span>
                </div>
                <div class="flex justify-between text-slate-400">
                    <span>Comprados</span>
                    <span class="text-emerald-400 font-medium" x-text="checkedCount"></span>
                </div>
                <div class="flex justify-between text-slate-400 pt-1 border-t border-slate-700/30">
                    <span>Subtotal (precio ref.)</span>
                    <span class="text-slate-200 font-medium" x-text="'$' + refTotal.toFixed(2)"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-200 font-bold">TOTAL GASTO REAL</span>
                    <div class="text-right">
                        <span class="text-emerald-400 font-black text-lg" x-text="'$' + checkedTotal.toFixed(2)"></span>
                        <br>
                        <span class="text-amber-400 font-bold text-sm" x-text="'Bs. ' + bsFormat(checkedTotal)"></span>
                    </div>
                </div>
            </div>

            {{-- Botones --}}
            <div class="px-4 py-3 flex gap-2">
                <button @click="markAllPurchased()"
                    class="flex-1 py-3 text-sm font-bold rounded-xl bg-emerald-600 text-white hover:bg-emerald-500 active:bg-emerald-700 transition-all active:scale-95 shadow-lg shadow-emerald-900/40">
                    <i class="fas fa-check-double mr-1.5"></i> Comprar todo
                </button>
            </div>
        </div>
    @else
        <div class="flex flex-col items-center justify-center min-h-[60vh] px-6 text-center">
            <div class="h-16 w-16 rounded-full bg-emerald-500/10 flex items-center justify-center mb-4">
                <i class="fas fa-check-circle text-emerald-400 text-2xl"></i>
            </div>
            <h2 class="text-lg font-semibold text-slate-200 mb-2">No hay lista activa</h2>
            <p class="text-sm text-slate-500 mb-6">Generá una lista de mercado antes de venir al supermercado.</p>
            <a href="{{ route('admin.home.shopping-list.index') }}" class="px-6 py-3 text-sm font-bold rounded-xl bg-emerald-600 text-white hover:bg-emerald-500 transition-colors">
                <i class="fas fa-arrow-left mr-1.5"></i> Ir a lista de mercado
            </a>
        </div>
    @endif
</div>

@push('scripts')
<script>
function supermarketList() {
    return {
        @if($activeList)
            @foreach($activeList->items as $item)
                checked_{{ $item->id }}: false,
                qty_{{ $item->id }}: {{ $item->suggested_quantity }},
                price_{{ $item->id }}: {{ $item->product?->purchase_price ?? 0 }},
            @endforeach
        @endif

        init() {},

        bsFormat(usd) {
            return (usd * {{ $exchangeRate }}).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        },

        get totalCount() {
            return {{ $activeList ? $activeList->items->count() : 0 }};
        },

        get checkedCount() {
            @if($activeList)
                let count = 0;
                @foreach($activeList->items as $item)
                    if (this['checked_{{ $item->id }}']) count++;
                @endforeach
                return count;
            @endif
            return 0;
        },

        get progressPercent() {
            return this.totalCount > 0 ? Math.round((this.checkedCount / this.totalCount) * 100) : 0;
        },

        get refTotal() {
            @if($activeList)
                let total = 0;
                @foreach($activeList->items as $item)
                    total += {{ $item->product?->purchase_price ?? 0 }} * {{ $item->suggested_quantity }};
                @endforeach
                return total;
            @endif
            return 0;
        },

        get grandTotal() {
            @if($activeList)
                let total = 0;
                @foreach($activeList->items as $item)
                    total += this['price_{{ $item->id }}'] * this['qty_{{ $item->id }}'];
                @endforeach
                return total;
            @endif
            return 0;
        },

        get checkedTotal() {
            @if($activeList)
                let total = 0;
                @foreach($activeList->items as $item)
                    if (this['checked_{{ $item->id }}']) {
                        total += this['price_{{ $item->id }}'] * this['qty_{{ $item->id }}'];
                    }
                @endforeach
                return total;
            @endif
            return 0;
        },

        toggle(id) {
            this['checked_' + id] = !this['checked_' + id];
        },

        markAllPurchased() {
            @if($activeList)
                @foreach($activeList->items as $item)
                    this['checked_{{ $item->id }}'] = true;
                @endforeach
            @endif
        },

        resetAll() {
            @if($activeList)
                @foreach($activeList->items as $item)
                    this['checked_{{ $item->id }}'] = false;
                    this['qty_{{ $item->id }}'] = {{ $item->suggested_quantity }};
                    this['price_{{ $item->id }}'] = {{ $item->product?->purchase_price ?? 0 }};
                @endforeach
            @endif
        }
    };
}
</script>
@endpush
@endsection
