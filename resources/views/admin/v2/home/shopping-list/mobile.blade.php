@extends('layouts.app')

@section('title', 'Lista de mercado')

@section('content')
<div class="py-4 px-3 sm:px-4 lg:px-6" x-data="mobileShoppingList()">
    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-lg font-semibold text-slate-100">🛒 Lista de mercado</h1>
        <button @click="clearCompleted()" class="text-xs text-slate-400 hover:text-slate-300">
            <i class="fas fa-undo mr-1"></i> Reiniciar
        </button>
    </div>

    <div class="space-y-3">
        @php
            $activeList = \App\Models\Home\HomeShoppingList::where('company_id', auth()->user()->company_id)
                ->active()
                ->with('items.product')
                ->first();
        @endphp

        @if($activeList && $activeList->items->count() > 0)
            @foreach($activeList->items as $item)
            <div class="bg-slate-800/70 border border-slate-700/50 rounded-xl p-4 flex items-center gap-4"
                :class="{ 'opacity-60': checked_{{ $item->id }} }">
                <button @click="toggle({{ $item->id }})"
                    class="h-7 w-7 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-colors"
                    :class="checked_{{ $item->id }} ? 'bg-emerald-500 border-emerald-500' : 'border-slate-500'">
                    <template x-if="checked_{{ $item->id }}">
                        <i class="fas fa-check text-white text-xs"></i>
                    </template>
                </button>
                <div class="flex-1 min-w-0">
                    <p class="text-base font-medium text-slate-100 truncate">{{ $item->name_snapshot }}</p>
                    <p class="text-xs text-slate-400">x{{ $item->suggested_quantity }}</p>
                </div>
                @if($item->product && $item->product->purchase_price > 0)
                    <span class="text-sm text-slate-300 font-medium flex-shrink-0">
                        ${{ number_format($item->product->purchase_price * $item->suggested_quantity, 0) }}
                    </span>
                @endif
            </div>
            @endforeach
        @else
            <div class="text-center py-12">
                <p class="text-slate-400">No hay lista activa.</p>
                <a href="{{ route('admin.home.shopping-list.index') }}" class="text-blue-400 text-sm mt-2 inline-block">
                    Generar una lista
                </a>
            </div>
        @endif
    </div>

    @if($activeList && $activeList->items->count() > 0)
    <div class="mt-6 bg-slate-800/50 border border-slate-700/50 rounded-xl p-4">
        <p class="text-xs text-slate-400 text-center">
            Tocá cada item para marcarlo como comprado.
        </p>
    </div>
    @endif
</div>

@push('scripts')
<script>
function mobileShoppingList() {
    return {
        @foreach(($activeList->items ?? []) as $item)
        checked_{{ $item->id }}: false,
        @endforeach
        toggle(id) {
            this['checked_' + id] = !this['checked_' + id];
        },
        clearCompleted() {
            @foreach(($activeList->items ?? []) as $item)
            this['checked_' + this.$el.id] = false;
            @endforeach
            // Recargar la página para reiniciar
            location.reload();
        }
    };
}
</script>
@endpush
@endsection
