<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 資料表名稱確認為 'members'
        Schema::create('members', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            // 主鍵
            $table->id('member_id');

            // User ID
            $table->unsignedBigInteger('user_id');

            // 設定外鍵約束
            // 語法：foreign(欄位名稱, 自定義約束名稱)
            $table->foreign('user_id', 'fk_member_user')
                  ->references('user_id')
                  ->on('users')
                  ->onDelete('cascade');

            // 設定唯一索引
            // 語法：unique(欄位名稱, 自定義索引名稱)
            $table->unique('user_id', 'uq_member_user');

            // $table->timestamps();
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