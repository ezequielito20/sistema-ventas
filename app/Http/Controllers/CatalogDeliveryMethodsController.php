<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyDeliveryMethod;
use App\Models\DeliverySlot;
use App\Services\PlanEntitlementService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CatalogDeliveryMethodsController extends Controller
{
    public function create()
    {
        $user = Auth::user();
        abort_unless(
            app(PlanEntitlementService::class)->tenantUserMayUseCatalogDeliveriesAbility($user, 'create'),
            403
        );

        return view('admin.catalog-delivery-methods.create');
    }

    public function edit(int $id)
    {
        $user = Auth::user();
        abort_unless(
            app(PlanEntitlementService::class)->tenantUserMayUseCatalogDeliveriesAbility($user, 'edit'),
            403
        );

        $exists = CompanyDeliveryMethod::query()
            ->where('company_id', (int) $user->company_id)
            ->whereKey($id)
            ->exists();

        abort_unless($exists, 404);

        return view('admin.catalog-delivery-methods.edit', ['id' => $id]);
    }

    public function report(Request $request)
    {
        $user = Auth::user();
        abort_unless(
            app(PlanEntitlementService::class)->tenantUserMayUseCatalogDeliveriesAbility($user, 'report'),
            403
        );

        $company = Company::query()->findOrFail((int) $user->company_id);

        $deliveryMethods = CompanyDeliveryMethod::query()
            ->where('company_id', $company->id)
            ->with(['zones' => fn ($q) => $q->orderBy('name')])
            ->withCount(['orders', 'zones', 'deliverySlots'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $deliverySlots = DeliverySlot::query()
            ->where('company_id', $company->id)
            ->with(['deliveryMethod', 'zone'])
            ->orderBy('weekday_iso')
            ->orderBy('delivery_time')
            ->get();

        $emittedAt = now();
        $filename = 'informe-metodos-entrega-catalogo-'.$emittedAt->format('Y-m-d_His').'.pdf';

        $pdf = Pdf::loadView('pdf.catalog-delivery-methods.report', compact('deliveryMethods', 'deliverySlots', 'company', 'emittedAt'))
            ->setPaper('letter', 'portrait')
            ->setOption('enable_php', true)
            ->addInfo([
                'Title' => 'Informe de métodos de entrega del catálogo',
                'Author' => $company->name ?? config('app.name'),
            ]);

        if ($request->boolean('download')) {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }
}
