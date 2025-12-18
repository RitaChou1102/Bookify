<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            // 1. 主鍵
            $table->id('book_id');

            // 2. 基本資訊
            // SQL: VARCHAR(500) NOT NULL
            $table->string('name', 500);

            // SQL: VARCHAR(50) NOT NULL
            $table->string('isbn', 50);

            // SQL: DATE NOT NULL
            $table->date('publish_date');

            // SQL: INT NOT NULL
            $table->integer('edition');

            // SQL: VARCHAR(255) NOT NULL
            $table->string('publisher', 255);

            // SQL: TEXT DEFAULT NULL
            $table->text('description')->nullable();

            // SQL: DECIMAL(10,2) NOT NULL DEFAULT 0.00
            $table->decimal('price', 10, 2)->default(0.00);

            // SQL: ENUM('new','used') NOT NULL DEFAULT 'new'
            $table->enum('condition', ['new', 'used'])->default('new');

            // 3. 庫存與狀態
            // SQL: INT NOT NULL DEFAULT 0
            $table->integer('stock')->default(0);

            // SQL: TINYINT(1) NOT NULL DEFAULT 0 (上架狀態)
            $table->boolean('listing')->default(0);

            // 4. 外鍵欄位 (先定義欄位，後面設約束)
            $table->unsignedBigInteger('author_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('business_id');

            // 5. 索引 (Indexes)
            $table->index('author_id', 'idx_books_author');
            $table->index('category_id', 'idx_books_category');
            $table->index('business_id', 'idx_books_business');

            // 6. 複合唯一索引 (Unique Composite Key)
            // 代表：同一間廠商(business) + 同一本ISBN + 同一種書況(new/used) 只能有一筆資料
            $table->unique(['isbn', 'condition', 'business_id'], 'uq_book_selling_unit');

            // 7. 外鍵約束 (Constraints)
            
            // Author: 刪除作者時，書本的 author_id 設為 NULL
            $table->foreign('author_id', 'fk_books_author')
                  ->references('author_id')
                  ->on('authors')
                  ->onDelete('set null');

            // Category: 刪除分類時，書本的 category_id 設為 NULL
            // 注意：根據 schema，外鍵指向 'book_categories' 表，但 SQL 中寫的是 'categories'，這裡使用 'book_categories'
            $table->foreign('category_id', 'fk_books_category')
                  ->references('category_id')
                  ->on('book_categories') 
                  ->onDelete('set null');

            // Business: 刪除廠商時，書本一併刪除 (Cascade)
            $table->foreign('business_id', 'fk_books_business')
                  ->references('business_id')
                  ->on('businesses')
                  ->onDelete('cascade');

            // 注意：根據 schema，books 表沒有 timestamps
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
