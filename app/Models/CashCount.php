<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashCount extends Model
{
   use HasFactory;

   /**
    * Los atributos que son asignables masivamente.
    *
    * @var array<string>
    */
   protected $fillable = [
      'opening_date',
      'closing_date',
      'initial_amount',
      'final_amount',
      'observations',
      'company_id'
   ];

   /**
    * Los atributos que deben ser convertidos a tipos nativos.
    *
    * @var array<string, string>
    */
   protected $casts = [
      'opening_date' => 'date',
      'closing_date' => 'date',
      'initial_amount' => 'decimal:2',
      'final_amount' => 'decimal:2',
   ];

   /**
    * Obtiene la compañía asociada al arqueo de caja.
    */
   public function company()
   {
      return $this->belongsTo(Company::class);
   }

   /**
    * Obtiene los movimientos de caja asociados al arqueo.
    */
   public function movements()
   {
      return $this->hasMany(CashMovement::class);
   }
}
