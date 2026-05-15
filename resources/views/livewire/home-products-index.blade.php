<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-100">Inventario del Hogar</h1>
            <p class="text-sm text-slate-400 mt-1">{{ $stats->total ?? 0 }} productos · {{ $stats->low ?? 0 }} con stock bajo</p>
        </div>
        @if($permissions['create'])
            <button wire:click="$dispatch('open-create-product')"
                class="px-4 py-2 text-sm font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-500 transition-colors">
                <i class="fas fa-plus mr-1"></i> Nuevo producto
            </button>
        @endif
    </div>

    <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl overflow-hidden mb-6">
        <div class="p-4 border-b border-slate-700/50">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar producto..."
                        class="w-full rounded-lg border-slate-600 bg-slate-700 text-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <select wire:model.live="category"
                    class="rounded-lg border-slate-600 bg-slate-700 text-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Todas las categorías</option>
                    @foreach($categories as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <select wire:model.live="stock_status"
                    class="rounded-lg border-slate-600 bg-slate-700 text-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Todo stock</option>
                    <option value="low">Stock bajo</option>
                    <option value="excedent">Excedente</option>
                </select>
                @if($filtersOpen)
                    <button wire:click="clearFilters"
                        class="px-3 py-2 text-xs font-medium rounded-lg bg-slate-700 text-slate-300 hover:bg-slate-600">
                        <i class="fas fa-times mr-1"></i> Limpiar
                    </button>
                @endif
            </div>
        </div>

        @if($products->count() > 0)
            <div class="divide-y divide-slate-700/30">
                @foreach($products as $product)
                    <div class="flex items-center justify-between p-4 hover:bg-slate-700/20 transition-colors">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <div class="h-10 w-10 rounded-lg bg-slate-700 flex items-center justify-center flex-shrink-0 overflow-hidden">
                                @if($product->image)
                                    <img src="{{ $product->image_url }}" class="h-full w-full object-cover">
                                @else
                                    <i class="fas fa-box text-slate-400"></i>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-slate-200 truncate">{{ $product->name }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ $categories[$product->category] ?? $product->category }}
                                    @if($product->brand) · {{ $product->brand }} @endif
                                    @if($product->barcode) · {{ $product->barcode }} @endif
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="text-right">
                                <p class="text-sm font-medium {{ $product->stock_status === 'low' ? 'text-rose-400' : ($product->stock_status === 'excedent' ? 'text-amber-400' : 'text-emerald-400') }}">
                                    {{ $product->quantity }} {{ $product->unit }}
                                </p>
                                <p class="text-xs text-slate-500">mín: {{ $product->min_quantity }}</p>
                            </div>
                            <div class="flex gap-1">
                                @if($permissions['edit'])
                                    <button wire:click="editProduct({{ $product->id }})"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-blue-400 hover:bg-slate-700">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>
                                @endif
                                <button wire:click="$dispatch('open-movement', { id: {{ $product->id }} })"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-emerald-400 hover:bg-slate-700">
                                    <i class="fas fa-plus-minus text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="p-4 border-t border-slate-700/50">
                {{ $products->links() }}
            </div>
        @else
            <div class="p-6 text-center">
                <p class="text-sm text-slate-500">Sin productos registrados.</p>
            </div>
        @endif
    </div>

    @livewire('home.home-product-form')
</div>
