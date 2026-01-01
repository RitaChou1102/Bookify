<?php 

return [

    /*
    |--------------------------------------------------------------------------
    | Cloudinary Configuration
    |--------------------------------------------------------------------------
    |
    | 補上了套件所需的巢狀結構。
    |
    */

    'cloud_url' => null,
    'upload_preset' => env('CLOUDINARY_UPLOAD_PRESET'),
    'notification_url' => env('CLOUDINARY_NOTIFICATION_URL'),
    'secure' => env('CLOUDINARY_SECURE', true),

    // [重要] Cloudinary 套件主要讀取的是這個 'cloud' 陣列
    'cloud' => [
        'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
        'api_key'    => env('CLOUDINARY_API_KEY'),
        'api_secret' => env('CLOUDINARY_API_SECRET'),
        'url'        => env('CLOUDINARY_URL'),
    ],
    
    'url' => [
        'secure' => env('CLOUDINARY_SECURE', true),
    ],
];