<?php

namespace App\Models\Home;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HomeService extends Model
{
    protected $fillable = ['company_id', 'name', 'provider', 'contract_number'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function bills(): HasMany
    {
        return $this->hasMany(HomeServiceBill::class, 'home_service_id');
    }

    public function scopeWithDueBills($query, int $daysAhead = 7)
    {
        return $query->whereHas('bills', function ($q) use ($daysAhead) {
            $q->whereNull('paid_at')
                ->whereBetween('due_date', [now(), now()->addDays($daysAhead)]);
        });
    }
}
