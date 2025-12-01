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
            $table->unsignedBigInteger('coupon_id')->autoIncrement()->primary();
            $table->string('name'); // 優惠券名稱
            $table->unsignedBigInteger('business_id'); // 發行廠商ID
            $table->string('code')->unique(); // 優惠券代碼，必須唯一
            $table->text('description'); // 優惠券描述
            $table->dateTime('start_date'); // 開始時間
            $table->dateTime('end_date'); // 結束時間
            $table->tinyInteger('discount_type'); // 折扣種類：0=百分比，1=固定金額
            $table->decimal('discount_value', 10, 2); // 折扣數值
            $table->decimal('limit_price', 10, 2); // 使用優惠門檻金額
            $table->integer('usage_limit')->default(1); // 可使用次數
            $table->integer('used_count')->default(0); // 已被使用次數
            $table->tinyInteger('coupon_type'); // 優惠券種類：0=運費，1=季節性，2=特殊活動
            $table->timestamps();

            // 外鍵約束：關聯到 businesses 表
            $table->foreign('business_id')->references('business_id')->on('businesses')->onDelete('restrict');
            
            // 建立索引
            $table->index('business_id');
            $table->index('code');
            $table->index(['start_date', 'end_date']);
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

