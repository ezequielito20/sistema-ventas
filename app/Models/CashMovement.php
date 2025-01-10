<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashMovement extends Model
{
   use HasFactory;

   /**
    * Los atributos que son asignables masivamente.
    *
    * @var array<string>
    */
   protected $fillable = [
      'type',
      'amount',
      'description',
      'cash_count_id'
   ];

   /**
    * Los atributos que deben ser convertidos a tipos nativos.
    *
    * @var array<string, string>
    */
   protected $casts = [
      'amount' => 'decimal:2',
   ];

   /**
    * Las constantes para los tipos de movimiento.
    */
   const TYPE_INCOME = 'income';
   const TYPE_EXPENSE = 'expense';



   /**
    * Verifica si el movimiento es un ingreso.
    */
   public function isIncome(): bool
   {
      return $this->type === self::TYPE_INCOME;
   }

   /**
    * Verifica si el movimiento es un egreso.
    */
   public function isExpense(): bool
   {
      return $this->type === self::TYPE_EXPENSE;
   }
   /**
    * Obtiene el arqueo de caja asociado al movimiento.
    */
   public function cashCount()
   {
      return $this->belongsTo(CashCount::class);
   }
}
