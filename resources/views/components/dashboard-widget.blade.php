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

<div class="group relative overflow-hidden rounded-lg bg-gradient-to-br {{ $gradientFrom }} {{ $gradientTo }} text-white shadow-md hover:shadow-lg transition-all duration-300 h-34 sm:h-34 cursor-pointer">
    <!-- Animated Background -->
    <div class="absolute inset-0">
        <div class="absolute top-0 right-0 w-20 h-20 {{ str_replace('from-', 'bg-', $gradientFrom) }}/30 rounded-full blur-xl"></div>
        <div class="absolute bottom-0 left-0 w-16 h-16 {{ str_replace('to-', 'bg-', $gradientTo) }}/30 rounded-full blur-lg"></div>
    </div>

    <!-- Content -->
    <div class="relative z-10 p-3 sm:p-4 h-full flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center justify-center w-5 h-5 sm:w-6 sm:h-6 bg-white/20 backdrop-blur-lg rounded-md">
                <i class="{{ $icon }} text-xs"></i>
            </div>
            <div class="flex items-center gap-1 bg-white/20 backdrop-blur-lg px-1 py-0.5 sm:px-1.5 rounded-full text-xs font-bold">
                <i class="{{ $trendIcon }} {{ $trendColor }} text-xs"></i>
                <span class="hidden sm:inline">{{ $trend }}</span>
                <span class="sm:hidden">{{ str_replace('.0%', '%', $trend) }}</span>
            </div>
        </div>

        <!-- Body -->
        <div class="flex-1 flex flex-col justify-center">
            <!-- Title and Value Row -->
            <div class="flex items-center space-x-2 mb-2">
                <div class="text-base sm:text-lg font-bold opacity-90">{{ $title }}</div>
                <div class="text-xl sm:text-2xl font-black transition-all duration-300">
                    @if($valueType === 'currency')
                        <span x-text="formatCurrency({{ $value }})"></span>
                    @else
                        <span>{{ number_format($value) }}</span>
                    @endif
                </div>
            </div>
            @if($subtitle)
                <div class="flex items-center gap-1 text-xs opacity-80">
                    <i class="{{ $subtitleIcon }} text-xs"></i>
                    <span class="hidden sm:inline">{{ $subtitle }}</span>
                    <span class="sm:hidden">{{ Str::limit($subtitle, 15) }}</span>
                </div>
            @endif
        </div>

        <!-- Action Button (if provided) -->
        @if($actionButton)
            <div class="mt-2">
                <a href="{{ $actionButtonUrl }}" 
                   class="inline-flex items-center gap-1 bg-white/20 backdrop-blur-lg text-white px-1.5 py-0.5 sm:px-2 sm:py-1 rounded-lg text-xs font-bold hover:bg-white/30 transition-all duration-300">
                    <i class="{{ $actionButtonIcon }} text-xs"></i>
                    <span class="hidden sm:inline">{{ $actionButtonText }}</span>
                    <span class="sm:hidden">{{ Str::limit($actionButtonText, 3) }}</span>
                </a>
            </div>
        @endif

        <!-- Progress Bar -->
        <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-white/10">
            <div class="h-full bg-gradient-to-r {{ $progressGradientFrom }} {{ $progressGradientTo }} rounded-r-full transition-all duration-1000" style="width: {{ $progressWidth }}"></div>
        </div>
    </div>
</div>
