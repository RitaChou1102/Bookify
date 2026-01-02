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
        // 注意：這裡直接用字串 'Completed' 是沒問題的，只要你資料庫存的是這個字串
        $hasBought = Order::where('order_id', $request->order_id)
                          ->where('member_id', $user->member->member_id)
                          ->where('order_status', 'Completed') 
                          ->whereHas('details', function ($query) use ($request) {
                              $query->where('book_id', $request->book_id);
                          })
                          ->exists();

        if (!$hasBought) {
            // 如果你在測試階段想跳過這個檢查，可以把下面這行暫時註解掉
            return response()->json(['message' => '您尚未購買此書或訂單未完成'], 403);
        }

        // 3. 建立或更新評論
        $review = Review::updateOrCreate(
            [
                // 搜尋條件：同一個人、同一張單、同一本書
                'user_id'  => $user->user_id, // [重要修正] 必須加上 user_id
                'book_id'  => $request->book_id,
                'order_id' => $request->order_id,
            ],
            [
                // 更新內容
                'rating'  => $request->rating,
                'comment' => $request->comment,
                // [重要修正] 移除 'review_time'，Laravel 會自動更新 created_at 和 updated_at
            ]
        );

        return response()->json(['message' => '評價已送出', 'data' => $review]);
    }

    // 取得某本書的評價
    public function getBookReviews($bookId)
    {
        // [重要修正] 資料庫沒有 review_time，改用 created_at 排序
        return Review::where('book_id', $bookId)
                     ->with('user') // 順便把評論者的名字頭像抓出來
                     ->orderByDesc('created_at') 
                     ->paginate(10); // 建議用分頁，避免一次傳太多
    }
}