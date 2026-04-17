/**
 * Compras v2 — listado SPA (Alpine + fetch), sin dependencia del JS legacy.
 */
(function () {
    const cfg = () => window.purchasesV2Config || { routes: {}, currencySymbol: '$' };

    async function confirmBulkDeletePurchases(count) {
        const title = '¿Eliminar compras seleccionadas?';
        const text =
            'Se revertirán los movimientos de stock asociados a cada compra. Esta acción no se puede deshacer.';
        try {
            if (window.uiNotifications && typeof window.uiNotifications.confirmDialog === 'function') {
                return await window.uiNotifications.confirmDialog({
                    title,
                    text,
                    subtitle: 'Verifica antes de confirmar.',
                    type: 'warning',
                    confirmText: 'Sí, eliminar',
                    cancelText: 'Cancelar',
                    metrics: [{ label: 'Compras seleccionadas', value: String(count) }],
                });
            }
            if (typeof Swal !== 'undefined') {
                const r = await Swal.fire({
                    title,
                    text,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true,
                    focusCancel: true,
                    customClass: {
                        popup: 'ui-swal-popup ui-swal-popup--futuristic',
                        confirmButton: 'ui-swal-confirm',
                        cancelButton: 'ui-swal-cancel',
                        htmlContainer: 'ui-swal-html',
                    },
                });
                return Boolean(r.isConfirmed);
            }
        } catch (e) {
            console.error(e);
            return false;
        }
        return window.confirm(`${title}\n\n${text}`);
    }

    async function confirmDeletePurchase() {
        const title = '¿Eliminar esta compra?';
        const text = 'Se revertirán movimientos de stock asociados. Esta acción no se puede deshacer.';
        try {
            if (window.uiNotifications && typeof window.uiNotifications.confirmDialog === 'function') {
                return await window.uiNotifications.confirmDialog({
                    title,
                    text,
                    subtitle: 'Verifica antes de confirmar.',
                    type: 'warning',
                    confirmText: 'Sí, eliminar',
                    cancelText: 'Cancelar',
                });
            }
            if (typeof Swal !== 'undefined') {
                const r = await Swal.fire({
                    title,
                    text,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true,
                    focusCancel: true,
                    customClass: {
                        popup: 'ui-swal-popup ui-swal-popup--futuristic',
                        confirmButton: 'ui-swal-confirm',
                        cancelButton: 'ui-swal-cancel',
                        htmlContainer: 'ui-swal-html',
                    },
                });
                return Boolean(r.isConfirmed);
            }
        } catch (e) {
            console.error(e);
            return false;
        }
        return window.confirm(`${title}\n\n${text}`);
    }

    function notify(msg, type = 'success') {
        const titles = { success: 'Listo', error: 'Error', info: 'Información' };
        if (window.uiNotifications && typeof window.uiNotifications.showToast === 'function') {
            window.uiNotifications.showToast(msg, {
                type: type === 'error' ? 'error' : 'success',
                title: titles[type] || titles.info,
                timeout: type === 'success' ? 3800 : 5200,
                theme: 'futuristic',
            });
            return;
        }
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: type, title: titles[type] || titles.info, text: msg });
        } else {
            alert(msg);
        }
    }

    function getCsrfToken() {
        return (
            document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
            document.querySelector('input[name="_token"]')?.value ||
            ''
        );
    }

    function purchasesV2(initial = {}) {
        const products = Array.isArray(initial.products) ? initial.products : [];

        return {
            showFilters: false,
            searchTerm: initial.search || '',
            selectedProductId: initial.productId ? String(initial.productId) : '',
            selectedProductName: 'Todos los productos',
            paymentStatus: initial.paymentStatus || '',
            dateFrom: initial.dateFrom || '',
            dateTo: initial.dateTo || '',
            productMenuOpen: false,
            productQuery: '',
            products,
            _searchTimer: null,
            canDestroy: initial.canDestroy === true,
            selectionMode: false,
            selectedPurchaseIds: [],
            isDeleting: false,

            init() {
                window.__purchasesV2AlpineCtx = this;
                window.purchasesV2Data = window.purchasesV2Data || { pagePurchaseIds: [] };
                this.resolveProductLabel();
                this.$watch('searchTerm', () => {
                    clearTimeout(this._searchTimer);
                    this._searchTimer = setTimeout(() => this.executeServerSearch(), 320);
                });
            },

            toggleSelectionMode() {
                if (!this.canDestroy) return;
                this.selectionMode = !this.selectionMode;
                if (!this.selectionMode) {
                    this.selectedPurchaseIds = [];
                }
            },

            togglePurchaseSelection(purchaseId) {
                const id = Number(purchaseId);
                if (this.selectedPurchaseIds.includes(id)) {
                    this.selectedPurchaseIds = this.selectedPurchaseIds.filter((x) => x !== id);
                } else {
                    this.selectedPurchaseIds.push(id);
                }
            },

            currentPagePurchaseIds() {
                const raw = window.purchasesV2Data?.pagePurchaseIds || [];
                return raw.map((x) => Number(x)).filter(Number.isFinite);
            },

            allCurrentPageSelected() {
                const ids = this.currentPagePurchaseIds();
                return ids.length > 0 && ids.every((id) => this.selectedPurchaseIds.includes(id));
            },

            toggleSelectAllOnPage() {
                const ids = this.currentPagePurchaseIds();
                if (!ids.length) return;
                if (this.allCurrentPageSelected()) {
                    this.selectedPurchaseIds = this.selectedPurchaseIds.filter((id) => !ids.includes(id));
                } else {
                    const merged = new Set([...this.selectedPurchaseIds, ...ids]);
                    this.selectedPurchaseIds = [...merged];
                }
            },

            async deleteSelectedPurchases() {
                if (!this.canDestroy || !this.selectedPurchaseIds.length || this.isDeleting) return;
                const count = this.selectedPurchaseIds.length;
                const confirmed = await confirmBulkDeletePurchases(count);
                if (!confirmed) return;

                this.isDeleting = true;
                const base = cfg().routes.destroy || '';
                const baseUrl = base.replace(/\/$/, '');
                try {
                    for (const purchaseId of this.selectedPurchaseIds) {
                        const response = await fetch(`${baseUrl}/${purchaseId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': getCsrfToken(),
                                Accept: 'application/json',
                                'Content-Type': 'application/json',
                            },
                        });
                        const data = await response.json().catch(() => ({}));
                        if (!response.ok || !data.success) {
                            throw new Error(data.message || `No se pudo eliminar la compra ${purchaseId}`);
                        }
                    }
                    notify('Compras eliminadas correctamente.', 'success');
                    this.selectionMode = false;
                    this.selectedPurchaseIds = [];
                    setTimeout(() => window.location.reload(), 900);
                } catch (e) {
                    notify(e.message || 'Error al eliminar las compras seleccionadas.', 'error');
                } finally {
                    this.isDeleting = false;
                }
            },

            resolveProductLabel() {
                if (!this.selectedProductId) {
                    this.selectedProductName = 'Todos los productos';
                    return;
                }
                const p = this.products.find((x) => String(x.id) === String(this.selectedProductId));
                this.selectedProductName = p ? p.name : 'Producto';
            },

            filteredProducts() {
                const q = (this.productQuery || '').trim().toLowerCase();
                if (!q) return this.products;
                return this.products.filter((p) => {
                    const name = (p.name || '').toLowerCase();
                    const code = (p.code || '').toLowerCase();
                    const cat = p.category && p.category.name ? p.category.name.toLowerCase() : '';
                    return name.includes(q) || code.includes(q) || cat.includes(q);
                });
            },

            selectProduct(p) {
                if (!p) {
                    this.selectedProductId = '';
                    this.selectedProductName = 'Todos los productos';
                } else {
                    this.selectedProductId = String(p.id);
                    this.selectedProductName = p.name;
                }
                this.productQuery = '';
                this.executeServerSearch();
            },

            clearSearch() {
                this.searchTerm = '';
            },

            buildListUrl() {
                const base = cfg().routes.index || `${window.location.origin}/purchases/v2`;
                const url = new URL(base, window.location.href);
                if (this.searchTerm.trim()) url.searchParams.set('search', this.searchTerm.trim());
                else url.searchParams.delete('search');
                if (this.selectedProductId) url.searchParams.set('product_id', this.selectedProductId);
                else url.searchParams.delete('product_id');
                if (this.paymentStatus) url.searchParams.set('payment_status', this.paymentStatus);
                else url.searchParams.delete('payment_status');
                if (this.dateFrom) url.searchParams.set('date_from', this.dateFrom);
                else url.searchParams.delete('date_from');
                if (this.dateTo) url.searchParams.set('date_to', this.dateTo);
                else url.searchParams.delete('date_to');
                url.searchParams.delete('page');
                return url.toString();
            },

            executeServerSearch() {
                loadPurchasesV2Page(this.buildListUrl());
            },

            resetFilters() {
                this.searchTerm = '';
                this.selectedProductId = '';
                this.selectedProductName = 'Todos los productos';
                this.paymentStatus = '';
                this.dateFrom = '';
                this.dateTo = '';
                this.productQuery = '';
                this.executeServerSearch();
            },
        };
    }

    window.purchasesV2 = purchasesV2;

    function loadPurchasesV2Page(url) {
        const root = document.getElementById('purchases-v2-dynamic');
        if (!root) return;

        root.style.opacity = '0.65';
        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'text/html, application/xhtml+xml',
            },
        })
            .then((r) => {
                if (!r.ok) throw new Error('Error al cargar');
                return r.text();
            })
            .then((html) => {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const next = doc.getElementById('purchases-v2-dynamic');
                if (next && root) {
                    root.innerHTML = next.innerHTML;
                }
                const rowIds = Array.from(
                    doc.querySelectorAll('#purchases-v2-dynamic .purchases-v2-table tbody tr[data-purchase-id]'),
                )
                    .map((row) => parseInt(row.getAttribute('data-purchase-id'), 10))
                    .filter(Number.isFinite);
                window.purchasesV2Data = window.purchasesV2Data || {};
                window.purchasesV2Data.pagePurchaseIds = rowIds;
                const ctx = window.__purchasesV2AlpineCtx;
                if (ctx && Array.isArray(ctx.selectedPurchaseIds)) {
                    ctx.selectedPurchaseIds = [];
                }
                window.history.pushState({}, '', url);
            })
            .catch((e) => {
                console.error(e);
                notify('No se pudieron cargar los datos.', 'error');
            })
            .finally(() => {
                root.style.opacity = '';
            });
    }

    window.loadPurchasesV2Page = loadPurchasesV2Page;

    function openModalV2() {
        const el = document.getElementById('purchaseDetailsModalV2');
        if (!el) return;
        el.classList.remove('hidden');
        el.classList.add('flex');
    }

    function closeModalV2() {
        const el = document.getElementById('purchaseDetailsModalV2');
        if (!el) return;
        el.classList.add('hidden');
        el.classList.remove('flex');
        const tb = document.getElementById('purchaseDetailsTableBodyV2');
        if (tb) tb.innerHTML = '<tr><td colspan="7" class="py-6 text-center text-slate-500">—</td></tr>';
    }

    window.closePurchaseModalV2 = closeModalV2;

    const sym = () => cfg().currencySymbol || '$';

    function formatNum(n) {
        try {
            return Number(n).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        } catch (_) {
            return Number(n).toFixed(2);
        }
    }

    async function loadPurchaseDetailsV2(purchaseId) {
        const tbody = document.getElementById('purchaseDetailsTableBodyV2');
        if (!tbody) return;
        tbody.innerHTML =
            '<tr><td colspan="7" class="py-8 text-center text-slate-400">Cargando…</td></tr>';
        openModalV2();

        const base = cfg().routes.details || '';
        const url = `${base.replace(/\/$/, '')}/${purchaseId}/details`;

        try {
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                },
            });
            const data = await response.json();
            if (!data.success) {
                tbody.innerHTML = `<tr><td colspan="7" class="py-8 text-center text-rose-400">${data.message || 'Error'}</td></tr>`;
                return;
            }
            const details = data.details;
            const purchase = data.purchase;
            tbody.innerHTML = '';

            details.forEach((detail) => {
                const quantity = parseFloat(detail.quantity);
                const originalPrice = parseFloat(detail.product_price);
                const discountValue = parseFloat(detail.discount_value) || 0;
                const discountType = detail.discount_type;
                const subtotal = parseFloat(detail.subtotal);
                let discountDisplay = '—';
                if (discountValue > 0) {
                    discountDisplay =
                        discountType === 'percentage' ? `${discountValue}%` : `${sym()} ${formatNum(discountValue)}`;
                }
                const tr = document.createElement('tr');
                tr.innerHTML = `
          <td class="text-slate-300">${detail.product?.code || ''}</td>
          <td class="font-medium text-slate-100">${detail.product?.name || ''}</td>
          <td class="text-slate-400">${detail.product?.category || '—'}</td>
          <td class="text-center tabular-nums text-slate-200">${quantity}</td>
          <td class="text-right tabular-nums text-slate-200">${sym()} ${formatNum(originalPrice)}</td>
          <td class="text-right text-cyan-200/90">${discountDisplay}</td>
          <td class="text-right font-medium tabular-nums text-emerald-300">${sym()} ${formatNum(subtotal)}</td>`;
                tbody.appendChild(tr);
            });

            const subEl = document.getElementById('modalSubtotalBeforeV2');
            const discEl = document.getElementById('modalGeneralDiscountV2');
            const totEl = document.getElementById('modalTotalV2');
            if (subEl) subEl.textContent = formatNum(purchase.subtotal_before_discount);
            if (discEl) {
                if (parseFloat(purchase.general_discount_value) > 0) {
                    discEl.textContent =
                        purchase.general_discount_type === 'percentage'
                            ? `${purchase.general_discount_value}%`
                            : `${sym()} ${formatNum(purchase.general_discount_value)}`;
                } else {
                    discEl.textContent = '—';
                }
            }
            if (totEl) totEl.textContent = formatNum(purchase.total_with_discount || purchase.total_price);
        } catch (err) {
            console.error(err);
            tbody.innerHTML =
                '<tr><td colspan="7" class="py-8 text-center text-rose-400">Error de conexión</td></tr>';
        }
    }

    async function deletePurchaseV2(purchaseId) {
        const ok = await confirmDeletePurchase();
        if (!ok) return;
        const base = cfg().routes.destroy || '';
        const url = `${base.replace(/\/$/, '')}/${purchaseId}`;
        try {
            const response = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                },
            });
            const data = await response.json();
            if (data.success) {
                notify(data.message || 'Compra eliminada.', 'success');
                setTimeout(() => window.location.reload(), 900);
            } else {
                notify(data.message || 'No se pudo eliminar.', 'error');
            }
        } catch (e) {
            notify('Error de conexión al eliminar.', 'error');
        }
    }

    /** Delegación: paginación SPA */
    if (!window.__purchasesV2PaginationBound) {
        window.__purchasesV2PaginationBound = true;

        document.addEventListener('click', (e) => {
            const a = e.target.closest('#purchasesV2Pagination a[href]');
            if (!a || !a.getAttribute('href')) return;
            let targetUrl;
            try {
                targetUrl = new URL(a.href, window.location.href);
            } catch (_) {
                return;
            }
            if (targetUrl.origin !== window.location.origin) return;
            e.preventDefault();
            loadPurchasesV2Page(targetUrl.toString());
        });

        document.addEventListener('change', (e) => {
            const sel = e.target.closest('#purchasesV2Pagination select[name="per_page"]');
            if (!sel) return;
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', sel.value);
            url.searchParams.set('page', '1');
            loadPurchasesV2Page(url.toString());
        });

        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.purchases-v2-details');
            if (btn && btn.dataset.id) {
                e.preventDefault();
                loadPurchaseDetailsV2(btn.dataset.id);
            }
            const del = e.target.closest('.purchases-v2-delete');
            if (del && del.dataset.id) {
                e.preventDefault();
                deletePurchaseV2(del.dataset.id);
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeModalV2();
        });

        document.getElementById('purchaseDetailsModalV2')?.addEventListener('click', (e) => {
            if (e.target.id === 'purchaseDetailsModalV2') closeModalV2();
        });
    }
})();
