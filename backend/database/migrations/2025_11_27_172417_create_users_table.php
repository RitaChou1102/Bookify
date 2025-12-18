<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            // 1. 設定編碼
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            // 2. 主鍵
            $table->id('user_id');

            // 3. Login ID
            $table->string('login_id', 191)->unique();

            // 4. Name
            // 注意：對於 business 角色，name 欄位存儲的是商店名稱（不是個人名稱）
            $table->string('name', 191);

            // 5. Email
            $table->string('email', 191)->unique();

            // 6. Password
            $table->string('password', 255);

            // 7. Phone
            $table->string('phone', 50)->nullable();

            // 8. Address
            $table->string('address', 500)->nullable();

            // 9. Role
            $table->enum('role', ['member', 'business'])->default('member');

            // 10. Laravel 預設欄位
            $table->rememberToken();
            // $table->timestamps(); 
            // created_at, updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};