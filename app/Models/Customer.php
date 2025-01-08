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
      'company_id'
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

   public function purchases()
   {
      return $this->hasMany(Purchase::class);
   }

   public function company()
   {
      return $this->belongsTo(Company::class);
   }
}
