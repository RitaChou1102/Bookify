<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\OrderStatus;
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
            'order_status' => \App\Enums\OrderStatus::class,
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

