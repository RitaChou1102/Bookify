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

    'cloud_url' => env('CLOUDINARY_URL'),
    'upload_preset' => env('CLOUDINARY_UPLOAD_PRESET'),
    'notification_url' => env('CLOUDINARY_NOTIFICATION_URL'),
    'secure' => env('CLOUDINARY_SECURE', true),

    // [重要] Cloudinary 套件主要讀取的是這個 'cloud' 陣列
    'cloud' => [
        'cloud'  => env('CLOUDINARY_CLOUD_NAME', 'default_cloud'),
        'key'    => env('CLOUDINARY_API_KEY', 'default_key'),
        'secret' => env('CLOUDINARY_API_SECRET', 'default_secret'),
    ],
//     'cloud' => [
//         // 使用 env 的第二個參數作為測試時的預設值
//        'cloud'  => env('CLOUDINARY_CLOUD_NAME', 'test_cloud'),
//        'key'    => env('CLOUDINARY_API_KEY', 'test_key'),
//        'secret' => env('CLOUDINARY_API_SECRET', 'test_secret'),
// //     'url'        => env('CLOUDINARY_URL'),
//     ],
    
    'url' => [
        'secure' => env('CLOUDINARY_SECURE', true),
    ],
];