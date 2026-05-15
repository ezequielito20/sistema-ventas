<?php

namespace App\Livewire;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class OrdersTable extends Component
{
    use WithPagination;

    public string $status = '';

    public string $search = '';

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public int $perPage = 10;

    protected $queryString = [
        'status' => ['except' => ''],
        'search' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 10],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'desc';
        }
    }

    public function render()
    {
        $companyId = (int) Auth::user()->company_id;

        $query = Order::query()
            ->forCompany($companyId)
            ->withCount('items')
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->when($this->search !== '', function ($q) {
                $s = '%'.$this->search.'%';
                $q->where(function ($q2) use ($s) {
                    $q2->where('customer_name', 'like', $s)
                        ->orWhere('customer_phone', 'like', $s);
                    if (is_numeric($this->search)) {
                        $q2->orWhere('id', (int) $this->search);
                    }
                });
            })
            ->orderBy($this->sortField, $this->sortDirection);

        $orders = $query->paginate($this->perPage);

        return view('livewire.orders-table', [
            'orders' => $orders,
        ]);
    }
}
