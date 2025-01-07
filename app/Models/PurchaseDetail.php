<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'quantity',
        'product_price',
        'purchase_id',
        'supplier_id',
        'product_id'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'product_price' => 'decimal:2'
    ];

    /**
     * Obtiene la compra asociada
     */
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Obtiene el proveedor asociado
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Obtiene el producto asociado
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
