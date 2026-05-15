<?php

namespace App\Livewire\Home;

use App\Models\Home\HomeBankAccount;
use App\Models\Home\HomeTransaction;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class HomeTransactionsIndex extends Component
{
    use WithFileUploads, WithPagination;

    public string $type = '';

    public string $category = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public string $search = '';

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $form_type = 'expense';

    public string $form_category = 'alimentos';

    public float $form_amount = 0;

    public string $form_description = '';

    public string $form_date = '';

    public ?int $form_bank_account_id = null;

    public $form_receipt = null;

    protected function rules(): array
    {
        return [
            'form_type' => 'required|in:income,expense',
            'form_category' => 'required|string|max:100',
            'form_amount' => 'required|numeric|min:0.01',
            'form_description' => 'nullable|string|max:500',
            'form_date' => 'required|date',
            'form_bank_account_id' => 'nullable|exists:home_bank_accounts,id',
            'form_receipt' => 'nullable|image|max:5120',
        ];
    }

    public function mount(): void
    {
        Gate::authorize('home.finances.transactions');
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->form_date = now()->format('Y-m-d');
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        $companyId = (int) Auth::user()->company_id;

        $payload = [
            'company_id' => $companyId,
            'type' => $data['form_type'],
            'category' => $data['form_category'],
            'amount' => $data['form_amount'],
            'description' => $data['form_description'],
            'transaction_date' => $data['form_date'],
            'home_bank_account_id' => $data['form_bank_account_id'],
        ];

        if ($this->form_receipt) {
            $payload['receipt_image_path'] = $this->form_receipt->store(
                "home/receipts/{$companyId}",
                'public',
            );
        }

        if ($this->editingId) {
            $txn = HomeTransaction::where('company_id', $companyId)->findOrFail($this->editingId);
            $txn->update($payload);
            $this->dispatch('transaction-saved', message: 'Transacción actualizada.');
        } else {
            HomeTransaction::create($payload);
            $this->dispatch('transaction-saved', message: 'Transacción creada.');
        }

        $this->closeForm();
    }

    public function edit(int $id): void
    {
        $txn = HomeTransaction::where('company_id', Auth::user()->company_id)->findOrFail($id);
        $this->editingId = $txn->id;
        $this->form_type = $txn->type;
        $this->form_category = $txn->category;
        $this->form_amount = (float) $txn->amount;
        $this->form_description = $txn->description ?? '';
        $this->form_date = $txn->transaction_date->format('Y-m-d');
        $this->form_bank_account_id = $txn->home_bank_account_id;
        $this->showForm = true;
    }

    public function delete(int $id): void
    {
        HomeTransaction::where('company_id', Auth::user()->company_id)
            ->findOrFail($id)
            ->delete();
        $this->dispatch('transaction-deleted', message: 'Transacción eliminada.');
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->form_type = 'expense';
        $this->form_category = 'alimentos';
        $this->form_amount = 0;
        $this->form_description = '';
        $this->form_date = '';
        $this->form_bank_account_id = null;
        $this->form_receipt = null;
    }

    public function render(): View
    {
        $companyId = (int) Auth::user()->company_id;

        $query = HomeTransaction::where('company_id', $companyId);

        if ($this->type !== '') {
            $query->where('type', $this->type);
        }

        if ($this->category !== '') {
            $query->where('category', $this->category);
        }

        if ($this->dateFrom !== '') {
            $query->whereDate('transaction_date', '>=', $this->dateFrom);
        }

        if ($this->dateTo !== '') {
            $query->whereDate('transaction_date', '<=', $this->dateTo);
        }

        if ($this->search !== '') {
            $query->where('description', 'ILIKE', "%{$this->search}%");
        }

        $transactions = $query->with('bankAccount:id,bank_name')
            ->orderByDesc('transaction_date')
            ->paginate(15);

        $bankAccounts = HomeBankAccount::where('company_id', $companyId)
            ->get(['id', 'bank_name', 'account_number_encrypted', 'account_type']);

        $categories = [
            'alimentos' => 'Alimentos',
            'servicios' => 'Servicios',
            'transporte' => 'Transporte',
            'salud' => 'Salud',
            'entretenimiento' => 'Entretenimiento',
            'educacion' => 'Educación',
            'vivienda' => 'Vivienda',
            'ropa' => 'Ropa',
            'otros' => 'Otros',
        ];

        return view('livewire.home-transactions-index', [
            'transactions' => $transactions,
            'bankAccounts' => $bankAccounts,
            'categories' => $categories,
            'filtersOpen' => $this->type !== '' || $this->category !== '' || $this->dateFrom !== '' || $this->dateTo !== '' || $this->search !== '',
        ]);
    }
}
