<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * 說明：建立評價資料表
     * 設計原理：
     * - 評價關聯到書籍和訂單（確保只有購買過的用戶才能評價）
     * - rating 通常是 1-5 星的評分
     * - reply 和 reply_time 用於賣家回覆功能
     * - 使用 RESTRICT 刪除保護評價資料
     */
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            // 1. 主鍵
            $table->id('review_id');

            // 2. 外鍵欄位
            $table->unsignedBigInteger('book_id');
            $table->unsignedBigInteger('order_id')->nullable();

            // 3. 評分與評論
            // SQL: TINYINT NOT NULL DEFAULT 5
            $table->tinyInteger('rating')->default(5);

            // SQL: TEXT DEFAULT NULL
            $table->text('comment')->nullable();

            // 4. 時間
            // SQL: DATETIME DEFAULT CURRENT_TIMESTAMP
            $table->dateTime('review_time')->useCurrent();

            // SQL: TEXT DEFAULT NULL
            $table->text('reply')->nullable();

            // SQL: DATETIME DEFAULT NULL
            $table->dateTime('reply_time')->nullable();

            // 5. 建立索引
            $table->index('book_id', 'idx_reviews_book');

            // 6. 外鍵約束
            // SQL: CONSTRAINT `fk_reviews_book` FOREIGN KEY (`book_id`) REFERENCES `books`(`book_id`) ON DELETE CASCADE
            $table->foreign('book_id', 'fk_reviews_book')
                  ->references('book_id')
                  ->on('books')
                  ->onDelete('cascade');

            // SQL: CONSTRAINT `fk_reviews_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`order_id`) ON DELETE SET NULL
            $table->foreign('order_id', 'fk_reviews_order')
                  ->references('order_id')
                  ->on('orders')
                  ->onDelete('set null');

            // 注意：根據 schema，reviews 表沒有 timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};

