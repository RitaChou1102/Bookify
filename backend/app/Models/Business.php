<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasFactory;

    /**
     * 指定主鍵名稱
     */
    protected $primaryKey = 'business_id';

    /**
     * 指定資料表名稱
     */
    protected $table = 'businesses';

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
        'user_id',
        'store_name',
        'bank_account',
        'email',
        'phone',
    ];

    /**
     * 取得廠商的使用者資料
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * 取得廠商的書籍
     */
    public function books()
    {
        return $this->hasMany(Book::class, 'business_id', 'business_id');
    }

    /**
     * 取得廠商的優惠券
     */
    public function coupons()
    {
        return $this->hasMany(Coupon::class, 'business_id', 'business_id');
    }

    /**
     * 取得廠商的訂單
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'business_id', 'business_id');
    }
}

