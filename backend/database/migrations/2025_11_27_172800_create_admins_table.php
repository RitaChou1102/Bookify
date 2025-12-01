<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * 說明：建立管理員資料表
     * 設計原理：
     * - 管理員表是獨立存在的，有自己的登入系統
     * - admin_id 作為主鍵
     * - login_id 必須唯一，用於登入驗證
     * - 可以選擇性地關聯到 users 表（如果管理員也是系統使用者）
     */
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->unsignedBigInteger('admin_id')->autoIncrement()->primary();
            $table->string('login_id')->unique(); // 管理員登入ID，唯一
            $table->string('password'); // 密碼（Laravel 會自動 hash）
            $table->string('name'); // 管理員名稱
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};

