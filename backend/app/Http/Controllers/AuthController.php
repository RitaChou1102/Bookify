<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Member;
use App\Models\Business;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

            // 優化：使用資料庫交易確保資料一致性，並提升效能
            DB::beginTransaction();

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
                Cart::create(['member_id' => $user->user_id]);
            }

            // 4. 發放 Token（帶有 user-access 能力）
            $token = $user->createToken('auth_token', ['user-access'])->plainTextToken;

            DB::commit();

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
            DB::rollBack();
            return response()->json([
                'message' => '註冊失敗，請檢查輸入資料',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // 處理其他錯誤
            DB::rollBack();
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

        // 2. 根據 login_id 查找使用者（使用 select 只載入必要欄位）
        $user = User::select('user_id', 'login_id', 'name', 'email', 'password', 'role', 'phone', 'address')
            ->where('login_id', $validated['login_id'])
            ->first();

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
        // 優化：使用 DB 直接刪除，比 Eloquent 更快
        // 如果 token 數量很多，直接使用 SQL DELETE 會比 Eloquent 快很多
        DB::table('personal_access_tokens')
            ->where('tokenable_type', get_class($user))
            ->where('tokenable_id', $user->user_id)
            ->delete();
        
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
        try {
            // 先取得用戶基本資料，不載入任何關聯
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'message' => '未找到用戶資料',
                    'error' => '認證失敗'
                ], 401);
            }
            
            // 初始化變數
            $member = null;
            $business = null;
            
            // 根據角色查詢對應資料（使用 try-catch 避免查詢錯誤導致卡住）
            try {
                if ($user->role === 'member') {
                    $memberData = DB::table('members')
                        ->select('member_id', 'user_id')
                        ->where('user_id', $user->user_id)
                        ->limit(1)  // 確保只查詢一筆
                        ->first();
                    
                    if ($memberData) {
                        $member = [
                            'member_id' => $memberData->member_id,
                            'user_id' => $memberData->user_id,
                        ];
                    }
                } elseif ($user->role === 'business') {
                    $businessData = DB::table('businesses')
                        ->select('business_id', 'user_id', 'store_name', 'bank_account')
                        ->where('user_id', $user->user_id)
                        ->limit(1)  // 確保只查詢一筆
                        ->first();
                    
                    if ($businessData) {
                        $business = [
                            'business_id' => $businessData->business_id,
                            'user_id' => $businessData->user_id,
                            'store_name' => $businessData->store_name,
                            'bank_account' => $businessData->bank_account,
                        ];
                    }
                }
            } catch (\Exception $e) {
                // 如果查詢失敗，記錄錯誤但繼續執行
                Log::warning('Profile query failed', [
                    'user_id' => $user->user_id,
                    'error' => $e->getMessage()
                ]);
                // 不中斷執行，只返回 null
            }
            
            // 返回資料
            return response()->json([
                'user' => [
                    'user_id' => $user->user_id,
                    'login_id' => $user->login_id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'phone' => $user->phone,
                    'address' => $user->address,
                    'member' => $member,
                    'business' => $business,
                ]
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            
        } catch (\Exception $e) {
            // 捕獲所有異常，避免請求卡住
            Log::error('Profile API error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => '取得個人資料失敗',
                'error' => config('app.debug') ? $e->getMessage() : '伺服器錯誤'
            ], 500);
        }
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