<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Book;
use Illuminate\Http\Request;

class CartController extends Controller
{
    // 查看購物車
    public function show(Request $request)
    {
        $user = $request->user();
        
        // 確保是會員
        if (!$user->member) {
            return response()->json(['message' => '非會員無法使用購物車'], 403);
        }

        // 取得購物車及其內容（包含書籍資訊和封面圖）
        // 注意：根據 schema，Cart 的 member_id 外鍵指向 users 表
        $cart = Cart::with(['items.book.coverImage'])
                    ->firstOrCreate(['member_id' => $user->user_id]);

        return response()->json($cart);
    }

    // 加入購物車
    public function addItem(Request $request)
    {
        $request->validate([
            'book_id' => 'required|exists:books,book_id',
            'quantity' => 'required|integer|min:1'
        ]);

        $user = $request->user();
        // 注意：根據 schema，Cart 的 member_id 外鍵指向 users 表
        $cart = Cart::firstOrCreate(['member_id' => $user->user_id]);
        $book = Book::find($request->book_id);

        // 檢查庫存
        if ($book->stock < $request->quantity) {
            return response()->json(['message' => '庫存不足'], 400);
        }

        // 檢查購物車內是否已有該書，有則更新數量，無則新增
        $item = CartItem::where('cart_id', $cart->cart_id)
                        ->where('book_id', $book->book_id)
                        ->first();

        if ($item) {
            $item->quantity += $request->quantity;
            $item->save();
        } else {
            CartItem::create([
                'cart_id' => $cart->cart_id,
                'book_id' => $book->book_id,
                'quantity' => $request->quantity,
                'price' => $book->price, // 記錄當下價格
                'subtotal' => $book->price * $request->quantity
            ]);
        }

        return response()->json(['message' => '已加入購物車']);
    }

    // 更新購物車商品數量
    public function updateItem(Request $request, $id)
    {
        $request->validate(['quantity' => 'required|integer|min:1']);
        
        $item = CartItem::findOrFail($id);
        
        // 檢查是否為自己的購物車項目
        // 注意：根據 schema，Cart 的 member_id 外鍵指向 users 表
        if ($item->cart->member_id !== $request->user()->user_id) {
            return response()->json(['message' => '無權限'], 403);
        }

        $item->quantity = $request->quantity;
        $item->subtotal = $item->price * $request->quantity;
        $item->save();

        return response()->json(['message' => '更新成功']);
    }

    // 移除購物車項目
    public function removeItem(Request $request, $id)
    {
        $item = CartItem::findOrFail($id);
        
        // 注意：根據 schema，Cart 的 member_id 外鍵指向 users 表
        if ($item->cart->member_id !== $request->user()->user_id) {
            return response()->json(['message' => '無權限'], 403);
        }

        $item->delete();
        return response()->json(['message' => '已移除商品']);
    }

    // 清空購物車
    public function clear(Request $request)
    {
        // 注意：根據 schema，Cart 的 member_id 外鍵指向 users 表
        $cart = Cart::where('member_id', $request->user()->user_id)->first();
        if ($cart) {
            $cart->items()->delete();
        }
        return response()->json(['message' => '購物車已清空']);
    }
}