import '@fontsource/metropolis/600.css';
import '@fontsource/metropolis/700.css';
import '@fontsource/inter/400.css';
import '@fontsource/inter/600.css';
import '@fontsource/geist-sans/500.css';

import '../sass/catalog-public.scss';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.data('catalog', () => ({
        search: '',
        showFilters: false,
        selectedCategory: 'all',
        priceMin: null,
        priceMax: null,
        priceSliderMax: 5000,
        onlyDiscounted: false,
        allProducts: [],
        sortBy: 'name_asc',

        init() {
            this.allProducts = window.__CATALOG_PRODUCTS__ || [];
            const products = this.allProducts;

            if (!products.length) {
                this.priceSliderMax = 5000;
                return;
            }

            const max = Math.max(...products.map((p) => p.sale_price), 0);
            const rounded = Math.max(500, Math.ceil(max / 500) * 500);
            this.priceSliderMax = rounded;
        },

        formatPrice(value) {
            return Number(value).toLocaleString('es-VE', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        },

        productUrl(id) {
            const base = window.__CATALOG_PRODUCT_BASE__ || '';
            return `${base}/${id}`;
        },

        get filtered() {
            const min = this.priceMin !== null && this.priceMin !== '' ? Number(this.priceMin) : 0;
            const max = this.priceMax !== null && this.priceMax !== '' ? Number(this.priceMax) : Infinity;

            return this.allProducts
                .filter((p) => {
                    if (this.selectedCategory !== 'all' && p.category_name !== this.selectedCategory) return false;

                    const q = this.search.toLowerCase();
                    if (q && !p.name.toLowerCase().includes(q) && !(p.description && p.description.toLowerCase().includes(q)) && !(p.code && p.code.toLowerCase().includes(q))) return false;

                    const price = p.final_price ?? p.sale_price;
                    if (price < min || price > max) return false;

                    if (this.onlyDiscounted && !p.has_discount) return false;

                    return true;
                })
                .sort((a, b) => {
                    switch (this.sortBy) {
                        case 'name_asc': return a.name.localeCompare(b.name);
                        case 'name_desc': return b.name.localeCompare(a.name);
                        case 'price_asc': return (a.final_price ?? a.sale_price) - (b.final_price ?? b.sale_price);
                        case 'price_desc': return (b.final_price ?? b.sale_price) - (a.final_price ?? a.sale_price);
                        default: return 0;
                    }
                });
        },

        get filteredCount() {
            return this.filtered.length;
        },

        get hasActiveFilters() {
            const hasPriceMin = this.priceMin !== null && this.priceMin !== '';
            const hasPriceMax = this.priceMax !== null && this.priceMax !== '';
            return (
                this.selectedCategory !== 'all'
                || this.onlyDiscounted
                || hasPriceMin
                || hasPriceMax
                || this.sortBy !== 'name_asc'
            );
        },

        selectCat(cat) {
            this.selectedCategory = cat;
        },

        toggleDiscounted() {
            this.onlyDiscounted = !this.onlyDiscounted;
        },

        resetFilters() {
            this.search = '';
            this.selectedCategory = 'all';
            this.priceMin = null;
            this.priceMax = null;
            this.sortBy = 'name_asc';
            this.onlyDiscounted = false;
        },
    }));

    Alpine.data('gallery', () => ({
        active: 0,
        images: window.__CATALOG_GALLERY_IMAGES__ || [],

        init() {
            this.preloadAdjacent();
        },

        preloadAdjacent() {
            const n = this.images.length;
            if (n < 2) return;

            const prevIdx = this.active === 0 ? n - 1 : this.active - 1;
            const nextIdx = this.active === n - 1 ? 0 : this.active + 1;

            [prevIdx, nextIdx].forEach((idx) => {
                const img = new Image();
                img.src = this.images[idx].url;
            });
        },

        prev() {
            const n = this.images.length;
            if (!n) return;
            this.active = this.active === 0 ? n - 1 : this.active - 1;
            this.preloadAdjacent();
        },

        next() {
            const n = this.images.length;
            if (!n) return;
            this.active = this.active === n - 1 ? 0 : this.active + 1;
            this.preloadAdjacent();
        },

        goTo(i) {
            this.active = i;
            this.preloadAdjacent();
        },

        touchS: 0,
        touchE: 0,

        touchStart(e) {
            this.touchS = e.touches[0].clientX;
        },

        touchMove(e) {
            this.touchE = e.touches[0].clientX;
        },

        touchEnd() {
            if (Math.abs(this.touchS - this.touchE) > 50) {
                if (this.touchS > this.touchE) {
                    this.next();
                } else {
                    this.prev();
                }
            }
        },

        keyNav(e) {
            if (e.key === 'ArrowLeft') {
                e.preventDefault();
                this.prev();
            }
            if (e.key === 'ArrowRight') {
                e.preventDefault();
                this.next();
            }
        },
    }));
});

Alpine.start();
