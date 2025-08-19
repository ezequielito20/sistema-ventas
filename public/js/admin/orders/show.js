// ===== CONFIGURACIÓN GLOBAL =====
if (typeof ORDERS_SHOW_CONFIG === 'undefined') {
    window.ORDERS_SHOW_CONFIG = {
        routes: {
            process: '/admin/orders/process',
            cancel: '/admin/orders/cancel',
            back: '/admin/orders'
        },
        orderId: null
    };
}

// ===== FUNCIONES GLOBALES =====
if (typeof window.ordersShow === 'undefined') {
    window.ordersShow = {
        // Función para procesar orden
        processOrder: function(orderId) {
            console.log('Procesando orden:', orderId);
            
            // Mostrar modal de confirmación
            const processModal = document.getElementById('processModal');
            if (processModal) {
                $(processModal).modal('show');
            }
        },

        // Función para cancelar orden
        cancelOrder: function(orderId) {
            console.log('Cancelando orden:', orderId);
            
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción cancelará el pedido y no se puede revertir",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, cancelar',
                cancelButtonText: 'No, mantener'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submitCancelOrder(orderId);
                }
            });
        },

        // Función para enviar cancelación
        submitCancelOrder: function(orderId) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `${ORDERS_SHOW_CONFIG.routes.cancel}/${orderId}`;
            
            // Agregar token CSRF
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
            
            document.body.appendChild(form);
            form.submit();
        },

        // Función para mostrar notificaciones
        showNotification: function(message, type = 'success') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: type,
                    title: message,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            } else {
                alert(message);
            }
        },

        // Función para actualizar estado de la orden
        updateOrderStatus: function(status) {
            const statusBadge = document.querySelector('.status-badge');
            if (statusBadge) {
                statusBadge.className = `status-badge ${status}`;
                
                const statusText = {
                    'pending': 'Pendiente',
                    'processed': 'Procesado',
                    'cancelled': 'Cancelado'
                };
                
                statusBadge.innerHTML = `
                    <i class="fas fa-${status === 'pending' ? 'clock' : status === 'processed' ? 'check' : 'times'}"></i>
                    ${statusText[status]}
                `;
            }
        }
    };
}

// ===== FUNCIONES DE MODAL =====

// Función para inicializar modal de procesamiento
function initializeProcessModal() {
    const processModal = document.getElementById('processModal');
    if (processModal) {
        // Configurar fecha por defecto
        const saleDateInput = document.getElementById('sale_date');
        if (saleDateInput) {
            const now = new Date();
            const localDateTime = new Date(now.getTime() - now.getTimezoneOffset() * 60000)
                .toISOString()
                .slice(0, 16);
            saleDateInput.value = localDateTime;
        }

        // Eventos del modal
        $(processModal).on('show.bs.modal', function() {
            console.log('Modal de procesamiento abierto');
        });

        $(processModal).on('hidden.bs.modal', function() {
            console.log('Modal de procesamiento cerrado');
        });
    }
}

// Función para validar formulario de procesamiento
function validateProcessForm() {
    const saleDateInput = document.getElementById('sale_date');
    if (!saleDateInput || !saleDateInput.value) {
        ordersShow.showNotification('Por favor, selecciona una fecha de venta', 'error');
        return false;
    }

    const selectedDate = new Date(saleDateInput.value);
    const now = new Date();
    
    if (selectedDate > now) {
        ordersShow.showNotification('La fecha de venta no puede ser futura', 'error');
        return false;
    }

    return true;
}

// ===== FUNCIONES DE TIMELINE =====

// Función para inicializar timeline
function initializeTimeline() {
    const timelineItems = document.querySelectorAll('.timeline-item');
    timelineItems.forEach((item, index) => {
        // Agregar animación de entrada
        item.style.opacity = '0';
        item.style.transform = 'translateX(-20px)';
        
        setTimeout(() => {
            item.style.transition = 'all 0.5s ease';
            item.style.opacity = '1';
            item.style.transform = 'translateX(0)';
        }, index * 200);
    });
}

// Función para agregar evento al timeline
function addTimelineEvent(event) {
    const timeline = document.querySelector('.timeline');
    if (!timeline) return;

    const timelineItem = document.createElement('div');
    timelineItem.innerHTML = `
        <i class="fas fa-${event.icon} bg-${event.color}"></i>
        <div class="timeline-item">
            <span class="time">
                <i class="fas fa-clock"></i> ${event.time}
            </span>
            <h3 class="timeline-header">${event.title}</h3>
            <div class="timeline-body">
                ${event.description}
            </div>
        </div>
    `;

    // Insertar antes del último elemento (el ícono de reloj)
    const lastItem = timeline.lastElementChild;
    timeline.insertBefore(timelineItem, lastItem);

    // Animar entrada
    timelineItem.style.opacity = '0';
    timelineItem.style.transform = 'translateX(-20px)';
    
    setTimeout(() => {
        timelineItem.style.transition = 'all 0.5s ease';
        timelineItem.style.opacity = '1';
        timelineItem.style.transform = 'translateX(0)';
    }, 100);
}

