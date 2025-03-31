<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
   use HasFactory;

   protected $fillable = [
      'name',
      'nit_number',
      'phone',
      'email',
      'company_id',
      'total_debt',
   ];

   protected $casts = [
      'created_at' => 'datetime',
      'updated_at' => 'datetime',
   ];

   /**
    * Determina si el cliente está activo basado en su última interacción
    */
   public function isActive()
   {
      return $this->created_at->isCurrentMonth() ||
         $this->updated_at->isCurrentMonth();
   }

   /**
    * Obtiene el estado formateado del cliente
    */
   public function getStatusAttribute()
   {
      return $this->isActive() ? 'Activo' : 'Inactivo';
   }

   /**
    * Formatea el nombre del cliente
    */
   public function getFormattedNameAttribute()
   {
      return ucwords(strtolower($this->name));
   }
   /**
    * Obtiene el total de compras realizadas por el cliente
    *
    * @return float
    */
   public function getTotalPurchasesAmountAttribute()
   {
      return $this->sales()
         ->where('company_id', $this->company_id)
         ->sum('total_price');
   }

   /**
    * Formatea el teléfono del cliente
    */
   public function getFormattedPhoneAttribute()
   {
      return $this->phone ? $this->phone : 'No registrado';
   }

   /**
    * Obtiene la fecha de creación formateada
    */
   public function getCreatedAtFormattedAttribute()
   {
      return $this->created_at->format('d/m/Y H:i:s');
   }

   /**
    * Scope para clientes activos
    */
   public function scopeActive($query)
   {
      return $query->whereMonth('created_at', '=', Carbon::now()->month)
         ->orWhereMonth('updated_at', '=', Carbon::now()->month);
   }

   /**
    * Scope para clientes inactivos
    */
   public function scopeInactive($query)
   {
      return $query->whereMonth('created_at', '<', Carbon::now()->month)
         ->whereMonth('updated_at', '<', Carbon::now()->month);
   }


   public function sales()
   {
      return $this->hasMany(Sale::class);
   }

   public function company()
   {
      return $this->belongsTo(Company::class);
   }

   /**
    * Obtiene la última venta del cliente
    */
   public function lastSale()
   {
      return $this->hasOne(Sale::class)->latest();
   }

   /**
    * Obtiene el total de ventas del cliente
    */
   public function getTotalSalesAttribute()
   {
      return $this->sales->count();
   }

   /**
    * Obtiene el monto total de ventas del cliente
    */
   public function getTotalSalesAmountAttribute()
   {
      return $this->sales->sum('total_price');
   }

   /**
    * Obtiene el total de deuda pendiente del cliente
    *
    * @return float
    */
   public function getTotalDebtAttribute()
   {
      // Simplemente devuelve el valor almacenado en la columna total_debt
      return $this->attributes['total_debt'] ?? 0;
   }

   /**
    * Formatea la deuda total para mostrar
    */
   public function getFormattedTotalDebtAttribute()
   {
      return $this->total_debt > 0 ? $this->total_debt : 0;
   }
}
