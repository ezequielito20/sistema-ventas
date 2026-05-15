<?php

namespace App\Models\Home;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeProductMovement extends Model
{
    protected $fillable = [
        'company_id', 'home_product_id', 'user_id', 'type',
        'quantity', 'notes', 'metadata',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'metadata' => 'json',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(HomeProduct::class, 'home_product_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
