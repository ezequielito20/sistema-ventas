<?php

namespace App\Models\Home;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeServiceBill extends Model
{
    protected $fillable = [
        'home_service_id', 'period', 'amount', 'due_date',
        'cutoff_date', 'paid_at', 'bill_image_path',
        'ocr_status', 'ocr_payload', 'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'cutoff_date' => 'date',
        'paid_at' => 'datetime',
        'ocr_payload' => 'json',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(HomeService::class, 'home_service_id');
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->paid_at !== null;
    }

    public function getDaysToDueAttribute(): ?int
    {
        return $this->due_date ? now()->diffInDays($this->due_date, false) : null;
    }

    public function scopeUnpaid($query)
    {
        return $query->whereNull('paid_at');
    }

    public function scopeDueSoon($query, int $days = 7)
    {
        return $query->unpaid()
            ->whereBetween('due_date', [now(), now()->addDays($days)]);
    }
}
