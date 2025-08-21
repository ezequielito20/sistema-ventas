// Variables globales
let originalFormData = {};
let formData = {};

// Inicializar la página
document.addEventListener('DOMContentLoaded', function() {
    initializeForm();
    initializeEventListeners();
    saveOriginalFormData();
});

// Guardar datos originales del formulario
function saveOriginalFormData() {
    const inputs = document.querySelectorAll('#productForm input, #productForm select, #productForm textarea');
    
    originalFormData = {};
    inputs.forEach(input => {
        if (input.type !== 'file') {
            originalFormData[input.name] = input.value;
        }
    });
}

// Inicializar el formulario
function initializeForm() {
    initializeImagePreview();
    initializeProfitCalculator();
    restoreFormData();
}

// Validar formulario completo
function validateForm() {
    const requiredFields = [
        'code', 'name', 'category_id', 'entry_date', 
        'stock', 'min_stock', 'max_stock', 'purchase_price', 'sale_price'
    ];
    
    let isValid = true;
    let firstErrorField = null;

    requiredFields.forEach(fieldName => {
        const field = document.getElementById(fieldName);
        if (field && field.hasAttribute('required') && !field.value.trim()) {
            showFieldError(field, 'Este campo es requerido');
            isValid = false;
            if (!firstErrorField) firstErrorField = field;
        } else if (field) {
            clearFieldError(field);
        }
    });

    // Validaciones específicas
    const stock = document.getElementById('stock');
    const minStock = document.getElementById('min_stock');
    const maxStock = document.getElementById('max_stock');
    const purchasePrice = document.getElementById('purchase_price');
    const salePrice = document.getElementById('sale_price');

    if (stock && parseInt(stock.value) < 0) {
        showFieldError(stock, 'El stock no puede ser negativo');
        isValid = false;
        if (!firstErrorField) firstErrorField = stock;
    }

    if (minStock && maxStock) {
        const minValue = parseInt(minStock.value) || 0;
        const maxValue = parseInt(maxStock.value) || 0;
        if (minValue >= maxValue && maxValue > 0) {
            showFieldError(maxStock, 'El stock máximo debe ser mayor que el stock mínimo');
            isValid = false;
            if (!firstErrorField) firstErrorField = maxStock;
        }
    }

    if (purchasePrice && parseFloat(purchasePrice.value) < 0) {
        showFieldError(purchasePrice, 'El precio de compra no puede ser negativo');
        isValid = false;
        if (!firstErrorField) firstErrorField = purchasePrice;
    }

    if (salePrice && parseFloat(salePrice.value) < 0) {
        showFieldError(salePrice, 'El precio de venta no puede ser negativo');
        isValid = false;
        if (!firstErrorField) firstErrorField = salePrice;
    }

    // Validar imagen si se seleccionó
    const imageInput = document.getElementById('image');
    if (imageInput && imageInput.files.length > 0) {
        const file = imageInput.files[0];
        const maxSize = 2 * 1024 * 1024; // 2MB

        if (file.size > maxSize) {
            showAlert('Error', 'La imagen no puede ser mayor a 2MB', 'error');
            isValid = false;
        }

        if (!file.type.match('image.*')) {
            showAlert('Error', 'Por favor selecciona un archivo de imagen válido', 'error');
            isValid = false;
        }
    }

    if (!isValid && firstErrorField) {
        firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
        showAlert('Error', 'Por favor completa todos los campos requeridos correctamente', 'error');
    }

    return isValid;
}

// Mostrar error de campo
function showFieldError(input, message) {
    input.classList.add('is-invalid');
    let errorElement = input.parentNode.querySelector('.error-message');
    
    if (!errorElement) {
        errorElement = document.createElement('span');
        errorElement.className = 'error-message';
        input.parentNode.appendChild(errorElement);
    }
    
    errorElement.textContent = message;
}

// Limpiar error de campo
function clearFieldError(input) {
    input.classList.remove('is-invalid');
    const errorElement = input.parentNode.querySelector('.error-message');
    if (errorElement) {
        errorElement.remove();
    }
}

// Guardar datos del formulario
function saveFormData() {
    const inputs = document.querySelectorAll('#productForm input, #productForm select, #productForm textarea');
    
    formData = {};
    inputs.forEach(input => {
        if (input.type === 'file') {
            // Para archivos, guardamos el nombre si existe
            if (input.files.length > 0) {
                formData[input.name] = input.files[0].name;
            }
        } else {
            formData[input.name] = input.value;
        }
    });

    // Guardar en localStorage
    localStorage.setItem('productEditFormData', JSON.stringify(formData));
}

