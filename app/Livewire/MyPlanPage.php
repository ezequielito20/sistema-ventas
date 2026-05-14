<?php

namespace App\Livewire;

use App\Services\PlanEntitlementService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class MyPlanPage extends Component
{
    public function mount(): void
    {
        Gate::authorize('my-plan.view');
    }

    public function render(): View
    {
        $user = Auth::user();
        abort_unless($user && $user->company, 403);

        $overview = app(PlanEntitlementService::class)->tenantPlanOverviewForCompany($user->company);

        return view('livewire.my-plan-page', [
            'overview' => $overview,
        ]);
    }
}
