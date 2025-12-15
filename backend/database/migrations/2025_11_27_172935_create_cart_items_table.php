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
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            // 1. 主鍵
            $table->id('cart_item_id');

            // 2. 外鍵欄位
            $table->unsignedBigInteger('cart_id');
            $table->unsignedBigInteger('book_id');

            // 3. 數量與價格
            // SQL: INT NOT NULL DEFAULT 1
            $table->integer('quantity')->default(1);

            // SQL: DECIMAL(10,2) NOT NULL DEFAULT 0.00
            $table->decimal('price', 10, 2)->default(0.00);

            // SQL: DECIMAL(10,2) NOT NULL DEFAULT 0.00
            $table->decimal('subtotal', 10, 2)->default(0.00);

            // 4. 建立索引
            $table->index('cart_id', 'idx_cartitems_cart');
            $table->index('book_id', 'idx_cartitems_book');

            // 5. 外鍵約束
            $table->foreign('cart_id', 'fk_cartitems_cart')
                  ->references('cart_id')
                  ->on('carts')
                  ->onDelete('cascade');
            
            $table->foreign('book_id', 'fk_cartitems_book')
                  ->references('book_id')
                  ->on('books')
                  ->onDelete('cascade');

            // 注意：根據 schema，cart_items 表沒有 timestamps
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

