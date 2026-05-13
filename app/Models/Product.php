<?php

namespace App\Models;

use App\Services\ImageUrlService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'description',
        'image',
        'stock',
        'min_stock',
        'max_stock',
        'purchase_price',
        'sale_price',
        'discount_percent',
        'entry_date',
        'category_id',
        'company_id',
        'include_in_catalog',
    ];

    protected $appends = ['image_url', 'stock_status_label', 'stock_status_class', 'cover_image_url', 'final_price', 'has_discount'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'entry_date' => 'datetime',
        'purchase_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'discount_percent' => 'integer',
        'stock' => 'integer',
        'min_stock' => 'integer',
        'max_stock' => 'integer',
        'company_id' => 'integer',
        'include_in_catalog' => 'boolean',
    ];

    /**
     * Get the formatted purchase price.
     */
    /**
     * Get the formatted purchase price.
     */
    public function getFormattedPurchasePriceAttribute()
    {
        return number_format((float) $this->purchase_price, 2);
    }

    /**
     * Get the formatted sale price.
     */
    public function getFormattedSalePriceAttribute()
    {
        return number_format((float) $this->sale_price, 2);
    }

    /**
     * Get the formatted entry date.
     */
    public function getFormattedEntryDateAttribute()
    {
        return $this->entry_date->format('d/m/Y');
    }

    /**
     * Get the stock status.
     */
    public function getStockStatusAttribute()
    {
        if ($this->stock <= $this->min_stock) {
            return 'low';
        } elseif ($this->stock >= $this->max_stock) {
            return 'high';
        }

        return 'normal';
    }

    /**
     * Get the stock status label.
     */
    public function getStockStatusLabelAttribute()
    {
        return [
            'low' => 'Bajo',
            'normal' => 'Normal',
            'high' => 'Alto',
        ][$this->stock_status];
    }

    /**
     * Get the stock status class.
     */
    public function getStockStatusClassAttribute()
    {
        return [
            'low' => 'text-danger',
            'normal' => 'text-success',
            'high' => 'text-warning',
        ][$this->stock_status];
    }

    /**
     * Scope a query to only include products with low stock.
     */
    public function scopeLowStock($query)
    {
        return $query->whereRaw('stock <= min_stock');
    }

    /**
     * Scope a query to only include products with high stock.
     */
    public function scopeHighStock($query)
    {
        return $query->whereRaw('stock >= max_stock');
    }

    /**
     * Scope a query to only include products with normal stock.
     */
    public function scopeNormalStock($query)
    {
        return $query->whereRaw('stock > min_stock AND stock < max_stock');
    }

    /**
     * Products that may appear in the public catalog (flag + stock rule).
     */
    public function scopeVisibleInPublicCatalog($query)
    {
        return $query->where('include_in_catalog', true)
            ->where(function ($q) {
                $q->where('stock', '>', 0)->orWhereNull('stock');
            });
    }

    /**
     * Whether this product should be reachable from the public catalog (list/detail).
     */
    public function isVisibleInPublicCatalog(): bool
    {
        if (! $this->include_in_catalog) {
            return false;
        }

        $stock = $this->getAttributes()['stock'] ?? $this->stock;

        return $stock === null || $stock > 0;
    }

    /**
     * Check if the product has low stock.
     */
    public function hasLowStock()
    {
        return $this->stock <= $this->min_stock;
    }

    /**
     * Check if the product has high stock.
     */
    public function hasHighStock()
    {
        return $this->stock >= $this->max_stock;
    }

    /**
     * Get the profit margin.
     */
    public function getProfitMarginAttribute()
    {
        if ($this->purchase_price > 0) {
            return (($this->sale_price - $this->purchase_price) / $this->purchase_price) * 100;
        }

        return 0;
    }

    /**
     * Get the image URL using ImageUrlService.
     */
    public function getImageUrlAttribute()
    {
        if (! $this->image) {
            return asset('img/no-image.svg');
        }

        return ImageUrlService::getImageUrl($this->image);
    }

    public function getFinalPriceAttribute(): float
    {
        if ($this->discount_percent > 0) {
            return round($this->sale_price * (1 - $this->discount_percent / 100), 2);
        }

        return (float) $this->sale_price;
    }

    public function getHasDiscountAttribute(): bool
    {
        return $this->discount_percent > 0;
    }

    /**
     * Get the supplier that owns the product.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the category that owns the product.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function purchaseDetails(): HasMany
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    public function saleDetails(): HasMany
    {
        return $this->hasMany(SaleDetail::class);
    }

    /**
     * Get the images for the product.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * Get the cover image for the product.
     */
    public function coverImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_cover', true);
    }

    /**
     * Get the cover image URL or fall back to the legacy product image_url.
     */
    public function getCoverImageUrlAttribute(): string
    {
        if ($this->relationLoaded('images')) {
            $cover = $this->images->firstWhere('is_cover', true);
            if ($cover) {
                return $cover->image_url;
            }
        } else {
            $cover = $this->coverImage()->first();
            if ($cover) {
                return $cover->image_url;
            }
        }

        return $this->getImageUrlAttribute();
    }
}
