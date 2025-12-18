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
        Schema::create('admins', function (Blueprint $table) {
            // 1. 設定編碼
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            // 2. 主鍵 (簡寫語法，等同於 unsignedBigInteger + autoIncrement + primary)
            $table->id('admin_id');

            // 3. Login ID
            $table->string('login_id', 191)->unique();

            // 4. Name
            $table->string('name', 191);

            // 5. Password
            $table->string('password', 255);

            // 6. 時間戳記
            // 建議 Laravel 專案保留此欄位。
            // $table->timestamps(); 

            $table->rememberToken();
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