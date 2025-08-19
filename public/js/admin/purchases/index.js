// Purchases Index JS (refactor from inline scripts)
(() => {
  // Suprimir warning de múltiples instancias de Alpine
  if (window.Alpine) {
    try {
      window.Alpine.config = {
        ...window.Alpine.config,
        suppressMultipleInstancesWarning: true,
      };
    } catch (_) {}
  }

  document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('purchasesRoot');
    const currencySymbol = (root && root.dataset.currencySymbol) || '$';

    const purchaseDetailsModal = document.getElementById('purchaseDetailsModal');
    const supplierInfoModal = document.getElementById('supplierInfoModal');

    // Ensure hidden
    if (purchaseDetailsModal) {
      purchaseDetailsModal.style.display = 'none';
      purchaseDetailsModal.addEventListener('click', (e) => {
        if (e.target === purchaseDetailsModal) closePurchaseModal();
      });
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && purchaseDetailsModal.style.display === 'flex') closePurchaseModal();
      });
    }

    if (supplierInfoModal) {
      supplierInfoModal.style.display = 'none';
      supplierInfoModal.addEventListener('click', (e) => {
        if (e.target === supplierInfoModal) closeSupplierModal();
      });
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && supplierInfoModal.style.display === 'flex') closeSupplierModal();
      });
    }

    // View toggles and search
    let currentView = 'table';
    let searchTerm = '';
    const tableView = document.getElementById('tableView');
    const cardsView = document.getElementById('cardsView');
    const viewButtons = document.querySelectorAll('.view-btn');
    const searchInput = document.getElementById('purchasesSearch');
    const clearSearchBtn = document.getElementById('clearSearch');
    const purchaseCards = document.querySelectorAll('.purchase-card');
    const tableRows = document.querySelectorAll('.modern-table tbody tr');

    viewButtons.forEach((btn) => {
      btn.addEventListener('click', function () {
        const view = this.dataset.view;
        viewButtons.forEach((b) => b.classList.remove('active'));
        this.classList.add('active');
        if (view === 'table') {
          tableView && (tableView.style.display = 'block');
          cardsView && (cardsView.style.display = 'none');
          currentView = 'table';
        } else {
          tableView && (tableView.style.display = 'none');
          cardsView && (cardsView.style.display = 'block');
          currentView = 'cards';
        }
        applySearch();
      });
    });

    function applySearch() {
      if (currentView === 'table') {
        tableRows.forEach((row) => {
          const text = row.textContent.toLowerCase();
          row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
      } else {
        purchaseCards.forEach((card) => {
          const text = card.textContent.toLowerCase();
          card.style.display = text.includes(searchTerm) ? '' : 'none';
        });
      }
    }

    if (searchInput) {
      searchInput.addEventListener('input', function () {
        searchTerm = this.value.toLowerCase();
        applySearch();
      });
    }
    if (clearSearchBtn) {
      clearSearchBtn.addEventListener('click', function () {
        if (searchInput) searchInput.value = '';
        searchTerm = '';
        applySearch();
      });
    }

    // Details modal
    document.querySelectorAll('.view-details').forEach((btn) => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        const purchaseId = btn.dataset.id;
        if (purchaseId) loadPurchaseDetails(purchaseId);
      });
    });

    function loadPurchaseDetails(purchaseId) {
      const tableBody = document.getElementById('purchaseDetailsTableBody');
      if (!tableBody) return;
      tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-gray-500">Cargando...</td></tr>';
      if (purchaseDetailsModal) purchaseDetailsModal.style.display = 'flex';
      const productsSection = document.getElementById('productsDistributedSection');
      if (productsSection) productsSection.style.display = 'none';

      fetch(`/purchases/${purchaseId}/details`, {
        method: 'GET',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          Accept: 'application/json',
          'Content-Type': 'application/json',
        },
      })
        .then((r) => r.json())
        .then((data) => {
          if (data.success) {
            let total = 0;
            tableBody.innerHTML = '';
            data.details.forEach((detail) => {
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
                <td class="px-4 py-3 text-sm text-right text-gray-900">${currencySymbol} ${price.toFixed(2)}</td>
                <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900">${currencySymbol} ${subtotal.toFixed(2)}</td>`;
              tableBody.appendChild(row);
            });
            const totalEl = document.getElementById('modalTotal');
            if (totalEl) totalEl.textContent = total.toFixed(2);
          } else {
            tableBody.innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-red-600">Error al cargar los detalles</td></tr>';
            if (window.Swal) Swal.fire('Error', data.message || 'Error al cargar los detalles', 'error');
          }
        })
        .catch((err) => {
          tableBody.innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-red-600">Error de conexión</td></tr>';
          if (window.Swal) Swal.fire('Error', 'No se pudieron cargar los detalles: ' + err.message, 'error');
        });
    }

    // Delete
    document.querySelectorAll('.delete-purchase').forEach((btn) => {
      btn.addEventListener('click', () => {
        const purchaseId = btn.dataset.id;
        if (!purchaseId) return;
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
            if (result.isConfirmed) deletePurchase(purchaseId);
          });
        } else {
          if (confirm('¿Eliminar compra?')) deletePurchase(purchaseId);
        }
      });
    });

    function deletePurchase(purchaseId) {
      fetch(`/purchases/delete/${purchaseId}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          Accept: 'application/json',
          'Content-Type': 'application/json',
        },
      })
        .then((r) => r.json())
        .then((data) => {
          if (data.success) {
            if (window.Swal) {
              Swal.fire({ title: '¡Eliminado!', text: data.message, icon: 'success' }).then(() => location.reload());
            } else {
              alert('Eliminado');
              location.reload();
            }
          } else {
            if (window.Swal) Swal.fire('Error', data.message, 'error');
          }
        })
        .catch(() => {
          if (window.Swal) Swal.fire('Error', 'No se pudo eliminar la compra', 'error');
        });
    }

    // Simple hover effects for glass buttons (optional)
    document.querySelectorAll('.btn-glass').forEach((btn) => {
      btn.addEventListener('mouseenter', function () {
        this.style.transform = 'translateY(-2px)';
      });
      btn.addEventListener('mouseleave', function () {
        this.style.transform = 'translateY(0)';
      });
    });

    // Appear animations
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
          }
        });
      },
      { threshold: 0.1, rootMargin: '0px 0px -50px 0px' }
    );
    document.querySelectorAll('.stat-card, .purchase-card, .purchase-card-modern').forEach((el) => {
      el.style.opacity = '0';
      el.style.transform = 'translateY(20px)';
      el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
      observer.observe(el);
    });

    // Utilities in module scope so inline onclick handlers still work
    window.closePurchaseModal = function closePurchaseModal() {
      const modal = document.getElementById('purchaseDetailsModal');
      if (!modal) return;
      modal.style.display = 'none';
      setTimeout(() => {
        const tbody = document.getElementById('purchaseDetailsTableBody');
        const totalEl = document.getElementById('modalTotal');
        if (tbody) tbody.innerHTML = '';
        if (totalEl) totalEl.textContent = '0.00';
        const productsSection = document.getElementById('productsDistributedSection');
        if (productsSection) productsSection.style.display = 'none';
      }, 300);
    };

    window.showSupplierInfo = async function showSupplierInfo(supplierId) {
      try {
        if (supplierInfoModal) supplierInfoModal.style.display = 'flex';
        setSupplierModalLoading();
        const response = await fetch(`/suppliers/${supplierId}`);
        const data = await response.json();
        if (data.icons === 'success') {
          fillSupplierModal(data);
        } else {
          const errorMessage = data.message || 'No se pudieron obtener los datos del proveedor';
          if (window.Swal) Swal.fire('Error', errorMessage, 'error');
          closeSupplierModal();
        }
      } catch (e) {
        if (window.Swal) Swal.fire('Error', 'Error de conexión. Verifique su conexión a internet e inténtelo de nuevo.', 'error');
        closeSupplierModal();
      }
    };

    function setSupplierModalLoading() {
      setText('modalCompanyName', 'Cargando...');
      setText('modalCompanyEmail', 'Cargando...');
      setText('modalCompanyPhone', 'Cargando...');
      setText('modalCompanyAddress', 'Cargando...');
      setText('modalContactName', 'Cargando...');
      setText('modalContactPhone', 'Cargando...');
    }

    function setText(id, value) {
      const el = document.getElementById(id);
      if (el) el.textContent = value;
    }

    function fillSupplierModal(data) {
      setText('modalCompanyName', data.supplier.company_name || 'No disponible');
      setText('modalCompanyEmail', data.supplier.company_email || 'No disponible');
      setText('modalCompanyPhone', data.supplier.company_phone || 'No disponible');
      setText('modalCompanyAddress', data.supplier.company_address || 'No disponible');
      setText('modalContactName', data.supplier.supplier_name || 'No disponible');
      setText('modalContactPhone', data.supplier.supplier_phone || 'No disponible');
      const productsSection = document.getElementById('productsDistributedSection');
      if (productsSection) {
        if (data.stats && data.stats.length > 0) {
          productsSection.style.display = 'block';
          updateProductStats(data.stats);
        } else {
          productsSection.style.display = 'none';
        }
      }
    }

    function updateProductStats(stats) {
      const detailsContainer = document.getElementById('modalProductsTableBody');
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
              <td class="text-right">${formatCurrency(product.purchase_price)}</td>
              <td class="text-right">${formatCurrency(subtotal)}</td>
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
      const totalEl = document.getElementById('modalTotalAmount');
      if (totalEl) totalEl.innerHTML = formatCurrency(grandTotal);
    }

    function formatCurrency(amount) {
      return `${currencySymbol} ${numberFormat(amount)}`;
    }
    function numberFormat(number, decimals = 2) {
      try {
        return Number(number).toLocaleString('es-PE', {
          minimumFractionDigits: decimals,
          maximumFractionDigits: decimals,
        });
      } catch (_) {
        return Number(number).toFixed(decimals);
      }
    }

    window.closeSupplierModal = function closeSupplierModal() {
      if (!supplierInfoModal) return;
      supplierInfoModal.style.display = 'none';
      setTimeout(() => {
        setText('modalCompanyName', '');
        setText('modalCompanyEmail', '');
        setText('modalCompanyPhone', '');
        setText('modalCompanyAddress', '');
        setText('modalContactName', '');
        setText('modalContactPhone', '');
        const productsSection = document.getElementById('productsDistributedSection');
        if (productsSection) productsSection.style.display = 'none';
      }, 300);
    };
  });
})();


