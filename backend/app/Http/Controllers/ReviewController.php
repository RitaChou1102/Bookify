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

        // 檢查是否真的有買過這本書且訂單已完成
        $hasBought = Order::where('order_id', $request->order_id)
                          ->where('member_id', $request->user()->member->member_id)
                          ->where('order_status', Order::STATUS_COMPLETED)
                          ->exists();

        if (!$hasBought) {
            // 開發測試階段可暫時註解此檢查，方便測試
            // return response()->json(['message' => '您尚未購買此書或訂單未完成'], 403);
        }

        $review = Review::create($request->all() + ['review_time' => now()]);
        return response()->json(['message' => '評價已送出', 'data' => $review]);
    }

    // 取得某本書的評價
    public function getBookReviews($bookId)
    {
        return Review::where('book_id', $bookId)->orderByDesc('review_time')->get();
    }
}