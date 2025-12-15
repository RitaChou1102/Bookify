<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
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
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            // 1. 主鍵
            $table->id('detail_id');

            // 2. 外鍵欄位
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('book_id');

            // 3. 數量與價格
            // SQL: INT DEFAULT 1
            $table->integer('quantity')->default(1);

            // SQL: DECIMAL(10,2) NOT NULL DEFAULT 0.00
            $table->decimal('piece_price', 10, 2)->default(0.00);

            // 4. [關鍵] 虛擬生成欄位 (Virtual Column)
            // SQL: subtotal DECIMAL(10,2) AS (quantity * piece_price) VIRTUAL
            // Laravel 支援 virtualAs 方法來建立這種欄位
            $table->decimal('subtotal', 10, 2)->virtualAs('quantity * piece_price');

            // 5. 時間戳記
            // SQL: DATETIME DEFAULT CURRENT_TIMESTAMP (只有 created_at)
            $table->dateTime('created_at')->useCurrent();

            // 6. 建立索引 (Indexes)
            $table->index('order_id', 'idx_orderdetails_order');
            $table->index('book_id', 'idx_orderdetails_book');

            // 7. 外鍵約束 (Constraints)

            // Order: 訂單刪除時，詳情一併刪除 (Cascade)
            $table->foreign('order_id', 'fk_orderdetails_order')
                ->references('order_id')
                ->on('orders')
                ->onDelete('cascade');

            // Book: 書籍如果還有訂單紀錄，禁止刪除該書籍 (Restrict)
            // 這能保護歷史訂單資料的完整性
            $table->foreign('book_id', 'fk_orderdetails_book')
                ->references('book_id')
                ->on('books')
                ->onDelete('restrict');
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

