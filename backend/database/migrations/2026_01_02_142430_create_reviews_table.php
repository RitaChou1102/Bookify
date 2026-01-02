<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id('review_id');
            $table->unsignedBigInteger('user_id'); // 評論者
            $table->unsignedBigInteger('book_id'); // 被評論的書
            $table->unsignedBigInteger('order_id'); // 購買的訂單 (作為購買證明)
            
            $table->unsignedTinyInteger('rating'); // 星等 1-5
            $table->text('comment')->nullable();   // 文字評論
            
            $table->timestamps();

            // 外鍵約束
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('book_id')->references('book_id')->on('books')->onDelete('cascade');
            $table->foreign('order_id')->references('order_id')->on('orders')->onDelete('cascade');

            // 防止重複評論：同一個人在同一筆訂單中，對同一本書只能評一次
            $table->unique(['user_id', 'book_id', 'order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};