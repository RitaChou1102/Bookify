<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            // 使用自定義主鍵名稱 user_id
            // 注意：$table->id() 只能創建名為 'id' 的主鍵，所以要手動創建
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->unsignedBigInteger('user_id')->autoIncrement()->primary();
            $table->string('login_id')->unique(); // 唯一索引，不是主鍵
            $table->string('name');
            $table->string('email')->unique(); // 唯一索引，不是主鍵
            $table->string('password'); // Laravel 會自動 hash
            $table->string('phone'); // 改為 string，支援各種電話號碼格式
            $table->text('address');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
