<?php

namespace App\Models;

use App\Services\ImageUrlService;
use App\Services\PlanEntitlementService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Company extends Model
{
    use HasFactory;

    /**
     * Reserved slugs that cannot be used as company slugs
     * to avoid collisions with existing routes.
     */
    public const RESERVED_SLUGS = [
        'login', 'logout', 'password', 'register', 'home',
        'admin', 'super-admin', 'super_admin',
        'img', 'settings', 'livewire', 'api', 'dashboard',
        'users', 'roles', 'categories',
        'products', 'suppliers', 'purchases', 'customers',
        'sales', 'cash-counts',
        'permissions', 'notifications', 'orders',
        'exchange-rate', 'debt-payments',
        'catalog', 'catalogo', 'auth', 'verify', 'email', 'my-plan',
        'profile', 'security-questions', '_livewire',
    ];

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
        'slug',
        'catalog_is_public',
    ];

    protected $casts = [
        'tax_amount' => 'decimal:2',
        'billing_day' => 'integer',
        'catalog_is_public' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::creating(function (self $company) {
            // Auto-generate slug from name if slug is empty
            if (empty($company->slug)) {
                $company->slug = self::generateUniqueSlug($company->name);
            } else {
                // Validate slug is not reserved
                self::validateSlug($company->slug);
            }
        });

        static::updating(function (self $company) {
            if ($company->isDirty('slug')) {
                self::validateSlug($company->slug);
            }
        });
    }

    /**
     * Generate a unique slug from a given name.
     */
    public static function generateUniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;

        // If the base slug is reserved, append a suffix
        if (in_array($slug, self::RESERVED_SLUGS)) {
            $slug = $baseSlug.'-1';
        }

        $counter = 1;
        while (static::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Validate that the slug is not a reserved word.
     *
     * @throws ValidationException
     */
    protected static function validateSlug(string $slug): void
    {
        if (in_array(strtolower($slug), self::RESERVED_SLUGS)) {
            throw ValidationException::withMessages([
                'slug' => "El slug '{$slug}' es una palabra reservada y no puede utilizarse.",
            ]);
        }
    }

    /**
     * Get the public catalog URL for this company.
     */
    public function getCatalogUrlAttribute(): ?string
    {
        if (! $this->slug || ! $this->catalog_is_public) {
            return null;
        }

        return route('catalog.index', ['company' => $this->slug], false);
    }

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

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class);
    }

    public function cashCounts(): HasMany
    {
        return $this->hasMany(CashCount::class);
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

    public function canUseFeature(string $feature): bool
    {
        return app(PlanEntitlementService::class)->companyHasModule($this, $feature);
    }

    public function getPlanLimit(string $key): ?int
    {
        $plan = $this->plan;
        if (! $plan) {
            return null;
        }

        $limits = $plan->limits ?? [];
        if (array_key_exists($key, $limits)) {
            $v = $limits[$key];

            return $v === null ? null : (int) $v;
        }

        $legacy = ['max_users', 'max_transactions', 'max_products', 'max_customers'];
        if (in_array($key, $legacy, true) && isset($plan->{$key})) {
            return $plan->{$key} === null ? null : (int) $plan->{$key};
        }

        return null;
    }

    public function getLogoUrlAttribute()
    {
        if (! $this->logo) {
            return ImageUrlService::getImageUrl(null);
        }

        return ImageUrlService::getImageUrl($this->logo);
    }

    /**
     * Logo en URL absoluta (Open Graph / WhatsApp / redes).
     */
    public function getLogoUrlAbsoluteAttribute(): string
    {
        return ImageUrlService::absolutePublicUrl($this->logo_url);
    }

    /**
     * URL absoluta de imagen Open Graph para el listado del catálogo: logo reducido (<300px)
     * para que WhatsApp muestre miniatura compacta a la izquierda.
     */
    public function getCatalogOgImageUrlAbsoluteAttribute(): string
    {
        if (! $this->slug || ! $this->catalog_is_public) {
            return ImageUrlService::absolutePublicUrl($this->logo_url);
        }

        $relative = route('catalog.og-logo', ['company' => $this->slug], false);

        return ImageUrlService::absolutePublicUrl($relative);
    }
}
