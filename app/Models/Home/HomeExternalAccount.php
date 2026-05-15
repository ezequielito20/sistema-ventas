<?php

namespace App\Models\Home;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HomeExternalAccount extends Model
{
    protected $fillable = [
        'company_id', 'home_bank_connection_id', 'external_account_id',
        'institution_name', 'masked_number', 'currency_code',
        'balance_cached', 'last_synced_at',
    ];

    protected $casts = [
        'balance_cached' => 'decimal:2',
        'last_synced_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function connection(): BelongsTo
    {
        return $this->belongsTo(HomeBankConnection::class, 'home_bank_connection_id');
    }

    public function externalTransactions(): HasMany
    {
        return $this->hasMany(HomeExternalTransaction::class, 'home_external_account_id');
    }
}