// Restaurar datos del formulario
function restoreFormData() {
    const savedData = localStorage.getItem('productEditFormData');
    if (savedData) {
        try {
            formData = JSON.parse(savedData);
            
            Object.keys(formData).forEach(fieldName => {
                const input = document.querySelector(`[name="${fieldName}"]`);
                if (input && input.type !== 'file') {
                    input.value = formData[fieldName];
                }
            });

            // Restaurar preview de imagen si existe
            const imageName = formData['image'];
            if (imageName) {
                const imagePreview = document.getElementById('imagePreview');
                if (imagePreview) {
                    imagePreview.style.backgroundImage = `url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 200 200"><rect width="200" height="200" fill="%23f3f4f6"/><text x="100" y="100" text-anchor="middle" dy=".3em" fill="%236b7280" font-family="Arial" font-size="14">${imageName}</text></svg>')`;
                    imagePreview.style.border = '3px solid var(--primary-color)';
                    imagePreview.classList.add('has-image');
                    
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
}

// Restaurar formulario a valores originales
function restoreForm() {
    if (confirm('¿Estás seguro de que quieres restaurar todos los campos a sus valores originales?')) {
        Object.keys(originalFormData).forEach(fieldName => {
            const input = document.querySelector(`[name="${fieldName}"]`);
            if (input) {
                input.value = originalFormData[fieldName];
            }
        });

        // Restaurar imagen original
        const imagePreview = document.getElementById('imagePreview');
        if (imagePreview) {
            // Verificar si hay imagen original
            const hasOriginalImage = imagePreview.dataset.hasOriginalImage === 'true';
            
            if (hasOriginalImage) {
                imagePreview.style.backgroundImage = `url('${imagePreview.dataset.originalImageUrl}')`;
                imagePreview.style.border = '3px solid var(--primary-color)';
                imagePreview.classList.add('has-image');
                
                const placeholder = imagePreview.querySelector('.upload-placeholder');
                if (placeholder) {
                    placeholder.style.display = 'none';
                }
            } else {
                imagePreview.style.backgroundImage = 'none';
                imagePreview.style.border = '3px dashed #d1d5db';
                imagePreview.classList.remove('has-image');
                
                const placeholder = imagePreview.querySelector('.upload-placeholder');
                if (placeholder) {
                    placeholder.style.display = 'flex';
                }
            }
        }

        // Limpiar input de imagen
        const imageInput = document.getElementById('image');
        if (imageInput) {
            imageInput.value = '';
        }

        // Limpiar errores
        document.querySelectorAll('.is-invalid').forEach(input => {
            clearFieldError(input);
        });

        // Limpiar localStorage
        localStorage.removeItem('productEditFormData');
        
        showAlert('Éxito', 'Formulario restaurado a valores originales', 'success');
    }
}

// Inicializar preview de imagen
function initializeImagePreview() {
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
            if (!file.type.match('image.*')) {
                showAlert('Error', 'Por favor selecciona un archivo de imagen válido', 'error');
                return;
            }

            // Validar tamaño (2MB)
            if (file.size > 2 * 1024 * 1024) {
                showAlert('Error', 'La imagen no puede ser mayor a 2MB', 'error');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(event) {
                imagePreview.style.backgroundImage = `url(${event.target.result})`;
                imagePreview.style.border = '3px solid var(--primary-color)';
                imagePreview.classList.add('has-image');
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
            // Restaurar imagen original si no hay archivo seleccionado
            const hasOriginalImage = imagePreview.dataset.hasOriginalImage === 'true';
            
            if (hasOriginalImage) {
                imagePreview.style.backgroundImage = `url('${imagePreview.dataset.originalImageUrl}')`;
                imagePreview.style.border = '3px solid var(--primary-color)';
                imagePreview.classList.add('has-image');
                if (placeholder) {
                    placeholder.style.display = 'none';
                }
            } else {
                imagePreview.style.backgroundImage = 'none';
                imagePreview.style.border = '3px dashed #d1d5db';
                imagePreview.classList.remove('has-image');
                if (placeholder) {
                    placeholder.style.display = 'flex';
                }
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
}

// Inicializar calculadora de beneficio
function initializeProfitCalculator() {
    const purchasePriceInput = document.getElementById('purchase_price');
    const salePriceInput = document.getElementById('sale_price');
    const profitIndicator = document.getElementById('profitIndicator');
    const profitValue = document.getElementById('profitValue');

    if (!purchasePriceInput || !salePriceInput || !profitIndicator || !profitValue) {
        return;
    }

    function calculateProfit() {
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
    }

    purchasePriceInput.addEventListener('input', calculateProfit);
    salePriceInput.addEventListener('input', calculateProfit);
    
    // Calcular beneficio inicial
    calculateProfit();
}

// Inicializar event listeners
function initializeEventListeners() {
    // Botón restaurar formulario
    const restoreBtn = document.getElementById('restoreForm');
    if (restoreBtn) {
        restoreBtn.addEventListener('click', restoreForm);
    }

    // Validación en tiempo real
    document.querySelectorAll('.form-input').forEach(input => {
        input.addEventListener('blur', function() {
            if (this.hasAttribute('required') && !this.value.trim()) {
                showFieldError(this, 'Este campo es requerido');
            } else {
                clearFieldError(this);
            }
        });

        input.addEventListener('input', function() {
            clearFieldError(this);
            saveFormData(); // Guardar datos en tiempo real
        });
    });

    // Validación del formulario al enviar
    const productForm = document.getElementById('productForm');
    if (productForm) {
        productForm.addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                return false;
            }

            // Mostrar indicador de carga
            const submitBtn = document.getElementById('submitProduct');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Actualizando...';
            }

            // Limpiar localStorage después de envío exitoso
            localStorage.removeItem('productEditFormData');
        });
    }

    // Guardar datos cuando se cambia la imagen
    const imageInput = document.getElementById('image');
    if (imageInput) {
        imageInput.addEventListener('change', saveFormData);
    }
}

// Mostrar alerta
function showAlert(title, text, icon) {
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
}

// Mostrar notificación de éxito
function showSuccessNotification(message) {
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
}

// Mostrar notificación de error
function showErrorNotification(message) {
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
