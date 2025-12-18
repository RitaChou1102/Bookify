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

            // 3. Store Name (商店名稱，與 users.name 保持一致)
            // SQL: VARCHAR(255) DEFAULT NULL
            $table->string('store_name', 255)->nullable();

            // 4. Bank Account
            // SQL: VARCHAR(255) NOT NULL
            $table->string('bank_account', 255);

            // 5. Email (可選，商家聯絡信箱)
            // SQL: VARCHAR(191) DEFAULT NULL
            $table->string('email', 191)->nullable();

            // 6. Phone (可選，商家聯絡電話)
            // SQL: VARCHAR(50) DEFAULT NULL
            $table->string('phone', 50)->nullable();

            // 7. 設定外鍵約束
            // CONSTRAINT `fk_business_user` ... ON DELETE CASCADE
            $table->foreign('user_id', 'fk_business_user')
                  ->references('user_id')
                  ->on('users')
                  ->onDelete('cascade');

            // 8. 設定唯一索引
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
