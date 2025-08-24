@props([
    'title' => '',
    'subtitle' => '',
    'icon' => 'fas fa-chart-line',
    'iconBg' => 'from-blue-500 to-purple-600',
    'statusIcon' => 'fas fa-check',
    'statusText' => 'Sistema Activo',
    'statusColor' => 'green',
    'lastUpdate' => null,
    'dataMode' => 'current',
    'dataOptions' => [],
    'cashCounts' => [],
    'showDataSelector' => true,
    'showStatus' => true,
    'showLastUpdate' => true,
    'actionButton' => false,
    'actionButtonText' => '',
    'actionButtonUrl' => '',
    'actionButtonIcon' => 'fas fa-eye',
    'refreshButton' => false,
    'refreshButtonText' => 'Actualizar Datos',
    'refreshButtonIcon' => 'fas fa-sync-alt'
])

<div class="bg-gradient-to-r from-slate-50 to-gray-100 rounded-2xl sm:rounded-3xl p-4 sm:p-6 md:p-8 lg:p-8 mb-6 sm:mb-8 md:mb-10 lg:mb-10 shadow-lg sm:shadow-xl border border-gray-200/50">
    <div class="flex flex-col md:flex-row md:items-center lg:flex-row lg:items-center justify-between gap-4 sm:gap-6 md:gap-8 lg:gap-8">
        <!-- Title Section -->
        <div class="flex items-center gap-3 sm:gap-4 md:gap-6 lg:gap-6 flex-1 min-w-0">
            <div class="relative flex-shrink-0">
                <div class="flex items-center justify-center w-12 h-12 sm:w-16 sm:h-16 md:w-20 md:h-20 lg:w-20 lg:h-20 bg-gradient-to-br {{ $iconBg }} rounded-xl sm:rounded-2xl md:rounded-3xl lg:rounded-3xl shadow-lg sm:shadow-xl md:shadow-2xl lg:shadow-2xl">
                    <i class="{{ $icon }} text-lg sm:text-xl md:text-3xl lg:text-3xl text-white"></i>
                </div>
                @if($showStatus)
                    <div class="absolute -top-1 -right-1 sm:-top-2 sm:-right-2 w-5 h-5 sm:w-6 sm:h-6 md:w-8 md:h-8 lg:w-8 lg:h-8 bg-{{ $statusColor }}-500 rounded-full flex items-center justify-center">
                        <i class="{{ $statusIcon }} text-white text-xs sm:text-sm"></i>
                    </div>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-xl sm:text-2xl md:text-4xl lg:text-4xl font-black text-gray-800 mb-1 sm:mb-2">{{ $title }}</h3>
                <p class="text-sm sm:text-base md:text-lg lg:text-lg text-gray-600 font-medium">{{ $subtitle }}</p>
                @if($showLastUpdate)
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 mt-2 sm:mt-3">
                        <div class="text-xs sm:text-sm text-gray-500">
                            <span class="hidden sm:inline">Última actualización:</span>
                            <span class="sm:hidden">Actualizado:</span>
                            {{ $lastUpdate ?? date('H:i') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Controls Section -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 sm:gap-4 md:gap-6 lg:gap-6 flex-shrink-0">
            @if($showDataSelector)
                <!-- Data Selector -->
                <div class="relative w-full sm:w-auto">
                    <x-filter-select
                        name="data-mode-select"
                        placeholder="📊 Arqueo Actual"
                        searchPlaceholder="Buscar opción..."
                        :items="collect([
                            ['id' => 'current', 'name' => '📊 Arqueo Actual', 'icon' => 'fas fa-chart-bar'],
                            ['id' => 'historical', 'name' => '📈 Histórico Completo', 'icon' => 'fas fa-history']
                        ])->merge(collect($dataOptions)->map(function($option) {
                            return [
                                'id' => $option['value'],
                                'name' => '📋 ' . $option['text'],
                                'icon' => 'fas fa-file-alt'
                            ];
                        }))->merge(collect($cashCounts)->map(function($cashCount) {
                            return [
                                'id' => 'cash_' . $cashCount['id'],
                                'name' => $cashCount['opening_date_formatted'] . ' - ' . $cashCount['closing_date_formatted'],
                                'icon' => 'fas fa-cash-register',
                                'cashCountId' => $cashCount['id']
                            ];
                        }))"
                        itemKey="id"
                        itemText="name"
                        itemIcon="icon"
                        allItemsText="📊 Arqueo Actual"
                        allItemsIcon="fas fa-chart-bar"
                        :showAllOption="false"
                        :searchable="false"
                        noResultsText="No hay opciones disponibles"
                        containerClass="data-mode-filter-container"
                        :onChange="'window.sectionHeader.onDataModeChange'"
                        selectedValue="current"
                        selectedText="📊 Arqueo Actual"
                    />
                </div>
            @endif

            @if($actionButton)
                <!-- Action Button -->
                <a href="{{ $actionButtonUrl }}" 
                   class="bg-gradient-to-r {{ $iconBg }} text-white px-3 sm:px-4 md:px-6 lg:px-6 py-2 sm:py-2.5 md:py-3 lg:py-3 rounded-xl sm:rounded-2xl font-bold hover:shadow-lg sm:hover:shadow-xl transition-all duration-300 flex items-center justify-center gap-2 sm:gap-3 shadow-md sm:shadow-lg text-xs sm:text-sm md:text-base lg:text-base">
                    <i class="{{ $actionButtonIcon }} text-sm sm:text-base md:text-lg lg:text-lg"></i>
                    <span class="hidden sm:inline">{{ $actionButtonText }}</span>
                    <span class="sm:hidden">{{ strlen($actionButtonText) > 10 ? Str::limit($actionButtonText, 8) : $actionButtonText }}</span>
                </a>
            @endif

            @if($refreshButton)
                <!-- Refresh Button -->
                <button class="bg-gradient-to-r {{ $iconBg }} text-white px-3 sm:px-4 md:px-8 lg:px-8 py-2 sm:py-3 md:py-4 lg:py-4 rounded-xl sm:rounded-2xl font-bold hover:shadow-lg sm:hover:shadow-xl transition-all duration-300 flex items-center justify-center gap-2 sm:gap-3 shadow-md sm:shadow-lg text-xs sm:text-sm md:text-base lg:text-base">
                    <i class="{{ $refreshButtonIcon }} text-sm sm:text-base md:text-lg lg:text-lg"></i>
                    <span class="hidden sm:inline">{{ $refreshButtonText }}</span>
                    <span class="sm:hidden">{{ strlen($refreshButtonText) > 10 ? Str::limit($refreshButtonText, 8) : $refreshButtonText }}</span>
                </button>
            @endif

            <!-- Status Indicator (if no other buttons) -->
            @if(!$actionButton && !$refreshButton && $showStatus)
                <div class="flex items-center gap-1 sm:gap-2 px-3 sm:px-4 py-2 rounded-xl text-xs sm:text-sm font-bold shadow-md sm:shadow-lg bg-gradient-to-r from-{{ $statusColor }}-500 to-{{ $statusColor }}-600 text-white">
                    <div class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full animate-pulse bg-{{ $statusColor }}-300"></div>
                    <span>{{ $statusText }}</span>
                </div>
            @endif
        </div>
    </div>
