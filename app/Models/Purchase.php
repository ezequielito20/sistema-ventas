<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Purchase extends Model
{
   use HasFactory;

   /**
    * Los atributos que son asignables masivamente.
    *
    * @var array<string>
    */
   protected $fillable = [
      'purchase_date',
      'payment_receipt',
      'total_price',
      'company_id'
   ];

   /**
    * Los atributos que deben ser convertidos a tipos nativos.
    *
    * @var array<string, string>
    */
   protected $casts = [
      'purchase_date' => 'datetime',
      'total_price' => 'decimal:2'
   ];
   /**
    * Scope para filtrar compras por compañía
    */
   public function scopeByCompany($query, $companyId)
   {
      return $query->where('company_id', $companyId);
   }
   public function getTotalPurchases($companyId, $startDate = null, $endDate = null)
   {
      $query = self::byCompany($companyId);

      if ($startDate && $endDate) {
         $query->whereBetween('purchase_date', [$startDate, $endDate]);
      }

      return $query->sum('total_price');
   }
   /**
    * Obtiene los detalles de la compra
    */
   public function details(): HasMany
   {
      return $this->hasMany(PurchaseDetail::class);
   }

   /**
    * Obtiene la compañía asociada a la compra
    */
   public function company(): BelongsTo
   {
      return $this->belongsTo(Company::class);
   }

   
}
