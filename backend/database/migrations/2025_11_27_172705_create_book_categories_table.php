<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_categories', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            // 1. 主鍵
            $table->id('category_id');

            // 2. Name
            // SQL: VARCHAR(191) NOT NULL
            $table->string('name', 191);

            // 注意：根據 schema，book_categories 表沒有 timestamps
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_categories');
    }
};