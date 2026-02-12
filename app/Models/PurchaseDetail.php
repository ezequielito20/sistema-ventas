<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'quantity',
        'purchase_id',
        'supplier_id',
        'product_id',
        'discount_value',
        'discount_type',
        'original_price',
        'final_price'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'discount_value' => 'decimal:2',
        'original_price' => 'decimal:2',
        'final_price' => 'decimal:2'
    ];

    /**
     * Obtiene la compra asociada
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Obtiene el proveedor asociado
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Obtiene el producto asociado
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
