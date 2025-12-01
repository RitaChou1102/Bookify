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
            $table->unsignedBigInteger('review_id')->autoIncrement()->primary();
            $table->unsignedBigInteger('book_id'); // 被評價的書籍ID
            $table->unsignedBigInteger('order_id'); // 所屬訂單編號
            $table->integer('rating'); // 評分（通常是 1-5）
            $table->text('comment'); // 評論內容
            $table->dateTime('review_time'); // 評價時間
            $table->text('reply')->nullable(); // 賣家回覆內容（可選）
            $table->dateTime('reply_time')->nullable(); // 賣家回覆時間（可選）
            $table->timestamps();

            // 外鍵約束
            $table->foreign('book_id')->references('book_id')->on('books')->onDelete('restrict');
            $table->foreign('order_id')->references('order_id')->on('orders')->onDelete('restrict');
            
            // 建立索引
            $table->index('book_id');
            $table->index('order_id');
            $table->index('rating');
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

