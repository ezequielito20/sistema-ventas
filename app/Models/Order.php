<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'customer_name',
        'customer_phone',
        'notes',
        'status',
        'paid_at',
        'delivered_at',
        'company_payment_method_id',
        'company_delivery_method_id',
        'delivery_zone_id',
        'delivery_custom_location',
        'delivery_slot_id',
        'scheduled_delivery_date',
        'exchange_rate_used',
        'subtotal_products_usd',
        'payment_discount_percent_snapshot',
        'payment_discount_amount_usd',
        'delivery_fee_usd',
        'total_usd',
        'total_bs',
        'public_summary_token',
        'public_summary_expires_at',
        'payment_method_snapshot',
        'delivery_method_snapshot',
        'customer_id',
        'sale_id',
        'processed_at',
        'processed_by',
    ];

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'delivered_at' => 'datetime',
            'public_summary_expires_at' => 'datetime',
            'processed_at' => 'datetime',
            'scheduled_delivery_date' => 'date',
            'exchange_rate_used' => 'decimal:4',
            'subtotal_products_usd' => 'decimal:2',
            'payment_discount_percent_snapshot' => 'decimal:2',
            'payment_discount_amount_usd' => 'decimal:2',
            'delivery_fee_usd' => 'decimal:2',
            'total_usd' => 'decimal:2',
            'total_bs' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Order $order): void {
            if ($order->status === 'cancelled') {
                return;
            }
            if ($order->paid_at && $order->delivered_at) {
                $order->status = 'processed';
                $order->processed_at = $order->processed_at ?? now();
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(CompanyPaymentMethod::class, 'company_payment_method_id');
    }

    public function deliveryMethod(): BelongsTo
    {
        return $this->belongsTo(CompanyDeliveryMethod::class, 'company_delivery_method_id');
    }

    public function deliveryZone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class, 'delivery_zone_id');
    }

    public function deliverySlot(): BelongsTo
    {
        return $this->belongsTo(DeliverySlot::class, 'delivery_slot_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public static function generateSummaryToken(): string
    {
        return Str::random(48);
    }

    public function summaryUrl(): string
    {
        return route('order.summary.show', ['token' => $this->public_summary_token]);
    }

    public function currentExchangeRateForDisplay(): float
    {
        return (float) ($this->exchange_rate_used ?: ExchangeRate::current());
    }
}
