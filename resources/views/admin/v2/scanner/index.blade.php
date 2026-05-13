@extends('layouts.app')

@section('title', 'Escaner de Precios')

@section('content')
<script>
    window.initialRate = {{ $initialRate }};
    window.rateUpdatedAt = '{{ $rateUpdatedAt }}';
</script>

<div x-data="scannerApp()" x-init="init()" x-on:beforeunload.window="stopScanner()"
     class="flex flex-col min-h-[calc(100vh-8rem)]">

    {{-- Desktop: mensaje de no soporte --}}
    <template x-if="!isMobile">
        <div class="flex flex-col items-center justify-center flex-1 text-center px-6 py-20">
            <div class="text-7xl mb-6 opacity-60">📱</div>
            <h2 class="text-2xl font-bold text-gray-700 mb-3">Solo disponible en dispositivos móviles</h2>
            <p class="text-gray-500 max-w-md mb-2">
                Esta herramienta usa la cámara del teléfono para escanear precios
                y convertirlos a bolívares al instante.
            </p>
            <p class="text-gray-400 text-sm">Accedé desde tu teléfono para usar el escáner.</p>
        </div>
    </template>

    {{-- Mobile: escáner --}}
    <template x-if="isMobile">
        <div class="relative flex flex-col flex-1">
            {{-- Selector de modo USD→Bs / Bs→USD --}}
            <div class="flex justify-center mb-3">
                <div class="inline-flex rounded-xl bg-gray-100 p-1 shadow-sm">
                    <button @click="setMode('usd-to-bs')"
                            :class="mode === 'usd-to-bs'
                                ? 'bg-white text-gray-800 shadow-sm'
                                : 'text-gray-500 hover:text-gray-700'"
                            class="px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200">
                        $ → Bs
                    </button>
                    <button @click="setMode('bs-to-usd')"
                            :class="mode === 'bs-to-usd'
                                ? 'bg-white text-gray-800 shadow-sm'
                                : 'text-gray-500 hover:text-gray-700'"
                            class="px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200">
                        Bs → $
                    </button>
                </div>
            </div>

            {{-- Contenedor de la cámara (con zoom y touch) --}}
            <div class="relative flex-1 bg-black rounded-xl overflow-hidden shadow-lg"
                 @touchstart="handleTouchStart"
                 @touchmove="handleTouchMove"
                 @touchend="handleTouchEnd">
                <video x-ref="video" class="absolute inset-0 w-full h-full object-cover"
                       autoplay playsinline muted></video>
                <canvas x-ref="canvas" class="hidden"></canvas>

                {{-- Indicador de zoom --}}
                <template x-if="zoom > 1">
                    <div class="absolute top-2 left-2 bg-black/50 text-white text-xs px-2 py-0.5 rounded-full z-20"
                         x-text="zoomPercent"></div>
                </template>

                {{-- Overlay del frame de escaneo --}}
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <div class="relative w-80">
                        <div class="border-2 border-white/50 rounded-xl aspect-[3/1] flex items-center justify-center bg-white/5 backdrop-blur-[1px]">
                            <div class="flex items-center gap-1.5">
                                <span class="text-white/80 text-xl font-medium" x-text="inputSymbol"></span>
                                <div class="w-0.5 h-9 bg-green-400 animate-pulse shadow-lg shadow-green-400/50"></div>
                            </div>
                        </div>
                        <p class="text-white/60 text-xs text-center mt-2.5" x-text="mode === 'usd-to-bs' ? 'Colocá el precio en $ aquí' : 'Colocá el monto en Bs aquí'"></p>
                    </div>
                </div>

                {{-- Botón Convertir --}}
                <div class="absolute bottom-5 left-1/2 -translate-x-1/2 z-20 pointer-events-none">
                    <button @click="scanNow"
                            :disabled="scanning || !workerReady"
                            class="pointer-events-auto w-16 h-16 rounded-full bg-white shadow-xl
                                   flex flex-col items-center justify-center
                                   transition-all duration-150 active:scale-90
                                   disabled:opacity-60 disabled:cursor-not-allowed
                                   focus:outline-none focus:ring-2 focus:ring-white/50">
                        <template x-if="!scanning">
                            <span class="text-[11px] font-bold text-gray-700 leading-tight text-center px-1">
                                Conv.<br>ertir
                            </span>
                        </template>
                        <template x-if="scanning">
                            <div class="w-6 h-6 border-[3px] border-gray-300 border-t-emerald-500 rounded-full animate-spin"></div>
                        </template>
                    </button>
                </div>

                {{-- Estados de carga y error --}}
                <template x-if="!workerReady && !cameraError">
                    <div class="absolute inset-0 flex items-center justify-center bg-black/70">
                        <div class="text-center">
                            <div class="animate-spin rounded-full h-10 w-10 border-2 border-white/20 border-t-white mx-auto mb-3"></div>
                            <p class="text-white text-sm">Preparando escáner...</p>
                        </div>
                    </div>
                </template>

                <template x-if="cameraError">
                    <div class="absolute inset-0 flex items-center justify-center bg-black/80">
                        <div class="text-center px-6">
                            <i class="fas fa-exclamation-triangle text-yellow-400 text-3xl mb-3"></i>
                            <p class="text-white text-sm" x-text="cameraError"></p>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Zoom slider --}}
            <div class="flex items-center gap-3 px-3 py-2 mt-1">
                <span class="text-xs text-gray-400 font-medium w-6 text-center">1×</span>
                <input type="range" min="1" max="3" step="0.1"
                       :value="zoom"
                       @input="setZoom($event.target.value)"
                       class="w-full h-1.5 bg-gray-200 rounded-full appearance-none cursor-pointer accent-emerald-500
                              [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:h-4
                              [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-emerald-500
                              [&::-webkit-slider-thumb]:shadow-sm">
                <span class="text-xs text-gray-400 font-medium w-6 text-center">3×</span>
                <span class="text-xs text-emerald-600 font-semibold w-10 text-right" x-text="zoomPercent"></span>
            </div>

            {{-- Resultado de la conversión --}}
            <template x-if="result">
                <div x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="bg-white rounded-xl shadow-lg -mt-4 relative z-10 px-5 py-4 mx-2">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-3xl font-bold text-gray-800" x-text="result.original"></div>
                            <div class="text-xs text-gray-400 mt-0.5" x-text="result.mode === 'usd-to-bs' ? 'Precio en $' : 'Monto en Bs'"></div>
                        </div>
                        <div class="text-right">
                            <div class="text-3xl font-bold text-emerald-600" x-text="result.converted"></div>
                            <div class="text-xs text-gray-400 mt-0.5">
                                Tasa: <span x-text="result.rate"></span> Bs/USD
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Historial --}}
            <div class="mt-3 px-2" x-data="{ open: false }">
                <button @click="open = !open"
                        class="flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 transition-colors">
                    <i class="fas fa-history"></i>
                    <span x-text="'Historial (' + history.length + ')'"></span>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-200"
                       :class="{ 'rotate-180': open }"></i>
                </button>

                <template x-if="open">
                    <div x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="mt-2 max-h-44 overflow-y-auto rounded-xl bg-white shadow-sm border border-gray-100 divide-y divide-gray-50">
                        <template x-for="(item, i) in history" :key="i">
                            <div class="flex items-center justify-between px-4 py-2.5 text-sm">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="text-xs font-medium text-gray-400 uppercase shrink-0"
                                          x-text="item.mode === 'usd-to-bs' ? '$→Bs' : 'Bs→$'"></span>
                                    <span class="font-semibold text-gray-700 truncate" x-text="item.original"></span>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="text-emerald-600 font-semibold" x-text="'→ ' + item.converted"></span>
                                    <span class="text-gray-400 text-xs ml-2" x-text="item.time"></span>
                                </div>
                            </div>
                        </template>
                        <button x-show="history.length > 0"
                                @click="clearHistory()"
                                class="flex items-center gap-1.5 px-4 py-2.5 text-xs text-red-500 hover:text-red-700 hover:bg-red-50 w-full transition-colors">
                            <i class="fas fa-trash-alt"></i>
                            Limpiar historial
                        </button>
                        <div x-show="history.length === 0"
                             class="px-4 py-3 text-xs text-gray-400 text-center">
                            Todavía no escaneaste ningún precio
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </template>
</div>

<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
<script src="{{ asset('js/admin/scanner/index.js') }}"></script>
@endsection
