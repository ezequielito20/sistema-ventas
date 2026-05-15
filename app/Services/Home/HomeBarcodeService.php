<?php

namespace App\Services\Home;

use App\Contracts\DeductResult;
use App\Models\Home\HomeProduct;

class HomeBarcodeService
{
    public function resolveProduct(string $barcode, int $companyId): ?HomeProduct
    {
        return HomeProduct::where('company_id', $companyId)
            ->where('barcode', $barcode)
            ->first();
    }

    public function deductByBarcode(string $barcode, int $companyId, int $quantity = 1): DeductResult
    {
        $product = $this->resolveProduct($barcode, $companyId);

        if (!$product) {
            return DeductResult::notFound("No se encontró producto con el código \"{$barcode}\".");
        }

        return app(HomeInventoryService::class)->deduct(
            product: $product,
            quantity: $quantity,
            type: 'barcode_deduct',
            metadata: ['barcode' => $barcode],
        );
    }
}
