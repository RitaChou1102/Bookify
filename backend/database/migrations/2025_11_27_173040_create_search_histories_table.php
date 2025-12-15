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
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            // 1. 主鍵
            $table->id('history_id');

            // 2. 外鍵欄位
            // 注意：根據 schema，這裡雖然叫 member_id，但外鍵指向 users 表
            $table->unsignedBigInteger('member_id');

            // 3. 關鍵字與時間
            // SQL: VARCHAR(500) NOT NULL
            $table->string('keyword', 500);

            // SQL: DATETIME DEFAULT CURRENT_TIMESTAMP
            $table->dateTime('search_time')->useCurrent();

            // 4. 建立索引
            $table->index('member_id', 'idx_search_member');

            // 5. 外鍵約束
            // SQL: CONSTRAINT `fk_search_member` FOREIGN KEY (`member_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
            $table->foreign('member_id', 'fk_search_member')
                  ->references('user_id')
                  ->on('users')
                  ->onDelete('cascade');

            // 注意：根據 schema，search_histories 表沒有 timestamps
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

