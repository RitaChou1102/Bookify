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
        // 👇 [重點] 加這個判斷：如果資料庫還沒有 reviews 表，才執行建立
        if (!Schema::hasTable('reviews')) {
            Schema::create('reviews', function (Blueprint $table) {
                $table->id('review_id'); // 主鍵
                $table->foreignId('user_id')->constrained('users', 'user_id')->onDelete('cascade');
                $table->foreignId('book_id')->constrained('books', 'book_id')->onDelete('cascade');
                $table->foreignId('order_id')->constrained('orders', 'order_id')->onDelete('cascade');
                
                $table->integer('rating')->comment('1-5星');
                $table->text('comment')->nullable();
                $table->timestamp('review_time')->useCurrent();
                
                $table->timestamps(); // created_at, updated_at
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};