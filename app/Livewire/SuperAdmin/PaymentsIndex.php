<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Company;
use App\Models\SubscriptionPayment;
use App\Services\PaymentService;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class PaymentsIndex extends Component
{
    use WithFileUploads, WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public string $companyFilter = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public int $perPage = 10;

    public bool $showPaymentModal = false;

    public ?int $selectedPaymentId = null;

    public $receiptFile = null;

    public string $transactionReference = '';

    public string $paymentNotes = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'companyFilter' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    public function mount(): void
    {
        if (! auth()->user() || ! auth()->user()->canAccessPlatformConsole()) {
            abort(403);
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingCompanyFilter(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->companyFilter = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->resetPage();
    }

    protected function toast(string $message, string $type = 'success'): void
    {
        $titles = ['success' => 'Listo', 'error' => 'Atención', 'warning' => 'Atención', 'info' => 'Información'];
        $uiType = in_array($type, ['success', 'error', 'warning', 'info'], true) ? $type : 'info';
        $title = $titles[$uiType] ?? $titles['info'];
        $timeout = $uiType === 'error' ? 7200 : 4800;
        $options = json_encode(['type' => $uiType, 'title' => $title, 'timeout' => $timeout, 'theme' => 'futuristic'], JSON_THROW_ON_ERROR);
        $msg = json_encode($message, JSON_THROW_ON_ERROR);
        $this->js('if (window.uiNotifications && typeof window.uiNotifications.showToast === "function") {'
            .'window.uiNotifications.showToast('.$msg.', '.$options.');}');
    }

    public function openPaymentModal(int $paymentId): void
    {
        $this->selectedPaymentId = $paymentId;
        $this->receiptFile = null;
        $this->transactionReference = '';
        $this->paymentNotes = '';
        $this->showPaymentModal = true;
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
        $this->selectedPaymentId = null;
    }

    public function markAsPaid(): void
    {
        if (! $this->selectedPaymentId) {
            return;
        }

        $this->validate([
            'receiptFile' => 'nullable|image|mimes:jpeg,png,jpg,pdf|max:4096',
            'transactionReference' => 'nullable|string|max:255',
        ]);

        try {
            $payment = SubscriptionPayment::findOrFail($this->selectedPaymentId);
            $paymentService = app(PaymentService::class);
            $paymentService->markAsPaid($payment, (int) auth()->id(), $this->receiptFile);

            $payment->update([
                'transaction_reference' => $this->transactionReference ?: null,
                'notes' => $this->paymentNotes ?: null,
            ]);

            $this->closePaymentModal();
            $this->toast('Pago registrado correctamente.', 'success');
        } catch (\Throwable $e) {
            $this->toast('Error al registrar pago: '.$e->getMessage(), 'error');
        }
    }

    public function cancelPayment(int $paymentId): void
    {
        try {
            $payment = SubscriptionPayment::findOrFail($paymentId);
            app(PaymentService::class)->cancelPayment($payment);
            $this->toast('Pago cancelado.', 'success');
        } catch (\Throwable $e) {
            $this->toast('Error al cancelar: '.$e->getMessage(), 'error');
        }
    }

    public function render(): View
    {
        $query = SubscriptionPayment::with(['company:id,name,nit', 'subscription.plan:id,name'])
            ->orderBy('due_date', 'desc');

        if ($this->search !== '') {
            $s = $this->search;
            $query->whereHas('company', function ($q) use ($s) {
                $q->where('name', 'ILIKE', '%'.$s.'%')
                    ->orWhere('nit', 'ILIKE', '%'.$s.'%');
            });
        }

        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->companyFilter !== '') {
            $query->where('company_id', (int) $this->companyFilter);
        }

        if ($this->dateFrom !== '') {
            $query->whereDate('due_date', '>=', $this->dateFrom);
        }

        if ($this->dateTo !== '') {
            $query->whereDate('due_date', '<=', $this->dateTo);
        }

        $payments = $query->paginate($this->perPage);

        $companies = Company::orderBy('name')->get(['id', 'name']);

        $stats = app(PaymentService::class)->paymentStats();

        return view('livewire.super-admin.payments-index', [
            'payments' => $payments,
            'companies' => $companies,
            'stats' => $stats,
        ]);
    }
}
