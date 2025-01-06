<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<string>
     */
    protected $fillable = [
        'company_name',
        'company_address',
        'company_phone',
        'company_email',
        'supplier_name',
        'supplier_phone',
        'company_id',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Obtiene el nombre de la empresa en mayúsculas.
     */
    public function getUpperCompanyNameAttribute()
    {
        return strtoupper($this->company_name);
    }

    /**
     * Obtiene el nombre del proveedor en mayúsculas.
     */
    public function getUpperSupplierNameAttribute()
    {
        return strtoupper($this->supplier_name);
    }

    /**
     * Obtiene la información completa del proveedor.
     */
    public function getFullInfoAttribute()
    {
        return "{$this->company_name} - {$this->supplier_name}";
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
