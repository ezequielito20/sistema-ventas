<?php

namespace App\Livewire;

use App\Livewire\Concerns\MergesValidationErrors;
use App\Models\CashCount;
use App\Services\PlanEntitlementService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class CashCountForm extends Component
{
    use MergesValidationErrors;

    public ?int $cashCountId = null;

    public string $openingDate;

    public string $openingTime;

    public string $initialAmount = '0.00';

    public string $observations = '';

    public string $finalAmount = '0.00';

    public bool $isEdit = false;

    public bool $showCloseModal = false;

    public string $currencySymbol = '$';

    protected function rules(): array
    {
        $base = [
            'openingDate' => 'required|date',
            'openingTime' => 'required|date_format:H:i',
            'initialAmount' => 'required|numeric|min:0',
            'observations' => 'nullable|string|max:1000',
        ];

        if ($this->isEdit) {
            $base['finalAmount'] = 'nullable|numeric|min:0';
        }

        return $base;
    }

    protected function messages(): array
    {
        return [
            'openingDate.required' => 'La fecha de apertura es obligatoria',
            'openingTime.required' => 'La hora de apertura es obligatoria',
            'initialAmount.required' => 'El monto inicial es obligatorio',
            'initialAmount.numeric' => 'El monto inicial debe ser un número',
            'initialAmount.min' => 'El monto inicial no puede ser negativo',
            'finalAmount.numeric' => 'El monto final debe ser un número',
            'finalAmount.min' => 'El monto final no puede ser negativo',
            'observations.max' => 'Las observaciones no pueden exceder los 1000 caracteres',
        ];
    }

    public function mount(?int $cashCountId = null): void
    {
        if ($cashCountId) {
            Gate::authorize('cash-counts.edit');
            $this->loadCashCount($cashCountId);

            return;
        }

        Gate::authorize('cash-counts.create');
        $this->openingDate = now()->format('Y-m-d');
        $this->openingTime = now()->format('H:i');
        $this->loadCurrency();
    }

    protected function loadCashCount(int $id): void
    {
        $cashCount = CashCount::where('company_id', Auth::user()->company_id)
            ->findOrFail($id);

        $this->cashCountId = $id;
        $this->isEdit = true;
        $this->openingDate = Carbon::parse($cashCount->opening_date)->format('Y-m-d');
        $this->openingTime = Carbon::parse($cashCount->opening_date)->format('H:i');
        $this->initialAmount = number_format((float) $cashCount->initial_amount, 2, '.', '');
        $this->observations = $cashCount->observations ?? '';
        $this->finalAmount = $cashCount->final_amount !== null
            ? number_format((float) $cashCount->final_amount, 2, '.', '')
            : '0.00';

        $this->loadCurrency();
    }

    protected function loadCurrency(): void
    {
        $company = DB::table('companies')
            ->select('currency', 'country')
            ->where('id', Auth::user()->company_id)
            ->first();

        $currency = null;
        if ($company && $company->currency) {
            $currency = DB::table('currencies')
                ->where('code', $company->currency)
                ->first();
        }
        if (! $currency && $company && $company->country) {
            $currency = DB::table('currencies')
                ->where('country_id', $company->country)
                ->first();
        }

        $this->currencySymbol = $currency->symbol ?? '$';
    }

    public function save(): void
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $companyId = Auth::user()->company_id;
            $openingDateTime = "{$this->openingDate} {$this->openingTime}:00";

            if ($this->isEdit) {
                $cashCount = CashCount::where('company_id', $companyId)
                    ->findOrFail($this->cashCountId);

                $cashCount->update([
                    'opening_date' => $openingDateTime,
                    'initial_amount' => $this->initialAmount,
                    'observations' => $this->observations,
                ]);

                $msg = 'Caja actualizada correctamente.';
            } else {
                $existing = CashCount::where('company_id', $companyId)
                    ->whereNull('closing_date')
                    ->exists();

                if ($existing) {
                    throw new \RuntimeException('Ya existe una caja abierta. Debe cerrarla antes de abrir una nueva.');
                }

                app(PlanEntitlementService::class)->assertCanCreate(Auth::user(), 'cash_counts');

                CashCount::create([
                    'company_id' => $companyId,
                    'opening_date' => $openingDateTime,
                    'initial_amount' => $this->initialAmount,
                    'observations' => $this->observations,
                ]);

                $msg = 'Caja abierta correctamente con un monto inicial de '.$this->currencySymbol.number_format((float) $this->initialAmount, 2);
            }

            DB::commit();

            $this->toast($msg);
            $this->redirect(route('admin.cash-counts.index'), navigate: true);
        } catch (ValidationException $e) {
            DB::rollBack();
            $this->mergeValidationErrors($e);
            $msg = collect($e->errors())->flatten()->first() ?? 'No se pudo completar la acción.';
            $this->toast($msg, 'error');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->toast('Error: '.$e->getMessage(), 'error');
        }
    }

    public function openCloseModal(): void
    {
        $this->validate([
            'finalAmount' => 'required|numeric|min:0',
        ], [
            'finalAmount.required' => 'El monto final es obligatorio para cerrar la caja',
            'finalAmount.numeric' => 'El monto final debe ser un número',
            'finalAmount.min' => 'El monto final no puede ser negativo',
        ]);

        $this->showCloseModal = true;
    }

    public function confirmClose(): void
    {
        $this->showCloseModal = false;

        try {
            DB::beginTransaction();

            $cashCount = CashCount::where('company_id', Auth::user()->company_id)
                ->whereNull('closing_date')
                ->findOrFail($this->cashCountId);

            $cashCount->update([
                'closing_date' => now(),
                'final_amount' => $this->finalAmount,
                'observations' => $this->observations,
            ]);

            DB::commit();

            $this->toast('Caja cerrada correctamente. Monto final: '.$this->currencySymbol.number_format((float) $this->finalAmount, 2));
            $this->redirect(route('admin.cash-counts.index'), navigate: true);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->toast('Error al cerrar la caja: '.$e->getMessage(), 'error');
        }
    }

    protected function toast(string $message, string $type = 'success'): void
    {
        $titles = [
            'success' => 'Listo',
            'error' => 'Atención',
            'warning' => 'Atención',
            'info' => 'Información',
        ];
        $uiType = in_array($type, ['success', 'error', 'warning', 'info'], true) ? $type : 'info';
        $title = $titles[$uiType] ?? $titles['info'];
        $timeout = $uiType === 'error' ? 7200 : 4800;

        $options = json_encode([
            'type' => $uiType,
            'title' => $title,
            'timeout' => $timeout,
            'theme' => 'futuristic',
        ], JSON_THROW_ON_ERROR);

        $msg = json_encode($message, JSON_THROW_ON_ERROR);

        $this->js(
            'if (window.uiNotifications && typeof window.uiNotifications.showToast === "function") {'
            .'window.uiNotifications.showToast('.$msg.', '.$options.');}'
        );
    }

    public function render()
    {
        $isEdit = $this->isEdit;
        $title = $isEdit ? 'Editar Caja' : 'Abrir Caja';

        return view('livewire.cash-count-form', [
            'isEdit' => $isEdit,
            'title' => $title,
        ]);
    }
}