</div>

@push('css')
<style>
/* ===== ESTILOS PARA FILTER-SELECT EN SECTION-HEADER ===== */

/* Contenedor del filter-select */
.data-mode-filter-container {
    position: relative;
    width: 100%;
    min-width: 180px;
}

/* Estilos específicos para el select de modo de datos */
.data-mode-filter-container .filter-input {
    background: white !important;
    border: 2px solid #e5e7eb !important;
    border-radius: 12px !important;
    padding: 0.5rem 1rem 0.5rem 2.5rem !important;
    font-size: 0.875rem !important;
    color: #374151 !important;
    font-weight: bold !important;
    transition: all 0.3s ease !important;
    min-height: 44px !important;
    width: 100% !important;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
}

/* Asegurar que el texto del placeholder y valor seleccionado sea visible */
.data-mode-filter-container .filter-input::placeholder {
    color: #9ca3af !important;
}

/* Estilos para el texto del botón seleccionado */
.data-mode-filter-container .filter-input span {
    color: #374151 !important;
}

/* Asegurar que el texto del botón sea visible */
.data-mode-filter-container button span {
    color: #374151 !important;
}

.data-mode-filter-container button.bg-blue-50 span {
    color: #1d4ed8 !important;
}

/* Asegurar que el texto del input sea visible */
.data-mode-filter-container .filter-input {
    color: #374151 !important;
}

.data-mode-filter-container .filter-input * {
    color: #374151 !important;
}

