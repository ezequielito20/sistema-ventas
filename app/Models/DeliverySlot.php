<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliverySlot extends Model
{
    protected $fillable = [
        'company_id',
        'company_delivery_method_id',
        'delivery_zone_id',
        'starts_at',
        'ends_at',
        'max_orders',
        'booked_count',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function deliveryMethod(): BelongsTo
    {
        return $this->belongsTo(CompanyDeliveryMethod::class, 'company_delivery_method_id');
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class, 'delivery_zone_id');
    }

    public function hasCapacity(): bool
    {
        return (int) $this->booked_count < (int) $this->max_orders;
    }
}
