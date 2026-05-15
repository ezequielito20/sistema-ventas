<?php

namespace App\Models\Home;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HomeBankAccount extends Model
{
    protected $fillable = [
        'company_id', 'bank_name', 'account_type',
        'account_number_encrypted', 'balance', 'currency_code',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(HomeTransaction::class, 'home_bank_account_id');
    }

    public function getMaskedNumberAttribute(): ?string
    {
        if (!$this->account_number_encrypted) {
            return null;
        }

        $decrypted = decrypt($this->account_number_encrypted);

        return '****' . substr($decrypted, -4);
    }
}
