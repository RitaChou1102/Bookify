<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Member;
use App\Models\Business;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
                'role' => 'required|in:member,business',
                'phone' => 'nullable|string|max:50',
                'address' => 'nullable|string|max:500',
            ]);

            DB::beginTransaction();

            // 2. 建立使用者
            $user = User::create([
                'login_id' => $validated['login_id'],
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => $validated['role'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
            ]);

            // 3. 根據角色建立對應資料
            if ($validated['role'] === 'business') {
                Business::create([
                    'user_id' => $user->user_id,
                    'bank_account' => ''
                ]);
            } else {
                $member = Member::create(['user_id' => $user->user_id]);
                Cart::create(['member_id' => $member->member_id]);
            }

            // 4. 發放 Token
            $token = $user->createToken('auth_token', ['user-access'])->plainTextToken;

            // 5. 載入關聯資料以便回傳完整結構
            $user->load(['member', 'business']);

            DB::commit();

            return response()->json([
                'message' => '註冊成功',
                'user' => $user, // 直接回傳模型，它會自動包含 loaded 的 relations
                'token' => $token
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json(['message' => '註冊失敗', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => '註冊失敗', 'error' => $e->getMessage()], 500);
        }
    }

    // 🟢 [重點修正] 登入
    public function login(Request $request)
    {
        $validated = $request->validate([
            'login_id' => 'required|string',
            'password' => 'required|string',
        ]);

        // 🟢 修正 1: 使用 with() 預先載入 member 和 business 關聯
        $user = User::select('user_id', 'login_id', 'name', 'email', 'password', 'role', 'phone', 'address')
            ->where('login_id', $validated['login_id'])
            ->with(['member', 'business']) // 這裡告訴 Laravel 把這兩個關聯一起抓出來
            ->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json(['message' => '帳號或密碼錯誤'], 401);
        }

        // 刪除舊 Token
        DB::table('personal_access_tokens')
            ->where('tokenable_type', get_class($user))
            ->where('tokenable_id', $user->user_id)
            ->delete();
        
        $token = $user->createToken('auth_token', ['user-access'])->plainTextToken;

        // 🟢 修正 2: 回傳資料時，確保包含 member 和 business
        // 您原本的手動陣列寫法漏掉了這兩個，建議直接回傳 $user 物件
        // 或者像下面這樣手動補上：
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
                'avatar' => $user->avatar, // 假設有頭像
                // ⬇️ 這裡就是讓前端 Navbar 能夠判斷的關鍵
                'member' => $user->member,
                'business' => $user->business, 
            ],
            'token' => $token,
            'role' => $user->role
        ], 200);
    }

    // 登出
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => '已登出']);
    }
    
    // 🟢 [重點修正] 取得個人資料
    public function profile(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json(['message' => '認證失敗'], 401);
            }

            // 🟢 修正: 不用手動 DB::table 查詢，直接用 Eloquent 的 load
            // 這會自動根據 User 模型裡的設定去抓資料，無論 role 欄位是什麼
            $user->load(['member', 'business']);

            // 這裡可以檢查一下，如果有關聯但 role 不對，順手修復 (Optional)
            if ($user->business && $user->role !== 'business') {
                $user->update(['role' => 'business']);
            }

            return response()->json([
                'user' => $user // 這會包含 user 資訊以及 member 和 business 物件
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            
        } catch (\Exception $e) {
            Log::error('Profile API error', ['error' => $e->getMessage()]);
            return response()->json(['message' => '取得個人資料失敗'], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => 'string|max:255',
            'phone' => 'string|max:50',
            'address' => 'string|max:500',
        ]);

        $user->update($validated);
        // 更新後重新載入關聯，確保前端拿到最新狀態
        $user->load(['member', 'business']);

        return response()->json(['message' => '個人資料更新成功', 'user' => $user]);
    }

    public function registerVendor(Request $request)
    {
        $user = $request->user();

        if ($user->business) {
            return response()->json(['message' => '您已經是賣家了，無需重複申請'], 400);
        }

        $validated = $request->validate([
            'store_name' => 'required|string|max:50',
            'bank_account' => 'required|string|max:30',
            'email' => 'required|email',
            'phone' => 'required|string',
        ]);

        \App\Models\Business::create([
            'user_id' => $user->user_id,
            'store_name' => $validated['store_name'],
            'bank_account' => $validated['bank_account'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ]);

        // 🟢 修正: 申請通過後，將使用者角色更新為 business
        $user->update(['role' => 'business']);

        return response()->json(['message' => '恭喜！您已成功開通賣家功能']);
    }
}