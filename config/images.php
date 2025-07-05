<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Image Upload Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for image uploads and storage
    |
    */

    'default_fallback' => 'img/no-image.svg',
    
    'max_size' => 2048, // KB
    
    'allowed_types' => ['jpeg', 'jpg', 'png', 'gif', 'webp'],
    
    'quality' => 85,
    
    'resize' => [
        'thumbnail' => [
            'width' => 150,
            'height' => 150,
        ],
        'medium' => [
            'width' => 500,
            'height' => 500,
        ],
        'large' => [
            'width' => 1200,
            'height' => 1200,
        ],
    ],
    
    'storage' => [
        'products' => 'products',
        'companies' => 'company_logos',
        'users' => 'users',
    ],
    
    'cache_duration' => 24, // hours for temporary URLs
]; 