/* Estilos específicos para el texto del botón principal */
.data-mode-filter-container .filter-input-wrapper .filter-input span {
    color: #374151 !important;
    font-weight: bold !important;
}

/* Estilos para el icono del dropdown */
.data-mode-filter-container .filter-input svg {
    color: #6b7280 !important;
}

/* Estilos para el icono del input */
.data-mode-filter-container .filter-input-icon {
    color: #6b7280 !important;
}

.data-mode-filter-container .filter-input:hover {
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15) !important;
    transform: translateY(-1px) !important;
}

.data-mode-filter-container .filter-input:focus {
    box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.2) !important;
    outline: none !important;
}

/* Icono del input */
.data-mode-filter-container .filter-input-icon {
    position: absolute !important;
    left: 0.75rem !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    color: #6b7280 !important;
    font-size: 0.875rem !important;
    z-index: 1 !important;
    pointer-events: none !important;
}

/* Estilos específicos para el icono dentro del input */
.data-mode-filter-container .filter-input-icon i {
    color: #6b7280 !important;
}

/* Dropdown mejorado */
.data-mode-filter-container .absolute {
    border-radius: 12px !important;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
    border: 1px solid #e5e7eb !important;
    background: white !important;
    z-index: 9999 !important;
    max-height: 300px !important;
    overflow: hidden !important;
    position: absolute !important;
    top: 100% !important;
    left: 0 !important;
    right: 0 !important;
    margin-top: 0.25rem !important;
}

/* Opciones del dropdown */
.data-mode-filter-container button {
    transition: all 0.2s ease !important;
    border-bottom: 1px solid #f3f4f6 !important;
    min-height: 50px !important;
    padding: 0.75rem 1rem !important;
    color: #374151 !important;
}

.data-mode-filter-container button:last-child {
    border-bottom: none !important;
}

.data-mode-filter-container button:hover {
    background-color: #f8fafc !important;
    transform: translateX(2px) !important;
}

.data-mode-filter-container button.bg-blue-50 {
    background-color: #eff6ff !important;
    color: #1d4ed8 !important;
    font-weight: 600 !important;
}

/* Responsive */
@media (max-width: 768px) {
    .data-mode-filter-container {
        min-width: auto;
        width: 100%;
    }
    
    .data-mode-filter-container .filter-input {
        padding: 0.5rem 0.875rem 0.5rem 2.25rem !important;
        font-size: 0.8125rem !important;
    }
    
    .data-mode-filter-container .absolute {
        max-height: 250px !important;
    }
}
</style>
@endpush

@push('js')
<script>
// Función global para manejar el cambio de modo de datos
window.sectionHeader = window.sectionHeader || {};
window.sectionHeader.onDataModeChange = function(selectedValue, selectedItem) {
    console.log('Modo de datos cambiado:', selectedValue, selectedItem);
    
    // Manejar selección de arqueo específico
    if (selectedValue && selectedValue.startsWith('cash_')) {
        const cashCountId = selectedValue.replace('cash_', '');
        console.log(`✅ Arqueo específico seleccionado: ${cashCountId}`);
        
        // Disparar evento para arqueo específico
        console.log('📡 Dispatching cashCountSelected event with cashCountId:', cashCountId);
        window.dispatchEvent(new CustomEvent('cashCountSelected', {
            detail: {
                cashCountId: cashCountId,
                selectedItem: selectedItem
            }
        }));
        
        return;
    }
    
    // Aquí puedes agregar la lógica para manejar el cambio de modo
    // Por ejemplo, actualizar la variable Alpine.js correspondiente
    if (window.Alpine && window.Alpine.store) {
        // Buscar el componente Alpine.js que maneja el modo de datos
        const alpineComponent = document.querySelector('[x-data*="dataMode"]');
        if (alpineComponent && alpineComponent.__x) {
            const component = alpineComponent.__x;
            if (component.dataMode !== undefined) {
                component.dataMode = selectedValue;
                console.log(`✅ Modo de datos actualizado en Alpine: ${selectedValue}`);
            }
        }
    }
    
    // También puedes disparar un evento personalizado
    console.log('📡 Dispatching dataModeChanged event with value:', selectedValue);
    window.dispatchEvent(new CustomEvent('dataModeChanged', {
        detail: {
            value: selectedValue,
            item: selectedItem
        }
    }));
};
</script>
@endpush
