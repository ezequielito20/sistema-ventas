<?php

namespace App\Models\Home;

use App\Models\Company;
use App\Services\ImageUrlService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HomeProduct extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'name', 'brand', 'category', 'quantity',
        'min_quantity', 'max_quantity', 'unit', 'purchase_price',
        'barcode', 'image',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'min_quantity' => 'integer',
        'max_quantity' => 'integer',
        'purchase_price' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(HomeProductMovement::class, 'home_product_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(HomeProductImage::class, 'home_product_id');
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity', '<', 'min_quantity');
    }

    public function scopeExcedent($query)
    {
        return $query->whereNotNull('max_quantity')
            ->whereColumn('quantity', '>', 'max_quantity');
    }

    public function scopeOk($query)
    {
        return $query->where(function ($q) {
            $q->whereColumn('quantity', '>=', 'min_quantity');
            $q->where(function ($sub) {
                $sub->whereNull('max_quantity')
                    ->orWhereColumn('quantity', '<=', 'max_quantity');
            });
        });
    }

    public function scopeToBuy($query)
    {
        return $query->whereColumn('quantity', '<', 'min_quantity');
    }

    public function getToBuyAttribute(): int
    {
        return max(0, $this->min_quantity - $this->quantity);
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->quantity < $this->min_quantity) {
            return 'low';
        }

        if ($this->max_quantity !== null && $this->quantity > $this->max_quantity) {
            return 'excedent';
        }

        return 'ok';
    }

    public function getEstimatedTotalAttribute(): float
    {
        return $this->to_buy * (float) $this->purchase_price;
    }

    public function getImageUrlAttribute(): string
    {
        return ImageUrlService::getImageUrl($this->image);
    }
}
