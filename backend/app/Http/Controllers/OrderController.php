<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // 1. 結帳 (Checkout)
    public function checkout(Request $request)
    {
        $user = $request->user();

        // 確保抓到正確的 member_id
        $member = $user->member;
        if (!$member) {
            return response()->json(['message' => '找不到會員資料，請確認登入狀態'], 400);
        }

        $cart = Cart::where('member_id', $member->member_id)->first();
        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['message' => '購物車內無商品'], 400);
        }

        try {
            return DB::transaction(function () use ($request, $member, $cart) {
                $shippingFee = 60;
                $totalAmount = 0;

                // 建立訂單
                $order = Order::create([
                    'member_id'      => $member->member_id,
                    'total_amount'   => 0, 
                    'shipping_fee'   => $shippingFee,
                    'payment_method' => $request->payment_method ?? 'Cash',
                    'order_status'   => 'Received',
                    'order_time'     => now(),
                    'cart_id'        => $cart->cart_id,
                    'recipient_name'    => $request->recipient_name,
                    'recipient_phone'   => $request->recipient_phone,
                    'recipient_address' => $request->recipient_address,
                ]);

                // 搬移明細
                foreach ($cart->items as $item) {
                    $itemPrice = (float)$item->price;
                    
                    OrderDetail::create([
                        'order_id'    => $order->order_id,
                        'book_id'     => $item->book_id,
                        'quantity'    => $item->quantity,
                        'piece_price' => $itemPrice,
                    ]);
                    
                    $totalAmount += ($itemPrice * $item->quantity);

                    if ($item->book) {
                        $item->book->decrement('stock', $item->quantity);
                    }
                }

                // 更新總額
                $order->update([
                    'total_amount' => $totalAmount + $shippingFee
                ]);

                // 清空購物車
                $cart->items()->delete();

                return response()->json([
                    'message' => '結帳成功', 
                    'order_id' => $order->order_id
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => '交易失敗', 'error' => $e->getMessage()], 500);
        }
    }

    // 2. [買家] 查看我的訂單列表 (Index)
    public function index(Request $request)
    {
        $user = $request->user();
        $member = $user->member;
        
        if (!$member) {
            return response()->json([], 200);
        }

        $orders = Order::where('member_id', $member->member_id)
                       ->with(['orderDetails.book.coverImage']) 
                       ->orderByDesc('order_time')
                       ->get();

        return response()->json($orders);
    }

    // 3. 🟢 [新增] 查看單筆訂單詳情 (Show)
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $member = $user->member;

        if (!$member) {
            return response()->json(['message' => '會員資料異常'], 403);
        }

        // 查詢訂單，並預先載入必要的關聯 (書籍、封面圖、商家、優惠券)
        $order = Order::with(['orderDetails.book.coverImage', 'business', 'coupon'])
                      ->find($id);

        if (!$order) {
            return response()->json(['message' => '找不到此訂單'], 404);
        }

        // 安全檢查：確保這張訂單是屬於這個人的
        if ($order->member_id !== $member->member_id) {
            return response()->json(['message' => '您無權查看此訂單'], 403);
        }

        return response()->json($order);
    }

    // 4. [賣家] 查詢銷售紀錄
    public function sellerSales(Request $request)
    {
        $user = $request->user();
        $sales = OrderDetail::whereHas('book', function($q) use ($user) {
            $q->where('user_id', $user->user_id);
        })
        ->with(['book.coverImage', 'order.user'])
        ->orderByDesc('created_at')
        ->get();
        
        return response()->json($sales);
    }
}