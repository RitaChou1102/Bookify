<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * 結帳 (Checkout)
     * 
     * 功能說明：
     * 1. 驗證用戶身份（必須是會員）
     * 2. 檢查購物車是否有商品
     * 3. 檢查庫存是否足夠
     * 4. 按廠商分組商品（拆單邏輯）
     * 5. 為每個廠商處理優惠券（如果提供）
     * 6. 為每個廠商計算總金額（商品總額 + 運費 - 折扣）
     * 7. 為每個廠商建立訂單和訂單明細
     * 8. 扣除庫存
     * 9. 清空購物車
     * 
     * 拆單邏輯：
     * - 如果購物車中有多個廠商的商品，會自動拆分成多個訂單
     * - 每個訂單對應一個廠商
     * - 每個訂單獨立計算運費和優惠券
     * - 優惠券必須屬於對應的廠商才能使用
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkout(Request $request)
    {
        // 驗證請求資料
        $validator = Validator::make($request->all(), [
            'payment_method' => 'nullable|in:Cash,Credit_card,Bank_transfer',
            'coupon_codes' => 'nullable|array', // 改為陣列，支援多個優惠券
            'coupon_codes.*' => 'string|max:50',
            'shipping_fees' => 'nullable|array', // 支援為每個廠商設定不同運費
            'shipping_fees.*' => 'numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => '驗證失敗',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        
        // 檢查是否為會員
        if (!$user || $user->role !== 'member') {
            return response()->json([
                'message' => '只有會員可以結帳'
            ], 403);
        }

        $member = $user->member;
        if (!$member) {
            return response()->json([
                'message' => '找不到會員資料'
            ], 404);
        }

        // 取得購物車（注意：Cart 的 member_id 外鍵指向 members.member_id）
        $cart = Cart::with(['items.book.business'])->where('member_id', $member->member_id)->first();
        
        if (!$cart) {
            return response()->json([
                'message' => '購物車不存在'
            ], 404);
        }

        if ($cart->items->isEmpty()) {
            return response()->json([
                'message' => '購物車是空的，無法結帳'
            ], 400);
        }
        
        // 開始資料庫交易，確保資料一致性
        try {
            return DB::transaction(function () use ($request, $member, $cart) {
                $defaultShippingFee = $request->input('shipping_fee', 60); // 預設運費 60 元
                $shippingFees = $request->input('shipping_fees', []); // 各廠商的運費
                $couponCodes = $request->input('coupon_codes', []); // 優惠券代碼陣列
                $errors = [];

                // 1. 檢查庫存並驗證商品
                foreach ($cart->items as $item) {
                    $book = $item->book;
                    
                    if (!$book) {
                        $errors[] = "購物車中的書籍 ID {$item->book_id} 不存在";
                        continue;
                    }

                    if (!$book->business_id) {
                        $errors[] = "書籍「{$book->name}」沒有對應的廠商";
                        continue;
                    }

                    if ($book->stock < $item->quantity) {
                        $errors[] = "書籍「{$book->name}」庫存不足（目前庫存：{$book->stock}，需要：{$item->quantity}）";
                        continue;
                    }

                    if ($book->listing == false) {
                        $errors[] = "書籍「{$book->name}」已下架";
                        continue;
                    }
                }

                if (!empty($errors)) {
                    return response()->json([
                        'message' => '結帳失敗',
                        'errors' => $errors
                    ], 400);
                }

                // 2. 按廠商分組購物車商品
                $itemsByBusiness = $cart->items->groupBy(function ($item) {
                    return $item->book->business_id;
                });

                $createdOrders = [];
                $allOrderDetails = [];

                // 3. 為每個廠商建立訂單
                foreach ($itemsByBusiness as $businessId => $items) {
                    $businessTotalAmount = 0;
                    $businessCouponId = null;
                    $businessDiscountAmount = 0;
                    
                    // 3.1 計算該廠商的商品總額
                    foreach ($items as $item) {
                        $businessTotalAmount += $item->subtotal;
                    }

                    // 3.2 處理該廠商的優惠券（如果提供）
                    // 優惠券代碼可以是陣列，格式：{"business_id": "coupon_code"}
                    // 或簡單陣列，我們會嘗試匹配該廠商的優惠券
                    if (!empty($couponCodes)) {
                        $couponCode = null;
                        
                        // 如果 coupon_codes 是關聯陣列（key 為 business_id）
                        if (isset($couponCodes[$businessId])) {
                            $couponCode = $couponCodes[$businessId];
                        } 
                        // 如果 coupon_codes 是簡單陣列，嘗試找到屬於該廠商的優惠券
                        else {
                            foreach ($couponCodes as $code) {
                                $coupon = Coupon::where('code', $code)
                                                ->where('business_id', $businessId)
                                                ->where('is_deleted', false)
                                                ->first();
                                if ($coupon) {
                                    $couponCode = $code;
                                    break;
                                }
                            }
                        }

                        if ($couponCode) {
                            $coupon = Coupon::where('code', $couponCode)
                                            ->where('business_id', $businessId)
                                            ->where('is_deleted', false)
                                            ->first();
                            
                            if ($coupon) {
                                if (!$coupon->isAvailable()) {
                                    return response()->json([
                                        'message' => "廠商 ID {$businessId} 的優惠券「{$couponCode}」已過期或已達使用上限"
                                    ], 400);
                                }

                                // 檢查最低消費金額
                                if ($businessTotalAmount < $coupon->limit_price) {
                                    return response()->json([
                                        'message' => "廠商 ID {$businessId} 的優惠券最低消費金額為 {$coupon->limit_price} 元，目前商品總額為 {$businessTotalAmount} 元"
                                    ], 400);
                                }

                                // 計算折扣金額
                                if ($coupon->discount_type == 0) { // 百分比折扣
                                    $businessDiscountAmount = $businessTotalAmount * ($coupon->discount_value / 100);
                                    $businessDiscountAmount = min($businessDiscountAmount, $businessTotalAmount);
                                } else { // 固定金額折扣
                                    $businessDiscountAmount = min($coupon->discount_value, $businessTotalAmount);
                                }

                                $businessCouponId = $coupon->coupon_id;
                                $businessTotalAmount -= $businessDiscountAmount;
                            }
                        }
                    }

                    // 3.3 取得該廠商的運費
                    $businessShippingFee = isset($shippingFees[$businessId]) 
                        ? $shippingFees[$businessId] 
                        : $defaultShippingFee;

                    // 3.4 計算該廠商的最終金額（商品總額 - 折扣 + 運費）
                    $businessFinalAmount = $businessTotalAmount + $businessShippingFee;

                    // 3.5 建立該廠商的訂單
                    $order = Order::create([
                        'member_id' => $member->member_id,
                        'business_id' => $businessId,
                        'total_amount' => $businessFinalAmount,
                        'shipping_fee' => $businessShippingFee,
                        'payment_method' => $request->input('payment_method', 'Cash'),
                        'order_status' => 'Received',
                        'coupon_id' => $businessCouponId,
                        'cart_id' => $cart->cart_id,
                        'order_time' => now(),
                    ]);

                    // 3.6 建立該廠商的訂單明細並扣除庫存
                    $orderDetails = [];
                    foreach ($items as $item) {
                        $book = $item->book;
                        
                        // 建立訂單明細
                        $orderDetail = OrderDetail::create([
                            'order_id' => $order->order_id,
                            'book_id' => $item->book_id,
                            'quantity' => $item->quantity,
                            'piece_price' => $item->price,
                            // 注意：subtotal 是虛擬欄位，會自動計算為 quantity * piece_price
                        ]);

                        // 扣除庫存
                        $book->decrement('stock', $item->quantity);

                        $orderDetails[] = [
                            'detail_id' => $orderDetail->detail_id,
                            'book_id' => $orderDetail->book_id,
                            'book_name' => $book->name,
                            'quantity' => $orderDetail->quantity,
                            'piece_price' => $orderDetail->piece_price,
                            'subtotal' => $orderDetail->subtotal,
                        ];
                    }

                    // 3.7 更新優惠券使用次數
                    if ($businessCouponId) {
                        Coupon::where('coupon_id', $businessCouponId)->increment('used_count');
                    }

                    // 3.8 載入關聯資料（包含 business 的 user 關聯以取得商店名稱）
                    $order->load(['details.book', 'business.user', 'coupon']);

                    // 取得商店名稱：優先使用 business->user->name（因為 business 的 name 就是商店名稱）
                    $businessName = null;
                    if ($order->business) {
                        $businessName = $order->business->user ? $order->business->user->name : $order->business->store_name;
                    }

                    $createdOrders[] = [
                        'order_id' => $order->order_id,
                        'member_id' => $order->member_id,
                        'business_id' => $order->business_id,
                        'business_name' => $businessName,
                        'total_amount' => $order->total_amount,
                        'shipping_fee' => $order->shipping_fee,
                        'discount_amount' => $businessDiscountAmount,
                        'payment_method' => $order->payment_method,
                        'order_status' => $order->order_status,
                        'order_time' => $order->order_time,
                        'coupon_code' => $order->coupon ? $order->coupon->code : null,
                        'details' => $orderDetails,
                    ];

                    $allOrderDetails = array_merge($allOrderDetails, $orderDetails);
                }

                // 4. 清空購物車（所有訂單都建立成功後才清空）
                $cart->items()->delete();

                // 5. 計算總金額
                $grandTotal = collect($createdOrders)->sum('total_amount');
                $totalShippingFee = collect($createdOrders)->sum('shipping_fee');
                $totalDiscount = collect($createdOrders)->sum('discount_amount');

                return response()->json([
                    'message' => count($createdOrders) > 1 
                        ? '訂單已拆單並建立成功' 
                        : '訂單建立成功',
                    'orders_count' => count($createdOrders),
                    'grand_total' => $grandTotal,
                    'total_shipping_fee' => $totalShippingFee,
                    'total_discount' => $totalDiscount,
                    'orders' => $createdOrders
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json([
                'message' => '結帳過程中發生錯誤',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 查看會員的所有訂單列表
     * 
     * 功能說明：
     * 1. 驗證用戶身份（必須是會員）
     * 2. 取得該會員的所有訂單
     * 3. 載入訂單明細和書籍資訊
     * 4. 按訂單時間降序排列
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // 檢查是否為會員
        if (!$user || $user->role !== 'member') {
            return response()->json([
                'message' => '只有會員可以查看訂單'
            ], 403);
        }

        $member = $user->member;
        if (!$member) {
            return response()->json([
                'message' => '找不到會員資料'
            ], 404);
        }

        // 取得該會員的所有訂單，載入相關資料（包含 business 的 user 關聯以取得商店名稱）
        $orders = Order::with([
            'details.book.author',
            'details.book.coverImage',
            'business.user',
            'coupon'
        ])
        ->where('member_id', $member->member_id)
        ->orderByDesc('order_time')
        ->get();

        // 格式化訂單資料
        $formattedOrders = $orders->map(function ($order) {
            // 取得商店名稱：優先使用 business->user->name（因為 business 的 name 就是商店名稱）
            $businessName = null;
            if ($order->business) {
                $businessName = $order->business->user ? $order->business->user->name : $order->business->store_name;
            }

            return [
                'order_id' => $order->order_id,
                'business_id' => $order->business_id,
                'business_name' => $businessName,
                'total_amount' => $order->total_amount,
                'shipping_fee' => $order->shipping_fee,
                'payment_method' => $order->payment_method,
                'order_status' => $order->order_status,
                'order_time' => $order->order_time,
                'coupon_code' => $order->coupon ? $order->coupon->code : null,
                'details_count' => $order->details->count(),
                'details' => $order->details->map(function ($detail) {
                    return [
                        'detail_id' => $detail->detail_id,
                        'book_id' => $detail->book_id,
                        'book_name' => $detail->book ? $detail->book->name : null,
                        'book_cover' => $detail->book && $detail->book->coverImage 
                            ? $detail->book->coverImage->image_url 
                            : null,
                        'quantity' => $detail->quantity,
                        'piece_price' => $detail->piece_price,
                        'subtotal' => $detail->subtotal,
                    ];
                }),
            ];
        });

        return response()->json([
            'message' => '取得訂單列表成功',
            'count' => $formattedOrders->count(),
            'orders' => $formattedOrders
        ], 200);
    }

    /**
     * 查看單一訂單詳情
     * 
     * 功能說明：
     * 1. 驗證用戶身份（必須是會員）
     * 2. 檢查訂單是否存在
     * 3. 檢查訂單是否屬於該會員
     * 4. 返回完整的訂單資訊
     * 
     * @param Request $request
     * @param int $id 訂單 ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        
        // 檢查是否為會員
        if (!$user || $user->role !== 'member') {
            return response()->json([
                'message' => '只有會員可以查看訂單'
            ], 403);
        }

        $member = $user->member;
        if (!$member) {
            return response()->json([
                'message' => '找不到會員資料'
            ], 404);
        }

        // 取得訂單並載入相關資料（包含 business 的 user 關聯以取得商店名稱）
        $order = Order::with([
            'details.book.author',
            'details.book.category',
            'details.book.images',
            'business.user',
            'coupon',
            'member.user'
        ])->find($id);

        if (!$order) {
            return response()->json([
                'message' => '訂單不存在'
            ], 404);
        }

        // 檢查訂單是否屬於該會員
        if ($order->member_id !== $member->member_id) {
            return response()->json([
                'message' => '無權限查看此訂單'
            ], 403);
        }

        // 格式化訂單資料
        $formattedOrder = [
            'order_id' => $order->order_id,
            'member' => [
                'member_id' => $order->member->member_id,
                'name' => $order->member->user->name ?? null,
                'email' => $order->member->user->email ?? null,
                'phone' => $order->member->user->phone ?? null,
                'address' => $order->member->user->address ?? null,
            ],
            'business' => $order->business ? [
                'business_id' => $order->business->business_id,
                'store_name' => $order->business->user ? $order->business->user->name : $order->business->store_name,
                'email' => $order->business->email,
                'phone' => $order->business->phone,
            ] : null,
            'total_amount' => $order->total_amount,
            'shipping_fee' => $order->shipping_fee,
            'payment_method' => $order->payment_method,
            'order_status' => $order->order_status,
            'order_time' => $order->order_time,
            'coupon' => $order->coupon ? [
                'coupon_id' => $order->coupon->coupon_id,
                'code' => $order->coupon->code,
                'name' => $order->coupon->name,
                'discount_type' => $order->coupon->discount_type,
                'discount_value' => $order->coupon->discount_value,
            ] : null,
            'details' => $order->details->map(function ($detail) {
                return [
                    'detail_id' => $detail->detail_id,
                    'book_id' => $detail->book_id,
                    'book_name' => $detail->book ? $detail->book->name : null,
                    'book_isbn' => $detail->book ? $detail->book->isbn : null,
                    'book_author' => $detail->book && $detail->book->author 
                        ? $detail->book->author->name 
                        : null,
                    'book_category' => $detail->book && $detail->book->category 
                        ? $detail->book->category->name 
                        : null,
                    'book_images' => $detail->book && $detail->book->images 
                        ? $detail->book->images->pluck('image_url')->toArray() 
                        : [],
                    'quantity' => $detail->quantity,
                    'piece_price' => $detail->piece_price,
                    'subtotal' => $detail->subtotal,
                ];
            }),
        ];

        return response()->json([
            'message' => '取得訂單詳情成功',
            'order' => $formattedOrder
        ], 200);
    }
    
    /**
     * 更新訂單狀態（廠商專用）
     * 
     * 功能說明：
     * 1. 驗證用戶身份（必須是廠商）
     * 2. 檢查訂單是否存在
     * 3. 檢查訂單是否屬於該廠商
     * 4. 驗證新的訂單狀態是否有效
     * 5. 更新訂單狀態
     * 
     * @param Request $request
     * @param int $id 訂單 ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $id)
    {
        // 驗證請求資料
        $validator = Validator::make($request->all(), [
            'order_status' => 'required|in:Received,Processing,Shipped,Completed,Cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => '驗證失敗',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        
        // 檢查是否為廠商
        if (!$user || $user->role !== 'business') {
            return response()->json([
                'message' => '只有廠商可以更新訂單狀態'
            ], 403);
        }

        $business = $user->business;
        if (!$business) {
            return response()->json([
                'message' => '找不到廠商資料'
            ], 404);
        }

        // 取得訂單
        $order = Order::find($id);
        
        if (!$order) {
            return response()->json([
                'message' => '訂單不存在'
            ], 404);
        }

        // 檢查訂單是否屬於該廠商
        if ($order->business_id !== $business->business_id) {
            return response()->json([
                'message' => '無權限更新此訂單狀態'
            ], 403);
        }

        // 檢查狀態轉換是否合理（簡單的狀態機檢查）
        $currentStatus = $order->order_status;
        $newStatus = $request->order_status;
        
        // 定義允許的狀態轉換
        $allowedTransitions = [
            'Received' => ['Processing', 'Cancelled'],
            'Processing' => ['Shipped', 'Cancelled'],
            'Shipped' => ['Completed'],
            'Completed' => [], // 已完成不能再轉換
            'Cancelled' => [], // 已取消不能再轉換
        ];

        if (!in_array($newStatus, $allowedTransitions[$currentStatus] ?? [])) {
            return response()->json([
                'message' => "訂單狀態無法從「{$currentStatus}」轉換為「{$newStatus}」"
            ], 400);
        }

        // 更新訂單狀態
        $order->update([
            'order_status' => $newStatus
        ]);

        return response()->json([
            'message' => '訂單狀態更新成功',
            'order' => [
                'order_id' => $order->order_id,
                'order_status' => $order->order_status,
                'updated_at' => now(),
            ]
        ], 200);
    }
}