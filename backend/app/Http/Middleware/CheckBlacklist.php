<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Blacklist;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBlacklist
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. 取得目前登入的使用者
        $user = $request->user();

        // 2. 如果有登入，檢查其 user_id 是否在黑名單中
        if ($user) {
            $isBanned = Blacklist::where('blocked_userid', $user->user_id)->exists();

            if ($isBanned) {
                // 3. 如果在黑名單，直接攔截並回傳錯誤訊息
                return response()->json([
                    'status' => 'error',
                    'message' => '您的帳號已被封鎖，無法執行此操作。',
                    'code' => 403
                ], 403);
            }
        }
        return $next($request);
    }
}