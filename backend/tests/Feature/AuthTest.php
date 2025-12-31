<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 測試：一般會員 (Member) 註冊成功
     */
    public function test_member_can_register_successfully()
    {
        // 1. 準備註冊資料
        $data = [
            'login_id' => 'member01',
            'name' => 'Test Member',
            'email' => 'member@example.com',
            'password' => 'password123',
            'role' => 'member',
            'phone' => '0912345678',
        ];

        // 2. 發送請求
        $response = $this->postJson('/api/register', $data);

        // 3. 驗證回應：狀態 201 Created，且包含 token
        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'user' => ['user_id', 'login_id', 'role'],
                     'token'
                 ]);

        // 4. 驗證資料庫
        $this->assertDatabaseHas('users', ['email' => 'member@example.com']);
        
        // 取得剛剛建立的使用者
        $user = User::where('email', 'member@example.com')->first();
        
        // 確認有建立 Member 資料
        $this->assertDatabaseHas('members', ['user_id' => $user->user_id]);

        // 確認有建立購物車 (Cart)
        // 注意：Cart 的 member_id 外鍵指向 members.member_id
        $member = \App\Models\Member::where('user_id', $user->user_id)->first();
        $this->assertDatabaseHas('carts', ['member_id' => $member->member_id]);
    }

    /**
     * 測試：商家 (Business) 註冊成功
     */
    public function test_business_can_register_successfully()
    {
        $data = [
            'login_id' => 'biz01',
            'name' => 'Test Business',
            'email' => 'biz@example.com',
            'password' => 'password123',
            'role' => 'business',
        ];

        $response = $this->postJson('/api/register', $data);

        $response->assertStatus(201);

        // 驗證資料庫
        $user = User::where('email', 'biz@example.com')->first();
        
        // 確認有建立 Business 資料
        $this->assertDatabaseHas('businesses', ['user_id' => $user->user_id]);
        
        // 確認商家「沒有」建立 Member 資料
        $this->assertDatabaseMissing('members', ['user_id' => $user->user_id]);
        
        // 確認商家「沒有」建立 Cart (假設商家不需要購物車)
        $this->assertDatabaseMissing('carts', ['member_id' => $user->user_id]);
    }

    /**
     * 測試：註冊資料重複應失敗
     */
    public function test_cannot_register_with_existing_email_or_login_id()
    {
        // 先建立一個使用者
        User::factory()->create([
            'login_id' => 'existing_user',
            'email' => 'exist@example.com',
        ]);

        // 嘗試用同樣的資料註冊
        $data = [
            'login_id' => 'existing_user', // 重複
            'name' => 'New Guy',
            'email' => 'exist@example.com', // 重複
            'password' => 'password123',
            'role' => 'member',
        ];

        $response = $this->postJson('/api/register', $data);

        // 驗證：狀態 422 Unprocessable Entity
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['login_id', 'email']);
    }

    /**
     * 測試：登入成功
     */
    public function test_user_can_login()
    {
        $password = 'secret123';
        $user = User::factory()->create([
            'password' => $password, // User Model casts 會自動 hash，或是 Factory 裡面有處理
        ]);

        $response = $this->postJson('/api/login', [
            'login_id' => $user->login_id,
            'password' => $password,
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['token']);
    }

    /**
     * 測試：登入失敗 (密碼錯誤)
     */
    public function test_user_cannot_login_with_wrong_password()
    {
        $user = User::factory()->create(['password' => 'correct_password']);

        $response = $this->postJson('/api/login', [
            'login_id' => $user->login_id,
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(401);
    }
}