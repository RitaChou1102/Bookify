<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    // 驗證優惠券
    public function validateCode($code)
    {
        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            return response()->json(['message' => '優惠券不存在', 'valid' => false], 404);
        }

        if (!$coupon->isAvailable()) {
            return response()->json(['message' => '優惠券已過期或失效', 'valid' => false], 400);
        }

        return response()->json(['message' => '優惠券有效', 'valid' => true, 'coupon' => $coupon]);
    }
    
    // 取得廠商的所有優惠券
    public function getBusinessCoupons($businessId)
    {
        return Coupon::where('business_id', $businessId)->get();
    }
}