<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $primaryKey = 'order_id';
    protected $table = 'orders';
    public $timestamps = false;

    /**
     * 1. 修正 Fillable：補上收件人資訊，以免結帳時寫入失敗
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
        'recipient_name',    // 新增
        'recipient_phone',   // 新增
        'recipient_address', // 新增
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'order_time' => 'datetime',
            'shipping_fee' => 'decimal:2',
            // 如果你還沒建立 Enums，暫時註解掉這行以免報錯
            // 'order_status' => \App\Enums\OrderStatus::class, 
        ];
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'member_id');
    }

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id', 'business_id');
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id', 'coupon_id');
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class, 'cart_id', 'cart_id');
    }

    /**
     * 🟢 關鍵修正：將方法名稱從 details 改為 orderDetails
     * 這樣 Controller 寫 with('orderDetails') 才能抓到
     */
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'order_id', 'order_id');
    }

    /**
     * 保留舊名稱 (details) 作為別名，以防其他地方有用到
     */
    public function details()
    {
        return $this->orderDetails();
    }

    public function complains()
    {
        return $this->hasMany(Complain::class, 'order_id', 'order_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'order_id', 'order_id');
    }
}