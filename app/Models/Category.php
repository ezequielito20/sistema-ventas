<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'description',
        'company_id'
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Obtener el nombre de la categoría en mayúsculas.
     *
     * @return string
     */
    public function getUpperNameAttribute(): string
    {
        return strtoupper($this->name);
    }

    /**
     * Obtener la descripción formateada.
     *
     * @return string
     */
    public function getFormattedDescriptionAttribute(): string
    {
        return $this->description ?? 'Sin descripción';
    }
}
