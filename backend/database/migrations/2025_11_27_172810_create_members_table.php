<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * 說明：建立會員資料表
     * 設計原理：
     * - 會員是使用者的子類型，通過 user_id 關聯到 users 表
     * - 所有使用者基礎資訊（登入、密碼、聯絡方式）都在 users 表中
     * - members 表只存放會員特有的資訊
     * - 使用 CASCADE 刪除：當使用者被刪除時，會員資料也一併刪除
     */
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->unsignedBigInteger('member_id')->autoIncrement()->primary();
            $table->unsignedBigInteger('user_id')->unique(); // 一個使用者只能是一個會員
            $table->timestamps();

            // 外鍵約束：關聯到 users 表
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            
            // 建立索引以加快查詢速度
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};

