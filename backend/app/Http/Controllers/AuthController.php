<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Member;
use App\Models\Business;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // 註冊
    public function register(Request $request)
    {
        // 1. 驗證欄位
        $validated = $request->validate([
            'login_id' => 'required|unique:users',
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:member,business' // 確保角色正確
        ]);

        // 2. 建立使用者
        $user = User::create([
            'login_id' => $validated['login_id'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']), // 密碼加密
            'role' => $validated['role'] ?? 'member',
        ]);

        // 3. 根據角色建立對應資料
        if ($validated['role'] === 'business') {
            Business::create([
                'user_id' => $user->user_id,
                'bank_account' => '' // 暫空
            ]);
        } else {
            // 預設為一般會員
            $member = Member::create(['user_id' => $user->user_id]);
            // 會員註冊同時建立購物車
            Cart::create(['member_id' => $member->member_id]);
        }

        // 4. 發放 Token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => '註冊成功',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    // 登入
    public function login(Request $request)
    {
        // 檢查帳號密碼
        if (!Auth::attempt($request->only('login_id', 'password'))) {
            return response()->json(['message' => '帳號或密碼錯誤'], 401);
        }

        // 撈出使用者
        $user = User::where('login_id', $request['login_id'])->firstOrFail();
        
        // 刪除舊 Token 並發新 Token (單一裝置登入機制)
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => '登入成功',
            'user' => $user,
            'token' => $token,
            'role' => $user->role // 回傳角色以便前端導向不同頁面
        ]);
    }

    // 登出
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => '已登出']);
    }
    
    // 取得個人資料
    public function profile(Request $request)
    {
        return $request->user()->load(['member', 'business']);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'name' => 'string|max:255',
            'phone' => 'string|max:50',
            'address' => 'string|max:500',
            // 若要允許改密碼需額外處理 hash
        ]);

        $user->update($validated);

        return response()->json(['message' => '個人資料更新成功', 'user' => $user]);
    }
}