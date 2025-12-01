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
            $table->unsignedBigInteger('image_id')->autoIncrement()->primary();
            $table->unsignedBigInteger('book_id'); // 關聯到書籍
            $table->integer('index')->default(0); // 順序，0 通常是封面
            $table->string('image_url'); // 圖片網址
            $table->timestamps();

            // 外鍵約束：關聯到 books 表
            $table->foreign('book_id')->references('book_id')->on('books')->onDelete('cascade');
            
            // 建立索引以加快查詢速度
            $table->index(['book_id', 'index']);
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

