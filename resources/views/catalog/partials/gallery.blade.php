{{--
    Premium Gallery — Digital Vault style.
    Expects: $images (Collection of ProductImage)
--}}
<div x-data="{
    active: 0,
    images: {{ Js::from($images->map(fn($img) => ['url' => $img->image_url, 'id' => $img->id])) }},
    get total() { return this.images.length; },
    get hasMultiple() { return this.total > 1; },
    prev() { this.active = this.active === 0 ? this.total - 1 : this.active - 1; },
    next() { this.active = this.active === this.total - 1 ? 0 : this.active + 1; },
    goTo(i) { this.active = i; },
    touchS: 0, touchE: 0,
    touchStart(e) { this.touchS = e.touches[0].clientX; },
    touchMove(e) { this.touchE = e.touches[0].clientX; },
    touchEnd() { if (Math.abs(this.touchS - this.touchE) > 50) { this.touchS > this.touchE ? this.next() : this.prev(); } },
    keyNav(e) { if (e.key === 'ArrowLeft') { e.preventDefault(); this.prev(); } if (e.key === 'ArrowRight') { e.preventDefault(); this.next(); } }
}" class="w-full" @keydown.window="keyNav">

    {{-- Main Image --}}
    <div class="relative bg-dv-surface-container rounded-2xl overflow-hidden border border-dv-outline-variant/20"
         @touchstart="touchStart" @touchmove="touchMove" @touchend="touchEnd">

        <template x-if="total === 0">
            <div class="aspect-[4/3] flex flex-col items-center justify-center gap-3">
                <div class="w-16 h-16 rounded-full bg-dv-surface-container-highest flex items-center justify-center">
                    <i class="fas fa-image text-xl text-dv-outline"></i>
                </div>
                <span class="text-xs text-dv-outline">Sin imágenes</span>
            </div>
        </template>

        <template x-for="(img, i) in images" :key="img.id">
            <div x-show="active === i"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 class="aspect-[4/3] flex items-center justify-center cursor-pointer"
                 @click="window.open(img.url, '_blank')">
                <img :src="img.url" alt="" class="w-full h-full object-contain" loading="lazy">
            </div>
        </template>

        {{-- Arrows --}}
        <template x-if="hasMultiple">
            <div>
                <button @click="prev()" type="button"
                        class="absolute left-3 top-1/2 -translate-y-1/2 w-10 h-10 bg-dv-surface/80 backdrop-blur-sm text-dv-on-surface rounded-full flex items-center justify-center border border-white/10 hover:bg-dv-surface-container-high transition-all hover:scale-105">
                    <i class="fas fa-chevron-left text-xs"></i>
                </button>
                <button @click="next()" type="button"
                        class="absolute right-3 top-1/2 -translate-y-1/2 w-10 h-10 bg-dv-surface/80 backdrop-blur-sm text-dv-on-surface rounded-full flex items-center justify-center border border-white/10 hover:bg-dv-surface-container-high transition-all hover:scale-105">
                    <i class="fas fa-chevron-right text-xs"></i>
                </button>
            </div>
        </template>

        {{-- Counter --}}
        <template x-if="hasMultiple">
            <div class="absolute top-3 right-3 bg-dv-surface/80 backdrop-blur-sm text-xs font-medium px-2.5 py-1 rounded-full text-dv-on-surface-variant">
                <span x-text="active + 1"></span>/<span x-text="total"></span>
            </div>
        </template>
    </div>

    {{-- Dots + Thumbs --}}
    <template x-if="hasMultiple">
        <div class="mt-4 space-y-3">
            <div class="flex justify-center gap-2">
                <template x-for="(img, i) in images" :key="'dot-'+img.id">
                    <button @click="goTo(i)" type="button"
                            class="w-2 h-2 rounded-full transition-all duration-300"
                            :class="active === i ? 'bg-dv-primary w-6' : 'bg-dv-outline-variant/60 hover:bg-dv-outline'"></button>
                </template>
            </div>
            <div class="flex gap-2 overflow-x-auto justify-center pb-1 gallery-scroll">
                <template x-for="(img, i) in images" :key="'thumb-'+img.id">
                    <button @click="goTo(i)" type="button"
                            class="flex-shrink-0 w-16 h-16 rounded-xl overflow-hidden border-2 transition-all duration-200"
                            :class="active === i ? 'border-dv-primary ring-2 ring-dv-primary/30 scale-105 opacity-100' : 'border-transparent opacity-50 hover:opacity-80 hover:border-dv-outline-variant/50'">
                        <img :src="img.url" alt="" class="w-full h-full object-cover" loading="lazy">
                    </button>
                </template>
            </div>
        </div>
    </template>
</div>
