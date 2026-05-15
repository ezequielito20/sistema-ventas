<div>
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/60 transition-opacity" wire:click="close"></div>
            <div class="relative bg-slate-800 border border-slate-700/50 rounded-xl shadow-xl max-w-lg w-full p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-slate-100">
                        {{ $isEditing ? 'Editar producto' : 'Nuevo producto' }}
                    </h3>
                    <button wire:click="close" class="text-slate-400 hover:text-slate-300">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form wire:submit="save" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label for="name" class="block text-sm font-medium text-slate-300">Nombre</label>
                            <input type="text" id="name" wire:model="name"
                                class="mt-1 block w-full rounded-lg border-slate-600 bg-slate-700 text-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('name') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="brand" class="block text-sm font-medium text-slate-300">Marca</label>
                            <input type="text" id="brand" wire:model="brand"
                                class="mt-1 block w-full rounded-lg border-slate-600 bg-slate-700 text-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="category" class="block text-sm font-medium text-slate-300">Categoría</label>
                            <select id="category" wire:model="category"
                                class="mt-1 block w-full rounded-lg border-slate-600 bg-slate-700 text-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Seleccionar...</option>
                                @foreach($categoryOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('category') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="unit" class="block text-sm font-medium text-slate-300">Unidad</label>
                            <select id="unit" wire:model="unit"
                                class="mt-1 block w-full rounded-lg border-slate-600 bg-slate-700 text-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                                @foreach($unitOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="quantity" class="block text-sm font-medium text-slate-300">Cantidad actual</label>
                            <input type="number" id="quantity" wire:model="quantity" min="0"
                                class="mt-1 block w-full rounded-lg border-slate-600 bg-slate-700 text-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="min_quantity" class="block text-sm font-medium text-slate-300">Stock óptimo</label>
                            <input type="number" id="min_quantity" wire:model="min_quantity" min="0"
                                class="mt-1 block w-full rounded-lg border-slate-600 bg-slate-700 text-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <p class="text-xs text-slate-500 mt-1">Cantidad deseada</p>
                        </div>

                        <div>
                            <label for="max_quantity" class="block text-sm font-medium text-slate-300">Stock máximo</label>
                            <input type="number" id="max_quantity" wire:model="max_quantity" min="0"
                                class="mt-1 block w-full rounded-lg border-slate-600 bg-slate-700 text-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <p class="text-xs text-slate-500 mt-1">Opcional, sobre esto es excedente</p>
                        </div>

                        <div>
                            <label for="purchase_price" class="block text-sm font-medium text-slate-300">Precio compra</label>
                            <input type="number" id="purchase_price" wire:model="purchase_price" min="0" step="0.01"
                                class="mt-1 block w-full rounded-lg border-slate-600 bg-slate-700 text-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="barcode" class="block text-sm font-medium text-slate-300">Código de barras</label>
                            <input type="text" id="barcode" wire:model="barcode"
                                class="mt-1 block w-full rounded-lg border-slate-600 bg-slate-700 text-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('barcode') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Imagen</label>
                        <div class="flex gap-2">
                            <label class="flex-1 cursor-pointer text-center px-3 py-2.5 rounded-lg bg-purple-600/20 text-purple-300 text-sm hover:bg-purple-600/30 border border-purple-500/30 transition-colors">
                                <i class="fas fa-camera mr-1.5"></i> Cámara
                                <input type="file" accept="image/*" capture="environment" wire:model="image" class="hidden">
                            </label>
                            <label class="flex-1 cursor-pointer text-center px-3 py-2.5 rounded-lg bg-blue-600/20 text-blue-300 text-sm hover:bg-blue-600/30 border border-blue-500/30 transition-colors">
                                <i class="fas fa-folder-open mr-1.5"></i> Subir
                                <input type="file" accept="image/*" wire:model="image" class="hidden">
                            </label>
                        </div>
                        <div class="mt-2 flex items-center gap-3">
                            @if($imagePreview && !$image)
                                <div class="relative">
                                    <img src="{{ $imagePreview }}" class="h-20 w-20 object-cover rounded-lg border border-slate-600">
                                    <button type="button" wire:click="$set('imagePreview', null)"
                                        class="absolute -top-2 -right-2 h-5 w-5 rounded-full bg-slate-700 text-slate-300 text-xs flex items-center justify-center hover:bg-slate-600">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            @endif
                            @if($image)
                                <div class="relative">
                                    <img src="{{ $image->temporaryUrl() }}" class="h-20 w-20 object-cover rounded-lg border border-slate-600">
                                    <button type="button" wire:click="$set('image', null)"
                                        class="absolute -top-2 -right-2 h-5 w-5 rounded-full bg-slate-700 text-slate-300 text-xs flex items-center justify-center hover:bg-slate-600">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="close"
                            class="px-4 py-2 text-sm font-medium rounded-lg bg-slate-700 text-slate-300 hover:bg-slate-600 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-500 transition-colors">
                            {{ $isEditing ? 'Actualizar' : 'Crear' }} producto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
