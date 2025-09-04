@props([
    'title' => '',
    'value' => '0',
    'icon' => 'fas fa-chart-line',
    'trend' => '+0%',
    'trendIcon' => 'fas fa-trending-up',
    'trendColor' => 'text-green-300',
    'subtitle' => '',
    'subtitleIcon' => 'fas fa-clock',
    'gradientFrom' => 'from-blue-500',
    'gradientTo' => 'to-indigo-600',
    'progressWidth' => '85%',
    'progressGradientFrom' => 'from-blue-400',
    'progressGradientTo' => 'to-indigo-400',
    'actionButton' => null,
    'actionButtonText' => '',
    'actionButtonUrl' => '#',
    'actionButtonIcon' => 'fas fa-eye',
    'valueType' => 'currency',
    'currencySymbol' => '$'
])

<div class="group relative overflow-hidden rounded-lg bg-gradient-to-br {{ $gradientFrom }} {{ $gradientTo }} text-white shadow-md hover:shadow-lg transition-all duration-300 h-36 sm:h-36 cursor-pointer widget-responsive">
    <!-- Animated Background -->
    <div class="absolute inset-0">
        <div class="absolute top-0 right-0 w-20 h-20 {{ str_replace('from-', 'bg-', $gradientFrom) }}/30 rounded-full blur-xl"></div>
        <div class="absolute bottom-0 left-0 w-16 h-16 {{ str_replace('to-', 'bg-', $gradientTo) }}/30 rounded-full blur-lg"></div>
    </div>

    <!-- Content -->
    <div class="relative z-10 p-3 sm:p-4 h-full flex flex-col justify-between">
        <!-- Header -->
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center justify-center w-5 h-5 sm:w-6 sm:h-6 bg-white/20 backdrop-blur-lg rounded-md flex-shrink-0">
                <i class="{{ $icon }} text-xs"></i>
            </div>
            <div class="flex items-center gap-1 bg-white/20 backdrop-blur-lg px-1 py-0.5 sm:px-1.5 rounded-full text-xs font-bold flex-shrink-0">
                <i class="{{ $trendIcon }} {{ $trendColor }} text-xs"></i>
                <span class="trend-text">{{ $trend }}</span>
            </div>
        </div>

        <!-- Body -->
        <div class="flex-1 flex flex-col justify-center">
            <!-- Title -->
            <div class="text-base sm:text-lg font-bold opacity-90 mb-1">{{ $title }}</div>
            
            <!-- Value -->
            <div class="text-lg sm:text-xl lg:text-2xl font-black transition-all duration-300 mb-2">
                @if($valueType === 'currency')
                    <span class="widget-value">{{ $currencySymbol }}{{ number_format($value, 2) }}</span>
                @else
                    <span class="widget-value">{{ $value }}</span>
                @endif
            </div>
            
            @if($subtitle)
                <div class="flex items-center gap-1 text-xs opacity-80">
                    <i class="{{ $subtitleIcon }} text-xs flex-shrink-0"></i>
                    <span class="widget-subtitle truncate">{{ $subtitle }}</span>
                </div>
            @endif
        </div>

        <!-- Action Button (if provided) -->
        @if($actionButton)
            <div class="mt-auto">
                <a href="{{ $actionButtonUrl }}" 
                   class="inline-flex items-center gap-1 bg-white/20 backdrop-blur-lg text-white px-1.5 py-0.5 sm:px-2 sm:py-1 rounded-lg text-xs font-bold hover:bg-white/30 transition-all duration-300">
                    <i class="{{ $actionButtonIcon }} text-xs"></i>
                    <span class="hidden sm:inline">{{ $actionButtonText }}</span>
                    <span class="sm:hidden">{{ Str::limit($actionButtonText, 3) }}</span>
                </a>
            </div>
        @else
            <!-- Spacer to maintain consistent height -->
            <div class="mt-auto h-6"></div>
        @endif

        <!-- Progress Bar -->
        <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-white/10">
            <div class="h-full bg-gradient-to-r {{ $progressGradientFrom }} {{ $progressGradientTo }} rounded-r-full transition-all duration-1000" style="width: {{ $progressWidth }}"></div>
        </div>
    </div>
</div>

<style>
/* Responsividad específica para widgets */
.widget-responsive {
    min-height: 144px; /* h-36 */
}

/* Para pantallas menores a 1024px */
@media (max-width: 1023px) {
    .widget-responsive {
        min-height: 144px;
    }
    
    .widget-responsive .widget-value {
        font-size: 1.125rem; /* text-lg */
        line-height: 1.25rem;
    }
    
    .widget-responsive .trend-text {
        font-size: 0.75rem; /* text-xs */
    }
    
    .widget-responsive .widget-subtitle {
        font-size: 0.75rem; /* text-xs */
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
}

/* Para pantallas menores a 768px */
@media (max-width: 767px) {
    .widget-responsive .widget-value {
        font-size: 1rem; /* text-base */
        line-height: 1.25rem;
    }
    
    .widget-responsive .trend-text {
        font-size: 0.625rem; /* text-xs más pequeño */
    }
}

/* Para pantallas menores a 640px */
@media (max-width: 639px) {
    .widget-responsive {
        min-height: 128px; /* h-32 */
    }
    
    .widget-responsive .widget-value {
        font-size: 0.875rem; /* text-sm */
        line-height: 1.25rem;
    }
    
    .widget-responsive .widget-subtitle {
        font-size: 0.625rem; /* text-xs más pequeño */
    }
}

/* Asegurar que el contenido no se corte */
.widget-responsive .flex {
    min-width: 0;
}

.widget-responsive .truncate {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>
