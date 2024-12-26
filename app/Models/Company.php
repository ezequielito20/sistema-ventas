<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
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
        'nit'
    ];

    protected $casts = [
        'tax_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
