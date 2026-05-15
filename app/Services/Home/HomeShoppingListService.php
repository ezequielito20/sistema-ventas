<?php

namespace App\Services\Home;

use App\Events\Home\ShoppingListCompleted;
use App\Models\Home\HomeProduct;
use App\Models\Home\HomeShoppingList;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HomeShoppingListService
{
    public function generate(int $companyId, bool $force = false): HomeShoppingList
    {
        $activeList = HomeShoppingList::where('company_id', $companyId)
            ->active()
            ->first();

        if ($activeList && !$force) {
            return $activeList;
        }

        return DB::transaction(function () use ($companyId, $activeList, $force) {
            $products = HomeProduct::where('company_id', $companyId)
                ->toBuy()
                ->get();

            $list = HomeShoppingList::create([
                'company_id' => $companyId,
            ]);

            foreach ($products as $product) {
                $existingQuantity = 0;

                if ($activeList && $force) {
                    $existingItem = $activeList->items()
                        ->where('home_product_id', $product->id)
                        ->first();

                    if ($existingItem) {
                        $existingQuantity = $existingItem->suggested_quantity;
                    }
                }

                $list->items()->create([
                    'home_product_id' => $product->id,
                    'name_snapshot' => $product->name,
                    'suggested_quantity' => $existingQuantity + $product->to_buy,
                ]);
            }

            Log::channel('home')->info('home.shopping_list.generated', [
                'company_id' => $companyId,
                'list_id' => $list->id,
                'items_count' => $products->count(),
            ]);

            return $list;
        });
    }

    public function complete(HomeShoppingList $list): void
    {
        DB::transaction(function () use ($list) {
            foreach ($list->items as $item) {
                if (!$item->is_purchased || !$item->actual_purchased_quantity || !$item->product) {
                    continue;
                }

                $item->product->increment('quantity', $item->actual_purchased_quantity);

                $item->product->movements()->create([
                    'company_id' => $item->product->company_id,
                    'user_id' => Auth::id(),
                    'type' => 'in',
                    'quantity' => $item->actual_purchased_quantity,
                    'notes' => "Compra de mercado - lista #{$list->id}",
                ]);
            }

            $list->update(['is_completed' => true]);
        });

        ShoppingListCompleted::dispatch($list);

        Log::channel('home')->info('home.shopping_list.completed', [
            'company_id' => $list->company_id,
            'list_id' => $list->id,
        ]);
    }

    public function hasActiveList(int $companyId): bool
    {
        return HomeShoppingList::where('company_id', $companyId)
            ->active()
            ->exists();
    }
}
