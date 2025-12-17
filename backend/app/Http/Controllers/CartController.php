<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /**
     * 查看購物車
     * 
     * 功能說明：
     * 1. 驗證使用者是否為會員
     * 2. 取得或建立購物車
     * 3. 載入購物車項目及其相關書籍資訊
     * 4. 計算並返回購物車總金額、商品總數等統計資訊
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request)
    {
        try {
            $user = $request->user();
            
            // 檢查用戶是否已認證
            if (!$user) {
                return response()->json([
                    'message' => '未授權',
                    'error' => '請先登入並提供有效的 Token',
                    'hint' => '請在 Header 中帶上 Authorization: Bearer {your_token}'
                ], 401);
            }
            
            // 確保是會員（載入 member 關聯以檢查）
            $user->load('member');
            if (!$user->member) {
                return response()->json([
                    'message' => '非會員無法使用購物車',
                    'error' => '只有會員可以使用購物車功能',
                    'user_role' => $user->role ?? 'unknown',
                    'hint' => '請使用會員帳號登入'
                ], 403);
            }

            // 取得購物車（注意：根據 schema，Cart 的 member_id 外鍵指向 users 表）
            $cart = Cart::firstOrCreate(['member_id' => $user->user_id]);

            // 計算購物車統計資訊（使用 SQL 聚合函數，在資料庫層面計算，避免載入所有資料到記憶體）
            $totalAmount = $cart->items()->sum('subtotal');
            $totalItems = $cart->items()->sum('quantity');
            $itemCount = $cart->items()->count();

            // 如果購物車是空的，直接返回空結果
            if ($itemCount === 0) {
                return response()->json([
                    'cart_id' => $cart->cart_id,
                    'member_id' => $cart->member_id,
                    'items' => [],
                    'summary' => [
                        'total_items' => 0,
                        'item_count' => 0,
                        'total_amount' => 0.00,
                    ],
                    'created_at' => $cart->created_at,
                ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }

            // 載入購物車項目及其相關資料（簡化查詢，不使用 select 限制以避免問題）
            $cart->load([
                'items.book.coverImage',
                'items.book.business'
            ]);

            // 格式化返回資料（只返回必要的書籍資訊）
            return response()->json([
                'cart_id' => $cart->cart_id,
                'member_id' => $cart->member_id,
                'items' => $cart->items->map(function($item) {
                    return [
                        'cart_item_id' => $item->cart_item_id,
                        'book_id' => $item->book_id,
                        'quantity' => $item->quantity,
                        'price' => (float)$item->price,
                        'subtotal' => (float)$item->subtotal,
                        'book' => [
                            'name' => $item->book->name ?? null,
                            'price' => $item->book ? (float)$item->book->price : 0.00,
                            'cover_image' => $item->book && $item->book->coverImage ? [
                                'image_id' => $item->book->coverImage->image_id,
                                'image_url' => $item->book->coverImage->image_url,
                            ] : null,
                            'business' => $item->book && $item->book->business ? [
                                'business_id' => $item->book->business->business_id,
                                'store_name' => $item->book->business->store_name,
                            ] : null,
                        ],
                    ];
                }),
                'summary' => [
                    'total_items' => $totalItems,        // 商品總數量
                    'item_count' => $itemCount,          // 商品種類數
                    'total_amount' => (float)$totalAmount, // 購物車總金額
                ],
                'created_at' => $cart->created_at,
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            
        } catch (\Exception $e) {
            // 記錄錯誤並返回詳細錯誤訊息（僅在開發環境）
            \Log::error('Cart show error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()->user_id ?? null,
            ]);
            
            return response()->json([
                'message' => '取得購物車失敗',
                'error' => config('app.debug') ? $e->getMessage() : '伺服器錯誤，請稍後再試',
                'file' => config('app.debug') ? $e->getFile() . ':' . $e->getLine() : null,
            ], 500, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
    }

    /**
     * 加入購物車
     * 
     * 功能說明：
     * 1. 驗證輸入資料（book_id, quantity）
     * 2. 驗證使用者是否為會員
     * 3. 檢查書籍是否存在且上架
     * 4. 檢查庫存是否足夠（包含購物車中已有的數量）
     * 5. 如果購物車中已有該商品，則增加數量；否則新增項目
     * 6. 記錄加入時的書籍價格（價格快照）
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addItem(Request $request)
    {
        // 驗證輸入資料
        $validated = $request->validate([
            'book_id' => 'required|integer|exists:books,book_id',
            'quantity' => 'required|integer|min:1|max:999'
        ], [
            'book_id.required' => '書籍ID為必填欄位，請提供 book_id',
            'book_id.integer' => '書籍ID必須為整數',
            'book_id.exists' => '該書籍不存在，請確認 book_id 是否正確',
            'quantity.required' => '數量為必填欄位，請提供 quantity',
            'quantity.integer' => '數量必須為整數',
            'quantity.min' => '數量至少為1',
            'quantity.max' => '數量不能超過999',
        ]);

        $user = $request->user();
        
        // 確保是會員
        if (!$user->member) {
            return response()->json([
                'message' => '非會員無法使用購物車',
                'error' => '只有會員可以將商品加入購物車'
            ], 403);
        }

        // 取得或建立購物車
        // 注意：根據 schema，Cart 的 member_id 外鍵指向 users 表
        $cart = Cart::firstOrCreate(['member_id' => $user->user_id]);
        
        // 取得書籍資訊
        $book = Book::findOrFail($validated['book_id']);

        // 檢查書籍是否上架
        if (!$book->listing) {
            return response()->json([
                'message' => '該書籍已下架，無法加入購物車',
                'error' => '書籍未上架'
            ], 400);
        }

        // 檢查購物車內是否已有該書
        $existingItem = CartItem::where('cart_id', $cart->cart_id)
                                ->where('book_id', $book->book_id)
                                ->first();

        // 計算需要的總數量（購物車中已有的 + 要新增的）
        $requiredQuantity = $validated['quantity'];
        if ($existingItem) {
            $requiredQuantity += $existingItem->quantity;
        }

        // 檢查庫存是否足夠
        if ($book->stock < $requiredQuantity) {
            return response()->json([
                'message' => '庫存不足',
                'error' => "該書籍目前庫存為 {$book->stock} 本，無法滿足您的需求",
                'available_stock' => $book->stock,
                'required_quantity' => $requiredQuantity,
            ], 400);
        }

        // 使用資料庫交易確保資料一致性
        try {
            DB::beginTransaction();

            if ($existingItem) {
                // 如果購物車中已有該書，則增加數量
                $existingItem->quantity += $validated['quantity'];
                // subtotal 會自動計算（透過 CartItem 的 boot 方法）
                $existingItem->save();
                
                $message = '購物車商品數量已更新';
                $cartItem = $existingItem;
            } else {
                // 如果購物車中沒有該書，則新增項目
                // 注意：subtotal 會自動計算（透過 CartItem 的 boot 方法）
                $cartItem = CartItem::create([
                    'cart_id' => $cart->cart_id,
                    'book_id' => $book->book_id,
                    'quantity' => $validated['quantity'],
                    'price' => $book->price, // 記錄當下價格（價格快照）
                ]);
                
                $message = '商品已成功加入購物車';
            }

            DB::commit();

            // 載入相關資料以便返回（只載入需要的：封面圖、廠商）
            $cartItem->load('book.coverImage', 'book.business');

            return response()->json([
                'message' => $message,
                'cart_item' => [
                    'cart_item_id' => $cartItem->cart_item_id,
                    'book_id' => $cartItem->book_id,
                    'quantity' => $cartItem->quantity,
                    'price' => (float)$cartItem->price,
                    'subtotal' => (float)$cartItem->subtotal,
                    'book' => [
                        'name' => $cartItem->book->name,
                        'price' => (float)$cartItem->book->price,
                        'cover_image' => $cartItem->book->coverImage ? [
                            'image_id' => $cartItem->book->coverImage->image_id,
                            'image_url' => $cartItem->book->coverImage->image_url,
                        ] : null,
                        'business' => $cartItem->book->business ? [
                            'business_id' => $cartItem->book->business->business_id,
                            'store_name' => $cartItem->book->business->store_name,
                        ] : null,
                    ],
                ],
            ], 201, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => '加入購物車失敗',
                'error' => config('app.debug') ? $e->getMessage() : '伺服器錯誤，請稍後再試'
            ], 500);
        }
    }

    /**
     * 更新購物車商品數量
     * 
     * 功能說明：
     * 1. 驗證輸入資料（quantity）
     * 2. 驗證使用者是否為會員
     * 3. 檢查購物車項目是否存在
     * 4. 檢查是否為自己的購物車項目（權限驗證）
     * 5. 檢查庫存是否足夠
     * 6. 更新數量並重新計算小計
     * 
     * @param Request $request
     * @param int $id 購物車項目ID (cart_item_id)
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateItem(Request $request, $id)
    {
        try {
            // 驗證輸入資料
            $validated = $request->validate([
                'quantity' => 'required|integer|min:1|max:999'
            ], [
                'quantity.required' => '數量為必填欄位，請提供 quantity',
                'quantity.integer' => '數量必須為整數',
                'quantity.min' => '數量至少為1',
                'quantity.max' => '數量不能超過999',
            ]);

        $user = $request->user();
        
        // 確保是會員
        if (!$user->member) {
            return response()->json([
                'message' => '非會員無法使用購物車',
                'error' => '只有會員可以更新購物車'
            ], 403);
        }

        // 取得購物車項目並同時檢查權限（使用 join 避免額外查詢）
        // 注意：根據 schema，Cart 的 member_id 外鍵指向 users 表
        $item = CartItem::with(['book', 'cart'])
            ->whereHas('cart', function($query) use ($user) {
                $query->where('member_id', $user->user_id);
            })
            ->findOrFail($id);
        
        // 檢查是否為自己的購物車項目（已經在 whereHas 中驗證，這裡可以省略，但保留以確保邏輯清晰）
        if ($item->cart->member_id !== $user->user_id) {
            return response()->json([
                'message' => '無權限',
                'error' => '您只能修改自己的購物車項目'
            ], 403);
        }

        // 檢查書籍是否仍然上架
        if (!$item->book->listing) {
            return response()->json([
                'message' => '該書籍已下架，無法更新數量',
                'error' => '書籍未上架'
            ], 400);
        }

        // 檢查庫存是否足夠
        if ($item->book->stock < $validated['quantity']) {
            return response()->json([
                'message' => '庫存不足',
                'error' => "該書籍目前庫存為 {$item->book->stock} 本",
                'available_stock' => $item->book->stock,
                'requested_quantity' => $validated['quantity'],
            ], 400);
        }

        // 更新數量（subtotal 會自動計算）
        $item->quantity = $validated['quantity'];
        $item->save();

        return response()->json([
            'message' => '購物車商品數量已更新',
            'cart_item' => [
                'cart_item_id' => $item->cart_item_id,
                'quantity' => $item->quantity,
                'subtotal' => (float)$item->subtotal,
            ],
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        
        } catch (\Illuminate\Validation\ValidationException $e) {
            // 驗證錯誤，返回 JSON 格式的錯誤訊息
            return response()->json([
                'message' => '驗證失敗',
                'errors' => $e->errors(),
            ], 422, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (\Exception $e) {
            // 其他錯誤
            \Log::error('Cart updateItem error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()->user_id ?? null,
                'cart_item_id' => $id,
            ]);
            
            return response()->json([
                'message' => '更新購物車商品失敗',
                'error' => config('app.debug') ? $e->getMessage() : '伺服器錯誤，請稍後再試',
            ], 500, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
    }

    /**
     * 移除購物車項目
     * 
     * 功能說明：
     * 1. 驗證使用者是否為會員
     * 2. 檢查購物車項目是否存在
     * 3. 檢查是否為自己的購物車項目（權限驗證）
     * 4. 刪除購物車項目
     * 
     * @param Request $request
     * @param int $id 購物車項目ID (cart_item_id)
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeItem(Request $request, $id)
    {
        $user = $request->user();
        
        // 確保是會員
        if (!$user->member) {
            return response()->json([
                'message' => '非會員無法使用購物車',
                'error' => '只有會員可以移除購物車項目'
            ], 403);
        }

        // 取得購物車項目並同時檢查權限（使用 join 避免額外查詢）
        // 注意：根據 schema，Cart 的 member_id 外鍵指向 users 表
        $item = CartItem::with(['book', 'cart'])
            ->whereHas('cart', function($query) use ($user) {
                $query->where('member_id', $user->user_id);
            })
            ->findOrFail($id);
        
        // 檢查是否為自己的購物車項目（已經在 whereHas 中驗證，這裡可以省略，但保留以確保邏輯清晰）
        if ($item->cart->member_id !== $user->user_id) {
            return response()->json([
                'message' => '無權限',
                'error' => '您只能移除自己的購物車項目'
            ], 403);
        }

        // 記錄被刪除的商品資訊（用於返回訊息）
        $bookName = $item->book ? $item->book->name : '商品';

        // 刪除購物車項目
        $item->delete();

        return response()->json([
            'message' => '商品已從購物車移除',
            'removed_item' => [
                'cart_item_id' => $id,
                'book_name' => $bookName,
            ],
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * 清空購物車
     * 
     * 功能說明：
     * 1. 驗證使用者是否為會員
     * 2. 取得使用者的購物車
     * 3. 刪除購物車中的所有項目
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clear(Request $request)
    {
        $user = $request->user();
        
        // 確保是會員
        if (!$user->member) {
            return response()->json([
                'message' => '非會員無法使用購物車',
                'error' => '只有會員可以清空購物車'
            ], 403);
        }

        // 取得購物車
        // 注意：根據 schema，Cart 的 member_id 外鍵指向 users 表
        $cart = Cart::where('member_id', $user->user_id)->first();
        
        if (!$cart) {
            // 購物車不存在，表示已經是空的
            return response()->json([
                'message' => '購物車已經是空的',
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        
        // 記錄清空前的商品數量（使用 SQL COUNT，在資料庫層面計算）
        $itemCount = $cart->items()->count();
        
        // 如果購物車中沒有商品，直接返回
        if ($itemCount === 0) {
            return response()->json([
                'message' => '購物車已經是空的',
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        
        // 刪除所有購物車項目
        $cart->items()->delete();
        
        return response()->json([
            'message' => '購物車已清空',
            'cleared_items_count' => $itemCount,
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}