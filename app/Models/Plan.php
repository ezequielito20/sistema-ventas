<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'base_price',
        'price_per_user',
        'price_per_transaction',
        'limits',
        'features',
        'max_users',
        'max_transactions',
        'max_products',
        'max_customers',
        'is_active',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'price_per_user' => 'decimal:2',
        'price_per_transaction' => 'decimal:2',
        'limits' => 'array',
        'features' => 'array',
        'max_users' => 'integer',
        'max_transactions' => 'integer',
        'max_products' => 'integer',
        'max_customers' => 'integer',
        'is_active' => 'boolean',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
