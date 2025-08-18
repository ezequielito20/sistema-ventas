// ===== CONFIGURACIÓN GLOBAL =====
const COMPANY_CREATE_CONFIG = {
    routes: {
        searchCountry: '/admin/company/search_country',
        searchState: '/admin/company/search_state'
    },
    fileTypes: {
        allowed: ['image/*'],
        maxSize: 5 * 1024 * 1024 // 5MB
    }
};

// ===== FUNCIONES GLOBALES =====
window.companyCreate = {
    // Función para volver atrás
    goBack: function() {
        window.history.back();
    },

    // Función para mostrar notificaciones
    showNotification: function(message, type = 'success') {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: type === 'success' ? '¡Éxito!' : '¡Error!',
                text: message,
                icon: type,
                confirmButtonText: 'Aceptar',
                confirmButtonColor: type === 'success' ? '#28a745' : '#d33',
                customClass: {
                    confirmButton: `btn btn-${type === 'success' ? 'success' : 'danger'}`
                },
                buttonsStyling: true,
                background: '#fff',
                showCloseButton: true,
                timer: 10000,
                timerProgressBar: true,
                toast: false,
                position: 'center'
            });
        } else {
            // Fallback si SweetAlert2 no está disponible
            alert(type.toUpperCase() + ': ' + message);
        }
    },

    // Función para manejar archivos
    handleFileSelect: function(file) {
        if (!file.type.match('image.*')) {
            this.showNotification('Por favor selecciona solo archivos de imagen', 'error');
            return false;
        }

        if (file.size > COMPANY_CREATE_CONFIG.fileTypes.maxSize) {
            this.showNotification('El archivo es demasiado grande. Máximo 5MB', 'error');
            return false;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            const previewContainer = document.getElementById('previewContainer');
            if (previewContainer) {
                previewContainer.innerHTML = `
                    <img src="${e.target.result}" class="preview-image" alt="Preview">
                `;
            }
        };
        reader.readAsDataURL(file);
        return true;
    },

    // Función para buscar países
    searchCountry: function(countryId) {
        if (!countryId) {
            this.clearLocationFields();
            return;
        }

        fetch(`${COMPANY_CREATE_CONFIG.routes.searchCountry}/${countryId}`)
            .then(response => response.json())
            .then(data => {
                this.updateStateSelect(data.states || []);
                this.updateLocationFields(data.postal_code, data.currency_code);
            })
            .catch(error => {
                console.error('Error al obtener estados:', error);
                this.updateStateSelect([]);
                this.showNotification('Error al cargar los estados', 'error');
            });
    },

    // Función para buscar estados
    searchState: function(stateId) {
        if (!stateId) {
            this.clearCityFields();
            return;
        }

        fetch(`${COMPANY_CREATE_CONFIG.routes.searchState}/${stateId}`)
            .then(response => response.json())
            .then(data => {
                this.updateCitySelect(data.cities || []);
                this.updatePostalCode(data.postal_code);
            })
            .catch(error => {
                console.error('Error al obtener datos del estado:', error);
                this.updateCitySelect([]);
                this.showNotification('Error al cargar las ciudades', 'error');
            });
    },

    // Función para actualizar select de estados
    updateStateSelect: function(states) {
        const stateSelect = document.getElementById('state');
        if (stateSelect) {
            stateSelect.innerHTML = '<option value="">Estado</option>';
            states.forEach(state => {
                const option = document.createElement('option');
                option.value = state.id;
                option.textContent = state.name;
                stateSelect.appendChild(option);
            });
        }
    },

    // Función para actualizar select de ciudades
    updateCitySelect: function(cities) {
        const citySelect = document.getElementById('city');
        if (citySelect) {
            citySelect.innerHTML = '<option value="">Ciudad</option>';
            cities.forEach(city => {
                const option = document.createElement('option');
                option.value = city.id;
                option.textContent = city.name;
                citySelect.appendChild(option);
            });
        }
    },

    // Función para actualizar campos de ubicación
    updateLocationFields: function(postalCode, currencyCode) {
        const postalCodeInput = document.querySelector('input[name="postal_code"]');
        const currencyInput = document.querySelector('input[name="currency"]');
        
        if (postalCodeInput) postalCodeInput.value = postalCode || '';
        if (currencyInput) currencyInput.value = currencyCode || '';
    },

    // Función para actualizar código postal
    updatePostalCode: function(postalCode) {
        const postalCodeInput = document.querySelector('input[name="postal_code"]');
        if (postalCodeInput) {
            postalCodeInput.value = postalCode || '';
        }
    },

    // Función para limpiar campos de ubicación
    clearLocationFields: function() {
        this.updateStateSelect([]);
        this.updateCitySelect([]);
        this.updateLocationFields('', '');
    },

    // Función para limpiar campos de ciudad
    clearCityFields: function() {
        this.updateCitySelect([]);
        this.updatePostalCode('');
    }
};

