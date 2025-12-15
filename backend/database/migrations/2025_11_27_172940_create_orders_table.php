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
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            // 1. 主鍵
            $table->id('order_id');

            // 2. 外鍵欄位 (全部允許 NULL，因為是用 SET NULL 約束)
            $table->unsignedBigInteger('member_id')->nullable();
            $table->unsignedBigInteger('business_id')->nullable();
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->unsignedBigInteger('cart_id')->nullable();

            // 3. 金額與費用
            // SQL: DECIMAL(10,2) NOT NULL
            $table->decimal('total_amount', 10, 2);
            $table->decimal('shipping_fee', 10, 2);

            // 4. 時間
            // SQL: DATETIME DEFAULT CURRENT_TIMESTAMP
            $table->dateTime('order_time')->useCurrent();

            // 5. ENUM 狀態與付款方式
            // SQL: ENUM(...) NOT NULL DEFAULT 'Cash'
            $table->enum('payment_method', ['Cash', 'Credit_card', 'Bank_transfer'])
                  ->default('Cash');

            // SQL: ENUM(...) NOT NULL DEFAULT 'Received'
            $table->enum('order_status', ['Received', 'Processing', 'Shipped', 'Completed', 'Cancelled'])
                  ->default('Received');

            // 6. 建立索引 (Indexes)
            $table->index('member_id', 'idx_orders_member');
            $table->index('business_id', 'idx_orders_business');
            $table->index('cart_id', 'idx_orders_cart');
            // SQL 沒寫 coupon_id 的索引，但通常建議加，不過這裡我遵照 SQL 不加

            // 7. 外鍵約束 (Constraints)
            
            // Member
            // ⚠️ 注意：這裡指向 'members' 表 (修正 SQL 中的單數 member)
            $table->foreign('member_id', 'fk_orders_member')
                  ->references('member_id')
                  ->on('members') 
                  ->onDelete('set null');

            // Business
            $table->foreign('business_id', 'fk_orders_business')
                  ->references('business_id')
                  ->on('businesses')
                  ->onDelete('set null');

            // Cart
            $table->foreign('cart_id', 'fk_orders_cart')
                  ->references('cart_id')
                  ->on('carts')
                  ->onDelete('set null');

            // Coupon
            $table->foreign('coupon_id', 'fk_orders_coupon')
                  ->references('coupon_id')
                  ->on('coupons')
                  ->onDelete('set null');

            // $table->timestamps();
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

