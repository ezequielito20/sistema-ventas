<?php

namespace App\Models;

use App\Models\User;
use App\Models\City;
use App\Models\State;
use App\Models\Country;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{
   use HasFactory;

   protected $fillable = [
      'name',
      'country',
      'business_type',
      'phone',
      'email',
      'tax_amount',
      'tax_name',
      'currency',
      'address',
      'city',
      'state',
      'postal_code',
      'logo',
      'nit',
      'ig'
   ];

   protected $casts = [
      'tax_amount' => 'decimal:2',
      'created_at' => 'datetime',
      'updated_at' => 'datetime',
   ];

   public function users(): HasMany
   {
      return $this->hasMany(User::class);
   }

   public function purchases(): HasMany
   {
      return $this->hasMany(Purchase::class);
   }

   public function customers(): HasMany
   {
      return $this->hasMany(Customer::class);
   }

   public function countryModel(): BelongsTo
   {
      return $this->belongsTo(Country::class, 'country', 'id');
   }

   public function stateModel(): BelongsTo
   {
      return $this->belongsTo(State::class, 'state', 'id');
   }

   public function cityModel(): BelongsTo
   {
      return $this->belongsTo(City::class, 'city', 'id');
   }
}
