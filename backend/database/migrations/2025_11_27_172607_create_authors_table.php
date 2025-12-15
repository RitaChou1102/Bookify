<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('authors', function (Blueprint $table) {
            // 1. 設定編碼
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            // 2. 主鍵 (使用簡潔寫法，效果等同於 BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY)
            $table->id('author_id');

            // 3. Name
            $table->string('name', 255);

            // 4. 時間戳記
            // $table->timestamps(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('authors');
    }
};