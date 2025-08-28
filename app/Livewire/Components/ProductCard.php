<?php

namespace App\Livewire\Components;

use App\Models\Product;
use Livewire\Component;
use Livewire\Attributes\On;

class ProductCard extends Component
{
    public Product $product;
    public $quantity = 0;
    public $isInCart = false;
    public $showStockWarning = false;
    public $isLoading = false;

    // Propiedades para animaciones
    public $isHovered = false;
    public $isAdding = false;

    protected $listeners = [
        'cartUpdated' => 'checkCartStatus',
        'stockUpdated' => 'updateStockInfo'
    ];

    public function mount(Product $product)
    {
        $this->product = $product;
        $this->checkCartStatus();
    }

    public function checkCartStatus()
    {
        // Verificar si el producto está en el carrito
        $cart = session('cart', []);
        $this->isInCart = isset($cart[$this->product->id]);
        $this->quantity = $cart[$this->product->id]['quantity'] ?? 0;
    }

    public function updateStockInfo()
    {
        $this->product->refresh();
        $this->showStockWarning = $this->product->stock <= 5 && $this->product->stock > 0;
    }

    public function incrementQuantity()
    {
        if ($this->product->stock > $this->quantity) {
            $this->quantity++;
            $this->addToCart();
        }
    }

    public function decrementQuantity()
    {
        if ($this->quantity > 0) {
            $this->quantity--;
            $this->updateCart();
        }
    }

    public function addToCart()
    {
        $this->isLoading = true;
        
        try {
            $cart = session('cart', []);
            
            if ($this->quantity > 0) {
                $cart[$this->product->id] = [
                    'id' => $this->product->id,
                    'name' => $this->product->name,
                    'price' => $this->product->sale_price,
                    'quantity' => $this->quantity,
                    'image' => $this->product->image_url,
                    'stock' => $this->product->stock,
                    'category' => $this->product->category->name ?? 'Sin categoría'
                ];
                $this->isInCart = true;
            } else {
                unset($cart[$this->product->id]);
                $this->isInCart = false;
            }
            
            session(['cart' => $cart]);
            
            // Emitir evento para actualizar otros componentes
            $this->dispatch('cartUpdated', [
                'productId' => $this->product->id,
                'quantity' => $this->quantity,
                'totalItems' => array_sum(array_column($cart, 'quantity'))
            ]);
            
            // Animación de éxito
            $this->isAdding = true;
            $this->dispatch('productAdded', [
                'productId' => $this->product->id,
                'message' => $this->quantity > 0 ? 'Producto agregado al carrito' : 'Producto removido del carrito'
            ]);
            
        } catch (\Exception $e) {
            $this->dispatch('show-notification', [
                'type' => 'error',
                'message' => 'Error al actualizar el carrito'
            ]);
        } finally {
            $this->isLoading = false;
            $this->isAdding = false;
        }
    }

    public function updateCart()
    {
        $this->addToCart();
    }

    public function getStockStatusClass()
    {
        if ($this->product->stock <= 0) {
            return 'text-red-500 bg-red-50 border-red-200';
        } elseif ($this->product->stock <= 5) {
            return 'text-orange-500 bg-orange-50 border-orange-200';
        } else {
            return 'text-green-500 bg-green-50 border-green-200';
        }
    }

    public function getStockStatusText()
    {
        if ($this->product->stock <= 0) {
            return 'Sin stock';
        } elseif ($this->product->stock <= 5) {
            return 'Stock bajo';
        } else {
            return 'Disponible';
        }
    }

    public function getStockStatusIcon()
    {
        if ($this->product->stock <= 0) {
            return 'fas fa-times-circle';
        } elseif ($this->product->stock <= 5) {
            return 'fas fa-exclamation-triangle';
        } else {
            return 'fas fa-check-circle';
        }
    }

    public function getFormattedPrice()
    {
        return '$' . number_format($this->product->sale_price, 2);
    }

    public function getFormattedStock()
    {
        return number_format($this->product->stock);
    }

    public function render()
    {
        return view('livewire.components.product-card');
    }
}
