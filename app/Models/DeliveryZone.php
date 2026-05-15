<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryZone extends Model
{
    protected $fillable = [
        'company_delivery_method_id',
        'name',
        'extra_fee_usd',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'extra_fee_usd' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function deliveryMethod(): BelongsTo
    {
        return $this->belongsTo(CompanyDeliveryMethod::class, 'company_delivery_method_id');
    }

    public function deliverySlots(): HasMany
    {
        return $this->hasMany(DeliverySlot::class);
    }
}
