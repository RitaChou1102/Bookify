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
            'payment_method' => 'integer',
            'order_status' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
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

    /**
     * 付款方式常數
     */
    const PAYMENT_CASH = 0;        // 貨到付款
    const PAYMENT_CREDIT_CARD = 1; // 信用卡
    const PAYMENT_BANK_TRANSFER = 2; // 銀行轉帳

    /**
     * 訂單狀態常數
     */
    const STATUS_RECEIVED = 0;   // 已接收
    const STATUS_PROCESSING = 1; // 處理中
    const STATUS_SHIPPED = 2;    // 已出貨
    const STATUS_COMPLETED = 3;  // 已完成
}

