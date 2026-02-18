<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Barcode Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for barcode generation in the system
    |
    */

    // Storage path for temporary barcode files
    'storage_path' => storage_path('app/barcodes'),

    // Default barcode format for products
    'product_barcode_format' => 'C128',
    
    // Default barcode height
    'height' => 60,
    
    // Default barcode width factor
    'width_factor' => 3,
    
    // QR Code settings
    'qrcode' => [
        'size' => 6,
        'margin' => 2,
        'format' => 'PNG'
    ]
];