<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $primaryKey = 'coupon_id';
    protected $table = 'coupons';

    // 🟢 修正 1：這裡只保留一個 timestamps 定義
    public $timestamps = false;

    // 🟢 修正 2：統一使用 start_time / end_time (配合你的資料庫欄位)
    protected $fillable = [
        'code',
        'discount_type',
        'discount_value',
        'limit_price',
        'start_time',    // 資料庫欄位名
        'end_time',      // 資料庫欄位名
        'is_active',     // 建議用 is_active 或 status，需確認資料庫
        'usage_limit',   // 補上這個，不然無法設定上限
        'used_count',
        'business_id'
    ];

    protected function casts(): array
    {
        return [
            // 🟢 修正 3：對應 fillable 的名稱
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'discount_value' => 'decimal:2',
            'limit_price' => 'decimal:2',
            'usage_limit' => 'integer',
            'used_count' => 'integer',
            'is_active' => 'boolean', // 或是 'status' => 'boolean'

            // ⚠️ 如果你還沒建立 Enums 檔案，請暫時註解掉這兩行，否則會報錯 Class not found
            // 'discount_type' => \App\Enums\DiscountType::class,
            // 'coupon_type' => \App\Enums\CouponType::class,
        ];
    }

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id', 'business_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'coupon_id', 'coupon_id');
    }

    /**
     * 檢查優惠券是否可用
     */
    public function isAvailable(): bool
    {
        // 1. 檢查是否啟用
        if (isset($this->is_active) && !$this->is_active) {
            return false;
        }

        $now = now();

        // 2. 檢查開始時間 (修正變數名稱為 start_time)
        if ($this->start_time && $now->lt($this->start_time)) {
            return false;
        }

        // 3. 檢查結束時間 (修正變數名稱為 end_time)
        if ($this->end_time && $now->gt($this->end_time)) {
            return false;
        }

        // 4. 檢查使用次數上限
        if (!is_null($this->usage_limit) && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }
}