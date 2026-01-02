<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        // 確保至少有一個商家
        $businessId = DB::table('businesses')->value('business_id');
        
        if (!$businessId) {
             $userId = DB::table('users')->value('user_id');
             if (!$userId) return; 
             
             $businessId = DB::table('businesses')->insertGetId([
                 'user_id' => $userId,
                 'store_name' => 'Bookify 官方旗艦店',
                 'description' => '官方直營，品質保證',
                 'status' => 'approved',
                 'created_at' => now(),
                 'updated_at' => now(),
             ]);
        }

        // 建立測試用優惠券
        $coupons = [
            [
                'name' => '測試用：滿 0 折 100',
                'business_id' => $businessId,
                'code' => 'SAVE100',
                'description' => '測試用無門檻優惠券',
                'start_date' => Carbon::now()->subDays(1),
                'end_date' => Carbon::now()->addYears(10),
                // [修正] 對應資料庫 ENUM('percent_off', 'fixed')
                'discount_type' => 'fixed', 
                'discount_value' => 100,
                'limit_price' => 0,
                'usage_limit' => 9999,
                'used_count' => 0,
                'is_deleted' => 0,
                // [修正] 移除 created_at，因為資料表沒有這個欄位
            ],
            [
                'name' => '新會員折 50',
                'business_id' => $businessId,
                'code' => 'NEW50',
                'description' => '新會員專屬',
                'start_date' => Carbon::now()->subDays(1),
                'end_date' => Carbon::now()->addYears(1),
                'discount_type' => 'fixed',
                'discount_value' => 50,
                'limit_price' => 0,
                'usage_limit' => 100,
                'used_count' => 0,
                'is_deleted' => 0,
            ],
             [
                'name' => '全館 9 折',
                'business_id' => $businessId,
                'code' => 'HAPPY90',
                'description' => '開心閱讀季',
                'start_date' => Carbon::now()->subDays(1),
                'end_date' => Carbon::now()->addYears(1),
                'discount_type' => 'percent_off', // 百分比折扣
                'discount_value' => 10, // 10 代表 10% off
                'limit_price' => 500,
                'usage_limit' => 100,
                'used_count' => 0,
                'is_deleted' => 0,
            ],
        ];

        DB::table('coupons')->insert($coupons);
    }
}