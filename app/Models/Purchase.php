<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<string>
     */
    protected $fillable = [
        'purchase_date',
        'payment_receipt',
        'quantity',
        'total_price',
        'supplier_id',
        'product_id',
        'company_id',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'purchase_date' => 'date',
        'total_price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    /**
     * Obtiene el proveedor asociado a la compra.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Obtiene el producto asociado a la compra.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Obtiene la compañía asociada a la compra.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Scope para filtrar compras por compañía
     */
    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Calcula el total de compras para un período específico
     */
    public static function getTotalPurchases($companyId, $startDate = null, $endDate = null)
    {
        $query = self::byCompany($companyId);
        
        if ($startDate && $endDate) {
            $query->whereBetween('purchase_date', [$startDate, $endDate]);
        }
        
        return $query->sum('total_price');
    }
}
