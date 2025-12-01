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
            $table->unsignedBigInteger('report_id')->autoIncrement()->primary();
            $table->unsignedBigInteger('admin_id'); // 管理員ID
            $table->date('generation_date'); // 生成日期
            $table->string('report_type'); // 報表種類
            $table->date('time_period_start'); // 報表區間（起）
            $table->date('time_period_end'); // 報表區間（末）
            $table->text('stats_data'); // 統計數據（可以用 JSON 格式）
            $table->timestamps();

            // 外鍵約束：關聯到 admins 表
            $table->foreign('admin_id')->references('admin_id')->on('admins')->onDelete('restrict');
            
            // 建立索引
            $table->index('admin_id');
            $table->index('generation_date');
            $table->index('report_type');
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

