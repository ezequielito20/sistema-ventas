@props([
    'id' => 'simple-table',
    'items' => [],
    'columns' => [],
    'sortBy' => null,
    'sortDirection' => 'asc',
    'itemsPerPage' => 10,
    'searchable' => true,
    'sortable' => true,
    'selectable' => false,
    'actions' => false
])

<div id="{{ $id }}" x-data="simpleTable({
    items: {{ Js::from($items) }},
    sortBy: '{{ $sortBy }}',
    sortDirection: '{{ $sortDirection }}',
    itemsPerPage: {{ $itemsPerPage }}
})" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    
    <!-- Header con búsqueda y controles -->
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            
            <!-- Búsqueda -->
            @if($searchable)
            <div class="flex-1 max-w-sm">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input 
                        type="text" 
                        x-model="search"
                        placeholder="Buscar..."
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>
            </div>
            @endif
            
            <!-- Información de resultados -->
            <div class="text-sm text-gray-600">
                <span x-text="`Mostrando ${paginatedItems.length} de ${filteredItems.length} resultados`"></span>
            </div>
            
            <!-- Acciones masivas -->
            @if($selectable && $actions)
            <div x-show="selectedItems.length > 0" class="flex items-center gap-2">
                <span x-text="`${selectedItems.length} seleccionados`" class="text-sm text-gray-600"></span>
                <button 
                    @click="selectedItems = []"
                    class="px-3 py-1.5 text-sm text-gray-600 hover:text-gray-800 transition-colors"
                >
                    Limpiar
                </button>
            </div>
            @endif
        </div>
    </div>
    
    <!-- Tabla -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <!-- Checkbox para selección masiva -->
                    @if($selectable)
                    <th class="px-6 py-3 text-left">
                        <input 
                            type="checkbox" 
                            x-model="allSelected"
                            @change="toggleSelectAll()"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        >
                    </th>
                    @endif
                    
                    <!-- Columnas -->
                    @foreach($columns as $column)
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        @if($sortable && isset($column['sortable']) && $column['sortable'])
                        <button 
                            @click="sort('{{ $column['key'] }}')"
                            class="group flex items-center gap-1 hover:text-gray-700 transition-colors"
                        >
                            {{ $column['label'] }}
                            <div class="flex flex-col">
                                <i class="fas fa-chevron-up text-xs" 
                                   :class="sortBy === '{{ $column['key'] }}' && sortDirection === 'asc' ? 'text-blue-600' : 'text-gray-300'"></i>
                                <i class="fas fa-chevron-down text-xs" 
                                   :class="sortBy === '{{ $column['key'] }}' && sortDirection === 'desc' ? 'text-blue-600' : 'text-gray-300'"></i>
                            </div>
                        </button>
                        @else
                        {{ $column['label'] }}
                        @endif
                    </th>
                    @endforeach
                    
                    <!-- Columna de acciones -->
                    @if($actions)
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Acciones
                    </th>
                    @endif
                </tr>
            </thead>
            
            <tbody class="bg-white divide-y divide-gray-200">
                <template x-for="item in paginatedItems" :key="item.id">
                    <tr class="hover:bg-gray-50 transition-colors">
                        <!-- Checkbox para selección individual -->
                        @if($selectable)
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input 
                                type="checkbox" 
                                :checked="isSelected(item.id)"
                                @change="toggleSelectItem(item.id)"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            >
                        </td>
                        @endif
                        
                        <!-- Celdas de datos -->
                        @foreach($columns as $column)
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if(isset($column['format']))
                                @if($column['format'] === 'date')
                                <span x-text="formatDate(item.{{ $column['key'] }})"></span>
                                @elseif($column['format'] === 'currency')
                                <span x-text="formatCurrency(item.{{ $column['key'] }})"></span>
                                @elseif($column['format'] === 'number')
                                <span x-text="formatNumber(item.{{ $column['key'] }})"></span>
                                @elseif($column['format'] === 'status')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                                      :class="getStatusBadge(item.{{ $column['key'] }})" 
                                      x-text="item.{{ $column['key'] }}"></span>
                                @else
                                <span x-text="item.{{ $column['key'] }}"></span>
                                @endif
                            @else
                            <span x-text="item.{{ $column['key'] }}"></span>
                            @endif
                        </td>
                        @endforeach
                        
                        <!-- Acciones -->
                        @if($actions)
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            {{ $actions }}
                        </td>
                        @endif
                    </tr>
                </template>
                
                <!-- Estado vacío -->
                <tr x-show="paginatedItems.length === 0">
                    <td :colspan="{{ count($columns) + ($selectable ? 1 : 0) + ($actions ? 1 : 0) }}" class="px-6 py-12 text-center">
                        <div class="text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-4"></i>
                            <p class="text-lg font-medium">No se encontraron resultados</p>
                            <p class="text-sm">Intenta ajustar los filtros de búsqueda</p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <!-- Paginación -->
    <div x-show="totalPages > 1" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
        <div class="flex items-center justify-between">
            <!-- Información de página -->
            <div class="text-sm text-gray-700">
                <span x-text="`Página ${currentPage} de ${totalPages}`"></span>
            </div>
            
            <!-- Navegación -->
            <div class="flex items-center gap-2">
                <!-- Botón anterior -->
                <button 
                    @click="goToPage(currentPage - 1)"
                    :disabled="currentPage === 1"
                    class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Anterior
                </button>
                
                <!-- Números de página -->
                <template x-for="page in pageRange" :key="page">
                    <button 
                        @click="goToPage(page)"
                        :class="page === currentPage ? 'bg-blue-600 text-white' : 'text-gray-500 bg-white hover:bg-gray-50'"
                        class="px-3 py-2 text-sm font-medium border border-gray-300 rounded-lg transition-colors"
                        x-text="page"
                    ></button>
                </template>
                
                <!-- Botón siguiente -->
                <button 
                    @click="goToPage(currentPage + 1)"
                    :disabled="currentPage === totalPages"
                    class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Siguiente
                </button>
            </div>
        </div>
    </div>
</div>
