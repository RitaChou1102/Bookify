<?php

namespace App\Http\Controllers;

use App\Models\Blacklist;
use Illuminate\Http\Request;

class BlackListController extends Controller
{
    public function index()
    {
        return Blacklist::with(['blockedUser', 'admin'])->paginate(15);
    }

    // 封鎖使用者 (加入黑名單)
    public function banUser(Request $request)
    {
        // 這裡不需要手動檢查權限，因為能進到這裡的 Request，
        // 肯定已經通過了路由上的 middleware 驗證。

        $isAlreadyBanned = Blacklist::where('blocked_userid', $request->user_id)->exists();

        if ($isAlreadyBanned) {
            return response()->json(['message' => '該使用者已被封鎖，請勿重複操作'], 422);
        }

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

    public function unbanUser($user_id) // 直接接收路徑參數
    {
        $deleted = Blacklist::where('blocked_userid', $user_id)->delete();

        if (!$deleted) {
            return response()->json(['message' => '找不到該使用者的封鎖紀錄'], 404);
        }

        return response()->json(['message' => '使用者已成功解除封鎖']);
    }
}