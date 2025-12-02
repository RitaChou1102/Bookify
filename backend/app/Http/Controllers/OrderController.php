<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // 結帳 (Checkout)
    public function checkout(Request $request)
    {
        $user = $request->user();
        $member = $user->member;
        
        if (!$member || !$member->cart || $member->cart->items->isEmpty()) {
            return response()->json(['message' => '購物車是空的'], 400);
        }

        $cart = $member->cart;
        
        // 開始資料庫交易 (Transaction)，確保資料一致性
        return DB::transaction(function () use ($request, $member, $cart) {
            $totalAmount = 0;
            $shippingFee = 60; // 假設固定運費，實際可依需求調整
            
            // 1. 檢查庫存並計算總額
            foreach ($cart->items as $item) {
                if ($item->book->stock < $item->quantity) {
                    throw new \Exception("書籍 {$item->book->name} 庫存不足");
                }
                $totalAmount += $item->subtotal;
            }

            // 2. 處理優惠券 (如果有)
            $couponId = null;
            if ($request->has('coupon_code')) {
                $coupon = Coupon::where('code', $request->coupon_code)->first();
                if ($coupon && $coupon->isAvailable() && $totalAmount >= $coupon->limit_price) {
                    $couponId = $coupon->coupon_id;
                    // 簡易折扣邏輯
                    if ($coupon->discount_type == 0) { // 百分比
                        $discount = $totalAmount * ($coupon->discount_value / 100);
                    } else { // 固定金額
                        $discount = $coupon->discount_value;
                    }
                    $totalAmount -= $discount;
                    $coupon->increment('used_count');
                }
            }

            $finalAmount = $totalAmount + $shippingFee;

            // 3. 建立訂單
            // 注意：這裡簡化為一張訂單。若購物車包含不同廠商書籍，通常需拆單，這裡假設為單一廠商或平台統一單。
            // 為了範例簡單，我們取第一本書的廠商作為訂單廠商
            $businessId = $cart->items->first()->book->business_id; 

            $order = Order::create([
                'member_id' => $member->member_id,
                'business_id' => $businessId,
                'total_amount' => $finalAmount,
                'shipping_fee' => $shippingFee,
                'payment_method' => $request->payment_method ?? Order::PAYMENT_CASH,
                'order_status' => Order::STATUS_RECEIVED,
                'coupon_id' => $couponId,
                'cart_id' => $cart->cart_id, // 記錄來源購物車
                'order_time' => now(),
            ]);

            // 4. 建立訂單明細並扣除庫存
            foreach ($cart->items as $item) {
                OrderDetail::create([
                    'order_id' => $order->order_id,
                    'book_id' => $item->book_id,
                    'quantity' => $item->quantity,
                    'piece_price' => $item->price,
                    'subtotal' => $item->subtotal,
                ]);

                // 扣庫存
                $item->book->decrement('stock', $item->quantity);
            }

            // 5. 清空購物車
            $cart->items()->delete();

            return response()->json(['message' => '訂單建立成功', 'order_id' => $order->order_id], 201);
        });
    }

    // 查看會員訂單
    public function index(Request $request)
    {
        $orders = Order::with(['details.book', 'business'])
                       ->where('member_id', $request->user()->member->member_id)
                       ->orderByDesc('created_at')
                       ->get();
        return response()->json($orders);
    }

    // 查看單一訂單
    public function show($id)
    {
        return Order::with(['details.book'])->findOrFail($id);
    }
    
    // 更新訂單狀態 (廠商用)
    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        // 驗證是否為該廠商的訂單
        if ($request->user()->business->business_id !== $order->business_id) {
             return response()->json(['message' => '無權限'], 403);
        }
        
        $order->update(['order_status' => $request->status]);
        return response()->json(['message' => '訂單狀態已更新']);
    }
}