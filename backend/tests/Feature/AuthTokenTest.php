<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Member;
use App\Models\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTokenTest extends TestCase
{
    // 使用 RefreshDatabase 確保每次測試後資料庫重置，避免資料殘留
    use RefreshDatabase;

    /**
     * 測試使用者登入並成功獲取 Token
     */
    public function test_user_can_login_and_get_token()
    {
        // 1. 準備：建立一個使用者
        // 注意：根據 AuthController，密碼在建立時會被 Hash
        $password = 'password123';
        $user = User::factory()->create([
            'login_id' => 'testuser01',
            'password' => $password, // User Model 的 cast 或 mutator 會處理 hash，若沒有則需手動 Hash::make
            'role' => 'member',
        ]);

        // 2. 執行：發送登入請求
        $response = $this->postJson('/api/login', [
            'login_id' => 'testuser01',
            'password' => $password,
        ]);

        // 3. 驗證：檢查回應狀態與結構
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'user',
                     'token', // 確保回傳包含 token
                     'role'
                 ]);
    }

/**
     * 測試攜帶有效 Token 訪問受保護的 Profile 路由
     */
    public function test_authenticated_user_can_access_profile()
    {
        // 1. 準備：建立使用者與關聯資料
        $user = User::factory()->create(['role' => 'member']);
        Member::create(['user_id' => $user->user_id]); 
        
        // [修正] 不要寫死 'member_id' => 1，而是使用 $user->user_id
        Cart::create(['member_id' => $user->user_id]); 

        // 產生 Token
        $token = $user->createToken('test_token')->plainTextToken;

        // 2. 執行：攜帶 Token 訪問受保護路由
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/profile');

        // 3. 驗證
        $response->assertStatus(200)
                 ->assertJson([
                     'user' => [
                         'user_id' => $user->user_id,
                         'login_id' => $user->login_id,
                         'email' => $user->email,
                     ]
                 ]);
    }

    /**
     * 測試未攜帶 Token 訪問受保護路由應失敗
     */
    public function test_unauthenticated_user_cannot_access_profile()
    {
        // 1. 執行：不帶 Header 直接訪問
        $response = $this->getJson('/api/profile');

        // 2. 驗證：應該被拒絕 (401 Unauthorized)
        $response->assertStatus(401);
    }
}