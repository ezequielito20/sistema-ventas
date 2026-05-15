<?php

namespace App\Listeners\Home;

use App\Events\Home\ShoppingListCompleted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoUpdateInventoryFromList
{
    public function handle(ShoppingListCompleted $event): void
    {
        $list = $event->shoppingList;

        DB::transaction(function () use ($list) {
            foreach ($list->items as $item) {
                if (!$item->is_purchased || !$item->actual_purchased_quantity || !$item->product) {
                    continue;
                }

                $item->product->increment('quantity', $item->actual_purchased_quantity);

                $item->product->movements()->create([
                    'company_id' => $item->product->company_id,
                    'user_id' => null,
                    'type' => 'in',
                    'quantity' => $item->actual_purchased_quantity,
                    'notes' => "Compra de mercado - lista #{$list->id}",
                ]);
            }
        });

        Log::channel('home')->info('home.shopping_list.completed', [
            'company_id' => $list->company_id,
            'list_id' => $list->id,
            'items_count' => $list->items()->where('is_purchased', true)->count(),
        ]);
    }
}
