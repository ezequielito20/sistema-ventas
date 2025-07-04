<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Currency extends Model
{
    protected $fillable = [
        'country_id',
        'name',
        'code',
        'precision',
        'symbol',
        'symbol_native',
        'symbol_first',
        'decimal_mark',
        'thousands_separator',
    ];

    protected $casts = [
        'precision' => 'integer',
        'symbol_first' => 'integer',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
