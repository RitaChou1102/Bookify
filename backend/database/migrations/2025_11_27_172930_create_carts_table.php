<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            // 設定編碼
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            // 1. 主鍵 cart_id
            $table->id('cart_id');

            // 2. Member ID (外鍵指向 members.member_id)
            $table->unsignedBigInteger('member_id');

            // 3. 建立一般索引 (對應 SQL: KEY `idx_cart_member`)
            $table->index('member_id', 'idx_cart_member');

            // 4. 設定外鍵約束
            // 對應 SQL: CONSTRAINT `fk_cart_member` FOREIGN KEY (`member_id`) REFERENCES `members`(`member_id`)
            $table->foreign('member_id', 'fk_cart_member')
                  ->references('member_id')
                  ->on('members')
                  ->onDelete('cascade');

            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};

