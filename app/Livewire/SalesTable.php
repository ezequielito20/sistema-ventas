<?php

namespace App\Livewire;

use App\Models\Sale;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

class SalesTable extends Component
{
    use WithPagination;

    #[Url]
    public $search = '';

    #[Url]
    public $sortBy = 'created_at';

    #[Url]
    public $sortDirection = 'desc';

    public $selectedRows = [];
    public $selectAll = false;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updatedSelectAll()
    {
        if ($this->selectAll) {
            $this->selectedRows = $this->getSales()->pluck('id')->map(fn($id) => (string) $id);
        } else {
            $this->selectedRows = [];
        }
    }

    public function updatedSelectedRows()
    {
        $this->selectAll = false;
    }

    public function deleteSelected()
    {
        if (empty($this->selectedRows)) {
            return;
        }

        Sale::whereIn('id', $this->selectedRows)->delete();
        $this->selectedRows = [];
        $this->selectAll = false;
        
        session()->flash('message', 'Ventas eliminadas correctamente');
    }

    public function delete($id)
    {
        $sale = Sale::findOrFail($id);
        $sale->delete();
        
        session()->flash('message', 'Venta eliminada correctamente');
    }

    private function getSales()
    {
        return Sale::query()
            ->with('customer')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('id', 'like', '%' . $this->search . '%')
                      ->orWhere('total', 'like', '%' . $this->search . '%')
                      ->orWhere('created_at', 'like', '%' . $this->search . '%')
                      ->orWhereHas('customer', function ($customerQuery) {
                          $customerQuery->where('name', 'like', '%' . $this->search . '%')
                                       ->orWhere('email', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->orderBy($this->sortBy, $this->sortDirection);
    }

    public function render()
    {
        $sales = $this->getSales()->paginate(10);
        
        return view('livewire.sales-table', [
            'sales' => $sales
        ]);
    }
}
