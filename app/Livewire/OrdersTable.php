<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Product;
use App\Models\Notification;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrdersTable extends Component
{
    use WithPagination;

    public $status = '';
    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $selectedOrder = null;
    public $showProcessModal = false;
    public $saleDate = '';

    protected $queryString = [
        'status' => ['except' => ''],
        'search' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function mount()
    {
        $this->status = request('status', '');
        $this->saleDate = now()->format('Y-m-d\TH:i');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function openProcessModal($orderId)
    {
        $this->selectedOrder = Order::findOrFail($orderId);
        $this->showProcessModal = true;
    }

    public function closeProcessModal()
    {
        $this->selectedOrder = null;
        $this->showProcessModal = false;
    }

    public function processOrder()
    {
        if (!$this->selectedOrder) {
            return;
        }

        if ($this->selectedOrder->status !== 'pending') {
            $this->dispatch('showNotification', [
                'type' => 'error',
                'message' => 'Este pedido ya fue procesado o cancelado.'
            ]);
            $this->closeProcessModal();
            return;
        }

        DB::beginTransaction();
        
        try {
            // Create or get customer
            $customer = $this->selectedOrder->customer;
            
            if (!$customer) {
                // Create new customer
                $customer = Customer::create([
                    'name' => $this->selectedOrder->customer_name,
                    'phone' => $this->selectedOrder->customer_phone,
                    'company_id' => 1,
                    'total_debt' => 0,
                ]);
            }

            // Create sale
            $sale = Sale::create([
                'sale_date' => $this->saleDate ?: now(),
                'total_price' => $this->selectedOrder->total_price,
                'company_id' => 1,
                'customer_id' => $customer->id,
                'note' => $this->selectedOrder->notes,
            ]);

            // Create sale detail and update product stock
            SaleDetail::create([
                'sale_id' => $sale->id,
                'product_id' => $this->selectedOrder->product_id,
                'quantity' => $this->selectedOrder->quantity,
                'unit_price' => $this->selectedOrder->unit_price,
                'subtotal' => $this->selectedOrder->total_price,
            ]);

            // Update product stock
            $product = Product::find($this->selectedOrder->product_id);
            if ($product) {
                $product->stock -= $this->selectedOrder->quantity;
                $product->save();
            }

            // Update order
            $this->selectedOrder->update([
                'status' => 'processed',
                'customer_id' => $customer->id,
                'sale_id' => $sale->id,
                'processed_at' => now(),
                'processed_by' => Auth::id(),
            ]);

            // Update customer debt
            $customer->total_debt += $this->selectedOrder->total_price;
            $customer->save();

            // Delete notifications for this order
            Notification::where('type', 'new_order')
                ->whereJsonContains('data->order_id', $this->selectedOrder->id)
                ->delete();

            DB::commit();
            
            $this->closeProcessModal();
            $this->dispatch('showNotification', [
                'type' => 'success',
                'message' => 'Pedido procesado exitosamente'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('showNotification', [
                'type' => 'error',
                'message' => 'Error al procesar el pedido: ' . $e->getMessage()
            ]);
        }
    }

    public function cancelOrder($orderId)
    {
        $order = Order::findOrFail($orderId);
        $order->update(['status' => 'cancelled']);
        
        $this->dispatch('showNotification', [
            'type' => 'success',
            'message' => 'Pedido cancelado exitosamente'
        ]);
    }

    public function getStatusBadgeClass($status)
    {
        return match($status) {
            'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            'processed' => 'bg-green-100 text-green-800 border-green-200',
            'cancelled' => 'bg-red-100 text-red-800 border-red-200',
            default => 'bg-gray-100 text-gray-800 border-gray-200'
        };
    }

    public function getStatusIcon($status)
    {
        return match($status) {
            'pending' => 'fas fa-clock',
            'processed' => 'fas fa-check',
            'cancelled' => 'fas fa-times',
            default => 'fas fa-question'
        };
    }

    public function render()
    {
        $query = Order::with(['product', 'customer'])
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('customer_name', 'like', '%' . $this->search . '%')
                      ->orWhere('customer_phone', 'like', '%' . $this->search . '%')
                      ->orWhereHas('product', function ($pq) {
                          $pq->where('name', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->orderBy($this->sortField, $this->sortDirection);

        $orders = $query->paginate(15);

        return view('livewire.orders-table', [
            'orders' => $orders
        ]);
    }
}
