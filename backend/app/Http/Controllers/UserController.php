<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    // 取得個人資料
    public function show(Request $request)
    {
        // 取得目前登入使用者，並連帶抓取 member 關聯資料
        $user = $request->user()->load('member');
        
        return response()->json([
            'id' => $user->user_id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            // 如果 member 存在就回傳，不存在給空字串
            'phone' => $user->member ? $user->member->phone : '',
            'address' => $user->member ? $user->member->address : '',
            'avatar' => $user->avatar ?? '', // 如果你有做頭像上傳
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed', // confirmed 會自動檢查 new_password_confirmation
        ]);

        $user = $request->user();

        // 1. 檢查舊密碼是否正確
        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['目前的密碼不正確'],
            ]);
        }

        // 2. 更新密碼
        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json(['message' => '密碼修改成功']);
    }

    // 更新個人資料
    public function update(Request $request)
    {
        $user = $request->user();

        // 1. 驗證資料
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        return DB::transaction(function () use ($user, $validated) {
            // 2. 更新 User 表 (姓名)
            $user->update(['name' => $validated['name']]);

            // 3. 更新或建立 Member 表 (電話、地址)
            // 只有一般會員 (Buyer) 通常才會有 member 資料，但這裡我們寬鬆處理
            if ($user->role !== 'admin') {
                $user->member()->updateOrCreate(
                    ['user_id' => $user->user_id], // 搜尋條件
                    [
                        'phone' => $validated['phone'],
                        'address' => $validated['address']
                    ] // 更新內容
                );
            }

            // 重新載入資料回傳
            $user->load('member');

            return response()->json([
                'message' => '資料更新成功',
                'user' => [
                    'name' => $user->name,
                    'phone' => $user->member->phone ?? '',
                    'address' => $user->member->address ?? '',
                ]
            ]);
        });
    }
}