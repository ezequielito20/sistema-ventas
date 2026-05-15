<?php

namespace App\Support;

use App\Models\Company;
use Illuminate\Http\Request;

final class CatalogAccess
{
    public static function sessionKey(int $companyId): string
    {
        return 'catalog_private_unlock_'.$companyId;
    }

    public static function assert(Request $request, Company $company): void
    {
        if ($company->catalog_is_public) {
            return;
        }

        if ($request->hasValidSignature()) {
            $days = (int) config('catalog.private_catalog_signed_link_days', 7);
            session()->put(self::sessionKey($company->id), now()->addDays($days)->timestamp);

            return;
        }

        $until = (int) session(self::sessionKey($company->id), 0);
        if ($until > now()->timestamp) {
            return;
        }

        abort(404);
    }
}
