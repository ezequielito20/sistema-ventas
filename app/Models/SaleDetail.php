<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleDetail extends Model
{
   use HasFactory;

   /**
    * Los atributos que son asignables masivamente.
    *
    * @var array<string>
    */
   protected $fillable = [
      'quantity',
      'sale_id',
      'product_id',
      'unit_price',
      'subtotal',
   ];

   /**
    * Los atributos que deben ser convertidos a tipos nativos.
    *
    * @var array<string, string>
    */
   protected $casts = [
      'quantity' => 'integer',
      'unit_price' => 'decimal:2',
      'subtotal' => 'decimal:2',
   ];

   /**
    * Obtiene la venta asociada al detalle.
    */
   public function sale()
   {
      return $this->belongsTo(Sale::class);
   }

   /**
    * Obtiene el producto asociado al detalle.
    */
   public function product()
   {
      return $this->belongsTo(Product::class);
   }
}
