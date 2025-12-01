<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * 說明：建立購物車內容資料表
     * 設計原理：
     * - 購物車內容（Cart Item）是購物車中的單一商品項目
     * - 一個購物車可以包含多個商品項目（一對多關係）
     * - 需要在 carts 表之後建立，因為有外鍵依賴
     * - 儲存每本書的數量、單價和小計
     * - 單價和總價都儲存，避免因書籍價格變動而影響已加入購物車的商品
     */
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->unsignedBigInteger('cart_item_id')->autoIncrement()->primary();
            $table->unsignedBigInteger('cart_id'); // 購物車ID（關聯到 carts 表）
            $table->unsignedBigInteger('book_id'); // 書籍ID
            $table->integer('quantity'); // 數量
            $table->decimal('price', 10, 2); // 單價（加入購物車時的價格）
            $table->decimal('subtotal', 10, 2); // 小計（quantity * price）
            $table->timestamps();

            // 外鍵約束
            $table->foreign('cart_id')->references('cart_id')->on('carts')->onDelete('cascade');
            $table->foreign('book_id')->references('book_id')->on('books')->onDelete('cascade');
            
            // 建立索引
            $table->index('cart_id');
            $table->index('book_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};

