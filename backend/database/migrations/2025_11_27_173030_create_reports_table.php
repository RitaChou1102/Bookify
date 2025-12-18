<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * 說明：建立報表資料表
     * 設計原理：
     * - 報表由管理員生成，儲存各種統計數據
     * - report_type 定義報表種類（例如：銷售報表、用戶報表等）
     * - stats_data 使用 JSON 或 TEXT 格式儲存統計數據
     * - time_period_start 和 time_period_end 定義報表的時間範圍
     */
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            // 1. 主鍵
            $table->id('report_id');

            // 2. 外鍵欄位
            $table->unsignedBigInteger('admin_id')->nullable();

            // 3. 報表資訊
            // SQL: DATETIME DEFAULT CURRENT_TIMESTAMP
            $table->dateTime('generation_date')->useCurrent();

            // SQL: ENUM('sales_summary', 'inventory_status', 'user_activity', 'complaint_analysis') NOT NULL
            $table->enum('report_type', ['sales_summary', 'inventory_status', 'user_activity', 'complaint_analysis']);

            // SQL: DATETIME DEFAULT CURRENT_TIMESTAMP
            $table->dateTime('time_period_start')->useCurrent();

            // SQL: DATETIME DEFAULT CURRENT_TIMESTAMP
            $table->dateTime('time_period_end')->useCurrent();

            // SQL: TEXT DEFAULT NULL
            $table->text('stats_data')->nullable();

            // 4. 建立索引
            $table->index('admin_id', 'idx_report_admin');

            // 5. 外鍵約束
            // SQL: CONSTRAINT `fk_report_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins`(`admin_id`) ON DELETE SET NULL
            $table->foreign('admin_id', 'fk_report_admin')
                  ->references('admin_id')
                  ->on('admins')
                  ->onDelete('set null');

            // 注意：根據 schema，reports 表沒有 timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};

