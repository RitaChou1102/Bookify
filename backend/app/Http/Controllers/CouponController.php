<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;

use Illuminate\Validation\Rules\Enum;
use App\Enums\DiscountType;
use App\Enums\CouponType;

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
    public function getBusinessCoupons(Request $request, $businessId)
    {
        $user = $request->user();
        $role = $user->role;
        $query = Coupon::where('business_id', $businessId);

        if ($role !== 'business' || $user->business?->business_id != $businessId){
            $now = now();
            $query->where('is_deleted', 0)
                  ->where('start_date', '<=', $now)
                  ->where(function ($q) use ($now) {
                      $q->whereNull('end_date')->orWhere('end_date', '>=', $now);
                  });
        }

        return $query->get();
    }

    public function store(Request $request)
    {
        // 1. 驗證資料
        $validated = $request->validate([
            'name'           => 'required|string|max:100',
            'code'           => 'required|string|unique:coupons,code', // 不能跟現有的重複
            'discount_type'  => ['required', new Enum(DiscountType::class)],
            'discount_value' => 'required|numeric|min:0',
            'limit_price'    => 'required|numeric|min:0',
            'start_date'     => 'required|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',
            'usage_limit'    => 'nullable|integer|min:1',
            'coupon_type'    => ['required', new Enum(CouponType::class)],
        ]);

        // 2. 注入後台資訊（安全性：不從前端拿 business_id）
        $user = $request->user();
        $validated['business_id'] = $user->business?->business_id;
        $validated['used_count'] = 0;
        $validated['is_deleted'] = 0;

        if (is_null($validated['business_id'])) {
            return response()->json(['message' => '權限不足：找不到所屬商家'], 403);
        }

        // 3. 建立
        $coupon = Coupon::create($validated);

        return response()->json(['message' => '建立成功', 'coupon' => $coupon], 201);
    }

    public function update(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);

        // 1. 權限檢查：只有該老闆可以改
        $user = $request->user();
        if (!$user->business || $user->business->business_id != $coupon->business_id) {
            return response()->json(['message' => '無權修改此優惠券'], 403);
        }

        // 2. 驗證規則
        $validated = $request->validate([
            'name'           => 'sometimes|string|max:100',
            // 重點：忽略目前的 coupon_id，否則 code 沒變時會驗證失敗
            'code'           => 'sometimes|string|unique:coupons,code,' . $id . ',coupon_id',
            'discount_type'  => ['sometimes', new Enum(DiscountType::class)],
            'discount_value' => 'sometimes|numeric|min:0',
            'limit_price'    => 'sometimes|numeric|min:0',
            'start_date'     => 'sometimes|date',
            'end_date'       => 'sometimes|nullable|date|after_or_equal:start_date',
            'usage_limit'    => 'sometimes|nullable|integer|min:1',
            'coupon_type'    => ['sometimes', new Enum(CouponType::class)],
        ]);

        // 3. 商業邏輯鎖定：如果已經有人用過了，建議限制不能改折扣類型與數值
        if ($coupon->used_count > 0 && ($request->has('discount_type') || $request->has('discount_value'))) {
            return response()->json(['message' => '已有使用紀錄，不可修改折扣內容'], 422);
        }

        $coupon->update($validated);

        return response()->json(['message' => '更新成功', 'coupon' => $coupon]);
    }
}