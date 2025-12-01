<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * 說明：建立搜尋歷史資料表
     * 設計原理：
     * - 記錄會員的搜尋歷史，用於推薦系統和搜尋建議
     * - keyword 可為空，因為有些搜尋可能沒有關鍵字（例如：瀏覽全部）
     * - search_time 記錄搜尋時間，用於分析用戶行為
     * - 使用 CASCADE 刪除：當會員被刪除時，搜尋歷史也一併刪除
     */
    public function up(): void
    {
        Schema::create('search_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('history_id')->autoIncrement()->primary();
            $table->unsignedBigInteger('member_id'); // 會員ID
            $table->string('keyword')->nullable(); // 關鍵字（可選）
            $table->dateTime('search_time'); // 搜尋時間
            $table->timestamps();

            // 外鍵約束：關聯到 members 表
            $table->foreign('member_id')->references('member_id')->on('members')->onDelete('cascade');
            
            // 建立索引以加快查詢速度
            $table->index('member_id');
            $table->index('search_time');
            $table->index('keyword');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_histories');
    }
};

