<?php

namespace App\Support\Home;

final class HomeProductCategories
{
    const ALL = [
        'despensa',
        'limpieza',
        'personal',
        'electrodomestico',
        'mascotas',
        'herramientas',
        'cocina',
        'infantil',
        'salud',
        'hogar',
        'electronica',
        'jardin',
        'ropa',
        'otros',
    ];

    const TRANSLATIONS = [
        'despensa' => 'Despensa',
        'limpieza' => 'Limpieza',
        'personal' => 'Higiene Personal',
        'electrodomestico' => 'Electrodoméstico',
        'mascotas' => 'Mascotas',
        'herramientas' => 'Herramientas',
        'cocina' => 'Cocina',
        'infantil' => 'Infantil',
        'salud' => 'Salud',
        'hogar' => 'Hogar',
        'electronica' => 'Electrónica',
        'jardin' => 'Jardín',
        'ropa' => 'Ropa',
        'otros' => 'Otros',
    ];

    public static function options(): array
    {
        $options = [];
        foreach (self::ALL as $value) {
            $options[$value] = self::TRANSLATIONS[$value] ?? $value;
        }

        return $options;
    }
}
