<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<string>
     */
    protected $fillable = [
        'sale_date',
        'total_price',
        'company_id',
        'customer_id',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sale_date' => 'date',
        'total_price' => 'decimal:2',
    ];

    /**
     * Obtiene el cliente asociado a la venta.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Obtiene la compañía asociada a la venta.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Obtiene los detalles de la venta.
     */
    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class);
    }
}
