<?php

namespace App\Livewire\Home;

use App\Models\Home\HomeService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class HomeServicesIndex extends Component
{
    public string $search = '';

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $provider = '';

    public string $contract_number = '';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'provider' => 'nullable|string|max:255',
            'contract_number' => 'nullable|string|max:255',
        ];
    }

    public function mount(): void
    {
        Gate::authorize('home.finances.services');
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $service = HomeService::where('company_id', Auth::user()->company_id)->findOrFail($id);
        $this->editingId = $service->id;
        $this->name = $service->name;
        $this->provider = $service->provider ?? '';
        $this->contract_number = $service->contract_number ?? '';
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        $companyId = (int) Auth::user()->company_id;

        if ($this->editingId) {
            $service = HomeService::where('company_id', $companyId)->findOrFail($this->editingId);
            $service->update($data);
            $this->dispatch('service-saved', message: 'Servicio actualizado.');
        } else {
            $data['company_id'] = $companyId;
            HomeService::create($data);
            $this->dispatch('service-saved', message: 'Servicio creado.');
        }

        $this->closeForm();
    }

    public function delete(int $id): void
    {
        HomeService::where('company_id', Auth::user()->company_id)
            ->findOrFail($id)
            ->delete();

        $this->dispatch('service-deleted', message: 'Servicio eliminado.');
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->provider = '';
        $this->contract_number = '';
    }

    public function render(): View
    {
        $services = HomeService::where('company_id', Auth::user()->company_id)
            ->when($this->search !== '', fn ($q) => $q->where('name', 'ILIKE', "%{$this->search}%"))
            ->orderBy('name')
            ->get();

        return view('livewire.home-services-index', [
            'services' => $services,
        ]);
    }
}
