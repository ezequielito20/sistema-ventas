<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyDeliveryMethod extends Model
{
    public const TYPE_PICKUP = 'pickup';

    public const TYPE_DELIVERY = 'delivery';

    protected $fillable = [
        'company_id',
        'type',
        'name',
        'instructions',
        'pickup_address',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'company_delivery_method_id');
    }

    public function zones(): HasMany
    {
        return $this->hasMany(DeliveryZone::class);
    }

    public function deliverySlots(): HasMany
    {
        return $this->hasMany(DeliverySlot::class);
    }

    public function isPickup(): bool
    {
        return $this->type === self::TYPE_PICKUP;
    }

    public function isDelivery(): bool
    {
        return $this->type === self::TYPE_DELIVERY;
    }
}
