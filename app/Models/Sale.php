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
        'note',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sale_date' => 'datetime',
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

    /**
     * Obtiene el número de factura formateado basado en la empresa
     */
    public function getFormattedInvoiceNumber()
    {
        // Obtener todas las ventas de la misma empresa ordenadas por ID
        $salesCount = Sale::where('company_id', $this->company_id)
                         ->where('id', '<=', $this->id)
                         ->count();
        
        // Formatear el número con ceros a la izquierda (8 dígitos)
        return str_pad($salesCount, 8, '0', STR_PAD_LEFT);
    }
}
