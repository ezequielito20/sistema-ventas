<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
      'entry_date',
      'category_id',
      'company_id'
   ];

   /**
    * The attributes that should be cast.
    *
    * @var array<string, string>
    */
   protected $casts = [
      'entry_date' => 'date',
      'purchase_price' => 'decimal:2',
      'sale_price' => 'decimal:2',
      'stock' => 'integer',
      'min_stock' => 'integer',
      'max_stock' => 'integer',
      'company_id' => 'integer'
   ];

   /**
    * Get the formatted purchase price.
    */
   public function getFormattedPurchasePriceAttribute()
   {
      return '$' . number_format($this->purchase_price, 2);
   }

   /**
    * Get the formatted sale price.
    */
   public function getFormattedSalePriceAttribute()
   {
      return '$' . number_format($this->sale_price, 2);
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
         'high' => 'Alto'
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
         'high' => 'text-warning'
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
    * Get the supplier that owns the product.
    */
   public function supplier(): BelongsTo
   {
      return $this->belongsTo(Supplier::class);
   }
   /**
    * Get the category that owns the product.
    */
   public function category(): BelongsTo
   {
      return $this->belongsTo(Category::class);
   }

   public function purchases(): HasMany
   {
      return $this->hasMany(Purchase::class);
   }
}
