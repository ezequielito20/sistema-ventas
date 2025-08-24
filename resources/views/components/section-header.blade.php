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
                @if($showStatus || $showLastUpdate)
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 mt-2 sm:mt-3">
                        @if($showStatus)
                            <div class="flex items-center gap-1 sm:gap-2 bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800 px-2 sm:px-3 md:px-4 lg:px-4 py-1 sm:py-1.5 md:py-2 lg:py-2 rounded-full text-xs sm:text-sm font-semibold">
                                <div class="w-1.5 h-1.5 sm:w-2 sm:h-2 bg-{{ $statusColor }}-500 rounded-full animate-pulse"></div>
                                <span class="hidden xs:inline">{{ $statusText }}</span>
                                <span class="xs:hidden">{{ strlen($statusText) > 10 ? Str::limit($statusText, 8) : $statusText }}</span>
                            </div>
                        @endif
                        @if($showLastUpdate)
                            <div class="text-xs sm:text-sm text-gray-500">
                                <span class="hidden sm:inline">Última actualización:</span>
                                <span class="sm:hidden">Actualizado:</span>
                                {{ $lastUpdate ?? date('H:i') }}
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Controls Section -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 sm:gap-4 md:gap-6 lg:gap-6 flex-shrink-0">
            @if($showDataSelector)
                <!-- Data Selector -->
                <div class="relative w-full sm:w-auto">
                    <div class="bg-gradient-to-r {{ $iconBg }} p-0.5 sm:p-1 rounded-xl sm:rounded-2xl shadow-lg sm:shadow-xl">
                        <select 
                            x-model="{{ $dataMode }}"
                            class="bg-gradient-to-r {{ str_replace('to-', 'from-', $iconBg) }} {{ str_replace('from-', 'to-', $iconBg) }} text-white font-bold py-2 px-3 sm:px-4 rounded-lg border-0 focus:ring-2 focus:ring-opacity-50 focus:outline-none appearance-none cursor-pointer w-full sm:min-w-[180px] md:min-w-[200px] lg:min-w-[200px] pr-6 sm:pr-8 text-xs sm:text-sm">
                            <option value="current">📊 Arqueo Actual</option>
                            <option value="historical">📈 Histórico Completo</option>
                            @foreach($dataOptions as $option)
                                <option value="{{ $option['value'] }}" 
                                        x-text="'📋 ' + '{{ $option['text'] }}'">
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute right-3 sm:right-4 md:right-6 lg:right-6 top-1/2 transform -translate-y-1/2 pointer-events-none">
                            <i class="fas fa-chevron-down text-white text-sm sm:text-lg md:text-xl lg:text-xl"></i>
                        </div>
                    </div>
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
                    <span class="hidden sm:inline">{{ $statusText }}</span>
                    <span class="sm:hidden">{{ strlen($statusText) > 10 ? Str::limit($statusText, 8) : $statusText }}</span>
                </div>
            @endif
        </div>
    </div>
</div>
