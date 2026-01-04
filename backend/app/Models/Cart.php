<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $primaryKey = 'cart_id';
    protected $table = 'carts';
    public $timestamps = false;

    protected $fillable = [
        'member_id', // 在我們的 C2C 環境中，這就是買家的 user_id
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'member_id', 'user_id');
    }

    public function items()
    {
        return $this->hasMany(CartItem::class, 'cart_id', 'cart_id');
    }

    /**
     * ✅ 修改這裡：直接加總 CartItem 的 subtotal 欄位
     * 使用方式：$cart->total_amount
     */
    public function getTotalAmountAttribute(): float
    {
        // 因為 CartItem 已經幫我們算好 subtotal 了，這裡直接 sum 即可
        return (float) $this->items->sum('subtotal');
    }
}