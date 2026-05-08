window.modalManagerV2 = function () {
    const baseFactory = window.modalManager;
    const base = typeof baseFactory === 'function' ? baseFactory() : {};

    return {
        ...base,
        showCustomerModalV2: false,
        debtReportModalV2: false,
        v2FiltersOpen: false,
        v2DebtReportPage: 1,
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
            this.debtReportModalV2 = false;
            this.v2FiltersOpen = false;

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

        openDebtReportV2() {
            this.debtReportModalV2 = true;
            this.v2FiltersOpen = false;
            this.v2DebtReportPage = 1;
            document.body.style.overflow = 'hidden';
            this.setDebtReportTimestampV2();
            this.bindDebtRateSyncEventsV2();
            const currentRate = this.resolveCurrentExchangeRateV2();
            const rateInput = document.getElementById('v2ModalExchangeRate');
            if (rateInput) {
                rateInput.value = String(currentRate);
            }
            this.loadDebtReportV2();
        },

        closeDebtReportV2() {
            this.debtReportModalV2 = false;
            this.v2FiltersOpen = false;
            this.v2DebtReportPage = 1;
            this.resetDebtReportFiltersV2(false);
            document.body.style.overflow = 'auto';
        },

        setDebtReportTimestampV2() {
            const now = new Date();
            const stamp = `${now.toLocaleDateString('es-VE')} ${now.toLocaleTimeString('es-VE', { hour: '2-digit', minute: '2-digit' })}`;
            const tsEl = document.getElementById('v2DebtReportTimestamp');
            if (tsEl) tsEl.textContent = stamp;
        },

        resolveCurrentExchangeRateV2() {
            const widgetElement = document.querySelector('[x-data*="exchangeRateWidget"]');
            if (widgetElement && widgetElement._x_dataStack && widgetElement._x_dataStack[0]) {
                const rate = parseFloat(widgetElement._x_dataStack[0].exchangeRate);
                if (!Number.isNaN(rate) && rate > 0) return rate;
            }

            const windowRate = parseFloat(window.currentExchangeRate || window.exchangeRate);
            if (!Number.isNaN(windowRate) && windowRate > 0) return windowRate;

            return 134;
        },

        buildDebtReportUrlV2(filters = {}) {
            const url = new URL('/admin/customers/debt-report', window.location.origin);
            url.searchParams.set('ajax', '1');
            url.searchParams.set('exchange_rate', String(filters.exchange_rate || this.resolveCurrentExchangeRateV2()));

            if (filters.search) url.searchParams.set('search', filters.search);
            if (filters.order) url.searchParams.set('order', filters.order);
            if (filters.debt_type) url.searchParams.set('debt_type', filters.debt_type);
            if (filters.date_from) url.searchParams.set('date_from', filters.date_from);
            if (filters.date_to) url.searchParams.set('date_to', filters.date_to);
            url.searchParams.set('page', String(filters.page || this.v2DebtReportPage || 1));
            url.searchParams.set('per_page', String(filters.per_page || 10));

            return url;
        },

        getDebtReportFiltersV2() {
            const perPageEl = document.getElementById('perPageFilter');
            return {
                search: document.getElementById('v2SearchFilter')?.value || '',
                order: document.getElementById('v2OrderFilter')?.value || 'debt_desc',
                debt_type: document.getElementById('v2DebtTypeFilter')?.value || '',
                date_from: document.getElementById('v2DateFromFilter')?.value || '',
                date_to: document.getElementById('v2DateToFilter')?.value || '',
                exchange_rate: parseFloat(document.getElementById('v2ModalExchangeRate')?.value || this.resolveCurrentExchangeRateV2()),
                page: this.v2DebtReportPage || 1,
                per_page: perPageEl ? parseInt(perPageEl.value, 10) : 10,
            };
        },

        applyDebtReportStylesV2() {
            const statsRoot = document.getElementById('v2DebtReportStats');
            const tableRoot = document.getElementById('v2DebtReportTable');

            if (!statsRoot || !tableRoot) return;

            statsRoot.querySelectorAll('h6, .text-gray-900').forEach((el) => {
                el.classList.remove('text-gray-900');
                el.classList.add('text-slate-100');
            });
            statsRoot.querySelectorAll('.text-gray-700, .text-gray-600').forEach((el) => {
                el.classList.remove('text-gray-700', 'text-gray-600');
                el.classList.add('text-slate-400');
            });

            tableRoot.querySelectorAll('table').forEach((t) => t.classList.add('debt-report-modal-v2__table'));
            tableRoot.querySelectorAll('thead').forEach((thead) => {
                thead.classList.remove('bg-gradient-to-r', 'from-blue-600', 'via-purple-600', 'to-indigo-700');
                thead.classList.add('bg-slate-800/95');
            });

            this.renderDebtReportCardsV2();
            this.applyDebtReportResponsiveViewV2();
        },

        renderDebtReportCardsV2() {
            const tableRoot = document.getElementById('v2DebtReportTable');
            if (!tableRoot) return;

            const table = tableRoot.querySelector('table');
            const tableWrapper = tableRoot.querySelector('.overflow-x-auto') || table?.parentElement;
            if (!table || !tableWrapper) return;

            const tbodyRows = Array.from(table.querySelectorAll('tbody tr'));
            const dataRows = tbodyRows.filter((row) => row.querySelectorAll('td').length >= 6);

            const existingCards = tableRoot.querySelector('.debt-report-modal-v2__cards');
            if (existingCards) existingCards.remove();

            const cardsContainer = document.createElement('div');
            cardsContainer.className = 'debt-report-modal-v2__cards';
            cardsContainer.style.display = 'grid';
            cardsContainer.style.gridTemplateColumns = '1fr';
            cardsContainer.style.gap = '0.95rem';
            cardsContainer.style.padding = '0.9rem';

            if (dataRows.length === 0) {
                const emptyCard = document.createElement('div');
                emptyCard.className = 'debt-report-modal-v2__card debt-report-modal-v2__card--empty';
                emptyCard.innerHTML = `
                    <div class="flex flex-col items-center gap-2 py-6 text-center text-slate-400">
                        <i class="fas fa-search text-xl text-slate-500"></i>
                        <p class="text-sm">No hay clientes con los filtros seleccionados</p>
                    </div>
                `;
                cardsContainer.appendChild(emptyCard);
                tableRoot.appendChild(cardsContainer);
                return;
            }

            dataRows.forEach((row) => {
                const cells = row.querySelectorAll('td');
                const index = cells[0]?.textContent?.trim() || '-';
                const clientHtml = cells[1]?.innerHTML?.trim() || 'Cliente';
                const clientName = cells[1]?.querySelector('span')?.textContent?.trim() || cells[1]?.textContent?.trim() || 'Cliente';
                const statusChipHtml = cells[1]?.querySelector('span.inline-flex, .ui-badge')?.outerHTML || '';
                const contact = cells[2]?.textContent?.trim() || 'Sin contacto';
                const debtSince = cells[3]?.textContent?.trim() || 'Sin fecha';
                const debtUsd = cells[4]?.textContent?.trim() || '$ 0.00';
                const debtBsCell = cells[5];
                const debtBs = debtBsCell?.textContent?.trim() || 'Bs. 0,00';
                const debtRaw = debtBsCell?.dataset?.debt || '';

                const card = document.createElement('article');
                card.className = 'debt-report-modal-v2__card';
                card.style.position = 'relative';
                card.style.overflow = 'hidden';
                card.style.border = '1px solid rgba(71,85,105,.55)';
                card.style.borderRadius = '0.9rem';
                card.style.background = 'linear-gradient(160deg, rgba(15,23,42,.92), rgba(2,6,23,.88))';
                card.style.boxShadow = '0 12px 28px rgba(2,6,23,.48), inset 0 0 0 1px rgba(56,189,248,.08)';
                card.style.padding = '0.78rem';
                card.innerHTML = `
                    <div style="position:absolute;left:0;right:0;top:0;height:1px;background:linear-gradient(90deg,rgba(56,189,248,.35),rgba(59,130,246,.08));"></div>
                    <div style="display:flex;flex-direction:column;gap:.55rem;">
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.55rem;">
                            <div style="display:flex;align-items:center;gap:.55rem;min-width:0;flex:1;">
                                <div style="display:flex;height:2.15rem;width:2.15rem;flex-shrink:0;align-items:center;justify-content:center;border-radius:.7rem;border:1px solid rgba(56,189,248,.25);background:rgba(2,6,23,.65);color:#67e8f9;">
                                    <i class="fas fa-user text-[0.8rem]"></i>
                                </div>
                                <div style="min-width:0;">
                                    <p style="margin:0;font-size:.68rem;color:#64748b;">#${index}</p>
                                    <p style="margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-weight:700;color:#f8fafc;font-size:.96rem;line-height:1.25rem;">${clientName}</p>
                                </div>
                            </div>
                            <div style="display:flex;align-items:center;justify-content:flex-end;flex-shrink:0;">
                                ${statusChipHtml}
                            </div>
                        </div>
                        <div style="height:1px;background:linear-gradient(90deg,rgba(56,189,248,.35),rgba(15,23,42,.25));"></div>

                        <div style="display:flex;flex-direction:column;gap:.38rem;">
                            <div style="display:flex;align-items:center;justify-content:space-between;gap:.65rem;padding:.42rem .55rem;border-radius:.58rem;border:1px solid rgba(71,85,105,.35);background:rgba(15,23,42,.45);">
                                <span style="display:inline-flex;align-items:center;gap:.35rem;font-size:.72rem;color:#94a3b8;">
                                    <i class="fas fa-phone text-cyan-300/90"></i>
                                    Contacto
                                </span>
                                <span style="font-size:.88rem;font-weight:600;color:#e2e8f0;line-height:1.1rem;text-align:right;">${contact}</span>
                            </div>

                            <div style="display:flex;align-items:center;justify-content:space-between;gap:.65rem;padding:.42rem .55rem;border-radius:.58rem;border:1px solid rgba(71,85,105,.35);background:rgba(15,23,42,.45);">
                                <span style="display:inline-flex;align-items:center;gap:.35rem;font-size:.72rem;color:#94a3b8;">
                                    <i class="far fa-calendar-alt text-amber-300/90"></i>
                                    Debe desde
                                </span>
                                <span style="font-size:.88rem;font-weight:600;color:#e2e8f0;line-height:1.1rem;text-align:right;">${debtSince}</span>
                            </div>
                        </div>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.42rem;">
                            <div style="padding:.5rem .55rem;border-radius:.62rem;border:1px solid rgba(239,68,68,.28);background:rgba(127,29,29,.2);">
                                <p style="margin:0;font-size:.7rem;color:#fecaca;letter-spacing:.01em;">Deuda USD</p>
                                <p style="margin:.2rem 0 0;font-size:.93rem;font-weight:700;color:#fee2e2;line-height:1.15rem;" class="tabular-nums">${debtUsd}</p>
                            </div>
                            <div style="padding:.5rem .55rem;border-radius:.62rem;border:1px solid rgba(59,130,246,.3);background:rgba(30,58,138,.2);">
                                <p style="margin:0;font-size:.7rem;color:#bfdbfe;letter-spacing:.01em;">Deuda Bs</p>
                                <p style="margin:.2rem 0 0;font-size:.93rem;font-weight:700;color:#dbeafe;line-height:1.15rem;" class="tabular-nums bs-debt" data-debt="${debtRaw}">${debtBs}</p>
                            </div>
                        </div>
                    </div>
                `;

                cardsContainer.appendChild(card);
            });

            tableRoot.appendChild(cardsContainer);
        },

        applyDebtReportResponsiveViewV2() {
            const tableRoot = document.getElementById('v2DebtReportTable');
            if (!tableRoot) return;

            const tableWrapper = tableRoot.querySelector('.overflow-x-auto');
            const cardsContainer = tableRoot.querySelector('.debt-report-modal-v2__cards');
            if (!tableWrapper || !cardsContainer) return;

            const isMobile = window.matchMedia('(max-width: 640px)').matches;

            if (isMobile) {
                tableWrapper.style.display = 'none';
                cardsContainer.style.display = 'grid';

                // 1 columna para 400px aprox, 2 columnas para móviles grandes.
                const isWideMobile = window.matchMedia('(min-width: 480px)').matches;
                cardsContainer.style.gridTemplateColumns = isWideMobile ? 'repeat(2, minmax(0, 1fr))' : '1fr';
                cardsContainer.style.gap = isWideMobile ? '0.9rem' : '0.75rem';
                cardsContainer.style.padding = isWideMobile ? '0.85rem' : '0.65rem';
            } else {
                tableWrapper.style.display = '';
                cardsContainer.style.display = 'none';
                cardsContainer.style.gridTemplateColumns = '';
                cardsContainer.style.gap = '';
                cardsContainer.style.padding = '';
            }

            if (window.__v2DebtResponsiveResizeBound === true) return;
            window.addEventListener('resize', () => this.applyDebtReportResponsiveViewV2());
            window.__v2DebtResponsiveResizeBound = true;
        },

        updateModalBsValuesV2(rate) {
            const roots = ['#v2DebtReportStats .modal-bs-debt', '#v2DebtReportTable .bs-debt'];
            roots.forEach((selector) => {
                document.querySelectorAll(selector).forEach((element) => {
                    const debtUsd = parseFloat(element.dataset.debt || '');
                    if (!Number.isNaN(debtUsd)) {
                        const debtBs = debtUsd * rate;
                        element.textContent = `Bs. ${debtBs.toLocaleString('es-VE', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2,
                        })}`;
                    }
                });
            });
        },

        syncExchangeRateAcrossWidgetsV2(rate) {
            const modalRate = document.getElementById('v2ModalExchangeRate');
            if (modalRate) modalRate.value = String(rate);

            window.currentExchangeRate = rate;
            window.exchangeRate = rate;
            localStorage.setItem('exchangeRate', String(rate));

            if (window.customersIndex && typeof window.customersIndex.updateBsValues === 'function') {
                window.customersIndex.updateBsValues(rate);
            }

            const widgetElements = document.querySelectorAll('[x-data*="exchangeRateWidget"]');
            widgetElements.forEach((element) => {
                if (element._x_dataStack && element._x_dataStack[0]) {
                    const widget = element._x_dataStack[0];
                    if (typeof widget.syncFromModal === 'function') {
                        widget.syncFromModal(rate);
                    } else {
                        widget.exchangeRate = rate;
                    }
                }
            });
        },

        bindDebtRateSyncEventsV2() {
            if (window.__v2DebtRateSyncBound === true) return;

            window.addEventListener('sync-rate', (event) => {
                if (!this.debtReportModalV2) return;
                const syncedRate = parseFloat(event?.detail?.rate || '');
                if (Number.isNaN(syncedRate) || syncedRate <= 0) return;
                this.syncExchangeRateAcrossWidgetsV2(syncedRate);
                this.updateModalBsValuesV2(syncedRate);
                this.loadDebtReportV2(this.getDebtReportFiltersV2());
            });

            window.__v2DebtRateSyncBound = true;
        },

        async loadDebtReportV2(filters = null) {
            const activeFilters = filters || this.getDebtReportFiltersV2();
            const statsRoot = document.getElementById('v2DebtReportStats');
            const tableRoot = document.getElementById('v2DebtReportTable');
            const paginationRoot = document.getElementById('v2DebtReportPagination');
            const companyName = document.getElementById('v2DebtCompanyName');

            this.v2DebtReportPage = parseInt(activeFilters.page || this.v2DebtReportPage || 1, 10);

            if (statsRoot) {
                statsRoot.innerHTML = '<div class="py-8 text-center text-slate-400"><i class="fas fa-spinner fa-spin mr-2"></i>Cargando resumen...</div>';
            }
            if (tableRoot) {
                tableRoot.innerHTML = '<div class="py-10 text-center text-slate-400"><i class="fas fa-spinner fa-spin mr-2"></i>Cargando tabla...</div>';
            }
            if (paginationRoot) {
                paginationRoot.innerHTML = '';
            }

            try {
                const response = await fetch(this.buildDebtReportUrlV2(activeFilters), {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'text/html',
                    },
                });

                if (!response.ok) throw new Error('Respuesta no valida');
                const html = await response.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');

                const incomingStats = doc.getElementById('debtReportStats');
                const incomingTable = doc.getElementById('debtReportTable');
                const incomingPagination = doc.getElementById('debtReportPagination');
                const incomingCompanyName = doc.querySelector('h6.font-semibold.text-gray-900');

                if (statsRoot && incomingStats) statsRoot.innerHTML = incomingStats.innerHTML;
                if (tableRoot && incomingTable) tableRoot.innerHTML = incomingTable.innerHTML;
                if (paginationRoot) paginationRoot.innerHTML = incomingPagination ? incomingPagination.innerHTML : '';
                if (companyName && incomingCompanyName) companyName.textContent = incomingCompanyName.textContent?.trim() || 'Empresa';

                this.applyDebtReportStylesV2();
                this.updateModalBsValuesV2(activeFilters.exchange_rate || this.resolveCurrentExchangeRateV2());
                this.bindDebtReportEventsV2();
            } catch (e) {
                if (statsRoot) {
                    statsRoot.innerHTML = '<div class="py-8 text-center text-rose-300"><i class="fas fa-exclamation-triangle mr-2"></i>Error al cargar el resumen</div>';
                }
                if (tableRoot) {
                    tableRoot.innerHTML = '<div class="py-10 text-center text-rose-300"><i class="fas fa-exclamation-triangle mr-2"></i>No se pudo cargar la tabla</div>';
                }
            }
        },

        bindDebtReportEventsV2() {
            const guardAttr = 'data-v2-debt-bound';
            const bindOnce = (element, eventName, handler) => {
                if (!element) return;
                const key = `${guardAttr}-${eventName}`;
                if (element.getAttribute(key) === '1') return;
                element.setAttribute(key, '1');
                element.addEventListener(eventName, handler);
            };

            const search = document.getElementById('v2SearchFilter');
            const order = document.getElementById('v2OrderFilter');
            const debtType = document.getElementById('v2DebtTypeFilter');
            const dateFrom = document.getElementById('v2DateFromFilter');
            const dateTo = document.getElementById('v2DateToFilter');
            const clearBtn = document.getElementById('v2ClearFiltersBtn');
            const updateBtn = document.getElementById('v2UpdateModalExchangeRate');
            const rateInput = document.getElementById('v2ModalExchangeRate');
            const pdfBtn = document.getElementById('v2DownloadPdfBtn');

            let timer;
            const applyDebounced = () => {
                clearTimeout(timer);
                timer = setTimeout(() => {
                    this.v2DebtReportPage = 1;
                    this.loadDebtReportV2(this.getDebtReportFiltersV2());
                }, 500);
            };

            [search, order, debtType, dateFrom, dateTo].forEach((el) => {
                bindOnce(el, 'input', applyDebounced);
                bindOnce(el, 'change', applyDebounced);
            });

            bindOnce(clearBtn, 'click', () => this.resetDebtReportFiltersV2(true));
            bindOnce(updateBtn, 'click', async () => {
                updateBtn.disabled = true;
                updateBtn.classList.add('opacity-70', 'cursor-not-allowed');

                try {
                    const widgetElement = document.querySelector('[x-data*="exchangeRateWidget"]');
                    const widget = widgetElement?._x_dataStack?.[0];

                    if (widget && typeof widget.forceUpdateFromApi === 'function') {
                        await widget.forceUpdateFromApi();
                    } else if (window.exchangeRateUpdateUrl && window.csrfToken) {
                        const response = await fetch(window.exchangeRateUpdateUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': window.csrfToken,
                                Accept: 'application/json',
                            },
                        });
                        const data = await response.json();
                        if (!data.success) return;
                    }

                    const freshRate = this.resolveCurrentExchangeRateV2();
                    if (Number.isNaN(freshRate) || freshRate <= 0) return;

                    this.syncExchangeRateAcrossWidgetsV2(freshRate);
                    this.updateModalBsValuesV2(freshRate);
                    this.v2DebtReportPage = 1;
                    this.loadDebtReportV2(this.getDebtReportFiltersV2());

                    if (window.uiNotifications && typeof window.uiNotifications.showToast === 'function') {
                        window.uiNotifications.showToast(`Tasa BCV actualizada: ${freshRate.toFixed(2)} Bs/USD`, {
                            type: 'success',
                            title: 'Listo',
                            timeout: 4200,
                            theme: 'futuristic',
                        });
                    } else if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Listo',
                            text: `Tasa BCV actualizada: ${freshRate.toFixed(2)} Bs/USD`,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                        });
                    }
                } catch (e) {
                    // keep silent to preserve legacy behavior style
                } finally {
                    updateBtn.disabled = false;
                    updateBtn.classList.remove('opacity-70', 'cursor-not-allowed');
                }
            });
            bindOnce(pdfBtn, 'click', () => this.downloadDebtReportPdfV2());

            const paginationRoot = document.getElementById('v2DebtReportPagination');
            if (paginationRoot) {
                paginationRoot.querySelectorAll('a[data-page]').forEach((link) => {
                    bindOnce(link, 'click', (event) => {
                        event.preventDefault();
                        const targetPage = parseInt(link.dataset.page || '1', 10);
                        if (Number.isNaN(targetPage) || targetPage < 1) return;
                        this.v2DebtReportPage = targetPage;
                        this.loadDebtReportV2(this.getDebtReportFiltersV2());
                    });
                });
            }

            // Selector de registros por página
            const perPageSelect = document.getElementById('perPageFilter');
            bindOnce(perPageSelect, 'change', () => {
                this.v2DebtReportPage = 1;
                this.loadDebtReportV2(this.getDebtReportFiltersV2());
            });
        },

        resetDebtReportFiltersV2(reload = true) {
            const defaults = {
                v2SearchFilter: '',
                v2OrderFilter: 'debt_desc',
                v2DebtTypeFilter: '',
                v2DateFromFilter: '',
                v2DateToFilter: '',
            };
            Object.entries(defaults).forEach(([id, value]) => {
                const el = document.getElementById(id);
                if (el) el.value = value;
            });

            this.v2DebtReportPage = 1;

            if (reload) {
                this.loadDebtReportV2(this.getDebtReportFiltersV2());
            }
        },

        downloadDebtReportPdfV2() {
            const filters = this.getDebtReportFiltersV2();
            const url = new URL('/admin/customers/debt-report/download', window.location.origin);
            Object.entries(filters).forEach(([k, v]) => {
                if (v !== '' && v !== null && v !== undefined) {
                    url.searchParams.set(k, String(v));
                }
            });
            window.open(url.toString(), '_blank');
        },
    };
};

