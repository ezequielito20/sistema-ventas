// ===== CONFIGURACIÓN GLOBAL =====
const COMPANY_EDIT_CONFIG = {
    routes: {
        searchCountry: '/create-company',
        searchState: '/search-state'
    },
    fileTypes: {
        allowed: ['image/*'],
        maxSize: 5 * 1024 * 1024 // 5MB
    }
};

// ===== FUNCIONES GLOBALES =====
window.companyEdit = {
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

        if (file.size > COMPANY_EDIT_CONFIG.fileTypes.maxSize) {
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
        const stateSelect = document.getElementById('state');
        const citySelect = document.getElementById('city');
        
        // Limpiar y deshabilitar campos dependientes
        if (stateSelect) {
            stateSelect.innerHTML = '<option value="">Seleccione estado</option>';
            stateSelect.disabled = true;
        }
        if (citySelect) {
            citySelect.innerHTML = '<option value="">Seleccione ciudad</option>';
            citySelect.disabled = true;
        }
        
        if (!countryId) {
            return;
        }

        fetch(`${COMPANY_EDIT_CONFIG.routes.searchCountry}/${countryId}`)
            .then(response => response.json())
            .then(data => {
                this.updateStateSelect(data.states || []);
            })
            .catch(error => {
                console.error('Error al obtener estados:', error);
                this.updateStateSelect([]);
                this.showNotification('Error al cargar los estados', 'error');
            });
    },

    // Función para buscar estados
    searchState: function(stateId) {
        const citySelect = document.getElementById('city');
        
        // Limpiar y deshabilitar campos dependientes
        if (citySelect) {
            citySelect.innerHTML = '<option value="">Seleccione ciudad</option>';
            citySelect.disabled = true;
        }
        
        if (!stateId) {
            return;
        }

        fetch(`${COMPANY_EDIT_CONFIG.routes.searchState}/${stateId}`)
            .then(response => response.json())
            .then(data => {
                this.updateCitySelect(data.cities || []);
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
            stateSelect.innerHTML = '<option value="">Seleccione estado</option>';
            if (states && states.length > 0) {
                states.forEach(state => {
                    const option = document.createElement('option');
                    option.value = state.id;
                    option.textContent = state.name;
                    stateSelect.appendChild(option);
                });
                stateSelect.disabled = false;
            } else {
                stateSelect.disabled = true;
            }
        }
    },

    // Función para actualizar select de ciudades
    updateCitySelect: function(cities) {
        const citySelect = document.getElementById('city');
        if (citySelect) {
            citySelect.innerHTML = '<option value="">Seleccione ciudad</option>';
            if (cities && cities.length > 0) {
                cities.forEach(city => {
                    const option = document.createElement('option');
                    option.value = city.id;
                    option.textContent = city.name;
                    citySelect.appendChild(option);
                });
                citySelect.disabled = false;
            } else {
                citySelect.disabled = true;
            }
        }
    },

    // Función para actualizar campos de ubicación (ya no se usa)
    updateLocationFields: function(postalCode, currencyCode) {
        // Ya no actualizamos el código postal automáticamente, ahora es independiente
    },

    // Función para limpiar campos de ubicación
    clearLocationFields: function() {
        this.updateStateSelect([]);
        this.updateCitySelect([]);
        // Ya no limpiamos el código postal automáticamente, ahora es independiente
    },

    // Función para cargar estados iniciales
    loadInitialStates: function(countryId, initialStateId, initialCityId) {
        if (!countryId) return;

        fetch(`${COMPANY_EDIT_CONFIG.routes.searchCountry}/${countryId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                const stateSelect = document.getElementById('state');
                if (stateSelect) {
                    stateSelect.innerHTML = '<option value="">Seleccione un estado</option>';
                    
                    if (data.states && data.states.length > 0) {
                        data.states.forEach(state => {
                            const option = document.createElement('option');
                            option.value = state.id;
                            option.textContent = state.name;
                            if (state.id == initialStateId) {
                                option.selected = true;
                            }
                            stateSelect.appendChild(option);
                        });
                        stateSelect.disabled = false;
                        
                        // Después de cargar los estados, cargar las ciudades del estado seleccionado
                        if (initialStateId) {
                            this.loadInitialCities(initialStateId, initialCityId);
                        }
                    } else {
                        stateSelect.disabled = true;
                    }
                }
            })
            .catch(error => {
                console.error('Error al cargar estados:', error);
            });
    },

    // Función para cargar ciudades iniciales
    loadInitialCities: function(stateId, initialCityId) {
        if (!stateId) return;

        fetch(`${COMPANY_EDIT_CONFIG.routes.searchState}/${stateId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                const citySelect = document.getElementById('city');
                if (citySelect) {
                    citySelect.innerHTML = '<option value="">Seleccione una ciudad</option>';
                    
                    if (data.cities && data.cities.length > 0) {
                        data.cities.forEach(city => {
                            const option = document.createElement('option');
                            option.value = city.id;
                            option.textContent = city.name;
                            if (city.id == initialCityId) {
                                option.selected = true;
                            }
                            citySelect.appendChild(option);
                        });
                        citySelect.disabled = false;
                    } else {
                        citySelect.disabled = true;
                    }
                }
            })
            .catch(error => {
                console.error('Error al cargar ciudades:', error);
            });
    }
};

// ===== FUNCIÓN PRINCIPAL DE INICIALIZACIÓN =====
function initializeCompanyEdit() {
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
                window.companyEdit.handleFileSelect(files[0]);
            }
        });
        
        fileInput.addEventListener('change', function(e) {
            if (this.files.length > 0) {
                window.companyEdit.handleFileSelect(this.files[0]);
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
                window.companyEdit.searchCountry(countryId);
            });
        }
        
        if (stateSelect) {
            stateSelect.addEventListener('change', function() {
                const stateId = this.value;
                window.companyEdit.searchState(stateId);
            });
        }
    }

    // ===== INICIALIZACIÓN DE DATOS INICIALES =====
    function initializeInitialData() {
        // Obtener valores iniciales de la empresa desde el DOM
        const initialCountryId = document.querySelector('meta[name="initial-country-id"]')?.content;
        const initialStateId = document.querySelector('meta[name="initial-state-id"]')?.content;
        const initialCityId = document.querySelector('meta[name="initial-city-id"]')?.content;

        // Verificar que los valores no estén vacíos o sean "null"
        const validCountryId = initialCountryId && initialCountryId !== 'null' && initialCountryId !== '';
        const validStateId = initialStateId && initialStateId !== 'null' && initialStateId !== '';
        const validCityId = initialCityId && initialCityId !== 'null' && initialCityId !== '';

        // Cargar estados iniciales basados en el país de la compañía
        if (validCountryId) {
            window.companyEdit.loadInitialStates(initialCountryId, validStateId ? initialStateId : null, validCityId ? initialCityId : null);
        }
    }

    // ===== INICIALIZAR TODO =====
    initializeFileUpload();
    initializeForm();
    initializeInputEffects();
    initializeLocationSelectors();
    initializeInitialData();
}

// ===== INICIALIZAR CUANDO EL DOM ESTÉ LISTO =====
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        // Cargar SweetAlert2 si está disponible
        if (typeof loadSweetAlert2 === 'function') {
            loadSweetAlert2(function() {
                initializeCompanyEdit();
            });
        } else {
            initializeCompanyEdit();
        }
    });
} else {
    // Si el DOM ya está listo
    if (typeof loadSweetAlert2 === 'function') {
        loadSweetAlert2(function() {
            initializeCompanyEdit();
        });
    } else {
        initializeCompanyEdit();
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
