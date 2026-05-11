<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubscriptionUsageLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'period_start',
        'period_end',
        'user_count',
        'transaction_count',
        'sale_count',
        'product_count',
        'customer_count',
        'total_revenue',
        'calculated_amount',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'user_count' => 'integer',
        'transaction_count' => 'integer',
        'sale_count' => 'integer',
        'product_count' => 'integer',
        'customer_count' => 'integer',
        'total_revenue' => 'decimal:2',
        'calculated_amount' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
