<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            // 設定編碼
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            // 1. 主鍵 business_id
            $table->id('business_id');

            // 2. User ID (關聯欄位)
            $table->unsignedBigInteger('user_id');

            // 3. Bank Account
            // SQL: VARCHAR(255) NOT NULL
            $table->string('bank_account', 255);

            // 4. 設定外鍵約束
            // CONSTRAINT `fk_business_user` ... ON DELETE CASCADE
            $table->foreign('user_id', 'fk_business_user')
                  ->references('user_id')
                  ->on('users')
                  ->onDelete('cascade');

            // 5. 設定唯一索引
            // UNIQUE KEY `uq_business_user`
            $table->unique('user_id', 'uq_business_user');

            // $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
