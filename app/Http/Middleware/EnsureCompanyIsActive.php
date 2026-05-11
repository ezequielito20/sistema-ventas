<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureCompanyIsActive
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        // El super admin siempre puede acceder
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $companyId = $user->company_id;

        if ($companyId) {
            $company = \App\Models\Company::select('id', 'subscription_status')->find($companyId);

            if ($company && $company->subscription_status === 'suspended') {
                if ($request->routeIs('logout') || $request->routeIs('account.suspended')) {
                    return $next($request);
                }

                return redirect()->route('account.suspended');
            }
        }

        return $next($request);
    }
}
