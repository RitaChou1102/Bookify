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
        try {
            // 1. 驗證欄位
            $validated = $request->validate([
                'login_id' => 'required|string|max:191|unique:users,login_id',
                'name' => 'required|string|max:191',
                'email' => 'required|email|max:191|unique:users,email',
                'password' => 'required|string|min:6|max:255',
                'role' => 'required|in:member,business', // 確保角色正確
                'phone' => 'nullable|string|max:50',
                'address' => 'nullable|string|max:500',
            ]);

            // 2. 建立使用者（密碼會自動透過 casts 進行 Hash）
            $user = User::create([
                'login_id' => $validated['login_id'],
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'], // 會自動透過 casts 進行 Hash
                'role' => $validated['role'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
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

            // 4. 發放 Token（帶有 user-access 能力）
            $token = $user->createToken('auth_token', ['user-access'])->plainTextToken;

            // 5. 回傳成功響應（不包含密碼）
            return response()->json([
                'message' => '註冊成功',
                'user' => [
                    'user_id' => $user->user_id,
                    'login_id' => $user->login_id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'phone' => $user->phone,
                    'address' => $user->address,
                ],
                'token' => $token
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // 處理驗證錯誤
            return response()->json([
                'message' => '註冊失敗，請檢查輸入資料',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // 處理其他錯誤
            return response()->json([
                'message' => '註冊失敗',
                'error' => config('app.debug') ? $e->getMessage() : '伺服器錯誤，請稍後再試'
            ], 500);
        }
    }

    // 登入
    public function login(Request $request)
    {
        // 1. 驗證輸入欄位
        $validated = $request->validate([
            'login_id' => 'required|string',
            'password' => 'required|string',
        ]);

        // 2. 根據 login_id 查找使用者
        $user = User::where('login_id', $validated['login_id'])->first();

        // 3. 驗證使用者是否存在且密碼正確
        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => '帳號或密碼錯誤',
                'errors' => [
                    'login_id' => ['提供的帳號或密碼不正確']
                ]
            ], 401);
        }

        // 4. 刪除舊 Token 並發新 Token (單一裝置登入機制)
        $user->tokens()->delete();
        $token = $user->createToken('auth_token', ['user-access'])->plainTextToken;

        // 5. 回傳成功響應
        return response()->json([
            'message' => '登入成功',
            'user' => [
                'user_id' => $user->user_id,
                'login_id' => $user->login_id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'phone' => $user->phone,
                'address' => $user->address,
            ],
            'token' => $token,
            'role' => $user->role // 回傳角色以便前端導向不同頁面
        ], 200);
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

        return response()->json(['message' => '個人資料更新成功', 'user' => $user], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            ->header('Content-Type', 'application/json; charset=utf-8');
    }
}