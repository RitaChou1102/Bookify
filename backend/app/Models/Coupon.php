<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    /**
     * 指定主鍵名稱
     */
    protected $primaryKey = 'coupon_id';

    /**
     * 指定資料表名稱
     */
    protected $table = 'coupons';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'business_id',
        'code',
        'description',
        'start_date',
        'end_date',
        'discount_type',
        'discount_value',
        'limit_price',
        'usage_limit',
        'used_count',
        'coupon_type',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'discount_type' => 'integer',
            'discount_value' => 'decimal:2',
            'limit_price' => 'decimal:2',
            'usage_limit' => 'integer',
            'used_count' => 'integer',
            'coupon_type' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * 取得優惠券的發行廠商
     */
    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id', 'business_id');
    }

    /**
     * 取得使用此優惠券的訂單
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'coupon_id', 'coupon_id');
    }

    /**
     * 檢查優惠券是否可用
     */
    public function isAvailable(): bool
    {
        $now = now();
        return $this->start_date <= $now 
            && $this->end_date >= $now 
            && $this->used_count < $this->usage_limit;
    }
}

