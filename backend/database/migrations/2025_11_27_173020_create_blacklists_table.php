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
        Schema::create('blacklists', function (Blueprint $table) {
            $table->unsignedBigInteger('blacklist_id')->autoIncrement()->primary();
            $table->unsignedBigInteger('blocked_userid'); // 被封鎖者（users 表）
            $table->text('reason'); // 封鎖原因
            $table->dateTime('created_at'); // 建立時間（根據 Schema，黑名單只需要建立時間，不需要 updated_at）
            $table->unsignedBigInteger('banned_by'); // 管理員ID

            // 外鍵約束
            $table->foreign('blocked_userid')->references('user_id')->on('users')->onDelete('restrict');
            $table->foreign('banned_by')->references('admin_id')->on('admins')->onDelete('restrict');
            
            // 建立索引
            $table->index('blocked_userid');
            $table->index('banned_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blacklists');
    }
};

