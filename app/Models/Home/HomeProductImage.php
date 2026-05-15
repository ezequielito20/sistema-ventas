<?php

namespace App\Models\Home;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeProductImage extends Model
{
    protected $fillable = ['home_product_id', 'path'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(HomeProduct::class, 'home_product_id');
    }
}
