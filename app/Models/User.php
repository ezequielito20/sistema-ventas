<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected static function booted(): void
    {
        static::saving(function (User $user) {
            if ($user->isDirty('is_super_admin') && $user->is_super_admin) {
                // Solo el usuario ID 1 puede ser super admin
                if ($user->id !== 1) {
                    $user->is_super_admin = false;
                }
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'company_id',
        'security_questions_setup',
        'is_super_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->is_super_admin === true || $this->hasRole('super-admin');
    }

    /**
     * Consola Super Admin (planes, empresas, pagos): super admin y empresa plataforma.
     */
    public function canAccessPlatformConsole(): bool
    {
        if (! $this->isSuperAdmin()) {
            return false;
        }

        $platformCompanyId = config('saas.platform_company_id');

        if ($platformCompanyId === null || $platformCompanyId === 0) {
            return true;
        }

        return (int) $this->company_id === (int) $platformCompanyId;
    }

    public function scopeSuperAdmins($query)
    {
        return $query->where('is_super_admin', true);
    }
}
