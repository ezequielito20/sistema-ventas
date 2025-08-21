// Logic moved from resources/views/admin/purchases/edit.blade.php
// Alpine components: purchaseForm() and searchModal()

function purchaseForm() {
    if (typeof Alpine === 'undefined') return {};
    const root = document.getElementById('purchasesEditRoot');
    const currencySymbol = root?.dataset.currencySymbol || '';
    const referrerUrl = root?.dataset.referrerUrl || '';
    const indexUrl = root?.dataset.indexUrl || '';
    const initialProducts = (() => {
        try { return JSON.parse(root?.dataset.initialProducts || '[]'); } catch { return []; }
    })();
    const initialTotals = {
        amount: parseFloat(root?.dataset.totalAmount || 0),
        products: parseInt(root?.dataset.totalProducts || 0),
        quantity: parseInt(root?.dataset.totalQuantity || 0),
    };

    // Hydrate from Blade data present in the DOM via x-init if needed; fallback to window vars can be added
    return {
        formChanged: false,
        products: initialProducts.map(p => ({
            id: p.id,
            code: p.code,
            name: p.name,
            quantity: p.quantity,
            price: parseFloat(p.purchase_price ?? p.price ?? 0),
            purchase_price: parseFloat(p.purchase_price ?? p.price ?? 0),
            subtotal: parseFloat(p.subtotal ?? (p.quantity * (p.purchase_price ?? p.price ?? 0))),
            category: p.category ?? {},
            image_url: p.image_url,
            stock: p.stock ?? 0,
        })),
        totalAmount: initialTotals.amount || 0,
        totalProducts: initialTotals.products || 0,
        totalQuantity: initialTotals.quantity || 0,
        productCode: '',

        init() {
            if (window.purchaseFormInstance) return;
            this.$nextTick(() => {
                this.initializeEventListeners();
                this.updateEmptyState();
                window.purchaseFormInstance = this;
            });
        },

        initializeEventListeners() {
            this.$watch('productCode', (value) => {
                if (value && this.productCode) this.searchProductByCode(this.productCode);
            });
            this.$el.addEventListener('input', () => { this.formChanged = true; });
        },

        searchProductByCode(code) {
            if (!code) return;
            if (this.products.some(p => p.code === code)) {
                this.showToast('Este producto ya está en la lista de compra', 'warning');
                return;
            }
            fetch(`/purchases/product-by-code/${code}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        this.addProductToTable(data.product);
                        this.productCode = '';
                    } else {
                        this.showToast(data.message || 'No se encontró el producto', 'error');
                    }
                })
                .catch(() => this.showToast('No se encontró el producto', 'error'));
        },

        addProductToTable(product) {
            if (!product?.id) { this.showToast('El producto no tiene un ID válido', 'error'); return; }
            if (this.products.some(p => p.code === product.code)) { this.showToast('Este producto ya está en la lista de compra', 'warning'); return; }
            this.products.push({
                ...product,
                quantity: product.quantity ?? 1,
                price: parseFloat(product.purchase_price ?? product.price ?? 0),
                subtotal: parseFloat(product.purchase_price ?? product.price ?? 0)
            });
            this.updateTotal();
            this.updateEmptyState();
            this.showToast(`${product.name} se agregó a la lista de compra`, 'success');
        },

        updateProduct(index, field, value) {
            this.products[index][field] = parseFloat(value) || 0;
            this.products[index].subtotal = this.products[index].quantity * this.products[index].price;
            this.updateTotal();
        },

        removeProduct(index) {
            this.products.splice(index, 1);
            this.updateTotal();
            this.updateEmptyState();
        },

        updateTotal() {
            this.totalAmount = this.products.reduce((s, p) => s + (p.subtotal || 0), 0);
            this.totalProducts = this.products.length;
            this.totalQuantity = this.products.reduce((s, p) => s + (p.quantity || 0), 0);
            const totalInput = document.getElementById('totalAmountInput');
            if (totalInput) totalInput.value = this.totalAmount.toFixed(2);
            this.updateProductCounter();
        },

        updateProductCounter() {
            const el = document.querySelector('.counter-badge');
            if (el) el.textContent = `${this.totalProducts} producto${this.totalProducts !== 1 ? 's' : ''}`;
        },

        updateEmptyState() {
            const emptyState = document.getElementById('emptyState');
            if (emptyState) emptyState.style.display = this.products.length === 0 ? 'block' : 'none';
        },

        openSearchModal() { window.dispatchEvent(new CustomEvent('openSearchModal')); },

        cancelEdit() {
            if (this.formChanged) {
                if (confirm('¿Está seguro de que desea cancelar la edición? Los cambios no guardados se perderán.')) this.goBack();
            } else this.goBack();
        },

        goBack() {
            if (referrerUrl) { window.location.href = referrerUrl; return; }
            if (document.referrer && !document.referrer.includes('purchases/create') && !document.referrer.includes('purchases/edit')) {
                window.history.back();
            } else if (indexUrl) {
                window.location.href = indexUrl;
            }
        },

        submitForm() {
            if (this.products.length === 0) { this.showToast('Debe agregar al menos un producto a la compra', 'warning'); return false; }
            if (!document.getElementById('purchase_date').value) { this.showToast('Debe seleccionar una fecha de compra', 'warning'); return false; }
            this.prepareFormData();
            // evitar alerta de beforeunload durante submit
            window.__suppressBeforeUnload = true;
            return true;
        },

        prepareFormData() {
            this.products.forEach(product => {
                const q = document.createElement('input'); q.type = 'hidden'; q.name = `items[${product.id}][quantity]`; q.value = product.quantity;
                const p = document.createElement('input'); p.type = 'hidden'; p.name = `items[${product.id}][price]`; p.value = product.price;
                document.getElementById('purchaseForm').appendChild(q);
                document.getElementById('purchaseForm').appendChild(p);
            });
        },

        showToast(message, type = 'success') {
            if (typeof Swal !== 'undefined') {
                const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true });
                Toast.fire({ icon: type, title: message });
            } else {
            }
        }
    };
}

function searchModal() {
    if (typeof Alpine === 'undefined') return {};
    return {
        isOpen: false,
        searchTerm: '',
        init() { this.$nextTick(() => { this.listenForOpenEvent(); }); },
        listenForOpenEvent() { window.addEventListener('openSearchModal', () => { this.openModal(); }); },
        openModal() { this.isOpen = true; this.searchTerm = ''; document.body.style.overflow = 'hidden'; },
        closeModal() { this.isOpen = false; document.body.style.overflow = 'auto'; },
        filterProductsInModal() {
            const term = this.searchTerm.toLowerCase();
            const rows = document.querySelectorAll('#searchProductModal tbody tr');
            rows.forEach(row => {
                const code = row.querySelector('td:first-child span')?.textContent.toLowerCase() || '';
                const name = row.querySelector('td:nth-child(2) .text-sm.font-medium')?.textContent.toLowerCase() || '';
                const category = row.querySelector('td:nth-child(3) span')?.textContent.toLowerCase() || '';
                const matches = code.includes(term) || name.includes(term) || category.includes(term);
                row.style.display = matches ? '' : 'none';
            });
        },
        addProductFromModal(id, code, name, imageUrl, stock, price, categoryName) {
            const existing = window.purchaseFormInstance?.products?.find(p => p.code === code);
            if (existing) { this.showToast('Este producto ya está en la lista de compra', 'warning'); return; }
            const product = { id, code, name, image_url: imageUrl, stock, purchase_price: price, category: { name: categoryName }, quantity: 1, price, subtotal: price };
            window.purchaseFormInstance.addProductToTable(product);
            this.closeModal();
            this.showToast(`${name} se agregó a la lista de compra`, 'success');
        },
        showToast(message, type = 'success') {
            if (typeof Swal !== 'undefined') {
                const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true });
                Toast.fire({ icon: type, title: message });
            } else {
            }
        }
    };
}

// Global goBack mapping to purchaseForm.goBack for buttons using onclick="goBack()"
window.goBack = function () {
    if (window.purchaseFormInstance?.goBack) window.purchaseFormInstance.goBack();
};

// Leave page guard
const __editBeforeUnloadHandler = (event) => {
    if (window.__suppressBeforeUnload) return;
    if (window.purchaseFormInstance && window.purchaseFormInstance.formChanged) {
        event.preventDefault();
        event.returnValue = '';
    }
};
window.addEventListener('beforeunload', __editBeforeUnloadHandler);

// After Alpine is ready, wire up instance reference
document.addEventListener('DOMContentLoaded', () => {
    if (typeof Alpine !== 'undefined') {
        Alpine.nextTick(() => {
            const el = document.querySelector('[x-data="purchaseForm()"]');
            if (el && el._x_dataStack && el._x_dataStack[0]) {
                window.purchaseFormInstance = el._x_dataStack[0];
            }
        });
    }
});


