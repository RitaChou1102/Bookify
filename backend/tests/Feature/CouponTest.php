<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Business;
use App\Models\Coupon;
use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponTest extends TestCase
{
    use RefreshDatabase;

    protected $businessUser;
    protected $businessToken;
    protected $business;
    
    protected $memberUser;
    protected $memberToken;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. 建立廠商用戶與廠商資料
        $this->businessUser = User::factory()->create(['role' => 'business']);
        $this->business = Business::create([
            'user_id' => $this->businessUser->user_id,
            'store_name' => 'Coupon Store',
            'bank_account' => '888-888'
        ]);
        $this->businessToken = $this->businessUser->createToken('biz_token')->plainTextToken;

        // 2. 建立一般會員
        $this->memberUser = User::factory()->create(['role' => 'member']);
        Member::create(['user_id' => $this->memberUser->user_id]);
        $this->memberToken = $this->memberUser->createToken('member_token')->plainTextToken;
    }

    /**
     * 測試：廠商可以新增優惠券
     */
    public function test_business_can_create_coupon()
    {
        $data = [
            'name' => 'Grand Opening',
            'code' => 'SAVE2025',
            'discount_type' => 'fixed',
            'discount_value' => 100,
            'limit_price' => 500, // 滿 500 折 100
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
            'usage_limit' => 100,
            'coupon_type' => 'seasonal'
        ];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->businessToken])
                         ->postJson('/api/coupons', $data);

        $response->assertStatus(201)
                 ->assertJson(['message' => '建立成功']);

        $this->assertDatabaseHas('coupons', [
            'code' => 'SAVE2025',
            'business_id' => $this->business->business_id
        ]);
    }

    /**
     * 測試：一般會員不能新增優惠券
     */
    public function test_member_cannot_create_coupon()
    {
        $data = [
            'name' => 'Hacker Coupon',
            'code' => 'HACK100',
            'discount_type' => 'fixed',
            'discount_value' => 100,
            'limit_price' => 0,
            'start_date' => now()->toDateString(),
            'coupon_type' => 'seasonal'
        ];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->memberToken])
                         ->postJson('/api/coupons', $data);

        // 預期 403 Forbidden
        $response->assertStatus(403);
    }

    /**
     * 測試：驗證優惠券有效性 (Code Validation)
     */
    public function test_validate_active_coupon()
    {
        // 建立一個有效的優惠券
        $coupon = Coupon::create([
            'business_id' => $this->business->business_id,
            'name' => 'Active Coupon',
            'code' => 'ACTIVE',
            'discount_type' => 'percent_off',
            'discount_value' => 10,
            'limit_price' => 0,
            'start_date' => now()->subDay(), // 昨天開始
            'end_date' => now()->addDay(),   // 明天結束
            'coupon_type' => 'seasonal',
            'is_deleted' => false
        ]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->memberToken])
                         ->getJson("/api/coupons/validate/{$coupon->code}");

        $response->assertStatus(200)
                 ->assertJson(['valid' => true]);
    }

    /**
     * 測試：驗證過期或未開始的優惠券無效
     */
    public function test_validate_expired_coupon()
    {
        $coupon = Coupon::create([
            'business_id' => $this->business->business_id,
            'name' => 'Expired Coupon',
            'code' => 'EXPIRED',
            'discount_type' => 'fixed',
            'discount_value' => 50,
            'limit_price' => 0,
            'start_date' => now()->subDays(10),
            'end_date' => now()->subDays(5), // 已經結束
            'coupon_type' => 'seasonal',
            'is_deleted' => false
        ]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->memberToken])
                         ->getJson("/api/coupons/validate/{$coupon->code}");

        $response->assertStatus(400)
                 ->assertJson(['valid' => false]);
    }

    /**
     * 測試：廠商可以更新自己的優惠券
     */
    public function test_business_can_update_coupon()
    {
        $coupon = Coupon::create([
            'business_id' => $this->business->business_id,
            'name' => 'Old Name',
            'code' => 'UPDATE_ME',
            'discount_type' => 'fixed',
            'discount_value' => 50,
            'limit_price' => 0,
            'start_date' => now(),
            'coupon_type' => 'seasonal',
            'is_deleted' => false
        ]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->businessToken])
                         ->putJson("/api/coupons/{$coupon->coupon_id}", [
                             'name' => 'New Name'
                         ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('coupons', ['name' => 'New Name']);
    }

    /**
     * 測試：若優惠券已被使用，禁止修改折扣內容 (Business Logic Lock)
     */
    public function test_cannot_update_discount_value_if_coupon_used()
    {
        $coupon = Coupon::create([
            'business_id' => $this->business->business_id,
            'name' => 'Popular Coupon',
            'code' => 'POPULAR',
            'discount_type' => 'fixed',
            'discount_value' => 50,
            'limit_price' => 0,
            'start_date' => now(),
            'coupon_type' => 'seasonal',
            'is_deleted' => false,
            'used_count' => 5 // 已經被用過 5 次
        ]);

        // 嘗試修改折扣金額
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->businessToken])
                         ->putJson("/api/coupons/{$coupon->coupon_id}", [
                             'discount_value' => 100
                         ]);

        // 預期 422 Unprocessable Entity
        $response->assertStatus(422)
                 ->assertJson(['message' => '已有使用紀錄，不可修改折扣內容']);
    }

    /**
     * 測試：會員瀏覽廠商優惠券時，看不到未開始或過期的券
     */
    public function test_member_views_only_active_coupons()
    {
        // 1. 建立一個有效的
        Coupon::create([
            'business_id' => $this->business->business_id,
            'name' => 'Valid One',
            'code' => 'VALID',
            'discount_type' => 'fixed',
            'discount_value' => 10,
            'limit_price' => 0,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'coupon_type' => 'seasonal',
            'is_deleted' => 0
        ]);

        // 2. 建立一個還沒開始的 (未來)
        Coupon::create([
            'business_id' => $this->business->business_id,
            'name' => 'Future One',
            'code' => 'FUTURE',
            'discount_type' => 'fixed',
            'discount_value' => 10,
            'limit_price' => 0,
            'start_date' => now()->addDays(5),
            'coupon_type' => 'seasonal',
            'is_deleted' => 0
        ]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->memberToken])
                         ->getJson("/api/coupons/business/{$this->business->business_id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['code' => 'VALID'])
                 ->assertJsonMissing(['code' => 'FUTURE']);
    }
}