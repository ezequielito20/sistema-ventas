<?php

namespace App\Models\Home;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HomeBankConnection extends Model
{
    protected $fillable = [
        'company_id', 'user_id', 'provider', 'external_link_id',
        'access_token_encrypted', 'refresh_token_encrypted',
        'token_expires_at', 'status', 'last_successful_sync_at',
        'last_error_message',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'last_successful_sync_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function externalAccounts(): HasMany
    {
        return $this->hasMany(HomeExternalAccount::class, 'home_bank_connection_id');
    }

    public function getAccessTokenAttribute(): ?string
    {
        return $this->access_token_encrypted ? decrypt($this->access_token_encrypted) : null;
    }

    public function getRefreshTokenAttribute(): ?string
    {
        return $this->refresh_token_encrypted ? decrypt($this->refresh_token_encrypted) : null;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function isExpired(): bool
    {
        return $this->token_expires_at && $this->token_expires_at->isPast();
    }
}
