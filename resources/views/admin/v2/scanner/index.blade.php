@extends('layouts.app')

@section('title', 'Escaner de Precios')

@section('content')
<script>
    window.initialRate = {{ $initialRate }};
    window.rateUpdatedAt = '{{ $rateUpdatedAt }}';
</script>

<div x-data="scannerApp()" x-init="init()" x-on:beforeunload.window="stopScanner()"
     class="flex flex-col min-h-[calc(100vh-8rem)] gap-3">

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
        <div class="flex flex-col gap-3 flex-1">
            {{-- Selector de modo USD→Bs / Bs→USD --}}
            <div class="flex justify-center">
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

            {{-- Cámara chica -- solo el recuadro de escaneo --}}
            <div class="relative h-56 bg-black rounded-xl overflow-hidden shadow-lg">
                <video x-ref="video" class="absolute inset-0 w-full h-full object-cover"
                       autoplay playsinline muted></video>
                <canvas x-ref="canvas" class="hidden"></canvas>

                {{-- Overlay del recuadro de escaneo --}}
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <div class="flex flex-col items-center" x-ref="scanZone">
                        <div class="w-72 sm:w-80 border-2 border-white/60 rounded-xl aspect-[3/1] flex items-center justify-center bg-white/5 backdrop-blur-[1px] shadow-[0_0_20px_rgba(255,255,255,0.08)]">
                            <div class="flex items-center gap-1.5">
                                <span class="text-white/80 text-xl font-medium" x-text="inputSymbol"></span>
                                <div class="w-0.5 h-9 bg-green-400 animate-pulse shadow-lg shadow-green-400/50"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Texto indicador debajo del recuadro (dentro de la cámara) --}}
                <div class="absolute bottom-3 left-0 right-0 text-center pointer-events-none">
                    <p class="text-white/60 text-xs" x-text="mode === 'usd-to-bs' ? 'Colocá el precio en $ aquí' : 'Colocá el monto en Bs aquí'"></p>
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

            {{-- Botón Convertir -- fuera de la cámara, siempre accesible --}}
            <button @click="scanNow"
                    :disabled="scanning || !workerReady || !hasCamera"
                    class="w-full py-3.5 rounded-xl font-bold text-base transition-all duration-150
                           active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed
                           focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                    :class="scanning
                        ? 'bg-emerald-50 text-emerald-600'
                        : 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30 hover:bg-emerald-700'">
                <template x-if="!scanning">
                    <span class="flex items-center justify-center gap-2">
                        <i class="fas fa-camera text-sm"></i>
                        Convertir
                    </span>
                </template>
                <template x-if="scanning">
                    <span class="flex items-center justify-center gap-2">
                        <div class="w-5 h-5 border-[3px] border-emerald-300 border-t-emerald-600 rounded-full animate-spin"></div>
                        Escaneando...
                    </span>
                </template>
            </button>

            {{-- Resultado de la conversión --}}
            <template x-if="result">
                <div x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="bg-white rounded-xl shadow-lg px-5 py-4">
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
            <div x-data="{ open: false }">
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
