<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    // 管理員登入 (Session + Remember Me)
    public function login(Request $request)
    {
        // 1. 驗證欄位
        $request->validate([
            'login_id' => 'required|string',
            'password' => 'required|string',
            'remember' => 'boolean', // 接收前端傳來的 true/false
        ]);

        // 2. 準備登入憑證
        $credentials = [
            'login_id' => $request->input('login_id'),
            'password' => $request->input('password'),
        ];

        // 3. 取得 Remember 狀態 (預設 false)
        // $request->boolean() 很聰明，可以處理 true, "true", 1, "1", "on"
        $remember = $request->boolean('remember');

        // 4. 嘗試登入
        // Auth::attempt 會自動做以下幾件事：
        // A. 找資料庫有沒有這個 login_id
        // B. 自動 Hash 檢查密碼是否正確
        // C. 如果正確，建立 Session
        // D. 如果 $remember 為 true，自動生成 remember_token 寫入資料庫並設定 Cookie
        if (Auth::guard('admin')->attempt($credentials, $remember)) {
            
            // 5. 安全性：重新產生 Session ID (防止 Session Fixation 攻擊)
            $request->session()->regenerate();

            return response()->json([
                'message' => '管理員登入成功',
                'role' => 'admin',
                'admin' => Auth::guard('admin')->user(), // 回傳管理員資料
            ]);
        }

        // 6. 登入失敗
        return response()->json([
            'message' => '登入失敗，帳號或密碼錯誤'
        ], 401);
    }

    // 管理員登出
    public function logout(Request $request)
    {
        // 1. 登出 admin guard
        Auth::guard('admin')->logout();

        // 2. 讓目前的 Session 失效
        $request->session()->invalidate();

        // 3. 重新產生 CSRF Token (安全性)
        $request->session()->regenerateToken();

        return response()->json(['message' => '管理員已登出']);
    }

    // 取得管理員自己資料
    public function me(Request $request)
    {
        // 直接回傳目前 Session 中的管理員
        return response()->json(Auth::guard('admin')->user());
    }
}