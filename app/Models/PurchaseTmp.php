<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseTmp extends Model
{
    use HasFactory;

    protected $fillable = [
        'quantity',
        'product_id',
        'session_id'
    ];

    protected $casts = [
        'quantity' => 'integer'
    ];

    /**
     * Obtiene el producto asociado
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope para filtrar por session_id
     */
    public function scopeBySession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }
}
