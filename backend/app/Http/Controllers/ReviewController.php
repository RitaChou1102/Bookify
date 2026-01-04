<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Order;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    // 新增或更新評價
    public function store(Request $request)
    {
        $request->validate([
            'book_id' => 'required',
            'order_id' => 'required',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string'
        ]);

        $user = $request->user();

        // 1. 嚴格檢查會員身分
        if (!$user || !$user->member) {
             return response()->json(['message' => '只有會員可以評價'], 403);
        }

        // 2. 檢查是否真的有買過這本書且訂單已完成
        $hasBought = Order::where('order_id', $request->order_id)
                          ->where('member_id', $user->member->member_id)
                          ->where('order_status', 'Completed') 
                          ->whereHas('orderDetails', function ($query) use ($request) { // 注意：這裡是 orderDetails (根據 Order 模型)
                              $query->where('book_id', $request->book_id);
                          })
                          ->exists();

        if (!$hasBought) {
            return response()->json(['message' => '您尚未購買此書或訂單未完成'], 403);
        }

        // 3. 建立或更新評論
        $review = Review::updateOrCreate(
            [
                // 搜尋條件
                'user_id'  => $user->user_id,
                'book_id'  => $request->book_id,
                'order_id' => $request->order_id,
            ],
            [
                // 更新內容
                'rating'  => $request->rating,
                'comment' => $request->comment,
                // 🟢 [關鍵修正] 因為 Model 關閉了 timestamps，必須手動寫入 review_time
                'review_time' => now(), 
            ]
        );

        return response()->json(['message' => '評價已送出', 'data' => $review]);
    }

    // 取得某本書的評價
    public function getBookReviews($bookId)
    {
        return Review::where('book_id', $bookId)
                     ->with('user') // 載入評論者資訊
                     // 🟢 [關鍵修正] 使用 review_time 排序，而非 created_at
                     ->orderByDesc('review_time') 
                     ->paginate(10);
    }
}