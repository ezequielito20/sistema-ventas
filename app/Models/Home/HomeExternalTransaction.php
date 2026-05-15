<?php

namespace App\Models\Home;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeExternalTransaction extends Model
{
    protected $fillable = [
        'company_id', 'home_external_account_id', 'external_transaction_id',
        'amount', 'currency_code', 'posted_at', 'description', 'raw_category',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'posted_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function externalAccount(): BelongsTo
    {
        return $this->belongsTo(HomeExternalAccount::class, 'home_external_account_id');
    }
}
