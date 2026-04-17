window.modalManagerV2 = function () {
    const baseFactory = window.modalManager;
    const base = typeof baseFactory === 'function' ? baseFactory() : {};

    return {
        ...base,
        showCustomerModalV2: false,
        customerSalesDataV2: [],

        openCustomerDetailsModal(customerId) {
            this.showCustomerModalV2 = true;
            document.body.style.overflow = 'hidden';
            this.loadCustomerDetailsV2(customerId);
        },

        closeCustomerDetailsModal() {
            this.showCustomerModalV2 = false;
            this.clearCustomerFiltersV2();
            document.body.style.overflow = 'auto';
        },

        closeAllModals() {
            this.showCustomerModalV2 = false;

            if (typeof base.closeAllModals === 'function') {
                base.closeAllModals.call(this);
            } else {
                document.body.style.overflow = 'auto';
            }
        },

        async loadCustomerDetailsV2(customerId) {
            try {
                const [customerResp, salesResp] = await Promise.all([
                    fetch(`/customers/${customerId}?customer_details=1`),
                    fetch(`/admin/customers/${customerId}/sales-history`),
                ]);

                const customerData = await customerResp.json();
                const salesData = await salesResp.json();

                if (!customerData.success) {
                    return;
                }

                const c = customerData.customer;

                const customerNameEl = document.getElementById('v2_customerName');
                const nameInput = document.getElementById('v2_customer_name_details');
                const phoneInput = document.getElementById('v2_customer_phone_details');
                const lastPaymentInput = document.getElementById('v2_customer_last_payment_details');
                const statusEl = document.getElementById('v2_customer_status_details');

                if (customerNameEl) customerNameEl.textContent = c.name;
                if (nameInput) nameInput.value = c.name;
                if (phoneInput) phoneInput.value = c.phone || 'No disponible';
                if (lastPaymentInput) {
                    if (c.last_payment) {
                        lastPaymentInput.value = `${c.last_payment.date} por $${c.last_payment.amount}`;
                    } else {
                        lastPaymentInput.value = 'Sin pagos registrados';
                    }
                }

                if (statusEl) {
                    if (c.is_defaulter) {
                        statusEl.className = 'ui-badge ui-badge-danger';
                        statusEl.innerHTML = '<i class="fas fa-exclamation-triangle mr-1"></i>Moroso';
                    } else {
                        statusEl.className = 'ui-badge ui-badge-success';
                        statusEl.innerHTML = '<i class="fas fa-check-circle mr-1"></i>Actual';
                    }
                }

                this.customerSalesDataV2 = salesData.success ? (salesData.sales || []) : [];
                this.renderSalesHistoryV2(this.customerSalesDataV2);
                this.bindCustomerDetailsFiltersV2();
            } catch (error) {
                // No-op: preservamos comportamiento silencioso del módulo original.
            }
        },

        bindCustomerDetailsFiltersV2() {
            const dateFrom = document.getElementById('v2_dateFrom');
            const dateTo = document.getElementById('v2_dateTo');
            const amountFrom = document.getElementById('v2_amountFrom');
            const amountTo = document.getElementById('v2_amountTo');
            const clearBtn = document.getElementById('v2_clearFilters');

            const guardAttr = 'data-v2-bound';
            const controls = [dateFrom, dateTo, amountFrom, amountTo, clearBtn].filter(Boolean);
            if (controls.some((el) => el.getAttribute(guardAttr) === '1')) {
                return;
            }

            let filterTimeout;
            const applyDebounced = () => {
                clearTimeout(filterTimeout);
                filterTimeout = setTimeout(() => this.applyCustomerFiltersV2(), 250);
            };

            [dateFrom, dateTo, amountFrom, amountTo].forEach((el) => {
                if (!el) return;
                el.setAttribute(guardAttr, '1');
                el.addEventListener('input', applyDebounced);
                el.addEventListener('change', applyDebounced);
            });

            if (clearBtn) {
                clearBtn.setAttribute(guardAttr, '1');
                clearBtn.addEventListener('click', () => this.clearCustomerFiltersV2());
            }
        },

        applyCustomerFiltersV2() {
            const dateFrom = document.getElementById('v2_dateFrom')?.value;
            const dateTo = document.getElementById('v2_dateTo')?.value;
            const amountFrom = parseFloat(document.getElementById('v2_amountFrom')?.value || '0');
            const amountToRaw = document.getElementById('v2_amountTo')?.value;
            const amountTo = amountToRaw ? parseFloat(amountToRaw) : Infinity;

            const filtered = (this.customerSalesDataV2 || []).filter((sale) => {
                let saleDate;
                if (sale.date && sale.date.includes('/')) {
                    const [day, month, year] = sale.date.split('/');
                    saleDate = new Date(year, month - 1, day);
                } else if (sale.created_at) {
                    saleDate = new Date(sale.created_at);
                } else {
                    saleDate = new Date();
                }

                const total = parseFloat(sale.total || 0);
                if (dateFrom && saleDate < new Date(dateFrom)) return false;
                if (dateTo && saleDate > new Date(`${dateTo}T23:59:59`)) return false;
                if (total < amountFrom) return false;
                if (amountTo !== Infinity && total > amountTo) return false;
                return true;
            });

            this.renderSalesHistoryV2(filtered);
        },

        clearCustomerFiltersV2() {
            const dateFrom = document.getElementById('v2_dateFrom');
            const dateTo = document.getElementById('v2_dateTo');
            const amountFrom = document.getElementById('v2_amountFrom');
            const amountTo = document.getElementById('v2_amountTo');

            if (dateFrom) dateFrom.value = '';
            if (dateTo) dateTo.value = '';
            if (amountFrom) amountFrom.value = '';
            if (amountTo) amountTo.value = '';

            this.renderSalesHistoryV2(this.customerSalesDataV2 || []);
        },

        renderSalesHistoryV2(rows) {
            const tableBody = document.getElementById('v2_salesHistoryTable');
            const salesCount = document.getElementById('v2_salesCount');
            if (!tableBody || !salesCount) return;

            const data = Array.isArray(rows) ? rows : [];
            salesCount.textContent = String(data.length);

            if (data.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="4" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center space-y-3">
                                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-800">
                                    <i class="fas fa-search text-2xl text-slate-500"></i>
                                </div>
                                <p class="text-slate-400">No se encontraron ventas con los filtros aplicados</p>
                            </div>
                        </td>
                    </tr>
                `;
                return;
            }

            tableBody.innerHTML = data
                .map((sale) => {
                    const isPaid = !!sale.is_paid;
                    const remainingDebt = parseFloat(sale.remaining_debt || 0);
                    const total = parseFloat(sale.total || 0);
                    const statusBadge = isPaid
                        ? `<span class="ui-badge ui-badge-success"><i class="fas fa-check-circle mr-1"></i>Pagado</span>`
                        : `<span class="ui-badge ui-badge-danger"><i class="fas fa-clock mr-1"></i>Pendiente: $${remainingDebt.toFixed(2)}</span>`;

                    return `
                        <tr class="border-b border-slate-700/60 transition-colors last:border-0 hover:bg-slate-800/45">
                            <td class="px-4 py-3 text-sm font-medium text-slate-100">${sale.date || 'Fecha no disponible'}</td>
                            <td class="products-cell px-4 py-3 text-sm text-slate-300">${sale.products || 'Sin productos'}</td>
                            <td class="px-4 py-3 text-sm font-semibold text-slate-100">$ ${total.toFixed(2)}</td>
                            <td class="px-4 py-3 text-sm">${statusBadge}</td>
                        </tr>
                    `;
                })
                .join('');
        },
    };
};
