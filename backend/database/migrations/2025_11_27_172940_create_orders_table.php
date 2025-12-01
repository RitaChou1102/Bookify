<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * 說明：建立訂單資料表
     * 設計原理：
     * - 訂單是購物車結帳後的產物
     * - payment_method：0=貨到付款，1=信用卡，2=銀行轉帳
     * - order_status：0=已接收，1=處理中，2=已出貨，3=已完成
     * - coupon_id 可選，因為訂單不一定使用優惠券
     * - 使用 RESTRICT 刪除保護重要資料
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')->autoIncrement()->primary();
            $table->unsignedBigInteger('member_id'); // 買家ID
            $table->decimal('total_amount', 10, 2); // 總金額
            $table->dateTime('order_time'); // 下單時間
            $table->unsignedBigInteger('business_id'); // 廠商ID
            $table->decimal('shipping_fee', 10, 2); // 運費
            $table->tinyInteger('payment_method'); // 付款方式：0=貨到付款，1=信用卡，2=銀行轉帳
            $table->tinyInteger('order_status')->default(0); // 訂單狀態：0=已接收，1=處理中，2=已出貨，3=已完成
            $table->unsignedBigInteger('coupon_id')->nullable(); // 使用的優惠券ID（可選）
            $table->unsignedBigInteger('cart_id'); // 購物車ID
            $table->timestamps();

            // 外鍵約束
            $table->foreign('member_id')->references('member_id')->on('members')->onDelete('restrict');
            $table->foreign('business_id')->references('business_id')->on('businesses')->onDelete('restrict');
            $table->foreign('coupon_id')->references('coupon_id')->on('coupons')->onDelete('set null');
            $table->foreign('cart_id')->references('cart_id')->on('carts')->onDelete('restrict');
            
            // 建立索引以加快查詢速度
            $table->index('member_id');
            $table->index('business_id');
            $table->index('order_status');
            $table->index('order_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

