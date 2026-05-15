<?php

namespace App\Events\Home;

use App\Models\Home\HomeShoppingList;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShoppingListCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public HomeShoppingList $shoppingList,
    ) {}
}