class SPAPaymentHandlerV2 {
    constructor() {
        this.currentCustomerId = null;
        this.processing = false;
        this.currentDebt = 0;
        this.bindCoreEvents();
    }

    bindCoreEvents() {
        const form = document.getElementById('debtPaymentFormV2');
        const amount = document.getElementById('v2_payment_amount');
        const maxBtn = document.getElementById('v2_max_payment_btn');

        if (form && !form.dataset.boundV2Payment) {
            form.dataset.boundV2Payment = '1';
            form.addEventListener('submit', (event) => this.handleSubmit(event));
        }

        if (amount && !amount.dataset.boundV2Payment) {
            amount.dataset.boundV2Payment = '1';
            amount.addEventListener('input', () => this.updateRemainingDebt());
        }

        if (maxBtn && !maxBtn.dataset.boundV2Payment) {
            maxBtn.dataset.boundV2Payment = '1';
            maxBtn.addEventListener('click', () => this.setMaxPayment());
        }
    }

    async openPaymentModal(customerId) {
        this.bindCoreEvents();
        this.currentCustomerId = customerId;

        const modal = document.getElementById('debtPaymentModalV2');
        if (!modal) return;

        this.resetForm();
        modal.style.display = 'block';
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        const today = new Date();
        const todayDate = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
        const nowTime = `${String(today.getHours()).padStart(2, '0')}:${String(today.getMinutes()).padStart(2, '0')}`;

        const dateInput = document.getElementById('v2_payment_date');
        const timeInput = document.getElementById('v2_payment_time');
        if (dateInput) dateInput.value = todayDate;
        if (timeInput) timeInput.value = nowTime;

        await this.loadCustomerPaymentData(customerId);
    }

