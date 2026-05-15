import '@fontsource/metropolis/600.css';
import '@fontsource/metropolis/700.css';
import '@fontsource/inter/400.css';
import '@fontsource/inter/600.css';
import '@fontsource/geist-sans/500.css';

import '../sass/catalog-public.scss';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener('catalog-cart-error', (e) => {
    const msg = e.detail?.message ?? 'No se pudo actualizar el carrito.';
    window.alert(msg);
});

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

        cartLineCount: 0,
        cartQtyTotal: 0,
        cartSubtotalUsd: 0,
        cartItems: [],
        cartUrls: window.__CATALOG_CART_URLS__ || {},
        cartPanelOpen: false,
        lineSyncing: false,
        /** Posición viewport del panel fijo del carrito (evita clipping por overflow-x del nav). */
        cartPopoverStyle: {},
        cartPopoverCaretLeft: 272,

        init() {
            this.allProducts = window.__CATALOG_PRODUCTS__ || [];
            const products = this.allProducts;

            if (!products.length) {
                this.priceSliderMax = 5000;
            } else {
                const max = Math.max(...products.map((p) => p.sale_price), 0);
                const rounded = Math.max(500, Math.ceil(max / 500) * 500);
                this.priceSliderMax = rounded;
            }

            this.refreshCart();

            const repositionIfOpen = () => {
                if (this.cartPanelOpen) {
                    this.positionCatalogCartPopover();
                }
            };
            window.addEventListener('resize', repositionIfOpen);
            window.addEventListener('scroll', repositionIfOpen, true);

            const vp = typeof window.visualViewport !== 'undefined' ? window.visualViewport : null;
            if (vp) {
                vp.addEventListener('resize', repositionIfOpen);
                vp.addEventListener('scroll', repositionIfOpen);
            }

            this.$watch('cartPanelOpen', (open) => {
                if (open) {
                    this.$nextTick(() => this.positionCatalogCartPopover());
                } else {
                    this.cartPopoverStyle = {};
                }
            });
        },

        positionCatalogCartPopover() {
            this.$nextTick(() => {
                // Dos frames: después de que Alpine aplique x-show/plantillas, el alto real existe.
                requestAnimationFrame(() => {
                    this.runCatalogCartPopoverLayout();
                    requestAnimationFrame(() => this.runCatalogCartPopoverLayout());
                });
            });
        },

        runCatalogCartPopoverLayout() {
            const trig = document.getElementById('catalog-cart-trigger');
            const panel = document.getElementById('catalog-cart-panel');
            if (! trig || ! panel || ! this.cartPanelOpen) {
                this.cartPopoverStyle = {};

                return;
            }

            const br = trig.getBoundingClientRect();
            const vv = window.visualViewport;
            const vw = vv?.width ?? window.innerWidth;
            const vvHeight = vv?.height ?? window.innerHeight;
            const vt = vv?.offsetTop ?? 0;
            const margin = 12;
            /** Área usable del viewport visible (útil cuando la UI del navegador reduce `innerHeight`). */
            const visibleTop = vt + margin;
            const visibleBottom = vt + vvHeight - margin;

            const gap = 8;
            const caretOverhangPx = 10;
            const panelW = Math.min(Math.max(260, vw - 2 * margin), 352);
            const maxPanelH = Math.max(160, Math.min(Math.floor(vvHeight * 0.92), visibleBottom - visibleTop));

            let left = Math.round(br.right - panelW);
            left = Math.max(margin, Math.min(left, vw - panelW - margin));

            const preferBelowTop = Math.round(br.bottom + gap);

            // Medimos anclado «debajo del botón» (sin teleportar fuera del viewport): evita alturas 0 al primer pintado.
            this.cartPopoverStyle = {
                position: 'fixed',
                zIndex: 9999,
                top: `${preferBelowTop}px`,
                left: `${left}px`,
                width: `${panelW}px`,
                maxWidth: `${panelW}px`,
                maxHeight: `${maxPanelH}px`,
                right: 'auto',
                visibility: 'hidden',
                pointerEvents: 'none',
                transform: 'none',
            };

            panel.offsetHeight;
            let ph = panel.getBoundingClientRect().height;
            if (! Number.isFinite(ph) || ph < 32) {
                ph = Math.min(panel.scrollHeight, maxPanelH);
            }
            if (! Number.isFinite(ph) || ph < 32) {
                ph = 280;
            }
            ph = Math.min(ph, maxPanelH);

            let top = preferBelowTop;
            const fitsBelow = top + ph <= visibleBottom;

            if (! fitsBelow) {
                const aboveTop = Math.round(br.top - gap - ph);
                if (aboveTop >= visibleTop) {
                    top = aboveTop;
                } else {
                    top = Math.max(visibleTop, Math.round(visibleBottom - ph));
                }
            }

            top = Math.max(Math.ceil(visibleTop + caretOverhangPx), Math.round(top));

            const cx = br.left + br.width / 2;
            const caret = Math.max(18, Math.min(Math.round(cx - left), panelW - 18));
            this.cartPopoverCaretLeft = caret;

            this.cartPopoverStyle = {
                position: 'fixed',
                zIndex: 9999,
                top: `${top}px`,
                left: `${left}px`,
                width: `${panelW}px`,
                maxWidth: `${panelW}px`,
                maxHeight: `${maxPanelH}px`,
                right: 'auto',
                visibility: 'visible',
                pointerEvents: 'auto',
                transform: 'none',
            };
        },


        qtyInCart(productId) {
            const line = this.cartItems.find((r) => Number(r.product_id) === Number(productId));

            return line ? Number(line.quantity) : 0;
        },

        async refreshCart() {
            const u = this.cartUrls.get;
            if (!u) return;
            try {
                const r = await fetch(u, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });
                if (!r.ok) return;
                const j = await r.json();
                this.cartLineCount = j.line_count || 0;
                this.cartQtyTotal = j.quantity_total || 0;
                this.cartItems = Array.isArray(j.items) ? j.items : [];
                this.cartSubtotalUsd = Number(j.subtotal_usd ?? 0);
            } catch (e) {
                /* ignore */
            }
        },

        async syncCartLine(productId, quantity) {
            const u = this.cartUrls.sync;
            if (!u) return;
            const q = Math.max(0, Math.min(9999, Math.floor(Number(quantity))));
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            this.lineSyncing = true;
            try {
                const r = await fetch(u, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': token || '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ product_id: productId, quantity: q }),
                });
                if (!r.ok) {
                    let msg = 'No se pudo actualizar el carrito.';
                    try {
                        const j = await r.json();
                        if (j.message) msg = j.message;
                        if (j.errors) {
                            const first = Object.values(j.errors)[0];
                            if (Array.isArray(first) && first[0]) msg = first[0];
                        }
                    } catch (e) {
                        /* */
                    }
                    window.dispatchEvent(new CustomEvent('catalog-cart-error', { detail: { message: msg } }));
                    return;
                }
                await this.refreshCart();
                window.dispatchEvent(new CustomEvent('catalog-cart-updated'));
                if (this.cartPanelOpen) {
                    this.positionCatalogCartPopover();
                }
            } finally {
                this.lineSyncing = false;
            }
        },

        async addToCart(productId, qty = 1) {
            await this.syncCartLine(productId, qty);
        },

        incrementProductInCart(product) {
            const stock = Number(product?.stock ?? 0);
            if (stock <= 0) return;
            const cur = this.qtyInCart(product.id);
            if (cur >= stock) return;
            this.syncCartLine(product.id, cur + 1);
        },

        incrementProductById(productId, stockMax) {
            const max = Number(stockMax);
            const cur = this.qtyInCart(productId);
            if (max > 0 && cur >= max) return;
            this.syncCartLine(productId, cur + 1);
        },

        incrementLine(line) {
            const cur = Number(line.quantity ?? 0);
            if (typeof line.stock === 'number' && cur >= line.stock) {
                return;
            }
            this.syncCartLine(line.product_id, cur + 1);
        },

        decrementLine(line) {
            const cur = Number(line.quantity ?? 0);
            this.syncCartLine(line.product_id, cur - 1);
        },

        removeLineFromCart(line) {
            this.syncCartLine(line.product_id, 0);
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
