@props([
    'id' => null,
    'name' => 'filter-select',
    'placeholder' => 'Seleccionar...',
    'searchPlaceholder' => 'Buscar...',
    'items' => [],
    'selectedValue' => '',
    'selectedText' => null,
    'itemKey' => 'id',
    'itemText' => 'name',
    'itemSubtext' => null,
    'itemIcon' => 'fas fa-box',
    'allItemsText' => 'Todos',
    'allItemsIcon' => 'fas fa-list',
    'showAllOption' => true,
    'searchable' => true,
    'disabled' => false,
    'required' => false,
    'class' => '',
    'containerClass' => '',
    'onChange' => null,
    'searchFields' => ['name'],
    'noResultsText' => 'No se encontraron resultados',
    'zIndex' => 'z-[9999]'
])

@php
    // Generar ID único si no se proporciona
    $uniqueId = $id ?? 'filter-select-' . uniqid();
    
    // Determinar el texto seleccionado
    $displayText = $selectedText;
    if (!$displayText && $selectedValue) {
        $selectedItem = collect($items)->firstWhere($itemKey, $selectedValue);
        $displayText = $selectedItem ? $selectedItem[$itemText] : $placeholder;
    } elseif (!$displayText) {
        $displayText = $showAllOption ? $allItemsText : $placeholder;
    }
    
    // Preparar los campos de búsqueda
    $searchFieldsJson = json_encode($searchFields);
@endphp

<div class="filter-select-container {{ $containerClass }}" 
     x-data="{ 
         isOpen: false, 
         searchTerm: '', 
         filteredItems: @js($items),
         selectedText: @js($displayText),
         selectedValue: @js($selectedValue),
         items: @js($items),
         filterItems() {
             if (!this.searchTerm) {
                 this.filteredItems = this.items;
                 return;
             }
             const term = this.searchTerm.toLowerCase();
             const searchFields = {{ $searchFieldsJson }};
             this.filteredItems = this.items.filter(item => 
                 searchFields.some(field => {
                     const value = item[field];
                     return value && value.toString().toLowerCase().includes(term);
                 })
             );
         },
         selectItem(item) {
             if (item) {
                 this.selectedText = item.{{ $itemText }};
                 this.selectedValue = item.{{ $itemKey }};
             } else {
                 this.selectedText = '{{ $showAllOption ? $allItemsText : $placeholder }}';
                 this.selectedValue = '';
             }
             this.isOpen = false;
             this.searchTerm = '';
             this.filteredItems = this.items;
             
             // Trigger change event
             @if($onChange)
                 {{ $onChange }}(this.selectedValue, this.selectedText);
             @endif
             
             // Dispatch custom event
             this.$dispatch('filter-select-changed', {
                 value: this.selectedValue,
                 text: this.selectedText,
                 item: item
             });
         }
     }" 
     @click.away="isOpen = false">
    
    <div class="filter-input-wrapper">
        <div class="filter-input-icon">
            <i class="{{ $itemIcon }}"></i>
        </div>
        
        <!-- Select Button -->
        <button type="button" 
                id="{{ $uniqueId }}"
                name="{{ $name }}"
                @click="isOpen = !isOpen; if (isOpen && {{ $searchable ? 'true' : 'false' }}) { $nextTick(() => $refs.searchInput?.focus()) }"
                class="filter-input w-full text-left flex items-center justify-between {{ $class }} {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}"
                {{ $disabled ? 'disabled' : '' }}
                {{ $required ? 'required' : '' }}>
            <span class="block truncate" x-text="selectedText"></span>
            <svg class="h-4 w-4 text-gray-400 transition-transform duration-200 ml-2" 
                 :class="{ 'rotate-180': isOpen }" 
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>
        <div class="filter-input-border"></div>
    </div>

    <!-- Dropdown -->
    <div x-show="isOpen" 
         x-cloak
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute {{ $zIndex }} mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg max-h-60 overflow-auto"
         style="z-index: 9999 !important;">
        
        @if($searchable)
            <!-- Search Input -->
            <div class="p-2 border-b border-gray-100">
                <input type="text" 
                       x-ref="searchInput"
                       x-model="searchTerm" 
                       @input="filterItems()"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="{{ $searchPlaceholder }}">
            </div>
        @endif
        
        <!-- Options -->
        <div class="py-1">
            @if($showAllOption)
                <!-- All items option -->
                <button type="button" 
                        @click="selectItem(null)"
                        class="w-full px-4 py-2.5 text-left text-sm hover:bg-gray-50 flex items-center gap-3 transition-colors duration-150"
                        :class="{ 'bg-blue-50 text-blue-700 font-medium': selectedValue === '' }">
                    <i class="{{ $allItemsIcon }} text-gray-400"></i>
                    <span>{{ $allItemsText }}</span>
                </button>
            @endif
            
            <!-- Item options -->
            <template x-for="item in filteredItems" :key="item.{{ $itemKey }}">
                <button type="button" 
                        @click="selectItem(item)"
                        class="w-full px-4 py-2.5 text-left text-sm hover:bg-gray-50 flex items-center gap-3 transition-colors duration-150"
                        :class="{ 'bg-blue-50 text-blue-700 font-medium': selectedValue == item.{{ $itemKey }} }">
                    <i class="{{ $itemIcon }} text-gray-400"></i>
                    <div class="flex flex-col">
                        <span x-text="item.{{ $itemText }}" class="font-medium"></span>
                        @if($itemSubtext)
                            <span x-text="item.{{ $itemSubtext }}" class="text-xs text-gray-500"></span>
                        @endif
                    </div>
                </button>
            </template>
            
            <!-- No results -->
            <div x-show="filteredItems.length === 0" class="px-4 py-2 text-sm text-gray-500 text-center">
                {{ $noResultsText }}
            </div>
        </div>
    </div>
</div>

@push('css')
<style>
/* Estilos específicos del componente si no están en el CSS global */
.filter-select-container {
    position: relative;
    min-width: 280px;
}

.filter-select-container .filter-input-wrapper {
    position: relative;
}

.filter-select-container .filter-input-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #64748b;
    font-size: 0.9rem;
    z-index: 1;
}

.filter-select-container .filter-input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 2.5rem;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    background: white;
    transition: all 0.3s;
    font-size: 0.9rem;
    color: #1f2937;
    cursor: pointer;
}

.filter-select-container .filter-input:hover:not(:disabled) {
    border-color: #cbd5e1;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.filter-select-container .filter-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.filter-select-container .filter-input-border {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 2px;
    background: #667eea;
    border-radius: 2px;
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.filter-select-container .filter-input:focus + .filter-input-border {
    transform: scaleX(1);
}

/* Responsive */
@media (max-width: 768px) {
    .filter-select-container {
        min-width: auto;
        width: 100%;
    }
}
</style>
@endpush
