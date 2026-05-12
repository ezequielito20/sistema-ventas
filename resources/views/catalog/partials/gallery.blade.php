{{--
    Image gallery / carousel partial — Premium version.
    Expects: $images (Collection of ProductImage), each with image_url attribute.

    Features:
    - Alpine.js carousel with swipe, keyboard nav, dots, thumbnails
    - Auto-hide controls for single image
    - Click to expand / lightbox-like behavior
    - Smooth fade transitions
--}}
<div x-data="{
    active: 0,
    images: {{ Js::from($images->map(fn($img) => ['url' => $img->image_url, 'id' => $img->id])) }},
    get total() { return this.images.length; },
    get hasMultiple() { return this.total > 1; },
    prev() { this.active = this.active === 0 ? this.total - 1 : this.active - 1; },
    next() { this.active = this.active === this.total - 1 ? 0 : this.active + 1; },
    goTo(idx) { this.active = idx; },
    touchStart: 0, touchEnd: 0,
    handleTouchStart(e) { this.touchStart = e.touches[0].clientX; },
    handleTouchMove(e) { this.touchEnd = e.touches[0].clientX; },
    handleTouchEnd() {
        const diff = this.touchStart - this.touchEnd;
        if (Math.abs(diff) > 50) { diff > 0 ? this.next() : this.prev(); }
    },
    handleKeydown(e) {
        if (e.key === 'ArrowLeft') { e.preventDefault(); this.prev(); }
        if (e.key === 'ArrowRight') { e.preventDefault(); this.next(); }
    }
}" class="w-full" @keydown.window="handleKeydown">

    {{-- Main Image Container --}}
    <div class="relative bg-gray-100 dark:bg-gray-800 rounded-2xl overflow-hidden shadow-sm border border-gray-200 dark:border-gray-700"
         @touchstart="handleTouchStart" @touchmove="handleTouchMove" @touchend="handleTouchEnd">

        {{-- No Images Placeholder --}}
        <template x-if="total === 0">
            <div class="aspect-[1/1] sm:aspect-[4/3] flex flex-col items-center justify-center gap-3">
                <div class="w-20 h-20 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                    <i class="fas fa-image text-3xl text-gray-400 dark:text-gray-500"></i>
                </div>
                <span class="text-sm text-gray-400 dark:text-gray-500">Sin imágenes disponibles</span>
            </div>
        </template>

        {{-- Images with Crossfade --}}
        <template x-for="(image, index) in images" :key="image.id">
            <div x-show="active === index"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 class="aspect-[1/1] sm:aspect-[4/3] flex items-center justify-center cursor-zoom-in"
                 @click="window.open(image.url, '_blank')">
                <img :src="image.url" :alt="'Imagen ' + (index + 1)"
                     class="w-full h-full object-contain"
                     loading="lazy">
            </div>
        </template>

        {{-- Navigation Arrows — only if multiple images --}}
        <template x-if="hasMultiple">
            <div>
                <button @click="prev()" type="button"
                        class="absolute left-3 top-1/2 -translate-y-1/2 w-11 h-11 bg-white/90 dark:bg-gray-900/90 backdrop-blur-sm text-gray-800 dark:text-white rounded-full flex items-center justify-center shadow-lg hover:bg-white dark:hover:bg-gray-800 transition-all hover:scale-105 border border-gray-200 dark:border-gray-700">
                    <i class="fas fa-chevron-left text-sm"></i>
                </button>
                <button @click="next()" type="button"
                        class="absolute right-3 top-1/2 -translate-y-1/2 w-11 h-11 bg-white/90 dark:bg-gray-900/90 backdrop-blur-sm text-gray-800 dark:text-white rounded-full flex items-center justify-center shadow-lg hover:bg-white dark:hover:bg-gray-800 transition-all hover:scale-105 border border-gray-200 dark:border-gray-700">
                    <i class="fas fa-chevron-right text-sm"></i>
                </button>
            </div>
        </template>

        {{-- Image Counter — top right --}}
        <template x-if="hasMultiple">
            <div class="absolute top-3 right-3 bg-black/60 backdrop-blur-sm text-white text-xs font-medium px-2.5 py-1 rounded-full">
                <span x-text="active + 1"></span> / <span x-text="total"></span>
            </div>
        </template>
    </div>

    {{-- Dots + Thumbnails Row --}}
    <template x-if="hasMultiple">
        <div class="mt-4 space-y-3">
            {{-- Dot Indicators --}}
            <div class="flex justify-center gap-2">
                <template x-for="(image, index) in images" :key="'dot-' + image.id">
                    <button @click="goTo(index)" type="button"
                            class="w-2.5 h-2.5 rounded-full transition-all duration-300"
                            :class="active === index
                                ? 'bg-blue-600 dark:bg-blue-400 w-7'
                                : 'bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500'">
                    </button>
                </template>
            </div>

            {{-- Thumbnail Strip --}}
            <div class="gallery-scroll flex gap-2 overflow-x-auto pb-1 justify-center">
                <template x-for="(image, index) in images" :key="'thumb-' + image.id">
                    <button @click="goTo(index)" type="button"
                            class="flex-shrink-0 w-[72px] h-[72px] rounded-xl overflow-hidden border-2 transition-all duration-200 hover:opacity-100"
                            :class="active === index
                                ? 'border-blue-600 dark:border-blue-400 ring-2 ring-blue-600/30 opacity-100 scale-105'
                                : 'border-transparent opacity-60 hover:border-gray-300 dark:hover:border-gray-600 hover:opacity-80'">
                        <img :src="image.url" alt="" class="w-full h-full object-cover" loading="lazy">
                    </button>
                </template>
            </div>
        </div>
    </template>
</div>
