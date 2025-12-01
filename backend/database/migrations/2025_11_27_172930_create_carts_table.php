<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * 說明：建立購物車資料表
     * 設計原理：
     * - 每個會員有一個購物車（一對一關係）
     * - 購物車可以包含多個購物車項目（通過 cart_items 表的 cart_id 關聯）
     * - 使用 CASCADE 刪除：當會員被刪除時，購物車也一併刪除
     */
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->unsignedBigInteger('cart_id')->autoIncrement()->primary();
            $table->unsignedBigInteger('member_id')->unique(); // 會員ID，每個會員只有一個購物車
            $table->timestamps();

            // 外鍵約束：關聯到 members 表
            $table->foreign('member_id')->references('member_id')->on('members')->onDelete('cascade');
            
            // 建立索引
            $table->index('member_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};

