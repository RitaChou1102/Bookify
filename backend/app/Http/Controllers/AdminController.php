<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Blacklist;
use App\Models\Report;
use App\Models\Complain;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // 簡單的中介層檢查：實際專案應使用 Middleware
    private function checkAdmin($user)
    {
        // 假設 Users 表有一個 role 欄位是 'admin' 或者檢查 admins 表
        // 這裡暫時檢查 role
        if ($user->role !== 'admin') {
            abort(403, '非管理員權限');
        }
    }

    // 封鎖使用者 (加入黑名單)
    public function banUser(Request $request)
    {
        $this->checkAdmin($request->user());
        
        $request->validate([
            'user_id' => 'required|exists:users,user_id',
            'reason' => 'required|string'
        ]);

        // 取得管理員 ID (假設目前登入者有對應的 admin 記錄)
        // 注意：這裡假設 auth user 有關聯到 admin table，若無則需調整
        $adminId = $request->user()->admin->admin_id ?? 1; 

        $blacklist = Blacklist::create([
            'blocked_userid' => $request->user_id,
            'reason' => $request->reason,
            'banned_by' => $adminId, 
            'created_at' => now()
        ]);

        return response()->json(['message' => '使用者已封鎖', 'data' => $blacklist]);
    }

    // 處理投訴
    public function resolveComplain(Request $request, $id)
    {
        $this->checkAdmin($request->user());

        $complain = Complain::findOrFail($id);
        $complain->update([
            'result' => $request->result,
            'complaint_status' => Complain::STATUS_RESOLVED // 2: 已解決
        ]);

        return response()->json(['message' => '投訴已處理']);
    }

    // 查看報表 (這裡回傳假資料示意)
    public function getReports()
    {
        // 實務上這裡會進行複雜的 SQL 統計
        return Report::all();
    }
}