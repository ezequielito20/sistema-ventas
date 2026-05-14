<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Exceptions\RoleAlreadyExists;
use Spatie\Permission\Guard;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\PermissionRegistrar;

class Role extends SpatieRole
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'guard_name',
        'company_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'company_id' => 'integer',
    ];

    /**
     * Spatie solo valida duplicados por name+guard cuando teams=false.
     * En multi-empresa el índice único es (name, guard_name, company_id): repetimos esa lógica aquí.
     *
     * @param  array<string, mixed>  $attributes
     *
     * @throws RoleAlreadyExists
     */
    public static function create(array $attributes = [])
    {
        $attributes['guard_name'] ??= Guard::getDefaultName(static::class);

        $registrar = app(PermissionRegistrar::class);

        if ($registrar->teams) {
            return parent::create($attributes);
        }

        $query = static::query()
            ->where('name', $attributes['name'])
            ->where('guard_name', $attributes['guard_name']);

        if (array_key_exists('company_id', $attributes)) {
            if ($attributes['company_id'] === null) {
                $query->whereNull('company_id');
            } else {
                $query->where('company_id', $attributes['company_id']);
            }
        }

        if ($query->exists()) {
            throw RoleAlreadyExists::create($attributes['name'], $attributes['guard_name']);
        }

        return static::query()->create($attributes);
    }

    /**
     * Get the company that owns the role.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Scope a query to only include roles for a specific company.
     */
    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope a query to only include global roles (system roles).
     */
    public function scopeGlobal($query)
    {
        return $query->whereNull('company_id');
    }

    /**
     * Check if the role is a system role.
     */
    public function isSystemRole(): bool
    {
        return is_null($this->company_id) && in_array($this->name, ['admin', 'superadmin', 'administrator', 'root']);
    }

    /**
     * Check if the role belongs to a specific company.
     */
    public function belongsToCompany($companyId): bool
    {
        return $this->company_id == $companyId;
    }
}
