<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Admin;
use App\Models\Blacklist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlacklistTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $adminToken;
    protected $user;
    protected $userToken;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. 建立管理員 (Admin) 與 Token
        // 注意：Admin 路由需要 'admin:all' 的能力
        $this->admin = Admin::create([
            'name' => 'Super Admin',
            'login_id' => 'admin01',
            'password' => bcrypt('password'),
        ]);
        $this->adminToken = $this->admin->createToken('admin-token', ['admin:all'])->plainTextToken;

        // 2. 建立一般會員 (User) 與 Token
        $this->user = User::factory()->create();
        $this->userToken = $this->user->createToken('user-token')->plainTextToken;
    }

    /**
     * 測試：管理員可以查看黑名單列表
     */
    public function test_admin_can_view_blacklist()
    {
        // 先手動建立一筆黑名單資料
        Blacklist::create([
            'blocked_userid' => $this->user->user_id,
            'banned_by' => $this->admin->admin_id,
            'reason' => 'Spamming',
            'created_at' => now(),
        ]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->adminToken])
                         ->getJson('/api/admin/blacklist');

        $response->assertStatus(200)
                 ->assertJsonFragment(['reason' => 'Spamming'])
                 ->assertJsonFragment(['blocked_userid' => $this->user->user_id]);
    }

    /**
     * 測試：管理員可以封鎖使用者 (Ban User)
     */
    public function test_admin_can_ban_user()
    {
        $data = [
            'user_id' => $this->user->user_id,
            'reason' => 'Violation of terms',
        ];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->adminToken])
                         ->postJson('/api/admin/ban-user', $data);

        $response->assertStatus(200)
                 ->assertJson(['message' => '使用者已封鎖']);

        // 驗證資料庫確實有資料
        $this->assertDatabaseHas('blacklist', [
            'blocked_userid' => $this->user->user_id,
            'reason' => 'Violation of terms',
            'banned_by' => $this->admin->admin_id
        ]);
    }

    /**
     * 測試：不能重複封鎖已經在黑名單的使用者
     */
    public function test_admin_cannot_ban_already_banned_user()
    {
        // 1. 先封鎖第一次
        Blacklist::create([
            'blocked_userid' => $this->user->user_id,
            'reason' => 'First ban',
            'banned_by' => $this->admin->admin_id,
        ]);

        // 2. 嘗試封鎖第二次
        $data = [
            'user_id' => $this->user->user_id,
            'reason' => 'Second ban',
        ];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->adminToken])
                         ->postJson('/api/admin/ban-user', $data);

        // 預期回傳 422 (Controller 邏輯設定)
        $response->assertStatus(422)
                 ->assertJson(['message' => '該使用者已被封鎖，請勿重複操作']);
    }

    /**
     * 測試：管理員可以解除封鎖 (Unban User)
     */
    public function test_admin_can_unban_user()
    {
        // 1. 先建立封鎖紀錄
        Blacklist::create([
            'blocked_userid' => $this->user->user_id,
            'reason' => 'To be unbanned',
            'banned_by' => $this->admin->admin_id,
        ]);

        // 2. 呼叫解除封鎖 API
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->adminToken])
                         ->deleteJson("/api/admin/unban-user/{$this->user->user_id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => '使用者已成功解除封鎖']);

        // 驗證資料庫已刪除該筆資料
        $this->assertDatabaseMissing('blacklist', [
            'blocked_userid' => $this->user->user_id,
        ]);
    }

    /**
     * 測試：正常會員(未被封鎖)可以訪問受保護的 API
     */
    public function test_normal_user_can_access_protected_api()
    {
        // 嘗試訪問 /api/profile (受 auth + check.blacklist 保護)
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->userToken])
                         ->getJson('/api/profile');

        // 只要不是 403 就代表通過了黑名單檢查 (可能是 200)
        $response->assertStatus(200); 
    }

    /**
     * 測試：被封鎖的會員 **無法** 訪問受保護的 API (Middleware 生效驗證)
     */
    public function test_banned_user_cannot_access_protected_api()
    {
        // 1. 先將此會員加入黑名單
        Blacklist::create([
            'blocked_userid' => $this->user->user_id,
            'reason' => 'You are banned!',
            'banned_by' => $this->admin->admin_id,
        ]);

        // 2. 嘗試訪問受保護的路徑 /api/profile
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->userToken])
                         ->getJson('/api/profile');

        // 3. 預期被 Middleware 攔截，回傳 403 Forbidden
        $response->assertStatus(403)
                 ->assertJson([
                     'status' => 'error',
                     'message' => '您的帳號已被封鎖，無法執行此操作。'
                 ]);
    }
}