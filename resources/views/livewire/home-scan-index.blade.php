<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-slate-100">Escaner</h1>
        <p class="text-sm text-slate-400 mt-1">Descontá productos del inventario</p>
    </div>

    @if($message)
        <div class="mb-4 px-4 py-3 rounded-xl text-sm font-medium
            {{ $messageType === 'success' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : '' }}
            {{ $messageType === 'error' ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30' : '' }}
            {{ $messageType === 'warning' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : '' }}
            {{ $messageType === 'info' ? 'bg-blue-500/20 text-blue-300 border border-blue-500/30' : '' }}">
            <div class="flex items-center justify-between">
                <span>{{ $message }}</span>
                @if($canUndo)
                    <button wire:click="undoLast"
                        class="ml-4 px-3 py-1 text-xs font-medium rounded-lg bg-slate-700 text-slate-200 hover:bg-slate-600 transition-colors">
                        <i class="fas fa-undo mr-1"></i> Deshacer
                    </button>
                @endif
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="h-10 w-10 rounded-lg bg-blue-500/20 flex items-center justify-center">
                    <i class="fas fa-barcode text-blue-400"></i>
                </div>
                <div>
                    <h2 class="text-lg font-medium text-slate-100">Código de barras</h2>
                    <p class="text-xs text-slate-500">Escaneá o ingresá el código</p>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Código de barras</label>
                    <input type="text" wire:model="lastBarcode" placeholder="Ej: 7791234567890"
                        class="block w-full rounded-lg border-slate-600 bg-slate-700 text-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500"
                        wire:keydown.enter="deductByBarcode(lastBarcode)">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Cantidad</label>
                    <input type="number" wire:model="deductQuantity" min="1" value="1"
                        class="block w-24 rounded-lg border-slate-600 bg-slate-700 text-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <button wire:click="deductByBarcode(lastBarcode)"
                    class="w-full px-4 py-2 text-sm font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-500 transition-colors">
                    <i class="fas fa-search mr-1"></i> Buscar y descontar
                </button>
            </div>
        </div>

        <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="h-10 w-10 rounded-lg bg-purple-500/20 flex items-center justify-center">
                    <i class="fas fa-camera text-purple-400"></i>
                </div>
                <div>
                    <h2 class="text-lg font-medium text-slate-100">Foto del producto</h2>
                    <p class="text-xs text-slate-500">Sacá una foto para identificar y descontar</p>
                </div>
            </div>

            <div class="space-y-4">
                <div class="border-2 border-dashed border-slate-600 rounded-xl p-6 text-center">
                    <i class="fas fa-camera text-3xl text-slate-500 mb-3"></i>
                    <p class="text-sm text-slate-400 mb-2">Capturá o seleccioná una foto</p>
                    <input type="file" accept="image/*" capture="environment"
                        class="block w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:bg-purple-500/20 file:text-purple-400 hover:file:bg-purple-500/30"
                        wire:model="lastPhotoData">
                    <p class="text-xs text-slate-500 mt-2">O usá descuento manual</p>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6 bg-slate-800/50 border border-slate-700/50 rounded-xl p-4">
        <h3 class="text-sm font-medium text-slate-200 mb-3">Descuento manual</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
            @foreach($products as $product)
                <button wire:click="selectProduct({{ $product->id }})"
                    class="text-left px-3 py-2 rounded-lg bg-slate-700/30 hover:bg-slate-700/50 text-sm text-slate-200 transition-colors">
                    {{ $product->name }}
                    <span class="text-xs text-slate-500 ml-1">({{ $product->quantity }} {{ $product->unit }})</span>
                </button>
            @endforeach
        </div>
    </div>

    @if($showModal && $matchedProduct)
    <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/60" wire:click="$set('showModal', false)"></div>
            <div class="relative bg-slate-800 border border-slate-700/50 rounded-xl shadow-xl max-w-sm w-full p-6">
                <h3 class="text-lg font-medium text-slate-100 mb-2">¿Descontar producto?</h3>
                <p class="text-sm text-slate-300 mb-1">{{ $matchedProduct['name'] }}</p>
                <p class="text-xs text-slate-500 mb-4">Confianza: {{ $matchedProduct['confidence'] }}</p>
                <div class="flex justify-end gap-3">
                    <button wire:click="$set('showModal', false)"
                        class="px-4 py-2 text-sm font-medium rounded-lg bg-slate-700 text-slate-300 hover:bg-slate-600">
                        Cancelar
                    </button>
                    <button wire:click="confirmDeduct"
                        class="px-4 py-2 text-sm font-medium rounded-lg bg-rose-600 text-white hover:bg-rose-500">
                        Descontar 1
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
