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
        Schema::create('complains', function (Blueprint $table) {
            $table->unsignedBigInteger('complaint_id')->autoIncrement()->primary();
            $table->unsignedBigInteger('order_id'); // 訂單ID
            $table->text('content'); // 投訴內容
            $table->dateTime('complaint_time'); // 投訴時間
            $table->string('complaint_status')->default('0'); // 處理狀態：0=待處理，1=處理中，2=已解決
            $table->text('result')->nullable(); // 處理結果
            $table->timestamps();

            // 外鍵約束：關聯到 orders 表
            $table->foreign('order_id')->references('order_id')->on('orders')->onDelete('restrict');
            
            // 建立索引
            $table->index('order_id');
            $table->index('complaint_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complains');
    }
};

