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
        // 取得目前登入使用者
        $user = $request->user();
        
        return response()->json([
            'id' => $user->user_id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            // phone 和 address 直接在 users 表中
            'phone' => $user->phone ?? '',
            'address' => $user->address ?? '',
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
            // 2. 更新 User 表 (姓名、電話、地址都在 users 表)
            $user->update([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'address' => $validated['address']
            ]);

            return response()->json([
                'message' => '資料更新成功',
                'user' => [
                    'name' => $user->name,
                    'phone' => $user->phone ?? '',
                    'address' => $user->address ?? '',
                ]
            ]);
        });
    }
}