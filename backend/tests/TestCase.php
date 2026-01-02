<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    //
    protected function setUp(): void
    {
        parent::setUp();

        // 直接在所有測試開始前，就把 config 塞好塞滿
        // 確保任何時候 ServiceProvider 被觸發，都不會抓到 null
        config([
            'cloudinary.cloud' => [
                'cloud'  => 'test',
                'key'    => 'test',
                'secret' => 'test',
            ]
        ]);
    }
}
