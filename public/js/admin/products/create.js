// ===== CONFIGURACIÓN GLOBAL =====
if (typeof PRODUCTS_CREATE_CONFIG === 'undefined') {
    window.PRODUCTS_CREATE_CONFIG = {
        routes: {
            store: '/admin/products',
            index: '/admin/products'
        },
        validation: {
            maxImageSize: 2 * 1024 * 1024, // 2MB
            allowedImageTypes: ['image/jpeg', 'image/png', 'image/gif', 'image/webp']
        }
    };
}

// ===== FUNCIONES GLOBALES =====
if (typeof window.productsCreate === 'undefined') {
    window.productsCreate = {
        // Variables globales
        formData: {},

        // Inicializar la página
        init: function() {
            this.initializeForm();
            this.initializeEventListeners();
        },

        // Inicializar el formulario
        initializeForm: function() {
            this.initializeImagePreview();
            this.initializeProfitCalculator();
            this.initializeCodeGenerator();
            this.restoreFormData();
        },

        // Validar formulario completo
        validateForm: function() {
            const requiredFields = [
                'code', 'name', 'category_id', 'entry_date', 
                'stock', 'min_stock', 'max_stock', 'purchase_price', 'sale_price'
            ];
            
            let isValid = true;
            let firstErrorField = null;

            requiredFields.forEach(fieldName => {
                const field = document.getElementById(fieldName);
                if (field && field.hasAttribute('required') && !field.value.trim()) {
                    this.showFieldError(field, 'Este campo es requerido');
                    isValid = false;
                    if (!firstErrorField) firstErrorField = field;
                } else if (field) {
                    this.clearFieldError(field);
                }
            });

            // Validaciones específicas
            const stock = document.getElementById('stock');
            const minStock = document.getElementById('min_stock');
            const maxStock = document.getElementById('max_stock');
            const purchasePrice = document.getElementById('purchase_price');
            const salePrice = document.getElementById('sale_price');

            if (stock && parseInt(stock.value) < 0) {
                this.showFieldError(stock, 'El stock no puede ser negativo');
                isValid = false;
                if (!firstErrorField) firstErrorField = stock;
            }

            if (minStock && maxStock) {
                const minValue = parseInt(minStock.value) || 0;
                const maxValue = parseInt(maxStock.value) || 0;
                if (minValue >= maxValue && maxValue > 0) {
                    this.showFieldError(maxStock, 'El stock máximo debe ser mayor que el stock mínimo');
                    isValid = false;
                    if (!firstErrorField) firstErrorField = maxStock;
                }
            }

            if (purchasePrice && parseFloat(purchasePrice.value) < 0) {
                this.showFieldError(purchasePrice, 'El precio de compra no puede ser negativo');
                isValid = false;
                if (!firstErrorField) firstErrorField = purchasePrice;
            }

            if (salePrice && parseFloat(salePrice.value) < 0) {
                this.showFieldError(salePrice, 'El precio de venta no puede ser negativo');
                isValid = false;
                if (!firstErrorField) firstErrorField = salePrice;
            }

            // Validar imagen si se seleccionó
            const imageInput = document.getElementById('image');
            if (imageInput && imageInput.files.length > 0) {
                const file = imageInput.files[0];

                if (file.size > PRODUCTS_CREATE_CONFIG.validation.maxImageSize) {
                    this.showAlert('Error', 'La imagen no puede ser mayor a 2MB', 'error');
                    isValid = false;
                }

                if (!PRODUCTS_CREATE_CONFIG.validation.allowedImageTypes.includes(file.type)) {
                    this.showAlert('Error', 'Por favor selecciona un archivo de imagen válido', 'error');
                    isValid = false;
                }
            }

            if (!isValid && firstErrorField) {
                firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                this.showAlert('Error', 'Por favor completa todos los campos requeridos correctamente', 'error');
            }

            return isValid;
        },

        // Mostrar error de campo
        showFieldError: function(input, message) {
            input.classList.add('is-invalid');
            let errorElement = input.parentNode.querySelector('.error-message');
            
            if (!errorElement) {
                errorElement = document.createElement('span');
                errorElement.className = 'error-message';
                input.parentNode.appendChild(errorElement);
            }
            
            errorElement.textContent = message;
        },

        // Limpiar error de campo
        clearFieldError: function(input) {
            input.classList.remove('is-invalid');
            const errorElement = input.parentNode.querySelector('.error-message');
            if (errorElement) {
                errorElement.remove();
            }
        },

        // Guardar datos del formulario
        saveFormData: function() {
            const inputs = document.querySelectorAll('#productForm input, #productForm select, #productForm textarea');
            
            this.formData = {};
            inputs.forEach(input => {
                if (input.type === 'file') {
                    // Para archivos, guardamos el nombre si existe
                    if (input.files.length > 0) {
                        this.formData[input.name] = input.files[0].name;
                    }
                } else {
                    this.formData[input.name] = input.value;
                }
            });

            // Guardar en localStorage
            localStorage.setItem('productFormData', JSON.stringify(this.formData));
        },

        // Restaurar datos del formulario
        restoreFormData: function() {
            const savedData = localStorage.getItem('productFormData');
            if (savedData) {
                try {
                    this.formData = JSON.parse(savedData);
                    
                    Object.keys(this.formData).forEach(fieldName => {
                        const input = document.querySelector(`[name="${fieldName}"]`);
                        if (input && input.type !== 'file') {
                            input.value = this.formData[fieldName];
                        }
                    });

                    // Restaurar preview de imagen si existe
                    const imageName = this.formData['image'];
                    if (imageName) {
                        const imagePreview = document.getElementById('imagePreview');
                        if (imagePreview) {
                            imagePreview.style.backgroundImage = `url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 200 200"><rect width="200" height="200" fill="%23f3f4f6"/><text x="100" y="100" text-anchor="middle" dy=".3em" fill="%236b7280" font-family="Arial" font-size="14">${imageName}</text></svg>')`;
                            imagePreview.style.border = '3px solid var(--primary-color)';
                            
                            const placeholder = imagePreview.querySelector('.upload-placeholder');
                            if (placeholder) {
                                placeholder.style.display = 'none';
                            }
                        }
                    }
                } catch (e) {
                    // Error restoring form data
                }
            }
        },

        // Limpiar formulario
        clearForm: function() {
            if (confirm('¿Estás seguro de que quieres limpiar todos los campos del formulario?')) {
                document.getElementById('productForm').reset();
                
                // Limpiar preview de imagen
                const imagePreview = document.getElementById('imagePreview');
                if (imagePreview) {
                    imagePreview.style.backgroundImage = 'none';
                    imagePreview.style.border = '3px dashed #d1d5db';
                    
                    const placeholder = imagePreview.querySelector('.upload-placeholder');
                    if (placeholder) {
                        placeholder.style.display = 'flex';
                    }
                    
                    const overlay = imagePreview.querySelector('.image-overlay');
                    if (overlay) {
                        overlay.remove();
                    }
                }

                // Limpiar errores
                document.querySelectorAll('.is-invalid').forEach(input => {
                    this.clearFieldError(input);
                });

                // Limpiar localStorage
                localStorage.removeItem('productFormData');
                
                this.showAlert('Éxito', 'Formulario limpiado correctamente', 'success');
            }
        },

        // Inicializar preview de imagen
        initializeImagePreview: function() {
            const imageInput = document.getElementById('image');
            const imagePreview = document.getElementById('imagePreview');
            
            if (!imageInput || !imagePreview) {
                return;
            }

            const placeholder = imagePreview.querySelector('.upload-placeholder');

            imageInput.addEventListener('change', function() {
                const file = this.files[0];
                
                if (file) {
                    // Validar tipo de archivo
                    if (!PRODUCTS_CREATE_CONFIG.validation.allowedImageTypes.includes(file.type)) {
                        window.productsCreate.showAlert('Error', 'Por favor selecciona un archivo de imagen válido', 'error');
                        return;
                    }

                    // Validar tamaño
                    if (file.size > PRODUCTS_CREATE_CONFIG.validation.maxImageSize) {
                        window.productsCreate.showAlert('Error', 'La imagen no puede ser mayor a 2MB', 'error');
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function(event) {
                        imagePreview.style.backgroundImage = `url(${event.target.result})`;
                        imagePreview.style.border = '3px solid var(--primary-color)';
                        if (placeholder) {
                            placeholder.style.display = 'none';
                        }
                        
                        // Agregar overlay con información del archivo
                        const overlay = document.createElement('div');
                        overlay.className = 'image-overlay';
                        overlay.innerHTML = `
                            <div class="image-info">
                                <i class="fas fa-check-circle"></i>
                                <span>${file.name}</span>
                            </div>
                        `;
                        imagePreview.appendChild(overlay);
                    };
                    reader.readAsDataURL(file);
                } else {
                    imagePreview.style.backgroundImage = 'none';
                    imagePreview.style.border = '3px dashed #d1d5db';
                    if (placeholder) {
                        placeholder.style.display = 'flex';
                    }
                    const overlay = imagePreview.querySelector('.image-overlay');
                    if (overlay) {
                        overlay.remove();
                    }
                }
            });

            // Click en preview para seleccionar archivo
            imagePreview.addEventListener('click', function() {
                imageInput.click();
            });
        },

        // Inicializar calculadora de beneficio
        initializeProfitCalculator: function() {
            const purchasePriceInput = document.getElementById('purchase_price');
            const salePriceInput = document.getElementById('sale_price');
            const profitIndicator = document.getElementById('profitIndicator');
            const profitValue = document.getElementById('profitValue');

            if (!purchasePriceInput || !salePriceInput || !profitIndicator || !profitValue) {
                return;
            }

            const calculateProfit = () => {
                const purchasePrice = parseFloat(purchasePriceInput.value) || 0;
                const salePrice = parseFloat(salePriceInput.value) || 0;

                if (purchasePrice > 0) {
                    const profit = ((salePrice - purchasePrice) / purchasePrice) * 100;
                    profitValue.textContent = profit.toFixed(2) + '%';
                    profitIndicator.style.display = 'block';

                    // Cambiar color según el margen
                    profitIndicator.className = 'profit-indicator';
                    
                    if (profit < 0) {
                        profitIndicator.classList.add('alert-danger');
                        profitIndicator.querySelector('i').className = 'fas fa-arrow-down';
                    } else if (profit < 20) {
                        profitIndicator.classList.add('alert-warning');
                        profitIndicator.querySelector('i').className = 'fas fa-exclamation-triangle';
                    } else {
                        profitIndicator.classList.add('alert-success');
                        profitIndicator.querySelector('i').className = 'fas fa-arrow-up';
                    }
                } else {
                    profitIndicator.style.display = 'none';
                }
            };

            purchasePriceInput.addEventListener('input', calculateProfit);
            salePriceInput.addEventListener('input', calculateProfit);
        },

        // Auto-generar código
        initializeCodeGenerator: function() {
            const nameInput = document.getElementById('name');
            const codeInput = document.getElementById('code');

            if (!nameInput || !codeInput) {
                return;
            }

            nameInput.addEventListener('blur', function() {
                const code = codeInput.value.trim();
                const name = this.value.trim();
                
                if (!code && name) {
                    const generatedCode = 'PROD' + Date.now().toString().slice(-6);
                    codeInput.value = generatedCode;
                }
            });
        },

        // Inicializar event listeners
        initializeEventListeners: function() {
            // Botón limpiar formulario
            const clearBtn = document.getElementById('clearForm');
            if (clearBtn) {
                clearBtn.addEventListener('click', () => this.clearForm());
            }

            // Validación en tiempo real
            document.querySelectorAll('.form-input').forEach(input => {
                input.addEventListener('blur', function() {
                    if (this.hasAttribute('required') && !this.value.trim()) {
                        window.productsCreate.showFieldError(this, 'Este campo es requerido');
                    } else {
                        window.productsCreate.clearFieldError(this);
                    }
                });

                input.addEventListener('input', function() {
                    window.productsCreate.clearFieldError(this);
                    window.productsCreate.saveFormData(); // Guardar datos en tiempo real
                });
            });

            // Validación del formulario al enviar
            const productForm = document.getElementById('productForm');
            if (productForm) {
                productForm.addEventListener('submit', function(e) {
                    if (!window.productsCreate.validateForm()) {
                        e.preventDefault();
                        return false;
                    }

                    // Mostrar indicador de carga
                    const submitBtn = document.getElementById('submitProduct');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
                    }

                    // Limpiar localStorage después de envío exitoso
                    localStorage.removeItem('productFormData');
                });
            }

            // Guardar datos cuando se cambia la imagen
            const imageInput = document.getElementById('image');
            if (imageInput) {
                imageInput.addEventListener('change', () => this.saveFormData());
            }
        },

        // Mostrar alerta
        showAlert: function(title, text, icon) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: title,
                    text: text,
                    icon: icon,
                    confirmButtonText: 'Entendido',
                    timer: icon === 'success' ? 3000 : undefined,
                    timerProgressBar: icon === 'success'
                });
            } else {
                alert(`${title}: ${text}`);
            }
        },

        // Mostrar notificación de éxito
        showSuccessNotification: function(message) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¡Éxito!',
                    text: message,
                    icon: 'success',
                    confirmButtonText: 'Entendido',
                    timer: 3000,
                    timerProgressBar: true
                });
            } else {
                alert('Éxito: ' + message);
            }
        },

        // Mostrar notificación de error
        showErrorNotification: function(message) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Error',
                    text: message,
                    icon: 'error',
                    confirmButtonText: 'Entendido'
                });
            } else {
                alert('Error: ' + message);
            }
        }
    };
}

// ===== INICIALIZACIÓN =====
document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.productsCreate !== 'undefined') {
        window.productsCreate.init();
    }
});
