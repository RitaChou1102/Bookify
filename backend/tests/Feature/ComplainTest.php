<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Member;
use App\Models\Business;
use App\Models\Order;
use App\Models\Complain;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ComplainTest extends TestCase
{
    use RefreshDatabase;

    protected $memberUser;
    protected $memberToken;
    protected $adminUser;
    protected $adminToken;
    protected $order;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. 建立一般會員
        $this->memberUser = User::factory()->create(['role' => 'member']);
        Member::create(['user_id' => $this->memberUser->user_id]);
        
        // [修正重點 2] 明確限制會員 Token 的權限
        // 如果不傳第二個參數，預設是 ['*'] (代表擁有所有權限)，這樣會導致他能通過管理員的檢查
        $this->memberToken = $this->memberUser->createToken('member_token', ['role:member'])->plainTextToken;

        // 2. 建立管理員
        $this->adminUser = Admin::create([
            'name' => 'Test Admin',
            'login_id' => 'admin_test_' . rand(1000, 9999),
            'password' => Hash::make('password'),
        ]);
        // 管理員擁有 admin:all 權限
        $this->adminToken = $this->adminUser->createToken('admin_token', ['admin:all'])->plainTextToken;

        // 3. 建立訂單 (需要先有商家)
        $bizUser = User::factory()->create(['role' => 'business']);
        $business = Business::create([
            'user_id' => $bizUser->user_id,
            'store_name' => 'Test Store',
            'bank_account' => '123456'
        ]);

        $this->order = Order::create([
            'member_id' => $this->memberUser->member->member_id,
            'business_id' => $business->business_id,
            'total_amount' => 1000,
            'shipping_fee' => 60,
            'payment_method' => 'Credit_card',
            'order_status' => 'Completed',
        ]);
    }

    /**
     * 測試：會員可以對自己的訂單提交客訴
     */
    public function test_member_can_submit_complain()
    {
        $data = [
            'order_id' => $this->order->order_id,
            'content' => '商品有瑕疵，請處理',
        ];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->memberToken])
                         ->postJson('/api/complains', $data);

        $response->assertStatus(201)
                 ->assertJson(['message' => '投訴已提交']);

        $this->assertDatabaseHas('complaints', [
            'order_id' => $this->order->order_id,
            'content' => '商品有瑕疵，請處理',
            'complaint_status' => 'pending',
        ]);
    }

    /**
     * 測試：會員不能對別人的訂單提交客訴
     */
    public function test_member_cannot_complain_others_order()
    {
        $otherUser = User::factory()->create(['role' => 'member']);
        Member::create(['user_id' => $otherUser->user_id]);
        $otherToken = $otherUser->createToken('other_token', ['role:member'])->plainTextToken;

        $data = [
            'order_id' => $this->order->order_id,
            'content' => '這不是我的訂單',
        ];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $otherToken])
                         ->postJson('/api/complains', $data);

        $response->assertStatus(403);
    }

    /**
     * 測試：管理員可以將客訴標記為「處理中」
     */
    public function test_admin_can_mark_complain_in_progress()
    {
        $complain = Complain::create([
            'order_id' => $this->order->order_id,
            'content' => '等待處理的客訴',
            'complaint_status' => 'pending',
            'complaint_time' => now(),
        ]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->adminToken])
                         ->patchJson("/api/admin/complains/{$complain->complaint_id}/in-progress");

        $response->assertStatus(200)
                 ->assertJsonPath('data.complaint_status', 'in_progress');

        $this->assertDatabaseHas('complaints', [
            'complaint_id' => $complain->complaint_id,
            'complaint_status' => 'in_progress'
        ]);
    }

    /**
     * 測試：管理員可以解決客訴並填寫結果
     */
    public function test_admin_can_resolve_complain()
    {
        $complain = Complain::create([
            'order_id' => $this->order->order_id,
            'content' => '處理中的客訴',
            'complaint_status' => 'in_progress',
            'complaint_time' => now(),
        ]);

        $data = [
            'result' => '已協助退款。'
        ];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->adminToken])
                         ->putJson("/api/admin/complains/{$complain->complaint_id}/resolve", $data);

        $response->assertStatus(200)
                 ->assertJson(['message' => '投訴已成功處理完畢']);

        $this->assertDatabaseHas('complaints', [
            'complaint_id' => $complain->complaint_id,
            'complaint_status' => 'resolved',
            'result' => '已協助退款。'
        ]);
    }

    /**
     * 測試：一般會員不能使用管理員的客訴處理功能
     */
    public function test_member_cannot_access_admin_complain_routes()
    {
        $complain = Complain::create([
            'order_id' => $this->order->order_id,
            'content' => '測試權限',
            'complaint_status' => 'pending',
            'complaint_time' => now(),
        ]);

        // 因為上面 setUp 已經限制了 memberToken 的權限，這裡應該會被拒絕 (403)
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->memberToken])
                         ->patchJson("/api/admin/complains/{$complain->complaint_id}/in-progress");

        $response->assertStatus(403);
    }
}