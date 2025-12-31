<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /**
     * 指定主鍵名稱
     */
    protected $primaryKey = 'order_id';

    /**
     * 指定資料表名稱
     */
    protected $table = 'orders';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'member_id',
        'total_amount',
        'order_time',
        'business_id',
        'shipping_fee',
        'payment_method',
        'order_status',
        'coupon_id',
        'cart_id',
    ];

    /**
     * 不使用 timestamps（根據 schema）
     */
    public $timestamps = false;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'order_time' => 'datetime',
            'shipping_fee' => 'decimal:2',
            'payment_method' => 'string', // enum 值作為字串處理
            'order_status' => 'string',   // enum 值作為字串處理
        ];
    }

    /**
     * 付款方式常數
     */
    const PAYMENT_METHOD_CASH = 'Cash';
    const PAYMENT_METHOD_CREDIT_CARD = 'Credit_card';
    const PAYMENT_METHOD_BANK_TRANSFER = 'Bank_transfer';

    /**
     * 訂單狀態常數
     */
    const STATUS_RECEIVED = 'Received';
    const STATUS_PROCESSING = 'Processing';
    const STATUS_SHIPPED = 'Shipped';
    const STATUS_COMPLETED = 'Completed';
    const STATUS_CANCELLED = 'Cancelled';

    /**
     * 取得所有可用的付款方式
     * 
     * @return array
     */
    public static function getPaymentMethods(): array
    {
        return [
            self::PAYMENT_METHOD_CASH => '貨到付款',
            self::PAYMENT_METHOD_CREDIT_CARD => '信用卡',
            self::PAYMENT_METHOD_BANK_TRANSFER => '銀行轉帳',
        ];
    }

    /**
     * 取得所有可用的訂單狀態
     * 
     * @return array
     */
    public static function getOrderStatuses(): array
    {
        return [
            self::STATUS_RECEIVED => '已接收',
            self::STATUS_PROCESSING => '處理中',
            self::STATUS_SHIPPED => '已出貨',
            self::STATUS_COMPLETED => '已完成',
            self::STATUS_CANCELLED => '已取消',
        ];
    }

    /**
     * 取得訂單的買家（會員）
     */
    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'member_id');
    }

    /**
     * 取得訂單的廠商
     */
    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id', 'business_id');
    }

    /**
     * 取得訂單使用的優惠券
     */
    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id', 'coupon_id');
    }

    /**
     * 取得訂單的購物車
     */
    public function cart()
    {
        return $this->belongsTo(Cart::class, 'cart_id', 'cart_id');
    }

    /**
     * 取得訂單的所有明細
     */
    public function details()
    {
        return $this->hasMany(OrderDetail::class, 'order_id', 'order_id');
    }

    /**
     * 取得訂單的投訴
     */
    public function complains()
    {
        return $this->hasMany(Complain::class, 'order_id', 'order_id');
    }

    /**
     * 取得訂單的評價
     */
    public function reviews()
    {
        return $this->hasMany(Review::class, 'order_id', 'order_id');
    }

}

