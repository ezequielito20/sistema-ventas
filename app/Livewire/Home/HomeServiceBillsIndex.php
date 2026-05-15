<?php

namespace App\Livewire\Home;

use App\Models\Home\HomeService;
use App\Models\Home\HomeServiceBill;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class HomeServiceBillsIndex extends Component
{
    use WithFileUploads, WithPagination;

    public string $service_id = '';

    public string $status = '';

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $period = '';

    public float $amount = 0;

    public string $due_date = '';

    public string $cutoff_date = '';

    public string $notes = '';

    public $bill_image = null;

    protected function rules(): array
    {
        return [
            'home_service_id' => 'required|exists:home_services,id',
            'period' => 'required|string|regex:/^\d{4}-\d{2}$/',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'cutoff_date' => 'nullable|date',
            'bill_image' => 'nullable|image|max:5120',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public string $home_service_id = '';

    public function mount(): void
    {
        Gate::authorize('home.finances.bills');
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function markAsPaid(int $id): void
    {
        HomeServiceBill::findOrFail($id)->update(['paid_at' => now()]);
        $this->dispatch('bill-updated', message: 'Factura marcada como pagada.');
    }

    public function delete(int $id): void
    {
        HomeServiceBill::findOrFail($id)->delete();
        $this->dispatch('bill-deleted', message: 'Factura eliminada.');
    }

    public function save(): void
    {
        $data = $this->validate();
        $companyId = (int) Auth::user()->company_id;

        $service = HomeService::where('company_id', $companyId)
            ->findOrFail($this->home_service_id);

        if ($this->bill_image) {
            $data['bill_image_path'] = $this->bill_image->store(
                "home/bills/{$companyId}/{$service->id}",
                'public',
            );
        }
        unset($data['bill_image']);

        if ($this->editingId) {
            $bill = HomeServiceBill::findOrFail($this->editingId);
            $bill->update($data);
            $this->dispatch('bill-saved', message: 'Factura actualizada.');
        } else {
            $data['home_service_id'] = $service->id;
            HomeServiceBill::create($data);
            $this->dispatch('bill-saved', message: 'Factura creada.');
        }

        $this->closeForm();
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->home_service_id = '';
        $this->period = '';
        $this->amount = 0;
        $this->due_date = '';
        $this->cutoff_date = '';
        $this->notes = '';
        $this->bill_image = null;
    }

    public function render(): View
    {
        $companyId = (int) Auth::user()->company_id;

        $query = HomeServiceBill::whereHas('service', fn ($q) => $q->where('company_id', $companyId))
            ->with('service:id,name');

        if ($this->service_id !== '') {
            $query->where('home_service_id', $this->service_id);
        }

        if ($this->status === 'paid') {
            $query->whereNotNull('paid_at');
        } elseif ($this->status === 'unpaid') {
            $query->whereNull('paid_at');
        } elseif ($this->status === 'due_soon') {
            $query->dueSoon(7);
        }

        $bills = $query->orderByDesc('due_date')->paginate(15);

        $services = HomeService::where('company_id', $companyId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.home-service-bills-index', [
            'bills' => $bills,
            'services' => $services,
            'filtersOpen' => $this->service_id !== '' || $this->status !== '',
        ]);
    }
}
