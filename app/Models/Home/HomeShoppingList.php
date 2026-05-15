<?php

namespace App\Models\Home;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HomeShoppingList extends Model
{
    protected $fillable = ['company_id', 'generated_at', 'is_completed'];

    protected $casts = [
        'generated_at' => 'datetime',
        'is_completed' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(HomeShoppingListItem::class, 'home_shopping_list_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_completed', false);
    }
}
