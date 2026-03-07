<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $fillable = ['rate', 'source', 'currency_pair', 'fetched_at'];

    protected $casts = [
        'rate'       => 'float',
        'fetched_at' => 'datetime',
    ];

    /**
     * Obtiene la tasa de cambio actual (único registro global).
     */
    public static function current(): float
    {
        $record = static::first();
        return $record ? (float) $record->rate : 134.0;
    }

    /**
     * Obtiene el registro completo actual.
     */
    public static function currentRecord(): ?self
    {
        return static::first();
    }
}