// ===== FUNCIÓN PRINCIPAL DE INICIALIZACIÓN =====
function initializeCompanyCreate() {
    // ===== INICIALIZACIÓN DE CARGAS DE ARCHIVOS =====
    function initializeFileUpload() {
        const fileInput = document.getElementById('file');
        const fileUploadContainer = document.getElementById('fileUploadContainer');
        const previewContainer = document.getElementById('previewContainer');
        
        if (!fileInput || !fileUploadContainer) return;

        // Drag and drop functionality
        fileUploadContainer.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });
        
        fileUploadContainer.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
        });
        
        fileUploadContainer.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                window.companyCreate.handleFileSelect(files[0]);
            }
        });
        
        fileInput.addEventListener('change', function(e) {
            if (this.files.length > 0) {
                window.companyCreate.handleFileSelect(this.files[0]);
            }
        });
    }

    // ===== INICIALIZACIÓN DE FORMULARIO =====
    function initializeForm() {
        const companyForm = document.getElementById('companyForm');
        const submitButton = document.getElementById('submitButton');
        
        if (!companyForm || !submitButton) return;

        const buttonText = submitButton.querySelector('.button-text');
        const loading = submitButton.querySelector('.loading');
        const successCheckmark = submitButton.querySelector('.success-checkmark');
        
        // Form submission with loading animation
        companyForm.addEventListener('submit', function(e) {
            // Show loading state
            if (buttonText) buttonText.style.opacity = '0';
            if (loading) loading.classList.add('active');
            submitButton.disabled = true;
            
            // Form will submit naturally
        });
    }

    // ===== INICIALIZACIÓN DE EFECTOS DE INPUT =====
    function initializeInputEffects() {
        const inputs = document.querySelectorAll('.form-input, .form-select, .form-textarea');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                const parent = this.parentElement;
                if (parent) {
                    parent.style.transform = 'scale(1.02)';
                }
            });
            
            input.addEventListener('blur', function() {
                const parent = this.parentElement;
                if (parent) {
                    parent.style.transform = 'scale(1)';
                }
            });
        });
    }

    // ===== INICIALIZACIÓN DE SELECTORES DE UBICACIÓN =====
    function initializeLocationSelectors() {
        const countrySelect = document.getElementById('country');
        const stateSelect = document.getElementById('state');
        
        if (countrySelect) {
            countrySelect.addEventListener('change', function() {
                const countryId = this.value;
                window.companyCreate.searchCountry(countryId);
            });
        }
        
        if (stateSelect) {
            stateSelect.addEventListener('change', function() {
                const stateId = this.value;
                window.companyCreate.searchState(stateId);
            });
        }
    }

    // ===== INICIALIZACIÓN DE NOTIFICACIONES =====
    function initializeNotifications() {
        // Mostrar notificaciones de sesión si existen
        const errorMessage = document.querySelector('meta[name="error-message"]');
        const successMessage = document.querySelector('meta[name="success-message"]');
        
        if (errorMessage && errorMessage.content) {
            window.companyCreate.showNotification(errorMessage.content, 'error');
        }
        
        if (successMessage && successMessage.content) {
            window.companyCreate.showNotification(successMessage.content, 'success');
        }
    }

    // ===== INICIALIZAR TODO =====
    initializeFileUpload();
    initializeForm();
    initializeInputEffects();
    initializeLocationSelectors();
    initializeNotifications();
}

// ===== INICIALIZAR CUANDO EL DOM ESTÉ LISTO =====
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        // Cargar SweetAlert2 si está disponible
        if (typeof loadSweetAlert2 === 'function') {
            loadSweetAlert2(function() {
                initializeCompanyCreate();
            });
        } else {
            initializeCompanyCreate();
        }
    });
} else {
    // Si el DOM ya está listo
    if (typeof loadSweetAlert2 === 'function') {
        loadSweetAlert2(function() {
            initializeCompanyCreate();
        });
    } else {
        initializeCompanyCreate();
    }
}

// ===== FUNCIONES DE UTILIDAD =====
function loadSweetAlert2(callback) {
    if (typeof Swal !== 'undefined') {
        callback();
        return;
    }

    // Cargar SweetAlert2 dinámicamente si no está disponible
    const script = document.createElement('script');
    script.src = '/vendor/sweetalert2/sweetalert2.min.js';
    script.onload = function() {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = '/vendor/sweetalert2/sweetalert2.min.css';
        document.head.appendChild(link);
        callback();
    };
    script.onerror = function() {
        console.warn('SweetAlert2 no se pudo cargar, usando alertas nativas');
        callback();
    };
    document.head.appendChild(script);
}
