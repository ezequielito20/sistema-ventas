<?php

namespace App\Models;

use App\Models\User;
use App\Models\City;
use App\Models\State;
use App\Models\Country;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Services\ImageUrlService;

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
      'ig',
      'last_debt_alert_fingerprint',
      'subscription_status',
      'billing_day',
   ];

   protected $casts = [
      'tax_amount' => 'decimal:2',
      'billing_day' => 'integer',
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

   public function sales(): HasMany
   {
      return $this->hasMany(Sale::class);
   }

   public function products(): HasMany
   {
      return $this->hasMany(Product::class);
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

   /**
    * Get the logo URL using ImageUrlService.
    */
   public function subscription(): HasOne
   {
      return $this->hasOne(Subscription::class);
   }

   public function subscriptionPayments(): HasMany
   {
      return $this->hasMany(SubscriptionPayment::class);
   }

   public function usageLogs(): HasMany
   {
      return $this->hasMany(SubscriptionUsageLog::class);
   }

   public function latestUsageLog(): HasOne
   {
      return $this->hasOne(SubscriptionUsageLog::class)->latestOfMany();
   }

   public function plan()
   {
      return $this->hasOneThrough(Plan::class, Subscription::class, 'company_id', 'id', 'id', 'plan_id');
   }

   public function getLogoUrlAttribute()
   {
      if (!$this->logo) {
         return asset('assets/img/logotipo.jpg'); // Default logo
      }

      $imageUrlService = new ImageUrlService();
      return $imageUrlService->getImageUrl($this->logo);
   }
}
