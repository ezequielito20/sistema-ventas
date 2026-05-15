<?php

namespace App\Models\Home;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeShoppingListItem extends Model
{
    protected $fillable = [
        'home_shopping_list_id', 'home_product_id', 'name_snapshot',
        'suggested_quantity', 'actual_purchased_quantity',
        'is_purchased', 'notes',
    ];

    protected $casts = [
        'suggested_quantity' => 'integer',
        'actual_purchased_quantity' => 'integer',
        'is_purchased' => 'boolean',
    ];

    public function shoppingList(): BelongsTo
    {
        return $this->belongsTo(HomeShoppingList::class, 'home_shopping_list_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(HomeProduct::class, 'home_product_id');
    }
}
