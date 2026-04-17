<?php

namespace App\Livewire;

use App\Services\CustomerListingService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class CustomersIndex extends Component
{
    use WithPagination;

    public string $search = '';

    /** '' | defaulters | current_debt */
    public string $filter = '';

    public int $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'filter' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    public function mount(): void
    {
        Gate::authorize('customers.index');
    }

    public function updated($name, $value = null): void
    {
        if (in_array($name, ['search', 'filter', 'perPage'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->filter = '';
        $this->resetPage();
    }

    /**
     * Filtro por segmento (misma lógica que el índice legacy: todos / arqueo actual / morosos).
     */
    public function setFilter(string $value): void
    {
        if (! in_array($value, ['', 'defaulters', 'current_debt'], true)) {
            return;
        }

        $this->filter = $value;
        $this->resetPage();
    }

    /**
     * @return object{id: int, name: string, code: string, symbol: string, country_id: int|null}
     */
    protected function resolveCurrency(): object
    {
        $company = Auth::user()->company;

        if ($company && $company->currency) {
            $row = DB::table('currencies')
                ->select('id', 'name', 'code', 'symbol', 'country_id')
                ->where('code', $company->currency)
                ->first();
            if ($row) {
                return $row;
            }
        }

        $fallback = DB::table('currencies')
            ->select('id', 'name', 'code', 'symbol', 'country_id')
            ->where('country_id', $company->country)
            ->first();

        return $fallback ?? (object) [
            'id' => 0,
            'name' => '',
            'code' => '',
            'symbol' => '$',
            'country_id' => null,
        ];
    }

    public function render(CustomerListingService $listingService): View
    {
        $companyId = (int) Auth::user()->company_id;
        $currency = $this->resolveCurrency();

        $filterParam = $this->filter !== '' ? $this->filter : null;
        $searchParam = $this->search !== '' ? $this->search : null;

        $payload = $listingService->getIndexPayload(
            $companyId,
            $currency,
            $searchParam,
            $filterParam,
            max(1, $this->perPage),
            $this->getPage(),
        );

        $permissions = [
            'can_report' => Gate::allows('customers.report'),
            'can_create' => Gate::allows('customers.create'),
            'can_edit' => Gate::allows('customers.edit'),
            'can_show' => Gate::allows('customers.show'),
            'can_destroy' => Gate::allows('customers.destroy'),
            'can_create_sales' => Gate::allows('sales.create'),
        ];

        $filtersOpen = $searchParam !== null || $filterParam !== null;

        return view('livewire.customers-index', array_merge($payload, [
            'permissions' => $permissions,
            'filtersOpen' => $filtersOpen,
        ]));
    }
}
