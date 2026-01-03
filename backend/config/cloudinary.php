<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cloudinary Configuration
    |--------------------------------------------------------------------------
    |
    | 完整設定檔，讀取我們剛剛補上的 .env 變數
    |
    */

    'cloud_url' => env('CLOUDINARY_URL'),

    'upload_preset' => env('CLOUDINARY_UPLOAD_PRESET'),

    'notification_url' => env('CLOUDINARY_NOTIFICATION_URL'),

    'secure' => env('CLOUDINARY_SECURE', true),

    'cloud' => [
        'cloud_name' => env('CLOUDINARY_CLOUD_NAME'), // 剛剛補上了
        'api_key'    => env('CLOUDINARY_API_KEY'),    // 剛剛補上了
        'api_secret' => env('CLOUDINARY_API_SECRET'), // 剛剛補上了
        'url'        => env('CLOUDINARY_URL'),
    ],

    'url' => [
        'secure' => env('CLOUDINARY_SECURE', true),
    ],

];