<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * 說明：建立優惠券資料表
     * 設計原理：
     * - 優惠券由廠商發行，通過 business_id 關聯
     * - discount_type：0=百分比折扣，1=固定金額折扣
     * - coupon_type：0=運費優惠，1=季節性，2=特殊活動
     * - usage_limit 和 used_count 用於控制優惠券使用次數
     * - 使用軟刪除機制保護歷史數據
     */
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            // 1. 主鍵
            $table->id('coupon_id');

            // 2. 基本欄位
            $table->string('name', 255);
            $table->unsignedBigInteger('business_id');
            
            // Code: 唯一索引
            // SQL: VARCHAR(191) NOT NULL, UNIQUE KEY `uq_coupon_code`
            $table->string('code', 191)->unique('uq_coupon_code');

            // Description: TEXT DEFAULT NULL
            $table->text('description')->nullable();

            // 3. 日期設定
            // SQL: DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            $table->dateTime('start_date')->useCurrent();
            
            // SQL: DATETIME DEFAULT NULL
            $table->dateTime('end_date')->nullable();

            // 4. 折扣設定 (Enum & Decimal)
            // SQL: ENUM('percent_off', 'fixed') DEFAULT 'percent_off'
            $table->enum('discount_type', ['percent_off', 'fixed'])->default('percent_off');

            // SQL: DECIMAL(10,2) DEFAULT 0.00
            $table->decimal('discount_value', 10, 2)->default(0.00);
            
            // SQL: DECIMAL(10,2) DEFAULT 0.00
            $table->decimal('limit_price', 10, 2)->default(0.00);

            // 5. 使用限制
            // SQL: INT DEFAULT 1 (未指定 NOT NULL，所以是 nullable)
            $table->integer('usage_limit')->nullable()->default(1);
            
            // SQL: INT DEFAULT 0
            $table->integer('used_count')->nullable()->default(0);

            // 6. 優惠券類型
            // SQL: ENUM(...) DEFAULT 'shipping'
            $table->enum('coupon_type', ['shipping', 'seasonal', 'special_event'])->default('shipping');

            // 7. 軟刪除標記
            // SQL: TINYINT(1) DEFAULT 0
            // 注意：這是手動的 flag，不是 Laravel 標準的 deleted_at timestamp
            $table->tinyInteger('is_deleted')->default(0);

            // 8. 索引與外鍵
            // KEY `idx_coupon_business`
            $table->index('business_id', 'idx_coupon_business');

            // CONSTRAINT `fk_coupon_business` ... ON DELETE CASCADE
            $table->foreign('business_id', 'fk_coupon_business')
                  ->references('business_id')
                  ->on('businesses')
                  ->onDelete('cascade');

            // 注意：根據 schema，coupons 表沒有 timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};

