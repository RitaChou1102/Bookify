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
     * 查看購物車 (取得列表)
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            // ⚠️ 關鍵修正：必須先確定 User 關聯的 Member 存在
            $member = $user->member; 
            
            if (!$member) {
                return response()->json(['message' => '找不到會員資料，請先完成會員註冊'], 403);
            }

            // 使用 member_id 而非 user_id 來建立/尋找購物車
            $cart = Cart::firstOrCreate(['member_id' => $member->member_id]);

            // 預先載入圖片與書籍資訊
            $cart->load(['items.book.coverImage']);

            $formattedItems = $cart->items->map(function($item) {
                return [
                    'cart_item_id' => $item->cart_item_id,
                    'book_id'      => $item->book_id,
                    'quantity'     => $item->quantity,
                    'price'        => (float)$item->price,
                    'subtotal'     => (float)$item->subtotal,
                    'book'         => [
                        'name'        => $item->book->name ?? '未知書籍',
                        'cover_image' => $item->book->coverImage ? [
                            'image_url' => $item->book->coverImage->image_url
                        ] : null
                    ]
                ];
            });

            return response()->json([
                'cart_id' => $cart->cart_id,
                'items'   => $formattedItems,
                'summary' => [
                    'total_amount' => (float)$cart->items->sum('subtotal'),
                    'total_items'  => (int)$cart->items->sum('quantity'),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => '讀取購物車失敗', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * 加入商品至購物車
     */
    public function addItem(Request $request)
    {
        $validated = $request->validate([
            'book_id' => 'required|exists:books,book_id',
            'quantity' => 'required|integer|min:1'
        ]);

        try {
            $user = $request->user();
            
            // 1. 確保 Member 紀錄存在
            $member = \App\Models\Member::firstOrCreate(['user_id' => $user->user_id]);

            // 2. 獲取或建立購物車
            $cart = Cart::firstOrCreate(['member_id' => $member->member_id]);
            
            $book = \App\Models\Book::findOrFail($validated['book_id']);

            // 3. 檢查庫存
            if ($book->stock < $validated['quantity']) {
                return response()->json(['message' => '庫存不足'], 400);
            }

            // 4. 🔍 修正點：改用手動查找並累加數量，避免 "Object of class... could not be converted to int"
            $cartItem = CartItem::where('cart_id', $cart->cart_id)
                                ->where('book_id', $book->book_id)
                                ->first();

            if ($cartItem) {
                // 如果已存在，更新數量
                $cartItem->quantity += $validated['quantity'];
                $cartItem->save();
            } else {
                // 如果不存在，建立新項目
                CartItem::create([
                    'cart_id' => $cart->cart_id,
                    'book_id' => $book->book_id,
                    'quantity' => $validated['quantity'],
                    'price' => $book->price
                ]);
            }

            return response()->json(['message' => '商品已成功加入購物車']);
        } catch (\Exception $e) {
            return response()->json(['message' => '加入購物車失敗', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * 更新數量
     */
    public function updateItem(Request $request, $id)
    {
        $request->validate(['quantity' => 'required|integer|min:1']);
        
        $item = CartItem::findOrFail($id);
        $item->update(['quantity' => $request->quantity]);

        return response()->json(['message' => '數量已更新']);
    }

    /**
     * 移除商品
     */
    public function removeItem($id)
    {
        CartItem::destroy($id);
        return response()->json(['message' => '商品已移除']);
    }
}