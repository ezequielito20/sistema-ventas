<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'plan_id',
        'status',
        'started_at',
        'expires_at',
        'billing_day',
        'next_billing_date',
        'grace_period_end',
        'trial_ends_at',
        'amount',
        'discount_amount',
        'discount_reason',
        'reference_code',
        'auto_renew',
        'cancelled_at',
        'cancellation_reason',
        'billing_mode',
        'custom_recurring_amount',
    ];

    protected $casts = [
        'started_at' => 'date',
        'expires_at' => 'date',
        'next_billing_date' => 'date',
        'grace_period_end' => 'date',
        'trial_ends_at' => 'date',
        'amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'auto_renew' => 'boolean',
        'cancelled_at' => 'datetime',
        'custom_recurring_amount' => 'decimal:2',
        'billing_mode' => 'string',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    public function latestPayment()
    {
        return $this->hasOne(SubscriptionPayment::class)->latestOfMany();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isGracePeriod(): bool
    {
        return $this->status === 'grace_period';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isTrial(): bool
    {
        return $this->status === 'trial';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}
