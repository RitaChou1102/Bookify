<?php

namespace App\Http\Controllers;

use App\Models\Complain;
use App\Models\Order;
use Illuminate\Http\Request;

class ComplainUserController extends Controller
{
    // 會員提交投訴
    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,order_id',
            'content' => 'required|string',
        ]);

        $order = Order::findOrFail($request->order_id);

        // 驗證是否為該會員的訂單
        if ($order->member_id !== $request->user()->member->member_id) {
            return response()->json(['message' => '無權投訴此訂單'], 403);
        }

        $complain = Complain::create([
            'order_id' => $request->order_id,
            'content' => $request->content,
            'complaint_time' => now(),
            'complaint_status' => Complain::STATUS_PENDING // 'pending': 待處理
        ]);

        return response()->json(['message' => '投訴已提交', 'data' => $complain], 201);
    }

    // 會員查看自己的投訴紀錄
    public function index(Request $request)
    {
        // 透過訂單關聯找回投訴
        return Complain::whereHas('order', function($q) use ($request) {
            $q->where('member_id', $request->user()->member->member_id);
        })->get();
    }
}