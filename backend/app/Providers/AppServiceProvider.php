<?php

namespace App\Providers;

use Cloudinary\Cloudinary;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // 確保配置檔案已被讀取
            $this->mergeConfigFrom(
                __DIR__ . '/../../config/cloudinary.php', 'cloudinary'
            );
            $this->app->singleton('cloudinary', function ($app) {
                return new Cloudinary([
                    'cloud' => [
                        'cloud_name' => config('cloudinary.cloud_name'),
                        'api_key'    => config('cloudinary.api_key'),
                        'api_secret' => config('cloudinary.api_secret'),
                        'secure'     => config('cloudinary.secure', true) // 使用 config 檔案的值
                    ]
                ]);
            });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
