<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Order;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    // 新增評價
    public function store(Request $request)
    {
        $request->validate([
            'book_id' => 'required',
            'order_id' => 'required',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string'
        ]);

        $user = $request->user();

        // 嚴格檢查 $user 和 $user->member 是否存在
        // 防止如果是 Admin 或是資料異常的 User 呼叫此 API 時報 500 錯誤
        if (!$user || !$user->member) {
             return response()->json(['message' => '只有會員可以評價'], 403);
        }

        // 檢查是否真的有買過這本書且訂單已完成
        $hasBought = Order::where('order_id', $request->order_id)
                          ->where('member_id', $user->member->member_id)
                          ->where('order_status', 'Completed')          //Order 模型 (Order.php) 裡面並沒有定義 STATUS_COMPLETED 這個常數
                          ->whereHas('details', function ($query) use ($request) {
                              $query->where('book_id', $request->book_id);
                          })
                          ->exists();

        if (!$hasBought) {
            // 開發測試階段可暫時註解此檢查，方便測試
            return response()->json(['message' => '您尚未購買此書或訂單未完成'], 403);
        }

        $review = Review::updateOrCreate(
                [
                    'book_id'  => $request->book_id,
                    'order_id' => $request->order_id,
                ],
                [
                    'rating'      => $request->rating,
                    'comment'     => $request->comment,
                    'review_time' => now(), // 更新時也刷新評論時間
                ]
            );
        return response()->json(['message' => '評價已送出', 'data' => $review]);
    }

    // 取得某本書的評價
    public function getBookReviews($bookId)
    {
        return Review::where('book_id', $bookId)->orderByDesc('review_time')->get();
    }
}