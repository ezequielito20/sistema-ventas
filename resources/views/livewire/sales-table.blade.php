<div class="bg-white rounded-lg shadow-md" x-data="{ 
    showFilters: false,
    showDeleteModal: false,
    
    confirmDelete() {
        if (this.selectedRows.length === 0) {
            alert('No hay elementos seleccionados para eliminar.');
            return;
        }
        this.showDeleteModal = true;
    }
}">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">
                Ventas Recientes
            </h3>
            
            <div class="flex items-center space-x-3">
                <!-- Buscador -->
                <div class="relative">
                    <input 
                        wire:model.live="search"
                        type="text" 
                        placeholder="Buscar ventas..."
                        class="w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>
                
                <!-- Botón de filtros -->
                <button 
                    @click="showFilters = !showFilters"
                    class="btn-secondary"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"/>
                    </svg>
                    Filtros
                </button>

                <!-- Botón eliminar seleccionados -->
                <button 
                    @click="confirmDelete()"
                    class="btn-danger"
                    :disabled="selectedRows.length === 0"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Eliminar
                </button>
            </div>
        </div>

        <!-- Filtros expandibles -->
        <div x-show="showFilters" x-transition class="mt-4 p-4 bg-gray-50 rounded-md">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="form-label">Ordenar por:</label>
                    <select wire:model.live="sortBy" class="form-input">
                        <option value="created_at">Fecha</option>
                        <option value="total_price">Total</option>
                        <option value="id">ID</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Dirección:</label>
                    <select wire:model.live="sortDirection" class="form-input">
                        <option value="desc">Descendente</option>
                        <option value="asc">Ascendente</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button 
                        wire:click="$set('search', '')"
                        class="btn-secondary w-full"
                    >
                        Limpiar Filtros
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Mensajes Flash -->
    @if (session()->has('message'))
        <div class="px-6 py-3 bg-green-100 border-b border-green-200">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="text-green-800">{{ session('message') }}</span>
            </div>
        </div>
    @endif

    <!-- Tabla -->
    <div class="overflow-x-auto">
        <table class="table-modern">
            <thead>
                <tr>
                    <th class="w-12">
                        <input 
                            type="checkbox" 
                            wire:model.live="selectAll"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        >
                    </th>
                    <th>
                        <button 
                            wire:click="sortBy('id')"
                            class="flex items-center space-x-1 hover:text-gray-700 transition-colors"
                        >
                            <span>ID</span>
                            @if($sortBy === 'id')
                                <svg class="w-4 h-4" fill="currentColor">
                                    <path d="M7 14l5-5 5 5z"/>
                                </svg>
                            @endif
                        </button>
                    </th>
                    <th>Cliente</th>
                    <th>
                        <button 
                            wire:click="sortBy('total_price')"
                            class="flex items-center space-x-1 hover:text-gray-700 transition-colors"
                        >
                            <span>Total</span>
                            @if($sortBy === 'total_price')
                                <svg class="w-4 h-4" fill="currentColor">
                                    <path d="M7 14l5-5 5 5z"/>
                                </svg>
                            @endif
                        </button>
                    </th>
                    <th>Estado</th>
                    <th>
                        <button 
                            wire:click="sortBy('created_at')"
                            class="flex items-center space-x-1 hover:text-gray-700 transition-colors"
                        >
                            <span>Fecha</span>
                            @if($sortBy === 'created_at')
                                <svg class="w-4 h-4" fill="currentColor">
                                    <path d="M7 14l5-5 5 5z"/>
                                </svg>
                            @endif
                        </button>
                    </th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                <tr class="hover:bg-gray-50" x-data="{ showActions: false }">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <input 
                            type="checkbox" 
                            wire:model.live="selectedRows"
                            value="{{ $sale->id }}"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        >
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            #{{ $sale->id }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                    <span class="text-blue-600 font-medium">
                                        {{ strtoupper(substr($sale->customer->name ?? 'N/A', 0, 2)) }}
                                    </span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $sale->customer->name ?? 'Cliente no encontrado' }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $sale->customer->email ?? 'Sin email' }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            ${{ number_format($sale->total_price, 2) }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                            {{ $sale->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ ucfirst($sale->status ?? 'pending') }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $sale->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="relative" x-data="{ open: false }">
                            <button 
                                @click="open = !open"
                                class="text-gray-400 hover:text-gray-600 transition-colors"
                            >
                                <svg class="w-5 h-5" fill="currentColor">
                                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                                </svg>
                            </button>
                            
                            <div 
                                x-show="open"
                                @click.away="open = false"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute right-0 z-10 mt-2 w-48 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5"
                            >
                                <div class="py-1">
                                    <a href="{{ route('admin.sales.details', $sale->id) }}" 
                                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Ver detalles
                                    </a>
                                    <a href="{{ route('admin.sales.edit', $sale->id) }}" 
                                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Editar
                                    </a>
                                    <button 
                                        wire:click="delete({{ $sale->id }})"
                                        class="block w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-gray-100 transition-colors"
                                    >
                                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Eliminar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No hay ventas</h3>
                            <p class="text-gray-500">No se encontraron ventas que coincidan con los criterios de búsqueda.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    @if($sales->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $sales->links() }}
        </div>
    @endif

    <!-- Modal de confirmación de eliminación -->
    <div 
        x-show="showDeleteModal" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;"
    >
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Confirmar eliminación
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    ¿Estás seguro de que quieres eliminar las ventas seleccionadas? Esta acción no se puede deshacer.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button 
                        wire:click="deleteSelected"
                        @click="showDeleteModal = false"
                        type="button" 
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                    >
                        Eliminar
                    </button>
                    <button 
                        @click="showDeleteModal = false"
                        type="button" 
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                    >
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
