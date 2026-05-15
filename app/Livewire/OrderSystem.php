<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Product;
use Livewire\Attributes\Rule;
use Livewire\Component;

class OrderSystem extends Component
{
    // Tab management
    public $activeTab = 'orders';

    // Order form fields
    #[Rule('required|string|min:10|max:15')]
    public $phone = '';

    #[Rule('required_if:customer_exists,false|string|max:255')]
    public $customer_name = '';

    #[Rule('required_if:customer_exists,false|string|max:100')]
    public $department = '';

    #[Rule('required|exists:products,id')]
    public $product_id = '';

    #[Rule('required|integer|min:1')]
    public $quantity = 1;

    #[Rule('nullable|string|max:1000')]
    public $notes = '';

    // Customer lookup fields
    #[Rule('required|string|min:10|max:15')]
    public $lookup_phone = '';

    // State variables
    public $customer_exists = false;

    public $existing_customer = null;

    public $products = [];

    public $selected_product = null;

    public $total_price = 0;

    public $customer_debt = null;

    public $lookup_customer = null;

    public $show_success_message = false;

    public $success_message = '';

    public function mount()
    {
        $this->loadProducts();
    }

    public function loadProducts()
    {
        $this->products = Product::where('company_id', 1)
            ->where('stock', '>', 0)
            ->get();
    }

    // Método para normalizar números de teléfono
    private function normalizePhone($phone)
    {
        // Remover todos los caracteres no numéricos
        $normalized = preg_replace('/[^0-9]/', '', $phone);

        // Si empieza con 0, removerlo
        if (strlen($normalized) > 10 && substr($normalized, 0, 1) === '0') {
            $normalized = substr($normalized, 1);
        }

        return $normalized;
    }

    // Método para búsqueda manual de cliente en pedidos
    public function searchCustomer()
    {
        $this->validate([
            'phone' => 'required|string|min:10|max:15',
        ]);

        $this->checkCustomerExists();
    }

    // Método para búsqueda manual de cliente en consulta de deuda
    public function searchCustomerDebt()
    {
        $this->validate([
            'lookup_phone' => 'required|string|min:10|max:15',
        ]);

        $this->lookupCustomerDebt();
    }

    public function updatedProductId()
    {
        if ($this->product_id) {
            $this->selected_product = Product::find($this->product_id);
            $this->calculateTotal();
        } else {
            $this->selected_product = null;
            $this->total_price = 0;
        }
    }

    public function updatedQuantity()
    {
        $this->calculateTotal();
    }

    public function checkCustomerExists()
    {
        $normalizedPhone = $this->normalizePhone($this->phone);

        // Buscar cliente con búsqueda flexible
        $customer = Customer::where('company_id', 1)
            ->get()
            ->filter(function ($customer) use ($normalizedPhone) {
                if (empty($customer->phone)) {
                    return false;
                }
                $customerPhone = $this->normalizePhone($customer->phone);

                return $customerPhone === $normalizedPhone;
            })
            ->first();

        if ($customer) {
            $this->customer_exists = true;
            $this->existing_customer = $customer;
            $this->customer_name = '';
            $this->department = '';
        } else {
            $this->customer_exists = false;
            $this->existing_customer = null;
        }
    }

    public function lookupCustomerDebt()
    {
        $normalizedPhone = $this->normalizePhone($this->lookup_phone);

        // Buscar cliente con búsqueda flexible
        $customer = Customer::where('company_id', 1)
            ->get()
            ->filter(function ($customer) use ($normalizedPhone) {
                if (empty($customer->phone)) {
                    return false;
                }
                $customerPhone = $this->normalizePhone($customer->phone);

                return $customerPhone === $normalizedPhone;
            })
            ->first();

        if ($customer) {
            $this->lookup_customer = $customer;
            $this->customer_debt = $customer->total_debt;
        } else {
            $this->lookup_customer = null;
            $this->customer_debt = null;
        }
    }

    public function calculateTotal()
    {
        if ($this->selected_product && $this->quantity > 0) {
            $this->total_price = $this->selected_product->sale_price * $this->quantity;
        } else {
            $this->total_price = 0;
        }
    }

    public function resetCustomerData()
    {
        $this->customer_exists = false;
        $this->existing_customer = null;
        $this->customer_name = '';
        $this->department = '';
    }

    public function resetLookupData()
    {
        $this->lookup_customer = null;
        $this->customer_debt = null;
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetValidation();
        $this->resetCustomerData();
        $this->resetLookupData();
    }

    public function submitOrder()
    {
        $this->addError('general', 'Este formulario de pedidos ya no está disponible. Usá el catálogo público de la empresa.');

    }

    public function resetOrderForm()
    {
        $this->phone = '';
        $this->customer_name = '';
        $this->department = '';
        $this->product_id = '';
        $this->quantity = 1;
        $this->notes = '';
        $this->customer_exists = false;
        $this->existing_customer = null;
        $this->selected_product = null;
        $this->total_price = 0;
        $this->resetValidation();
    }

    public function closeSuccessMessage()
    {
        $this->show_success_message = false;
        $this->success_message = '';
    }

    public function render()
    {
        return view('livewire.order-system', [
            'products' => $this->products,
        ]);
    }
}
