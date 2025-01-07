<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseTmp extends Model
{
   use HasFactory;

   protected $fillable = [
      'quantity',
      'product_id',
      'session_id'
   ];

   protected $casts = [
      'quantity' => 'integer'
   ];

   /**
    * Obtiene el producto asociado
    */

   /**
    * Scope para filtrar por session_id
    */
   public function scopeBySession($query, $sessionId)
   {
      return $query->where('session_id', $sessionId);
   }
   public function product(): BelongsTo
   {
      return $this->belongsTo(Product::class);
   }
}
