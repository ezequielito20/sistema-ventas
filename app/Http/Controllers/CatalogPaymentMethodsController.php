<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyPaymentMethod;
use App\Services\PlanEntitlementService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CatalogPaymentMethodsController extends Controller
{
    public function create()
    {
        $user = Auth::user();
        abort_unless(
            app(PlanEntitlementService::class)->tenantUserMayUseCatalogPaymentsAbility($user, 'create'),
            403
        );

        return view('admin.catalog-payment-methods.create');
    }

    public function edit(int $id)
    {
        $user = Auth::user();
        abort_unless(
            app(PlanEntitlementService::class)->tenantUserMayUseCatalogPaymentsAbility($user, 'edit'),
            403
        );

        $exists = CompanyPaymentMethod::query()
            ->where('company_id', (int) $user->company_id)
            ->whereKey($id)
            ->exists();

        abort_unless($exists, 404);

        return view('admin.catalog-payment-methods.edit', ['id' => $id]);
    }

    public function report(Request $request)
    {
        $user = Auth::user();
        abort_unless(
            app(PlanEntitlementService::class)->tenantUserMayUseCatalogPaymentsAbility($user, 'report'),
            403
        );

        $company = Company::query()->findOrFail((int) $user->company_id);

        $paymentMethods = CompanyPaymentMethod::query()
            ->where('company_id', $company->id)
            ->withCount('orders')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $emittedAt = now();
        $filename = 'informe-metodos-pago-catalogo-'.$emittedAt->format('Y-m-d_His').'.pdf';

        $pdf = Pdf::loadView('pdf.catalog-payment-methods.report', compact('paymentMethods', 'company', 'emittedAt'))
            ->setPaper('letter', 'portrait')
            ->setOption('enable_php', true)
            ->addInfo([
                'Title' => 'Informe de métodos de pago del catálogo',
                'Author' => $company->name ?? config('app.name'),
            ]);

        if ($request->boolean('download')) {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }
}
