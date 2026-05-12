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
        selectedCategory: 'all',
        priceCeiling: 500000,
        priceSliderMax: 5000,
        priceSliderMin: 0,
        allProducts: [],

        init() {
            this.allProducts = window.__CATALOG_PRODUCTS__ || [];
            const products = this.allProducts;

            if (!products.length) {
                this.priceSliderMax = 5000;
                this.priceCeiling = 5000;

                return;
            }

            const max = Math.max(...products.map((p) => p.sale_price), 0);
            const rounded = Math.max(500, Math.ceil(max / 500) * 500);
            this.priceSliderMax = rounded;
            this.priceCeiling = rounded;
            this.priceSliderMin = 0;
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
            return this.allProducts.filter((p) => {
                const matchCat =
                    this.selectedCategory === 'all' || p.category_name === this.selectedCategory;
                const q = this.search.toLowerCase();
                const matchSearch =
                    !q ||
                    p.name.toLowerCase().includes(q) ||
                    (p.description && p.description.toLowerCase().includes(q)) ||
                    (p.code && p.code.toLowerCase().includes(q));
                const matchPrice = p.sale_price <= this.priceCeiling;

                return matchCat && matchSearch && matchPrice;
            });
        },

        selectCat(cat) {
            this.selectedCategory = cat;
        },
    }));

    Alpine.data('gallery', () => ({
        active: 0,
        images: window.__CATALOG_GALLERY_IMAGES__ || [],

        prev() {
            const n = this.images.length;
            if (!n) {
                return;
            }
            this.active = this.active === 0 ? n - 1 : this.active - 1;
        },

        next() {
            const n = this.images.length;
            if (!n) {
                return;
            }
            this.active = this.active === n - 1 ? 0 : this.active + 1;
        },

        goTo(i) {
            this.active = i;
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
