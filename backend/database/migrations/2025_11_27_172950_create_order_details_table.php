<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * 說明：建立訂單明細資料表
     * 設計原理：
     * - 訂單明細記錄訂單中每本書的詳細資訊
     * - 一個訂單可以有多個訂單明細（多本書）
     * - 儲存購買時的單價，避免書籍價格變動影響歷史訂單
     * - subtotal = quantity * piece_price（自動計算或手動儲存）
     */
    public function up(): void
    {
        Schema::create('order_details', function (Blueprint $table) {
            $table->unsignedBigInteger('detail_id')->autoIncrement()->primary();
            $table->unsignedBigInteger('order_id'); // 訂單ID
            $table->unsignedBigInteger('book_id'); // 書籍ID
            $table->integer('quantity'); // 購買數量
            $table->decimal('piece_price', 10, 2); // 單價（下單時的價格）
            $table->decimal('subtotal', 10, 2); // 小計（quantity * piece_price）
            $table->timestamps();

            // 外鍵約束
            $table->foreign('order_id')->references('order_id')->on('orders')->onDelete('cascade');
            $table->foreign('book_id')->references('book_id')->on('books')->onDelete('restrict');
            
            // 建立索引
            $table->index('order_id');
            $table->index('book_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_details');
    }
};

