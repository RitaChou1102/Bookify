<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * 說明：建立投訴資料表
     * 設計原理：
     * - 投訴與訂單關聯，用戶可以對訂單進行投訴
     * - complaint_status：0=待處理，1=處理中，2=已解決
     * - result 欄位儲存處理結果
     * - 使用 RESTRICT 刪除：保護投訴記錄的完整性
     */
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            // 1. 主鍵
            $table->id('complaint_id');

            // 2. 外鍵欄位
            $table->unsignedBigInteger('order_id');

            // 3. 投訴內容
            // SQL: TEXT NOT NULL
            $table->text('content');

            // 4. 時間與狀態
            // SQL: DATETIME DEFAULT CURRENT_TIMESTAMP
            $table->dateTime('complaint_time')->useCurrent();

            // SQL: ENUM('pending','in_progress', 'resolved') NOT NULL DEFAULT 'pending'
            $table->enum('complaint_status', ['pending', 'in_progress', 'resolved'])->default('pending');

            // SQL: TEXT DEFAULT NULL
            $table->text('result')->nullable();

            // 5. 建立索引
            $table->index('order_id', 'idx_complaint_order');

            // 6. 外鍵約束
            // SQL: CONSTRAINT `fk_complaint_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`order_id`) ON DELETE CASCADE
            $table->foreign('order_id', 'fk_complaint_order')
                  ->references('order_id')
                  ->on('orders')
                  ->onDelete('cascade');

            // 注意：根據 schema，complaints 表沒有 timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};