    closePaymentModal() {
        const modal = document.getElementById('debtPaymentModalV2');
        if (!modal) return;

        modal.style.display = 'none';
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
        this.resetForm();
    }

    resetForm() {
        const form = document.getElementById('debtPaymentFormV2');
        if (form) form.reset();

        this.currentDebt = 0;
        this.renderDebtValues(0, 0);
        const customerStatus = document.getElementById('v2_customer_status');
        if (customerStatus) customerStatus.innerHTML = '';
    }

    async loadCustomerPaymentData(customerId) {
        try {
            const response = await fetch(`/admin/customers/${customerId}/payment-data`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            });
            const data = await response.json();
            if (!data.success) return;

            const customer = data.customer || {};
            this.currentDebt = parseFloat(customer.total_debt || 0);

            const nameInput = document.getElementById('v2_customer_name');
            const phoneInput = document.getElementById('v2_customer_phone');
            const statusEl = document.getElementById('v2_customer_status');
            const amountInput = document.getElementById('v2_payment_amount');

            if (nameInput) nameInput.value = customer.name || 'No disponible';
            if (phoneInput) phoneInput.value = customer.phone || 'No disponible';
            if (amountInput) amountInput.setAttribute('data-max-debt', String(this.currentDebt));

            if (statusEl) {
                if (customer.is_defaulter) {
                    statusEl.className = 'ui-badge ui-badge-danger';
                    statusEl.innerHTML = '<i class="fas fa-exclamation-triangle mr-1"></i>Moroso';
                } else {
                    statusEl.className = 'ui-badge ui-badge-success';
                    statusEl.innerHTML = '<i class="fas fa-check-circle mr-1"></i>Actual';
                }
            }

            this.renderDebtValues(this.currentDebt, this.currentDebt);
        } catch (error) {
            // silent fallback to preserve current UX behavior
        }
    }

    setMaxPayment() {
        const amountInput = document.getElementById('v2_payment_amount');
        if (!amountInput) return;
        amountInput.value = this.currentDebt > 0 ? this.currentDebt.toFixed(2) : '';
        this.updateRemainingDebt();
    }

    updateRemainingDebt() {
        const amountInput = document.getElementById('v2_payment_amount');
        const amount = parseFloat(amountInput?.value || 0);
        const safeAmount = Number.isFinite(amount) ? amount : 0;
        const remaining = Math.max(this.currentDebt - safeAmount, 0);
        this.renderDebtValues(this.currentDebt, remaining);
    }

    renderDebtValues(currentDebt, remainingDebt) {
        const currentEl = document.getElementById('v2_current_debt');
        const remainingEl = document.getElementById('v2_remaining_debt');
        if (currentEl) currentEl.textContent = `$${Number(currentDebt || 0).toFixed(2)}`;
        if (remainingEl) remainingEl.textContent = `$${Number(remainingDebt || 0).toFixed(2)}`;
    }

    validateForm() {
        const amount = parseFloat(document.getElementById('v2_payment_amount')?.value || 0);
        const paymentDate = document.getElementById('v2_payment_date')?.value || '';
        const paymentTime = document.getElementById('v2_payment_time')?.value || '';

        if (!Number.isFinite(amount) || amount <= 0) return 'El monto debe ser mayor a 0.';
        if (amount > this.currentDebt) return 'El monto no puede ser mayor que la deuda actual.';
        if (!paymentDate) return 'La fecha del pago es requerida.';
        if (!paymentTime) return 'La hora del pago es requerida.';

        const today = new Date();
        const todayStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
        if (paymentDate > todayStr) return 'La fecha no puede ser mayor a hoy.';

        return null;
    }

    getPayload() {
        return {
            payment_amount: parseFloat(document.getElementById('v2_payment_amount')?.value || 0),
            payment_date: document.getElementById('v2_payment_date')?.value || '',
            payment_time: document.getElementById('v2_payment_time')?.value || '',
            notes: document.getElementById('v2_payment_notes')?.value || '',
        };
    }

    async handleSubmit(event) {
        event.preventDefault();
        if (this.processing || !this.currentCustomerId) return;

        const validationError = this.validateForm();
        if (validationError) {
            if (window.uiNotifications?.showToast) {
                window.uiNotifications.showToast(validationError, { type: 'error', title: 'Validación' });
            } else if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'warning', title: 'Validación', text: validationError });
            }
            return;
        }

        this.processing = true;
        const submitBtn = document.getElementById('v2_submit_payment_btn');
        if (submitBtn) submitBtn.disabled = true;

        try {
            const response = await fetch(`/admin/customers/${this.currentCustomerId}/register-payment-ajax`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    Accept: 'application/json',
                },
                body: JSON.stringify(this.getPayload()),
            });
            const data = await response.json();

            if (!data.success) {
                const message = data.message || 'No se pudo registrar el pago.';
                throw new Error(message);
            }

            if (window.uiNotifications?.showToast) {
                window.uiNotifications.showToast('Pago registrado correctamente.', {
                    type: 'success',
                    title: 'Listo',
                    theme: 'futuristic',
                });
            } else if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'success', title: 'Listo', text: 'Pago registrado correctamente.' });
            }

            this.closePaymentModal();
            window.location.reload();
        } catch (error) {
            if (window.uiNotifications?.showToast) {
                window.uiNotifications.showToast(error.message || 'Error al registrar pago.', { type: 'error', title: 'Error' });
            } else if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Error', text: error.message || 'Error al registrar pago.' });
            }
        } finally {
            this.processing = false;
            if (submitBtn) submitBtn.disabled = false;
        }
    }
}

window.spaPaymentHandlerV2 = new SPAPaymentHandlerV2();

// Función global para cambiar registros por página en el modal de deuda v2
// Se llama desde el onchange del select, no depende de event listeners cacheados
window.changeDebtReportPerPage = function(selectEl) {
    const container = selectEl.closest('[x-data]');
    if (!container) return;
    const alpineData = container._x_dataStack?.[0];
    if (alpineData && typeof alpineData.loadDebtReportV2 === 'function') {
        alpineData.v2DebtReportPage = 1;
        alpineData.loadDebtReportV2(alpineData.getDebtReportFiltersV2());
    }
};
