<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * 說明：建立黑名單資料表
     * 設計原理：
     * - 黑名單記錄被封鎖的使用者資訊
     * - blocked_userid 指向被封鎖的使用者（users 表）
     * - banned_by 指向執行封鎖的管理員（admins 表）
     * - created_at 記錄封鎖時間
     * - 使用 RESTRICT 刪除保護黑名單記錄
     */
    public function up(): void
    {
        Schema::create('blacklist', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            // 1. 主鍵
            $table->id('blacklist_id');

            // 2. 外鍵欄位
            $table->unsignedBigInteger('blocked_userid');
            $table->unsignedBigInteger('banned_by')->nullable();

            // 3. 封鎖原因與時間
            // SQL: TEXT NOT NULL
            $table->text('reason');

            // SQL: DATETIME DEFAULT CURRENT_TIMESTAMP
            $table->dateTime('created_at')->useCurrent();

            // 4. 建立索引
            $table->index('blocked_userid', 'idx_blacklist_blocked');

            // 5. 外鍵約束
            // SQL: CONSTRAINT `fk_blacklist_blocked` FOREIGN KEY (`blocked_userid`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
            $table->foreign('blocked_userid', 'fk_blacklist_blocked')
                  ->references('user_id')
                  ->on('users')
                  ->onDelete('cascade');

            // SQL: CONSTRAINT `fk_blacklist_bannedby` FOREIGN KEY (`banned_by`) REFERENCES `admins`(`admin_id`) ON DELETE SET NULL
            $table->foreign('banned_by', 'fk_blacklist_bannedby')
                  ->references('admin_id')
                  ->on('admins')
                  ->onDelete('set null');

            // 注意：根據 schema，blacklist 表沒有 timestamps（只有 created_at）
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blacklist');
    }
};

