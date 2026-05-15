<?php

namespace App\Listeners\Home;

use App\Events\Home\ProductConsumed;
use App\Models\Home\HomeShoppingList;
use Illuminate\Support\Facades\Log;

class CheckLowStockLevels
{
    public function handle(ProductConsumed $event): void
    {
        $product = $event->product->fresh();

        if ($product && $product->quantity < $product->min_quantity) {
            $hasActiveList = HomeShoppingList::where('company_id', $product->company_id)
                ->active()
                ->exists();

            if (!$hasActiveList) {
                Log::channel('home')->info('home.low_stock.detected', [
                    'company_id' => $product->company_id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'current_stock' => $product->quantity,
                    'min_quantity' => $product->min_quantity,
                ]);
            }
        }
    }
}