// ===== FUNCIONES DE FORMULARIOS =====

// Función para inicializar formularios
function initializeForms() {
    // Formulario de procesamiento
    const processForm = document.querySelector('form[action*="process"]');
    if (processForm) {
        processForm.addEventListener('submit', function(e) {
            if (!validateProcessForm()) {
                e.preventDefault();
                return false;
            }
            
            // Mostrar loading
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
            }
        });
    }

    // Formulario de cancelación
    const cancelForm = document.querySelector('form[action*="cancel"]');
    if (cancelForm) {
        cancelForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const orderId = this.action.split('/').pop();
            ordersShow.cancelOrder(orderId);
        });
    }
}

// ===== FUNCIONES DE EFECTOS VISUALES =====

// Función para inicializar efectos visuales
function initializeVisualEffects() {
    // Efectos en botones
    const actionButtons = document.querySelectorAll('.action-button');
    actionButtons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 8px 16px rgba(0, 0, 0, 0.15)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'none';
        });
    });

    // Efectos en cards
    const cards = document.querySelectorAll('.order-info-card, .actions-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 8px 25px rgba(0, 0, 0, 0.1)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 4px 6px -1px rgba(0, 0, 0, 0.1)';
        });
    });

    // Efectos en inputs
    const inputs = document.querySelectorAll('.form-control');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.style.borderColor = '#667eea';
            this.style.boxShadow = '0 0 0 3px rgba(102, 126, 234, 0.1)';
        });
        
        input.addEventListener('blur', function() {
            this.style.borderColor = '#e2e8f0';
            this.style.boxShadow = 'none';
        });
    });
}

// ===== FUNCIONES DE UTILIDAD =====

// Función para formatear moneda
function formatCurrency(amount) {
    return new Intl.NumberFormat('es-ES', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

// Función para formatear fecha
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Función para obtener el estado de la orden
function getOrderStatus() {
    const statusBadge = document.querySelector('.status-badge');
    if (statusBadge) {
        if (statusBadge.classList.contains('pending')) return 'pending';
        if (statusBadge.classList.contains('processed')) return 'processed';
        if (statusBadge.classList.contains('cancelled')) return 'cancelled';
    }
    return 'unknown';
}

// ===== FUNCIONES DE INICIALIZACIÓN =====

// Función principal de inicialización
function initializeOrdersShow() {
    console.log('✅ orders/show.js cargado correctamente');
    
    // Obtener ID de la orden de la URL
    const pathParts = window.location.pathname.split('/');
    const orderId = pathParts[pathParts.length - 1];
    if (orderId && !isNaN(orderId)) {
        ORDERS_SHOW_CONFIG.orderId = orderId;
    }
    
    // Inicializar componentes
    initializeProcessModal();
    initializeTimeline();
    initializeForms();
    initializeVisualEffects();
    
    // Configurar eventos adicionales
    setupAdditionalEvents();
}

// Función para configurar eventos adicionales
function setupAdditionalEvents() {
    // Evento para botón de procesar
    const processButton = document.querySelector('[data-target="#processModal"]');
    if (processButton) {
        processButton.addEventListener('click', function() {
            ordersShow.processOrder(ORDERS_SHOW_CONFIG.orderId);
        });
    }

    // Evento para botón de cancelar
    const cancelButton = document.querySelector('form[action*="cancel"] button[type="submit"]');
    if (cancelButton) {
        cancelButton.addEventListener('click', function(e) {
            e.preventDefault();
            ordersShow.cancelOrder(ORDERS_SHOW_CONFIG.orderId);
        });
    }

    // Evento para botón de volver
    const backButton = document.querySelector('a[href*="orders"]');
    if (backButton) {
        backButton.addEventListener('click', function(e) {
            // Agregar efecto de transición si es necesario
            console.log('Volviendo a la lista de órdenes');
        });
    }
}

// ===== EXPONER FUNCIONES GLOBALMENTE =====
if (typeof window.initializeOrdersShow === 'undefined') {
    window.initializeOrdersShow = initializeOrdersShow;
}
if (typeof window.addTimelineEvent === 'undefined') {
    window.addTimelineEvent = addTimelineEvent;
}
if (typeof window.formatCurrency === 'undefined') {
    window.formatCurrency = formatCurrency;
}
if (typeof window.formatDate === 'undefined') {
    window.formatDate = formatDate;
}
if (typeof window.getOrderStatus === 'undefined') {
    window.getOrderStatus = getOrderStatus;
}

// ===== INICIALIZAR CUANDO EL DOM ESTÉ LISTO =====
if (!window.ordersShowInitialized) {
    document.addEventListener('DOMContentLoaded', function() {
        if (!window.ordersShowInitialized) {
            initializeOrdersShow();
            window.ordersShowInitialized = true;
        }
    });
}
