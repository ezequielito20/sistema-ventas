<?php

namespace App\Models\Home;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeTransaction extends Model
{
    protected $fillable = [
        'company_id', 'home_bank_account_id', 'type',
        'category', 'amount', 'description', 'transaction_date',
        'receipt_image_path',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(HomeBankAccount::class, 'home_bank_account_id');
    }

    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }

    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }

    public function scopeByMonth($query, $year, $month)
    {
        return $query->whereYear('transaction_date', $year)
            ->whereMonth('transaction_date', $month);
    }

    public function scopeByCategory($query, ?string $category = null)
    {
        if ($category) {
            return $query->where('category', $category);
        }

        return $query;
    }
}
