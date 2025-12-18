<?php

namespace App\Http\Controllers;

use App\Models\Blacklist;
use App\Models\Report;
use App\Models\Complain;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // 驗證工作交給路由層的 'auth:sanctum' middleware 處理

    // 封鎖使用者 (加入黑名單)
    public function banUser(Request $request)
    {
        // 這裡不需要手動檢查權限，因為能進到這裡的 Request，
        // 肯定已經通過了路由上的 middleware 驗證。

        $request->validate([
            'user_id' => 'required|exists:users,user_id',
            'reason' => 'required|string'
        ]);

        // 取得當前登入的管理員 ID
        // 因為使用了 auth:sanctum 且 guard 設為 admin，
        // $request->user() 回傳的直接就是 Admin Model 的實例，而不是 User Model
        $currentAdmin = $request->user();

        $blacklist = Blacklist::create([
            'blocked_userid' => $request->user_id,
            'reason' => $request->reason,
            'banned_by' => $currentAdmin->admin_id, // 直接取用
            'created_at' => now()
        ]);

        return response()->json(['message' => '使用者已封鎖', 'data' => $blacklist]);
    }

    // 1. 管理員點開投訴時呼叫
    public function markAsInProgress($id)
    {
        $complain = Complain::findOrFail($id);

        // 只有在 pending 狀態才需要改為處理中
        if ($complain->complaint_status === 'pending') {
            $complain->update([
                'complaint_status' => 'in_progress'
            ]);
        }

        return response()->json([
            'message' => '投訴已進入處理中狀態',
            'data' => $complain
        ]);
    }

    // 2. 管理員輸入處理結果並點擊「送出」時呼叫
    public function resolveComplain(Request $request, $id)
    {
        $request->validate([
            'result' => 'required|string'
        ]);
        $complain = Complain::findOrFail($id);

        $complain->update([
            'result' => $request->result,
            'complaint_status' => 'resolved' // 狀態改為已解決
        ]);
        return response()->json([
            'message' => '投訴已成功處理完畢'
        ]);
    }

    // 查看報表
    public function getReports()
    {
        return Report::all();
    }
}