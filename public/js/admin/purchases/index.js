// Purchases Index JS (optimized version)
(() => {
  // Constants for frequently used selectors
  const SELECTORS = {
    ROOT: '#purchasesRoot',
    SEARCH: '#purchasesSearch',
    CLEAR_SEARCH: '#clearSearch',
    TABLE_VIEW: '#tableView',
    CARDS_VIEW: '#cardsView',
    PURCHASE_DETAILS_MODAL: '#purchaseDetailsModal',
    SUPPLIER_INFO_MODAL: '#supplierInfoModal',
    PURCHASE_DETAILS_TABLE: '#purchaseDetailsTableBody',
    MODAL_TOTAL: '#modalTotal',
    PRODUCTS_SECTION: '#productsDistributedSection',
    MODAL_PRODUCTS_TABLE: '#modalProductsTableBody',
    MODAL_TOTAL_AMOUNT: '#modalTotalAmount'
  };

  // Modal field IDs for supplier info
  const SUPPLIER_FIELDS = {
    COMPANY_NAME: 'modalCompanyName',
    COMPANY_EMAIL: 'modalCompanyEmail',
    COMPANY_PHONE: 'modalCompanyPhone',
    COMPANY_ADDRESS: 'modalCompanyAddress',
    CONTACT_NAME: 'modalContactName',
    CONTACT_PHONE: 'modalContactPhone'
  };

  // Utility functions
  const utils = {
    // Debounce function for search optimization
    debounce: (func, wait) => {
      let timeout;
      return function executedFunction(...args) {
        const later = () => {
          clearTimeout(timeout);
          func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
      };
    },

    // Safe element getter
    getElement: (selector) => document.querySelector(selector),

    // Safe text setter
    setText: (id, value) => {
      const el = document.getElementById(id);
      if (el) el.textContent = value;
    },

    // Currency formatter
    formatCurrency: (amount, symbol = '$') => {
      return `${symbol} ${utils.numberFormat(amount)}`;
    },

    // Number formatter
    numberFormat: (number, decimals = 2) => {
      try {
        return Number(number).toLocaleString('es-PE', {
          minimumFractionDigits: decimals,
          maximumFractionDigits: decimals,
        });
      } catch (_) {
        return Number(number).toFixed(decimals);
      }
    },

    // Show notification
    showNotification: (title, message, type = 'success') => {
      if (window.Swal) {
        Swal.fire({ title, text: message, icon: type });
      } else {
        alert(`${title}: ${message}`);
      }
    }
  };

  // State management
  const state = {
    currentView: 'table',
    searchTerm: '',
    currencySymbol: '$'
  };

  // Initialize Alpine.js configuration once
  const initializeAlpine = () => {
    if (window.Alpine && window.Alpine.config) {
      try {
        if (!window.Alpine.config.suppressMultipleInstancesWarning) {
          window.Alpine.config = {
            ...window.Alpine.config,
            suppressMultipleInstancesWarning: true,
          };
        }
      } catch (_) {}
    }
  };

  // Modal management
  const modalManager = {
    purchaseDetailsModal: null,
    supplierInfoModal: null,

    init() {
      this.purchaseDetailsModal = utils.getElement(SELECTORS.PURCHASE_DETAILS_MODAL);
      this.supplierInfoModal = utils.getElement(SELECTORS.SUPPLIER_INFO_MODAL);
      this.setupModalEvents();
    },

    setupModalEvents() {
      // Purchase details modal
      if (this.purchaseDetailsModal) {
        this.purchaseDetailsModal.style.display = 'none';
        this.purchaseDetailsModal.addEventListener('click', (e) => {
          if (e.target === this.purchaseDetailsModal) this.closePurchaseModal();
        });
      }

      // Supplier info modal
      if (this.supplierInfoModal) {
        this.supplierInfoModal.style.display = 'none';
        this.supplierInfoModal.addEventListener('click', (e) => {
          if (e.target === this.supplierInfoModal) this.closeSupplierModal();
        });
      }

      // Global escape key handler
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
          if (this.purchaseDetailsModal?.style.display === 'flex') {
            this.closePurchaseModal();
          }
          if (this.supplierInfoModal?.style.display === 'flex') {
            this.closeSupplierModal();
          }
        }
      });
    },

    closePurchaseModal() {
      if (!this.purchaseDetailsModal) return;
      this.purchaseDetailsModal.style.display = 'none';
      setTimeout(() => {
        const tbody = utils.getElement(SELECTORS.PURCHASE_DETAILS_TABLE);
        const totalEl = utils.getElement(SELECTORS.MODAL_TOTAL);
        const productsSection = utils.getElement(SELECTORS.PRODUCTS_SECTION);
        
        if (tbody) tbody.innerHTML = '';
        if (totalEl) totalEl.textContent = '0.00';
        if (productsSection) productsSection.style.display = 'none';
      }, 300);
    },

    closeSupplierModal() {
      if (!this.supplierInfoModal) return;
      this.supplierInfoModal.style.display = 'none';
      setTimeout(() => {
        Object.values(SUPPLIER_FIELDS).forEach(fieldId => {
          utils.setText(fieldId, '');
        });
        const productsSection = utils.getElement(SELECTORS.PRODUCTS_SECTION);
        if (productsSection) productsSection.style.display = 'none';
      }, 300);
    }
  };

  // View management
  const viewManager = {
    tableView: null,
    cardsView: null,
    viewButtons: null,

    init() {
      this.tableView = utils.getElement(SELECTORS.TABLE_VIEW);
      this.cardsView = utils.getElement(SELECTORS.CARDS_VIEW);
      this.viewButtons = document.querySelectorAll('.view-btn');
      this.setupViewToggle();
    },

    setupViewToggle() {
      this.viewButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
          const view = btn.dataset.view;
          this.switchView(view);
        });
      });
    },

    switchView(view) {
      this.viewButtons.forEach((b) => b.classList.remove('active'));
      const activeBtn = document.querySelector(`[data-view="${view}"]`);
      if (activeBtn) activeBtn.classList.add('active');

      if (view === 'table') {
        if (this.tableView) this.tableView.style.display = 'block';
        if (this.cardsView) this.cardsView.style.display = 'none';
        state.currentView = 'table';
      } else {
        if (this.tableView) this.tableView.style.display = 'none';
        if (this.cardsView) this.cardsView.style.display = 'block';
        state.currentView = 'cards';
      }
      searchManager.applySearch();
    }
  };

  // Search management
  const searchManager = {
    searchInput: null,
    clearSearchBtn: null,

    init() {
      this.searchInput = utils.getElement(SELECTORS.SEARCH);
      this.clearSearchBtn = utils.getElement(SELECTORS.CLEAR_SEARCH);
      this.setupSearchEvents();
    },

    setupSearchEvents() {
      if (this.searchInput) {
        const debouncedSearch = utils.debounce((value) => {
          state.searchTerm = value.toLowerCase();
          this.applySearch();
        }, 300);

        this.searchInput.addEventListener('input', (e) => {
          debouncedSearch(e.target.value);
        });
      }

      if (this.clearSearchBtn) {
        this.clearSearchBtn.addEventListener('click', () => {
          if (this.searchInput) this.searchInput.value = '';
          state.searchTerm = '';
          this.applySearch();
        });
      }
    },

    applySearch() {
      if (state.currentView === 'table') {
        const tableRows = document.querySelectorAll('.modern-table tbody tr');
        tableRows.forEach((row) => {
          const text = row.textContent.toLowerCase();
          row.style.display = text.includes(state.searchTerm) ? '' : 'none';
        });
      } else {
        const purchaseCards = document.querySelectorAll('.purchase-card-modern');
        purchaseCards.forEach((card) => {
          const text = card.textContent.toLowerCase();
          card.style.display = text.includes(state.searchTerm) ? '' : 'none';
        });
      }
    }
  };

  // Purchase details management
  const purchaseManager = {
    init() {
      this.setupDetailsButtons();
      this.setupDeleteButtons();
    },

    setupDetailsButtons() {
      document.querySelectorAll('.view-details').forEach((btn) => {
        btn.addEventListener('click', (e) => {
          e.preventDefault();
          e.stopPropagation();
          const purchaseId = btn.dataset.id;
          if (purchaseId) this.loadPurchaseDetails(purchaseId);
        });
      });
    },

    setupDeleteButtons() {
      document.querySelectorAll('.delete-purchase').forEach((btn) => {
        btn.addEventListener('click', () => {
          const purchaseId = btn.dataset.id;
          if (!purchaseId) return;
          this.confirmDelete(purchaseId);
        });
      });
    },

    async loadPurchaseDetails(purchaseId) {
      const tableBody = utils.getElement(SELECTORS.PURCHASE_DETAILS_TABLE);
      if (!tableBody) return;

      tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-gray-500">Cargando...</td></tr>';
      if (modalManager.purchaseDetailsModal) {
        modalManager.purchaseDetailsModal.style.display = 'flex';
      }
      
      const productsSection = utils.getElement(SELECTORS.PRODUCTS_SECTION);
      if (productsSection) productsSection.style.display = 'none';

      try {
        const response = await fetch(`/purchases/${purchaseId}/details`, {
          method: 'GET',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            Accept: 'application/json',
            'Content-Type': 'application/json',
          },
        });

        const data = await response.json();

        if (data.success) {
          this.renderPurchaseDetails(data.details, tableBody);
        } else {
          tableBody.innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-red-600">Error al cargar los detalles</td></tr>';
          utils.showNotification('Error', data.message || 'Error al cargar los detalles', 'error');
        }
      } catch (err) {
        tableBody.innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-red-600">Error de conexión</td></tr>';
        utils.showNotification('Error', 'No se pudieron cargar los detalles: ' + err.message, 'error');
      }
    },

    renderPurchaseDetails(details, tableBody) {
      let total = 0;
      tableBody.innerHTML = '';

      details.forEach((detail) => {
        const quantity = parseFloat(detail.quantity);
        const price = parseFloat(detail.product_price);
        const subtotal = quantity * price;
        total += subtotal;

        const row = document.createElement('tr');
        row.innerHTML = `
          <td class="px-4 py-3 text-sm text-gray-900">${detail.product.code || ''}</td>
          <td class="px-4 py-3 text-sm text-gray-900">${detail.product.name || ''}</td>
          <td class="px-4 py-3 text-sm text-gray-600">${detail.product.category || 'Sin categoría'}</td>
          <td class="px-4 py-3 text-sm text-center">
            <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded-full text-xs font-medium">${quantity}</span>
          </td>
          <td class="px-4 py-3 text-sm text-right text-gray-900">${utils.formatCurrency(price, state.currencySymbol)}</td>
          <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900">${utils.formatCurrency(subtotal, state.currencySymbol)}</td>`;
        tableBody.appendChild(row);
      });

      const totalEl = utils.getElement(SELECTORS.MODAL_TOTAL);
      if (totalEl) totalEl.textContent = total.toFixed(2);
    },

    confirmDelete(purchaseId) {
      if (window.Swal) {
        Swal.fire({
          title: '¿Estás seguro?',
          text: 'Esta acción no se puede revertir',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Sí, eliminar',
          cancelButtonText: 'Cancelar',
        }).then((result) => {
          if (result.isConfirmed) this.deletePurchase(purchaseId);
        });
      } else {
        if (confirm('¿Eliminar compra?')) this.deletePurchase(purchaseId);
      }
    },

    async deletePurchase(purchaseId) {
      try {
        const response = await fetch(`/purchases/delete/${purchaseId}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            Accept: 'application/json',
            'Content-Type': 'application/json',
          },
        });

        const data = await response.json();

        if (data.success) {
          utils.showNotification('¡Eliminado!', data.message, 'success');
          setTimeout(() => location.reload(), 1000);
        } else {
          utils.showNotification('Error', data.message, 'error');
        }
      } catch (err) {
        utils.showNotification('Error', 'No se pudo eliminar la compra', 'error');
      }
    }
  };

  // Supplier management
  const supplierManager = {
    async showSupplierInfo(supplierId) {
      try {
        if (modalManager.supplierInfoModal) {
          modalManager.supplierInfoModal.style.display = 'flex';
        }
        this.setSupplierModalLoading();

        const response = await fetch(`/suppliers/${supplierId}`);
        const data = await response.json();

        if (data.icons === 'success') {
          this.fillSupplierModal(data);
        } else {
          const errorMessage = data.message || 'No se pudieron obtener los datos del proveedor';
          utils.showNotification('Error', errorMessage, 'error');
          modalManager.closeSupplierModal();
        }
      } catch (e) {
        utils.showNotification('Error', 'Error de conexión. Verifique su conexión a internet e inténtelo de nuevo.', 'error');
        modalManager.closeSupplierModal();
      }
    },

    setSupplierModalLoading() {
      Object.values(SUPPLIER_FIELDS).forEach(fieldId => {
        utils.setText(fieldId, 'Cargando...');
      });
    },

    fillSupplierModal(data) {
      utils.setText(SUPPLIER_FIELDS.COMPANY_NAME, data.supplier.company_name || 'No disponible');
      utils.setText(SUPPLIER_FIELDS.COMPANY_EMAIL, data.supplier.company_email || 'No disponible');
      utils.setText(SUPPLIER_FIELDS.COMPANY_PHONE, data.supplier.company_phone || 'No disponible');
      utils.setText(SUPPLIER_FIELDS.COMPANY_ADDRESS, data.supplier.company_address || 'No disponible');
      utils.setText(SUPPLIER_FIELDS.CONTACT_NAME, data.supplier.supplier_name || 'No disponible');
      utils.setText(SUPPLIER_FIELDS.CONTACT_PHONE, data.supplier.supplier_phone || 'No disponible');

      const productsSection = utils.getElement(SELECTORS.PRODUCTS_SECTION);
      if (productsSection) {
        if (data.stats && data.stats.length > 0) {
          productsSection.style.display = 'block';
          this.updateProductStats(data.stats);
        } else {
          productsSection.style.display = 'none';
        }
      }
    },

    updateProductStats(stats) {
      const detailsContainer = utils.getElement(SELECTORS.MODAL_PRODUCTS_TABLE);
      if (!detailsContainer) return;

      let detailsHTML = '';
      let grandTotal = 0;

      if (stats && stats.length > 0) {
        stats.forEach((product) => {
          const subtotal = product.stock * product.purchase_price;
          grandTotal += subtotal;
          detailsHTML += `
            <tr>
              <td>${product.name}</td>
              <td class="text-center">
                <span class="badge badge-primary">${product.stock}</span>
              </td>
              <td class="text-right">${utils.formatCurrency(product.purchase_price, state.currencySymbol)}</td>
              <td class="text-right">${utils.formatCurrency(subtotal, state.currencySymbol)}</td>
            </tr>`;
        });
      } else {
        detailsHTML = `
          <tr>
            <td colspan="4" class="text-center">
              <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <p>No hay productos registrados para este proveedor</p>
              </div>
            </td>
          </tr>`;
      }

      detailsContainer.innerHTML = detailsHTML;
      const totalEl = utils.getElement(SELECTORS.MODAL_TOTAL_AMOUNT);
      if (totalEl) totalEl.innerHTML = utils.formatCurrency(grandTotal, state.currencySymbol);
    }
  };

  // Product filter manager
  const productFilterManager = {
    selectedProductId: '',
    
    init() {
      // No necesitamos setup de event listeners ya que Alpine.js maneja todo
    },

    filterByProduct(productId) {
      this.selectedProductId = productId;
      
      const rows = document.querySelectorAll('#purchasesTable tbody tr, .purchase-card-modern');
      
      rows.forEach(row => {
        if (productId === '') {
          // Mostrar todas las compras
          row.style.display = '';
        } else {
          // Filtrar por producto
          const hasProduct = this.checkPurchaseHasProduct(row, productId);
          row.style.display = hasProduct ? '' : 'none';
        }
      });
    },

    checkPurchaseHasProduct(row, productId) {
      // Buscar si la compra contiene el producto seleccionado
      const purchaseId = row.getAttribute('data-purchase-id');
      if (!purchaseId) return false;
      
      // Buscar en los detalles de la compra
      const productCells = row.querySelectorAll('[data-product-id]');
      for (let cell of productCells) {
        if (cell.getAttribute('data-product-id') == productId) {
          return true;
        }
      }
      
      return false;
    }
  };

  // Animation manager
  const animationManager = {
    init() {
      this.setupHoverEffects();
      // setupAppearAnimations() removido para eliminar efectos de scroll
    },

    setupHoverEffects() {
      document.querySelectorAll('.btn-glass').forEach((btn) => {
        btn.addEventListener('mouseenter', function () {
          this.style.transform = 'translateY(-2px)';
        });
        btn.addEventListener('mouseleave', function () {
          this.style.transform = 'translateY(0)';
        });
      });
    },

    setupAppearAnimations() {
      // Animaciones de aparición deshabilitadas para mejor performance
      // Las tarjetas aparecen inmediatamente sin efectos de scroll
    }
  };

  // Main initialization
  const init = () => {
    // Initialize Alpine.js
    initializeAlpine();

    // Get currency symbol from data attribute
    const root = utils.getElement(SELECTORS.ROOT);
    if (root && root.dataset.currencySymbol) {
      state.currencySymbol = root.dataset.currencySymbol;
    }

    // Initialize all managers
    modalManager.init();
    viewManager.init();
    searchManager.init();
    purchaseManager.init();
    productFilterManager.init();
    animationManager.init();

    // Expose functions to global scope for inline handlers
    window.closePurchaseModal = () => modalManager.closePurchaseModal();
    window.closeSupplierModal = () => modalManager.closeSupplierModal();
    window.showSupplierInfo = (supplierId) => supplierManager.showSupplierInfo(supplierId);
    
    // Expose purchases index for Alpine.js
    window.purchasesIndex = {
      filterByProduct: (productId) => productFilterManager.filterByProduct(productId)
    };
  };

  // Start when DOM is ready
  document.addEventListener('DOMContentLoaded', init);
})();


