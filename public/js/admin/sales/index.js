/**
 * JavaScript optimizado para la vista de ventas
 * Archivo: public/js/admin/sales/index.js
 * Versión: 1.0.0
 */

// ===== VARIABLES GLOBALES =====
let currentView = 'table';
let searchTerm = '';
let filters = {
    dateFrom: '',
    dateTo: '',
    amountMin: '',
    amountMax: ''
};
let sales = [];
let filteredSales = [];

// ===== FUNCIONES GLOBALES =====

// Mostrar detalles de venta
async function showSaleDetails(saleId) {
    console.log('showSaleDetails llamado con ID:', saleId);
    
    try {
        const response = await fetch(`/sales/${saleId}/details`);
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('Error response:', errorText);
            throw new Error(`Error ${response.status}: ${errorText}`);
        }
        
        const data = await response.json();
        console.log('Data received:', data);
        populateModalData(data);
        openModal();
    } catch (error) {
        console.error('Error:', error);
        showAlert('Error al cargar los detalles de la venta: ' + error.message, 'error');
    }
}

// Abrir modal
function openModal() {
    const overlay = document.getElementById('modalOverlay');
    const container = document.getElementById('modalContainer');
    
    if (overlay && container) {
        // Mostrar overlay
        overlay.style.display = 'flex';
        container.style.display = 'flex';
        
        // Forzar reflow
        overlay.offsetHeight;
        
        // Animar entrada
        overlay.style.opacity = '1';
        container.style.opacity = '1';
        container.style.transform = 'scale(1) translateY(0)';
        
        // Bloquear scroll del body
        document.body.style.overflow = 'hidden';
    }
}

// Cerrar modal
function closeModal() {
    const overlay = document.getElementById('modalOverlay');
    const container = document.getElementById('modalContainer');
    
    if (overlay && container) {
        // Animar salida
        overlay.style.opacity = '0';
        container.style.opacity = '0';
        container.style.transform = 'scale(0.95) translateY(20px)';
        
        // Ocultar después de la animación
        setTimeout(() => {
            overlay.style.display = 'none';
            container.style.display = 'none';
            
            // Restaurar scroll del body
            document.body.style.overflow = '';
        }, 300);
    }
}

// Poblar datos del modal
function populateModalData(data) {
    // Información del cliente
    const customerInfo = document.getElementById('customerInfo');
    if (customerInfo) {
        customerInfo.innerHTML = `
            <div class="info-item">
                <span class="info-label">Nombre:</span>
                <span class="info-value">${data.customer.name}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Email:</span>
                <span class="info-value">${data.customer.email || 'No especificado'}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Teléfono:</span>
                <span class="info-value">${data.customer.phone || 'No especificado'}</span>
            </div>
        `;
    }

    // Fecha de venta
    const saleDate = document.getElementById('saleDate');
    if (saleDate) {
        saleDate.innerHTML = `
            <div class="info-item">
                <span class="info-label">Fecha:</span>
                <span class="info-value">${data.sale_date}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Hora:</span>
                <span class="info-value">${data.sale_time}</span>
            </div>
        `;
    }

    // Productos
    const tableBody = document.getElementById('saleDetailsTableBody');
    if (tableBody) {
        tableBody.innerHTML = data.details.map(detail => `
            <tr>
                <td>${detail.product.code}</td>
                <td>${detail.product.name}</td>
                <td>${detail.product.category?.name || 'Sin categoría'}</td>
                <td class="text-center">${detail.quantity}</td>
                <td class="text-right">${formatCurrency(detail.unit_price)}</td>
                <td class="text-right">${formatCurrency(detail.subtotal)}</td>
            </tr>
        `).join('');
    }

    // Total
    const modalTotal = document.getElementById('modalTotal');
    if (modalTotal) {
        modalTotal.textContent = formatCurrency(data.total_price);
    }

    // Nota (si existe)
    const noteCard = document.getElementById('noteCard');
    const noteText = document.getElementById('noteText');
    if (noteCard && noteText) {
        if (data.note) {
            noteText.textContent = data.note;
            noteCard.style.display = 'flex';
        } else {
            noteCard.style.display = 'none';
        }
    }
    
    // Actualizar el botón de imprimir con el ID de la venta
    const printButton = document.querySelector('.print-details');
    if (printButton) {
        printButton.setAttribute('data-sale-id', data.id || '');
    }
}

// Editar venta
function editSale(saleId) {
    window.location.href = `/sales/edit/${saleId}`;
}

// Eliminar venta
async function deleteSale(saleId) {
    const confirmed = await showConfirmDialog(
        '¿Estás seguro de que quieres eliminar esta venta?',
        'Esta acción no se puede deshacer.'
    );

    if (!confirmed) return;

    try {
        const response = await fetch(`/sales/delete/${saleId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) throw new Error('Error al eliminar la venta');

        showAlert('Venta eliminada correctamente', 'success');
        
        // Recargar la página o actualizar la lista
        setTimeout(() => {
            window.location.reload();
        }, 1500);

    } catch (error) {
        console.error('Error:', error);
        showAlert('Error al eliminar la venta', 'error');
    }
}

// Imprimir venta
function printSale(saleId) {
    window.open(`/sales/print/${saleId}`, '_blank');
}

// Utilidades
function formatCurrency(amount) {
    const currencySymbol = document.getElementById('salesRoot').getAttribute('data-currency-symbol');
    return `${currencySymbol} ${parseFloat(amount).toFixed(2)}`;
}

// Mostrar alerta
function showAlert(message, type = 'info') {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: type === 'success' ? '¡Éxito!' : type === 'error' ? 'Error' : 'Información',
            text: message,
            icon: type,
            confirmButtonText: 'Aceptar',
            confirmButtonColor: '#667eea'
        });
    } else {
        alert(message);
    }
}

// Mostrar diálogo de confirmación
function showConfirmDialog(title, text) {
    return new Promise((resolve) => {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: title,
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#667eea',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                resolve(result.isConfirmed);
            });
        } else {
            resolve(confirm(`${title}\n${text}`));
        }
    });
}

// ===== INICIALIZACIÓN CUANDO EL DOM ESTÉ LISTO =====
document.addEventListener('DOMContentLoaded', function() {
    console.log('Aplicación de ventas inicializada');
    
    // Configurar event listeners para cerrar modal
    const overlay = document.getElementById('modalOverlay');
    if (overlay) {
        overlay.addEventListener('click', closeModal);
    }
    
    // Cerrar modal con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
    
    // Event listener para el botón de imprimir
    document.addEventListener('click', function(e) {
        if (e.target.closest('.print-details')) {
            e.preventDefault();
            const saleId = e.target.closest('.print-details').getAttribute('data-sale-id');
            if (saleId) {
                printSale(saleId);
            }
        }
    });
});