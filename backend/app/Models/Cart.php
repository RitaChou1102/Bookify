<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    /**
     * 指定主鍵名稱
     */
    protected $primaryKey = 'cart_id';

    /**
     * 指定資料表名稱
     */
    protected $table = 'carts';

    /**
     * 不使用 timestamps（根據 schema）
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'member_id',
    ];

    /**
     * 取得購物車的會員（注意：member_id 外鍵指向 members.member_id）
     */
    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'member_id');
    }

    /**
     * 取得購物車的所有商品項目
     */
    public function items()
    {
        return $this->hasMany(CartItem::class, 'cart_id', 'cart_id');
    }

    /**
     * 取得購物車的訂單（一個購物車只會產生一個訂單）
     */
    public function order()
    {
        return $this->hasOne(Order::class, 'cart_id', 'cart_id');
    }

    /**
     * 計算購物車總金額
     */
    public function getTotalAttribute(): float
    {
        return $this->items->sum('subtotal');
    }
}

