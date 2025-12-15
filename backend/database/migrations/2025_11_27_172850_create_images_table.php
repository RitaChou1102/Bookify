<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * 說明：建立圖片資料表
     * 設計原理：
     * - 一個書籍可以有多張圖片（封面、內頁等）
     * - 通過 index 欄位控制圖片顯示順序（0 通常是封面）
     * - book_id 關聯到 books 表
     * - 使用 RESTRICT 刪除：防止書籍有圖片時被刪除（資料完整性保護）
     */
    public function up(): void
    {
        Schema::create('images', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            // 1. 主鍵
            $table->id('image_id');

            // 2. 外鍵欄位
            $table->unsignedBigInteger('book_id');

            // 3. 圖片索引與 URL
            // SQL: INT DEFAULT 0
            $table->integer('image_index')->default(0);

            // SQL: VARCHAR(1000) NOT NULL
            $table->string('image_url', 1000);

            // 4. 建立索引
            $table->index('book_id', 'idx_images_book');

            // 5. 複合唯一索引
            // SQL: UNIQUE KEY `uq_book_sequence` (`book_id`, `image_index`)
            $table->unique(['book_id', 'image_index'], 'uq_book_sequence');

            // 6. 外鍵約束
            // SQL: CONSTRAINT `fk_image_book` FOREIGN KEY (`book_id`) REFERENCES `books`(`book_id`) ON DELETE CASCADE
            $table->foreign('book_id', 'fk_image_book')
                  ->references('book_id')
                  ->on('books')
                  ->onDelete('cascade');

            // 注意：根據 schema，images 表沒有 timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};

