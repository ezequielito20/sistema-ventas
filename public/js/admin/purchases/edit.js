(function(){
  document.addEventListener('alpine:init', () => {
    window.purchaseForm = function purchaseForm(){
      return {
        formChanged:false, products:[], totalAmount:0, totalProducts:0, totalQuantity:0, productCode:'', autoAddExecuted:false,
        init(){ 
          if(window.purchaseFormInstance) return; 
          this.$nextTick(()=>{ 
            this.initializeEventListeners(); 
            this.updateEmptyState(); 
            window.purchaseFormInstance=this; 
            this.loadInitialProducts(); 
          }); 
        },
        loadInitialProducts(){
          const root = document.querySelector('.main-container');
          const initialProducts = (() => {
            try { return JSON.parse(root?.dataset.initialProducts || '[]'); } catch { return []; }
          })();
          
          if(initialProducts.length > 0) {
            this.products = initialProducts.map(product => ({
              ...product,
              quantity: Number(product.quantity) || 1,
              price: Number(product.price) || 0,
              subtotal: Number(product.quantity || 1) * Number(product.price || 0)
            }));
            this.updateTotal();
            this.updateEmptyState();
          }
        },
        initializeEventListeners(){ 
          this.$watch('productCode',(v)=>{ 
            if(v){ 
              this.searchProductByCode(this.productCode); 
            }
          }); 
          this.$el.addEventListener('input',()=>{ 
            this.formChanged=true; 
          }); 
        },
        searchProductByCode(code){ 
          if(!code) return; 
          if(this.products.some(p=>p.code===code)){ 
            this.showToast('Este producto ya está en la lista de compra','warning'); 
            return; 
          } 
          fetch(`/purchases/product-by-code/${code}`).then(r=>r.json()).then(data=>{ 
            if(data.success){ 
              this.addProductToTable(data.product); 
              this.productCode=''; 
            } else { 
              this.showToast(data.message||'No se encontró el producto','error'); 
            } 
          }).catch(()=> this.showToast('No se encontró el producto','error')); 
        },
        addProductToTable(product){ 
          if(!product?.id){ 
            this.showToast('El producto no tiene un ID válido','error'); 
            return; 
          } 
          if(this.products.some(p=>p.code===product.code)){ 
            this.showToast('Este producto ya está en la lista de compra','warning'); 
            return; 
          } 
          
          // Usar la imagen del producto directamente
          const imageUrl = product.image_url || product.image;
          
          this.products.push({ 
            ...product, 
            image_url: imageUrl,
            quantity: 1, 
            price: Number(product.purchase_price ?? product.price ?? 0), 
            subtotal: Number(product.purchase_price ?? product.price ?? 0) 
          }); 
          this.updateTotal(); 
          this.updateEmptyState(); 
          this.showToast(`${product.name} se agregó a la lista de compra`,'success'); 
        },
        updateProduct(i,field,value){ 
          this.products[i][field]=Number(value)||0; 
          this.products[i].subtotal=this.products[i].quantity*this.products[i].price; 
          this.updateTotal(); 
        },
        removeProduct(i){ 
          this.products.splice(i,1); 
          this.updateTotal(); 
          this.updateEmptyState(); 
        },
        updateTotal(){ 
          this.totalAmount=this.products.reduce((s,p)=>s+p.subtotal,0); 
          this.totalProducts=this.products.length; 
          this.totalQuantity=this.products.reduce((s,p)=>s+p.quantity,0); 
          const hidden=document.getElementById('totalAmountInput'); 
          if(hidden) hidden.value=this.totalAmount.toFixed(2); 
          const badge=document.querySelector('.counter-badge'); 
          if(badge) badge.textContent=`${this.totalProducts} producto${this.totalProducts!==1?'s':''}`; 
        },
        updateEmptyState(){ 
          const es=document.getElementById('emptyState'); 
          if(es) es.style.display=this.products.length===0?'block':'none'; 
        },
        openSearchModal(){ 
          window.dispatchEvent(new CustomEvent('openSearchModal')); 
        },
        cancelEdit(){ 
          if(this.formChanged){ 
            if(confirm('¿Está seguro? Se perderán todos los cambios realizados en esta compra')) this.goBack(); 
          } else { 
            this.goBack(); 
          } 
        },
        goBack(){ 
          const root = document.querySelector('.main-container');
          const referrerUrl = root?.dataset.referrerUrl;
          const indexUrl = root?.dataset.indexUrl;
          
          if(referrerUrl && referrerUrl !== 'null') {
            window.location.href = referrerUrl;
          } else if(indexUrl) {
            window.location.href = indexUrl;
          } else {
            window.location.href = '/purchases';
          }
        },
        submitForm(){ 
          // Validaciones silenciosas - no mostrar alertas
          if(this.products.length===0){ 
            return false;
          } 
          if(!document.getElementById('purchase_date')?.value){ 
            return false;
          } 
          
          // Resetear formChanged para evitar la alerta de beforeunload
          this.formChanged = false;
          
          this.prepareFormData(); 
          
          // Enviar el formulario
          document.getElementById('purchaseForm').submit();
          return false; // Prevenir el comportamiento por defecto
        },
        prepareFormData(){ 
          this.products.forEach(p=>{ 
            const q=document.createElement('input'); 
            q.type='hidden'; 
            q.name=`items[${p.id}][quantity]`; 
            q.value=p.quantity; 
            const pr=document.createElement('input'); 
            pr.type='hidden'; 
            pr.name=`items[${p.id}][price]`; 
            pr.value=p.price; 
            document.getElementById('purchaseForm').appendChild(q); 
            document.getElementById('purchaseForm').appendChild(pr); 
          }); 
        },
        showToast(msg,type='success'){ 
          if(window.Swal){ 
            const Toast=Swal.mixin({
              toast:true,
              position:'top-end',
              showConfirmButton:false,
              timer:3000,
              timerProgressBar:true
            }); 
            Toast.fire({icon:type,title:msg}); 
          } else { 
          } 
        },
        // Función global para verificar si un producto está en la tabla
        isProductInTable(productId){
          return this.products?.some(p => p.id == productId) || false;
        }
      }
    }
    
    window.searchModal = function searchModal(){
      return { 
        isOpen:false, 
        searchTerm:'', 
        
        init(){ 
          this.$nextTick(()=>{ 
            window.addEventListener('openSearchModal',()=>this.openModal()); 
          }); 
        }, 
        
        openModal(){ 
          this.isOpen=true; 
          this.searchTerm=''; 
          document.body.style.overflow='hidden'; 
        }, 
        
        closeModal(){ 
          this.isOpen=false; 
          document.body.style.overflow='auto'; 
        }, 
        
        filterProductsInModal(){ 
          const term=this.searchTerm.toLowerCase(); 
          document.querySelectorAll('#searchProductModal tbody tr').forEach(row=>{ 
            const code=row.querySelector('td:first-child span')?.textContent.toLowerCase()||''; 
            const name=row.querySelector('td:nth-child(3) .text-sm.font-medium')?.textContent.toLowerCase()||''; 
            const cat=row.querySelector('td:nth-child(4) span')?.textContent.toLowerCase()||''; 
            row.style.display=(code.includes(term)||name.includes(term)||cat.includes(term))?'':'none'; 
          }); 
        }, 
        
        addProductFromModal(id,code,name,imageUrl,stock,price,categoryName){ 
          // Verificar si el producto ya está en la tabla
          if(window.purchaseFormInstance?.isProductInTable(id)){ 
            return; // No hacer nada si ya está agregado
          } 
          
          const product={ 
            id, 
            code, 
            name, 
            image_url:imageUrl, 
            stock, 
            purchase_price:price, 
            category:{name:categoryName}, 
            quantity:1, 
            price, 
            subtotal:price 
          }; 
          window.purchaseFormInstance.addProductToTable(product); 
          this.closeModal(); 
          this.showToast(`${name} se agregó a la lista de compra`,'success'); 
        }, 
        
        showToast(msg,type='success'){ 
          if(window.Swal){ 
            const Toast=Swal.mixin({
              toast:true,
              position:'top-end',
              showConfirmButton:false,
              timer:3000,
              timerProgressBar:true
            }); 
            Toast.fire({icon:type,title:msg}); 
          } else { 
          } 
        } 
      }
    }
  });

  window.addEventListener('beforeunload',(e)=>{ 
    if(window.purchaseFormInstance?.formChanged){ 
      e.preventDefault(); 
      e.returnValue=''; 
    }
  });
  
  // Función global para el botón "Volver"
  window.goBack = function() {
    if(window.purchaseFormInstance?.goBack) {
      window.purchaseFormInstance.goBack();
    } else {
      // Fallback si no hay instancia disponible
      if(document.referrer && !/purchases\/(create|edit)/.test(document.referrer)){
        history.back();
      } else {
        window.location.href='/purchases';
      }
    }
  };
})();


