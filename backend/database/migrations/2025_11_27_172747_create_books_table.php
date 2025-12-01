<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->unsignedBigInteger('book_id')->autoIncrement()->primary(); // 使用 book_id 作為主鍵名稱
            $table->string('name'); // 書籍名稱
            $table->unsignedBigInteger('author_id'); // 作者ID，必填
            $table->string('isbn'); // ISBN，必填
            $table->date('publish_date'); // 出版日期
            $table->integer('edition'); // 版次
            $table->string('publisher'); // 出版商
            $table->text('description'); // 描述
            $table->decimal('price', 10, 2); // 單價
            $table->string('condition'); // 書況
            $table->unsignedBigInteger('category_id'); // 書籍類別ID，必填
            $table->unsignedBigInteger('business_id'); // 上架者(廠商ID)，必填
            $table->boolean('listing')->default(true); // 上架狀態
            $table->timestamps();

            // 外鍵約束
            $table->foreign('author_id')->references('author_id')->on('authors')->onDelete('restrict');
            $table->foreign('category_id')->references('category_id')->on('book_categories')->onDelete('restrict');
            $table->foreign('business_id')->references('business_id')->on('businesses')->onDelete('restrict');
            // 注意：封面圖片通過 images 表的 book_id 關聯查詢，index=0 的圖片作為封面
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
