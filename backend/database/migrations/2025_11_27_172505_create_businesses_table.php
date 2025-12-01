<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->unsignedBigInteger('business_id')->autoIncrement()->primary(); // 使用 business_id 作為主鍵名稱
            $table->unsignedBigInteger('user_id'); // 改為 user_id，符合你的 Schema
            $table->string('bank_account'); // 新增銀行帳戶欄位
            $table->timestamps();

            // 外鍵關聯到 users 表，使用 user_id
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
