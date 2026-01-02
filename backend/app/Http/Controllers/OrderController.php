<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Cart;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // 結帳 (Checkout)
    public function checkout(Request $request)
    {
        $user = $request->user();
        $member = $user->member; // 這裡已經取得 member 物件
        
        if (!$member) {
            return response()->json(['message' => '非會員無法結帳'], 403);
        }

        // [修正重點] 必須使用 member_id 來尋找購物車，而不是 user_id
        $cart = Cart::where('member_id', $member->member_id)->first();
        
        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['message' => '購物車是空的'], 400);
        }
        
        // 開始資料庫交易
        return DB::transaction(function () use ($request, $member, $cart) {
            $totalAmount = 0;
            $shippingFee = 60; 
            
            // 1. 計算商品總額
            foreach ($cart->items as $item) {
                if ($item->book->stock < $item->quantity) {
                    throw new \Exception("書籍 {$item->book->name} 庫存不足");
                }
                $totalAmount += $item->subtotal;
            }

            // 2. 處理優惠券
            $couponId = null;
            if ($request->filled('coupon_code')) {
                $coupon = Coupon::where('code', $request->coupon_code)->first();

                // (A) 驗證存在與可用性
                if (!$coupon) {
                    return response()->json(['message' => '無效的優惠券代碼'], 400);
                }
                
                if (!$coupon->isAvailable()) {
                    return response()->json(['message' => '此優惠券已過期或無法使用'], 400);
                }

                // (B) 驗證門檻
                if ($totalAmount < $coupon->limit_price) {
                    return response()->json(['message' => "金額未達門檻 (需滿 {$coupon->limit_price})"], 400);
                }

                // (C) 計算折扣
                $couponId = $coupon->coupon_id;
                $discount = 0;
                
                // [修正] 改用字串判斷，對應資料庫 ENUM 的值
                if ($coupon->discount_type === 'percent_off') { 
                    // 百分比折扣 (例如 10 代表 10% off)
                    $discount = $totalAmount * ($coupon->discount_value / 100);
                } else { 
                    // 固定金額折扣 (fixed)
                    $discount = $coupon->discount_value;
                }

                $totalAmount -= $discount;

                // 防止變成負數
                if ($totalAmount < 0) {
                    $totalAmount = 0;
                }

                $coupon->increment('used_count');
            }

            // 加上運費
            $finalAmount = $totalAmount + $shippingFee;

            // 3. 建立訂單
            // 簡單處理：取第一本書的廠商作為訂單廠商 (實務上若有多廠商需拆單，這裡暫時簡化)
            $firstItemBook = $cart->items->first()->book;
            $businessId = $firstItemBook ? $firstItemBook->business_id : null;
            
            if (!$businessId) {
                 // 若找不到廠商，視需求拋出錯誤或給定預設值
                 throw new \Exception("無法確認書籍廠商資訊，無法建立訂單");
            }

            $order = Order::create([
                'member_id' => $member->member_id,
                'business_id' => $businessId,
                'total_amount' => $finalAmount,
                'shipping_fee' => $shippingFee,
                'payment_method' => $request->payment_method ?? 'Cash',
                'order_status' => 'Received',
                'coupon_id' => $couponId,
                'cart_id' => $cart->cart_id,
                'order_time' => now(),
            ]);

            // 4. 建立明細
            foreach ($cart->items as $item) {
                OrderDetail::create([
                    'order_id' => $order->order_id,
                    'book_id' => $item->book_id,
                    'quantity' => $item->quantity,
                    'piece_price' => $item->price,
                ]);
                $item->book->decrement('stock', $item->quantity);
            }

            // 5. 清空購物車
            $cart->items()->delete();

            return response()->json(['message' => '訂單建立成功', 'order_id' => $order->order_id], 201);
        });
    }

    // 其他原本的方法保持不變...
    public function index(Request $request)
    {
        $orders = Order::with(['details.book.coverImage', 'business'])
                       ->where('member_id', $request->user()->member->member_id)
                       ->orderByDesc('order_time')
                       ->get();
        return response()->json($orders);
    }

    public function show($id)
    {
        return Order::with(['details.book.coverImage', 'business'])->findOrFail($id);
    }
    
    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        if ($request->user()->business->business_id !== $order->business_id) {
             return response()->json(['message' => '無權限'], 403);
        }
        $order->update(['order_status' => $request->status]);
        return response()->json(['message' => '訂單狀態已更新']);
    }
}