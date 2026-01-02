<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\Enum;
use App\Enums\DiscountType;
use App\Enums\CouponType;

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
        'is_deleted',
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
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'discount_value' => 'decimal:2',
            'limit_price' => 'decimal:2',
            'usage_limit' => 'integer',
            'used_count' => 'integer',
            'is_deleted' => 'boolean',

            'discount_type' => \App\Enums\DiscountType::class,
            'coupon_type' => \App\Enums\CouponType::class,
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
        if ($this->is_deleted) return false;

        $isTimeValid = ($this->start_date <= $now) && (is_null($this->end_date) || $this->end_date >= $now);
        $hasQuota = is_null($this->usage_limit) || $this->used_count < $this->usage_limit;

        return $isTimeValid && $hasQuota;
    }
}

