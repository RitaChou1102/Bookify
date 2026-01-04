<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Cloudinary\Cloudinary;
abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * 測試環境不要載入 Cloudinary Laravel wrapper
     */
    protected function getPackageProviders($app)
    {
        return array_filter(
            parent::getPackageProviders($app),
            fn ($provider) =>
                $provider !== \CloudinaryLabs\CloudinaryLaravel\CloudinaryServiceProvider::class
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        /**
         * 手動注入 Cloudinary SDK（真實可用）
         */
        $cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key'    => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
            ],
            'url' => [
                'secure' => true,
            ],
        ]);

        // 綁定到 container（Controller / Service 可 resolve 到）
        $this->app->instance(Cloudinary::class, $cloudinary);
    }
}
