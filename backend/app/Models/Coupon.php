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

    protected $primaryKey = 'coupon_id';
    
    // 確保欄位可以被寫入
    protected $fillable = [
        'code',
        'discount_type',
        'discount_value',
        'limit_price',
        'start_time',
        'end_time',
        'status',
        'used_count',
        'business_id'
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
        // 1. 檢查是否被刪除 (你的資料庫是用 is_deleted，不是 status)
        if ($this->is_deleted) {
            return false;
        }

        $now = now();

        // 2. 檢查開始時間
        if ($this->start_date && $now->lt($this->start_date)) {
            return false;
        }

        // 3. 檢查結束時間
        if ($this->end_date && $now->gt($this->end_date)) {
            return false;
        }

        // 4. 檢查使用次數上限 (如果有設定 usage_limit)
        if (!is_null($this->usage_limit) && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }
}