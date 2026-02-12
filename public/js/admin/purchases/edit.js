(function () {
  document.addEventListener('alpine:init', () => {
    Alpine.data('purchaseForm', () => ({
      formChanged: false,
      products: [],
      totalAmount: 0,
      totalProducts: 0,
      totalQuantity: 0,
      productCode: '',
      autoAddExecuted: false,
      isSubmitting: false,

      // Variables para Descuento General
      generalDiscountValue: 0,
      generalDiscountIsPercentage: false, // false = Fijo ($), true = Porcentaje (%)

      init() {
        if (window.purchaseFormInstance) return;
        this.$nextTick(() => {
          this.initializeEventListeners();
          this.loadInitialData(); // Cargar datos iniciales
          window.purchaseFormInstance = this;
          this.setupFormSubmitListener();
        });
      },

      loadInitialData() {
        const root = document.querySelector('.main-container');
        if (!root) return;

        // Cargar Descuento General
        this.generalDiscountValue = parseFloat(root.dataset.generalDiscountValue) || 0;
        this.generalDiscountIsPercentage = root.dataset.generalDiscountType === 'percentage';

        // Cargar Productos
        const initialProducts = (() => {
          try { return JSON.parse(root.dataset.initialProducts || '[]'); } catch { return []; }
        })();

        if (initialProducts.length > 0) {
          this.products = initialProducts.map(product => {
            const price = Number(product.price) || 0;
            const discountValue = parseFloat(product.discountValue) || 0;
            const discountIsPercentage = product.discountIsPercentage === true;

            return {
              ...product,
              image_url: product.image_url || product.image,
              quantity: Number(product.quantity) || 1,
              price: price, // Precio Original
              // Propiedades de Descuento Individual
              discountValue: discountValue,
              discountIsPercentage: discountIsPercentage,
              originalPrice: price,
              subtotal: Number(product.subtotal) || 0
            };
          });
          this.updateTotal();
          this.updateEmptyState();
        }
      },

      initializeEventListeners() {
        this.$watch('productCode', (v) => { if (v) this.searchProductByCode(v); });
        this.$el.addEventListener('input', () => { this.formChanged = true; });
      },

      searchProductByCode(code) {
        if (!code) return;
        if (this.products.some(p => p.code === code)) {
          this.showToast('Este producto ya está en la lista de compra', 'warning');
          this.productCode = '';
          return;
        }
        fetch(`/purchases/product-by-code/${code}`)
          .then(r => r.json())
          .then(data => {
            if (data.success) {
              this.addProductToTable(data.product);
              this.productCode = '';
            } else {
              this.showToast(data.message || 'No se encontró el producto', 'error');
            }
          })
          .catch(() => this.showToast('Error al buscar el producto', 'error'));
      },

      addProductToTable(product) {
        if (!product?.id) {
          this.showToast('El producto no tiene un ID válido', 'error');
          return;
        }
        if (this.products.some(p => p.code === product.code)) {
          this.showToast('Este producto ya está en la lista de compra', 'warning');
          return;
        }

        const imageUrl = product.image_url || product.image;
        const price = Number(product.purchase_price ?? product.price ?? 0);

        this.products.push({
          ...product,
          image_url: imageUrl,
          quantity: 1,
          price: price,
          // Propiedades de Descuento Individual
          discountValue: 0,
          discountIsPercentage: false, // Por defecto fijo
          originalPrice: price, // Precio original sin descuento
          subtotal: price // Subtotal inicial
        });

        this.updateTotal();
        this.updateEmptyState();
        this.showToast(`${product.name} se agregó a la lista de compra`, 'success');
      },

      removeProduct(index) {
        this.products.splice(index, 1);
        this.updateTotal();
        this.updateEmptyState();
      },

      // ===== LÓGICA DE ACTUALIZACIÓN DE PRODUCTOS =====
      updateProduct(index, field, value) {
        const item = this.products[index];

        if (field === 'quantity') {
          item.quantity = Number(value) || 0;
        } else if (field === 'price') {
          // Si cambia el precio base, actualizamos el precio y el originalPrice
          item.price = Number(value) || 0;
          item.originalPrice = item.price;
          // Re-validar el descuento para que no exceda el nuevo precio
          this.validateItemDiscount(item);
        }

        this.updateTotal();
      },

      // ===== LÓGICA DE DESCUENTOS POR ÍTEM =====
      updateItemDiscount(index) {
        const item = this.products[index];
        let val = item.discountValue;

        if (val === '') {
          this.updateTotal();
          return;
        }

        if (typeof val === 'string' && val.endsWith('.')) return;

        let numericVal = parseFloat(val);
        if (isNaN(numericVal)) {
          item.discountValue = 0;
          this.updateTotal();
          return;
        }

        const price = item.price || 0;
        if (numericVal < 0) {
          item.discountValue = 0;
        } else if (item.discountIsPercentage && numericVal > 100) {
          item.discountValue = 100;
        } else if (!item.discountIsPercentage && numericVal > price) {
          item.discountValue = price;
        }

        this.updateTotal();
      },

      toggleItemDiscountType(index) {
        const item = this.products[index];
        item.discountIsPercentage = !item.discountIsPercentage;
        this.validateItemDiscount(item);
        this.updateTotal();
      },

      validateItemDiscount(item) {
        const price = getKey(item, 'price', 0); // Helper seguro

        if (item.discountIsPercentage) {
          if (item.discountValue > 100) item.discountValue = 100;
        } else {
          if (item.discountValue > price) item.discountValue = price;
        }
      },

      getItemPriceWithDiscount(item) {
        const price = item.price || 0;
        const discountVal = parseFloat(item.discountValue) || 0;

        if (discountVal <= 0) return price;

        let finalPrice = price;
        if (item.discountIsPercentage) {
          finalPrice = price - (price * (discountVal / 100));
        } else {
          finalPrice = price - discountVal;
        }
        return Math.max(0, finalPrice);
      },

      getItemSubtotalWithDiscount(item) {
        const unitPrice = this.getItemPriceWithDiscount(item);
        return unitPrice * (item.quantity || 0);
      },

      // ===== LÓGICA DE DESCUENTO GENERAL =====
      updateGeneralDiscount() {
        let val = this.generalDiscountValue;

        if (val === '') {
          this.updateTotal();
          return;
        }

        if (typeof val === 'string' && val.endsWith('.')) return;

        let numericVal = parseFloat(val);
        if (isNaN(numericVal)) {
          this.generalDiscountValue = 0;
          this.updateTotal();
          return;
        }

        const subtotal = this.getSubtotalBeforeGeneralDiscount();
        if (numericVal < 0) {
          this.generalDiscountValue = 0;
        } else if (this.generalDiscountIsPercentage && numericVal > 100) {
          this.generalDiscountValue = 100;
        } else if (!this.generalDiscountIsPercentage && numericVal > subtotal) {
          this.generalDiscountValue = subtotal;
        }

        this.updateTotal();
      },

      toggleGeneralDiscountType() {
        this.generalDiscountIsPercentage = !this.generalDiscountIsPercentage;
        this.validateGeneralDiscount();
      },

      validateGeneralDiscount() {
        const subtotal = this.getSubtotalBeforeGeneralDiscount();

        if (this.generalDiscountIsPercentage) {
          if (this.generalDiscountValue > 100) this.generalDiscountValue = 100;
        } else {
          if (this.generalDiscountValue > subtotal) this.generalDiscountValue = subtotal;
        }
      },

      getSubtotalBeforeGeneralDiscount() {
        // Suma de los subtotales de cada línea (ya con su descuento individual aplicado)
        return this.products.reduce((acc, item) => acc + this.getItemSubtotalWithDiscount(item), 0);
      },

      getTotalWithGeneralDiscount() {
        const subtotal = this.getSubtotalBeforeGeneralDiscount();
        const discountVal = parseFloat(this.generalDiscountValue) || 0;

        if (discountVal <= 0) return subtotal;

        let total = subtotal;
        if (this.generalDiscountIsPercentage) {
          total = subtotal - (subtotal * (discountVal / 100));
        } else {
          total = subtotal - discountVal;
        }
        return Math.max(0, total);
      },

      updateTotal() {
        // Recalcular todo
        this.products.forEach(p => {
          // Actualizar propiedad visual subtotal por si se usa en algún lado directo
          p.subtotal = this.getItemSubtotalWithDiscount(p);
        });

        this.totalQuantity = this.products.reduce((s, p) => s + p.quantity, 0);
        this.totalProducts = this.products.length;
        this.totalAmount = this.getTotalWithGeneralDiscount();

        // Actualizar input hidden
        const hidden = document.getElementById('totalAmountInput');
        /* Nota: En el blade original no vi este ID, pero lo mantenemos por si acaso */

        const badge = document.querySelector('.counter-badge');
        if (badge) badge.textContent = `${this.totalProducts} producto${this.totalProducts !== 1 ? 's' : ''}`;
      },

      updateEmptyState() {
        const es = document.getElementById('emptyState');
        if (es) es.style.display = this.products.length === 0 ? 'block' : 'none';
      },

      openSearchModal() {
        window.dispatchEvent(new CustomEvent('openSearchModal'));
      },

      cancelEdit() {
        if (this.formChanged) {
          if (confirm('¿Está seguro? Se perderán todos los cambios realizados en esta compra')) this.goBack();
        } else {
          this.goBack();
        }
      },

      goBack() {
        const root = document.querySelector('.main-container');
        const referrerUrl = root?.dataset.referrerUrl;
        const indexUrl = root?.dataset.indexUrl;

        if (referrerUrl && referrerUrl !== 'null' && referrerUrl !== '') {
          window.location.href = referrerUrl;
        } else if (indexUrl) {
          window.location.href = indexUrl;
        } else {
          window.location.href = '/purchases';
        }
      },

      submitForm(event) {
        if (this.products.length === 0) {
          this.showToast('Debe agregar al menos un producto', 'warning');
          return;
        }
        if (!document.getElementById('purchase_date')?.value) {
          this.showToast('Debe seleccionar una fecha de compra', 'warning');
          return;
        }

        if (this.isSubmitting) return;

        // Capturar los valores del botón ANTES del Swal
        const btn = event.currentTarget;
        const actionName = btn?.name;
        const actionValue = btn?.value;

        Swal.fire({
          title: '¿Confirmar Cambios?',
          text: "¿Está seguro de que desea actualizar esta compra?",
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Sí, actualizar',
          cancelButtonText: 'Cancelar',
          reverseButtons: true
        }).then((result) => {
          if (result.isConfirmed) {
            try {
              this.isSubmitting = true;
              this.prepareFormData();

              const form = document.getElementById('purchaseForm');
              if (!form) throw new Error('No se encontró el formulario #purchaseForm');

              // Si capturamos una acción, agregarla como input hidden
              if (actionName && actionValue) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = actionName;
                input.value = actionValue;
                input.className = 'dynamic-input';
                form.appendChild(input);
              }

              // Enviar el formulario programáticamente
              form.submit();
            } catch (err) {
              console.error('Error crítico en la actualización:', err);
              this.showToast('Error al procesar la actualización: ' + err.message, 'error');
              this.isSubmitting = false;
            }
          }
        });
      },

      prepareFormData() {
        const form = document.getElementById('purchaseForm');

        // Limpiar inputs hidden anteriores si los hubiera (para evitar duplicados en re-envíos fallidos sin recarga)
        form.querySelectorAll('.dynamic-input').forEach(e => e.remove());

        // Inputs para productos
        this.products.forEach((p, index) => {
          const createInput = (name, value) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            input.className = 'dynamic-input';
            form.appendChild(input);
          };

          createInput(`items[${p.id}][quantity]`, p.quantity);
          createInput(`items[${p.id}][price]`, p.price); // Precio Unitario Original

          // Campos de descuento por ítem
          createInput(`items[${p.id}][discount_value]`, p.discountValue);
          createInput(`items[${p.id}][discount_type]`, p.discountIsPercentage ? 'percentage' : 'fixed');
          createInput(`items[${p.id}][original_price]`, p.price);

          // Precio final unitario (Precio base - descuento unitario)
          const finalUnitPrice = this.getItemPriceWithDiscount(p);
          createInput(`items[${p.id}][final_price]`, finalUnitPrice);

          // Subtotal de la línea
          // createInput(`items[${p.id}][subtotal]`, p.subtotal); // No es estrictamente necesario enviar subtotal al backend si se recalcula, pero ayuda
        });

        // Inputs para Descuento General
        const createGeneralInput = (name, value) => {
          const input = document.createElement('input');
          input.type = 'hidden';
          input.name = name;
          input.value = value;
          input.className = 'dynamic-input';
          form.appendChild(input);
        };

        createGeneralInput('general_discount_value', this.generalDiscountValue);
        createGeneralInput('general_discount_type', this.generalDiscountIsPercentage ? 'percentage' : 'fixed');

        const subtotalGeneral = this.getSubtotalBeforeGeneralDiscount();
        createGeneralInput('subtotal_before_discount', subtotalGeneral);

        const totalFinal = this.getTotalWithGeneralDiscount();
        createGeneralInput('total_with_discount', totalFinal);

        // Actualizar el campo 'total_price' que ya existe en el form o crearlo
        let totalInput = form.querySelector('input[name="total_price"]');
        if (!totalInput) {
          totalInput = document.createElement('input');
          totalInput.type = 'hidden';
          totalInput.name = 'total_price';
          form.appendChild(totalInput);
        }
        totalInput.value = totalFinal;
      },

      showToast(msg, type = 'success') {
        if (window.Swal) {
          const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
          });
          Toast.fire({ icon: type, title: msg });
        } else {
          console.log(msg);
        }
      },

      setupFormSubmitListener() {
        // Manejado programáticamente en submitForm
      },

      // Función global para verificar si un producto está en la tabla
      isProductInTable(productId) {
        return this.products?.some(p => p.id == productId) || false;
      }
    }));

    Alpine.data('searchModal', () => ({
      isOpen: false,
      searchTerm: '',
      init() {
        this.$nextTick(() => { window.addEventListener('openSearchModal', () => this.openModal()); });
      },
      openModal() {
        this.isOpen = true;
        this.searchTerm = '';
        document.body.style.overflow = 'hidden';
        this.$nextTick(() => {
          const input = this.$el.querySelector('input[type="text"]');
          if (input) input.focus();
        });
      },
      closeModal() {
        this.isOpen = false;
        document.body.style.overflow = 'auto';
      },
      filterProductsInModal() {
        const term = this.searchTerm.toLowerCase();
        document.querySelectorAll('#searchProductModal tbody tr').forEach(row => {
          const code = row.querySelector('td:first-child span')?.textContent.toLowerCase() || '';
          const name = row.querySelector('td:nth-child(3) .ml-2 div:first-child')?.textContent.toLowerCase() || '';
          const cat = row.querySelector('td:nth-child(4) span')?.textContent.toLowerCase() || '';
          row.style.display = (code.includes(term) || name.includes(term) || cat.includes(term)) ? '' : 'none';
        });
      },
      addProductFromModal(id, code, name, imageUrl, stock, price, categoryName) {
        if (window.purchaseFormInstance) {
          // Adaptar al formato esperado por addProductToTable
          const product = {
            id,
            code,
            name,
            image_url: imageUrl,
            stock,
            purchase_price: price,
            price: price, // Asegurar que price esté presente
            category: { name: categoryName }
          };
          window.purchaseFormInstance.addProductToTable(product);
          this.closeModal();
        }
      }
    }));
  });

  // Helper seguro para obtener propiedades
  function getKey(obj, key, def) {
    return obj && obj[key] !== undefined ? obj[key] : def;
  }

  window.addEventListener('beforeunload', (e) => {
    if (window.purchaseFormInstance?.formChanged && !window.purchaseFormInstance.isSubmitting) {
      e.preventDefault();
      e.returnValue = '';
    }
  });

  window.goBack = function () {
    if (window.purchaseFormInstance?.goBack) {
      window.purchaseFormInstance.goBack();
    } else {
      const root = document.querySelector('.main-container');
      const referrerUrl = root?.dataset.referrerUrl;
      const indexUrl = root?.dataset.indexUrl;

      if (document.referrer && !/purchases\/(create|edit)/.test(document.referrer)) {
        history.back();
      } else if (referrerUrl && referrerUrl !== 'null' && referrerUrl !== '') {
        window.location.href = referrerUrl;
      } else if (indexUrl) {
        window.location.href = indexUrl;
      } else {
        window.location.href = '/purchases';
      }
    }
  };
})();


