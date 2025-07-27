<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

   /**
    * Scope para clientes morosos (con deudas de arqueos anteriores)
    */
   public function scopeDefaulters($query)
   {
      return $query->where('total_debt', '>', 0)
         ->whereHas('sales', function($q) {
            $q->where('sale_date', '<', $this->getCurrentCashCountOpeningDate());
         });
   }

   /**
    * Scope para clientes con deudas del arqueo actual
    */
   public function scopeCurrentDebtors($query)
   {
      return $query->where('total_debt', '>', 0)
         ->whereHas('sales', function($q) {
            $q->where('sale_date', '>=', $this->getCurrentCashCountOpeningDate());
         });
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

   /**
    * Obtiene la fecha de apertura del arqueo de caja actual
    */
   public function getCurrentCashCountOpeningDate()
   {
      $currentCashCount = \App\Models\CashCount::where('company_id', $this->company_id)
         ->whereNull('closing_date')
         ->first();
      
      return $currentCashCount ? $currentCashCount->opening_date : now();
   }

   /**
    * Obtiene la fecha de apertura del arqueo de caja anterior
    */
   public function getPreviousCashCountOpeningDate()
   {
      $currentCashCount = \App\Models\CashCount::where('company_id', $this->company_id)
         ->whereNull('closing_date')
         ->first();
      
      if (!$currentCashCount) {
         return now();
      }
      
      // Buscar el arqueo anterior (el último cerrado antes del actual)
      $previousCashCount = \App\Models\CashCount::where('company_id', $this->company_id)
         ->where('closing_date', '<', $currentCashCount->opening_date)
         ->orderBy('closing_date', 'desc')
         ->first();
      
      return $previousCashCount ? $previousCashCount->opening_date : $currentCashCount->opening_date;
   }

   /**
    * Determina si el cliente tiene deudas de arqueos anteriores
    */
   public function hasPreviousCashCountDebts()
   {
      if ($this->total_debt <= 0) {
         return false;
      }

      $currentCashCountOpeningDate = $this->getCurrentCashCountOpeningDate();
      
      // Verificar si tiene ventas antes del arqueo actual
      $hasSalesBeforeCurrentCashCount = $this->sales()
         ->where('sale_date', '<', $currentCashCountOpeningDate)
         ->exists();

      if (!$hasSalesBeforeCurrentCashCount) {
         return false;
      }

      // Verificar si tiene pagos que cubran las deudas anteriores
      $totalPaymentsBeforeCurrentCashCount = $this->getTotalPaymentsBeforeDate($currentCashCountOpeningDate);
      $totalDebtsBeforeCurrentCashCount = $this->getTotalDebtsBeforeDate($currentCashCountOpeningDate);

      // Si los pagos no cubren las deudas anteriores, entonces tiene deudas de arqueos anteriores
      return $totalPaymentsBeforeCurrentCashCount < $totalDebtsBeforeCurrentCashCount;
   }

   /**
    * Obtiene el total de pagos realizados antes de una fecha específica
    */
   public function getTotalPaymentsBeforeDate($date)
   {
      // Buscar en la tabla debt_payments si existe
      if (Schema::hasTable('debt_payments')) {
         return DB::table('debt_payments')
            ->where('customer_id', $this->id)
            ->where('created_at', '<', $date)
            ->sum('payment_amount');
      }

      // Si no existe la tabla debt_payments, buscar en cash_movements
      return DB::table('cash_movements')
         ->join('cash_counts', 'cash_movements.cash_count_id', '=', 'cash_counts.id')
         ->where('cash_counts.company_id', $this->company_id)
         ->where('cash_movements.type', 'income')
         ->where('cash_movements.description', 'like', '%' . $this->name . '%')
         ->where('cash_movements.created_at', '<', $date)
         ->sum('cash_movements.amount');
   }

   /**
    * Obtiene el total de pagos realizados después de una fecha específica
    */
   public function getTotalPaymentsAfterDate($date)
   {
      // Buscar en la tabla debt_payments si existe
      if (Schema::hasTable('debt_payments')) {
         return DB::table('debt_payments')
            ->where('customer_id', $this->id)
            ->where('created_at', '>=', $date)
            ->sum('payment_amount');
      }

      // Si no existe la tabla debt_payments, buscar en cash_movements
      return DB::table('cash_movements')
         ->join('cash_counts', 'cash_movements.cash_count_id', '=', 'cash_counts.id')
         ->where('cash_counts.company_id', $this->company_id)
         ->where('cash_movements.type', 'income')
         ->where('cash_movements.description', 'like', '%' . $this->name . '%')
         ->where('cash_movements.created_at', '>=', $date)
         ->sum('cash_movements.amount');
   }

   /**
    * Obtiene el total de deudas generadas antes de una fecha específica
    */
   public function getTotalDebtsBeforeDate($date)
   {
      return $this->sales()
         ->where('sale_date', '<', $date)
         ->sum('total_price');
   }

   /**
    * Obtiene el monto de deuda de arqueos anteriores
    */
   public function getPreviousCashCountDebtAmount()
   {
      if (!$this->hasPreviousCashCountDebts()) {
         return 0;
      }

      $currentCashCountOpeningDate = $this->getCurrentCashCountOpeningDate();
      $totalPaymentsBeforeCurrentCashCount = $this->getTotalPaymentsBeforeDate($currentCashCountOpeningDate);
      $totalDebtsBeforeCurrentCashCount = $this->getTotalDebtsBeforeDate($currentCashCountOpeningDate);

      return $totalDebtsBeforeCurrentCashCount - $totalPaymentsBeforeCurrentCashCount;
   }

   /**
    * Obtiene el monto de deuda del arqueo actual
    */
   public function getCurrentCashCountDebtAmount()
   {
      $currentCashCountOpeningDate = $this->getCurrentCashCountOpeningDate();
      
      // Solo considerar ventas realizadas DESPUÉS de la apertura del arqueo actual
      $salesInCurrentCashCount = $this->sales()
         ->where('sale_date', '>=', $currentCashCountOpeningDate)
         ->sum('total_price');
      
      // Solo contar pagos que corresponden a deudas del arqueo actual
      // Si el cliente tiene ventas en el arqueo actual, entonces los pagos cuentan
      if ($salesInCurrentCashCount > 0) {
         $paymentsInCurrentCashCount = $this->getTotalPaymentsAfterDate($currentCashCountOpeningDate);
         $debt = $salesInCurrentCashCount - $paymentsInCurrentCashCount;
         
         // La deuda nunca puede ser negativa
         return max(0, $debt);
      } else {
         // Si no tiene ventas en el arqueo actual, no tiene deuda del arqueo actual
         return 0;
      }
   }

   /**
    * Determina si el cliente es moroso (tiene deudas de arqueos anteriores)
    */
   public function isDefaulter()
   {
      return $this->hasPreviousCashCountDebts();
   }

   /**
    * Obtiene el tipo de deuda del cliente
    */
   public function getDebtTypeAttribute()
   {
      if ($this->total_debt <= 0) {
         return 'sin_deuda';
      }

      if ($this->hasPreviousCashCountDebts()) {
         return 'moroso';
      }

      return 'actual';
   }

   /**
    * Obtiene el texto descriptivo del tipo de deuda
    */
   public function getDebtTypeTextAttribute()
   {
      switch ($this->debt_type) {
         case 'moroso':
            return 'Deuda de arqueos anteriores';
         case 'actual':
            return 'Deuda del arqueo actual';
         default:
            return 'Sin deuda';
      }
   }
}