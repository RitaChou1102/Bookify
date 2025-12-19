<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Member;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Book;
use App\Models\Business; // 用於建立書籍的廠商
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;
    protected $book;
    protected $member;

    /**
     * 每個測試執行前的準備工作
     */
    protected function setUp(): void
    {
        parent::setUp();

        // 1. 建立會員
        $this->user = User::factory()->create(['role' => 'member']);
        
        // [修改] 儲存 member 實例
        $this->member = Member::create(['user_id' => $this->user->user_id]);

        // 2. 登入並取得 Token
        $this->token = $this->user->createToken('test_token')->plainTextToken;

        // 3. 準備書籍資料 (需要先有廠商)
        $businessUser = User::factory()->create(['role' => 'business']);
        $business = Business::create([
            'user_id' => $businessUser->user_id, 
            'bank_account' => '123'
        ]);
        
        $this->book = Book::create([
            'name' => 'Test Book',
            'business_id' => $business->business_id,
            'price' => 500,
            'stock' => 10,
            'listing' => true,
            'isbn' => '9789570000000',
            'publisher' => 'Test Publisher',
            'condition' => 'new',
            'publish_date' => '2023-01-01',
            'edition' => 1,
            
            // [修改] 設為 null 避免外鍵錯誤 (因為沒有建立 Author 資料)
            'author_id' => null, 
        ]);
    }

    /**
     * 測試：將商品加入購物車
     */
    public function test_can_add_item_to_cart()
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->postJson('/api/cart/items', [
                'book_id' => $this->book->book_id,
                'quantity' => 2,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => '商品已成功加入購物車',
                'cart_item' => [
                    'book_id' => $this->book->book_id,
                    'quantity' => 2,
                    'price' => 500,
                    'subtotal' => 1000, // 500 * 2
                ]
            ]);

        // 驗證資料庫
        $cart = Cart::where('member_id', $this->member->member_id)->first();
        $this->assertNotNull($cart);
        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->cart_id,
            'book_id' => $this->book->book_id,
            'quantity' => 2,
        ]);
    }

    /**
     * 測試：庫存不足無法加入
     */
    public function test_cannot_add_item_if_stock_insufficient()
    {
        // 書本庫存只有 10，嘗試加入 11
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->postJson('/api/cart/items', [
                'book_id' => $this->book->book_id,
                'quantity' => 11,
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => '庫存不足'
            ]);
    }

    /**
     * 測試：查看購物車內容
     */
    public function test_can_view_cart()
    {
        // 先手動建立一個購物車項目
        $cart = Cart::create(['member_id' => $this->member->member_id]);
        CartItem::create([
            'cart_id' => $cart->cart_id,
            'book_id' => $this->book->book_id,
            'quantity' => 3,
            'price' => 500,
            // subtotal 會由 boot 自動計算或手動填入，這裡手動填以防萬一
            'subtotal' => 1500, 
        ]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->getJson('/api/cart');

        $response->assertStatus(200)
            ->assertJson([
                'member_id' => $this->member->member_id,
                'items' => [
                    [
                        'book_id' => $this->book->book_id,
                        'quantity' => 3,
                        'subtotal' => 1500,
                    ]
                ],
                'summary' => [
                    'total_items' => 3,
                    'total_amount' => 1500,
                ]
            ]);
    }

    /**
     * 測試：更新購物車商品數量
     */
    public function test_can_update_cart_item_quantity()
    {
        // 準備資料
        $cart = Cart::create(['member_id' => $this->member->member_id]);
        $item = CartItem::create([
            'cart_id' => $cart->cart_id,
            'book_id' => $this->book->book_id,
            'quantity' => 1,
            'price' => 500,
        ]);

        // 發送請求：改成 5 本
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->putJson("/api/cart/items/{$item->cart_item_id}", [
                'quantity' => 5,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('cart_item.quantity', 5)
            ->assertJsonPath('cart_item.subtotal', 2500); // 5 * 500

        $this->assertDatabaseHas('cart_items', [
            'cart_item_id' => $item->cart_item_id,
            'quantity' => 5,
        ]);
    }

    /**
     * 測試：移除單一商品
     */
    public function test_can_remove_item_from_cart()
    {
        $cart = Cart::create(['member_id' => $this->member->member_id]);
        $item = CartItem::create([
            'cart_id' => $cart->cart_id,
            'book_id' => $this->book->book_id,
            'quantity' => 1,
            'price' => 500,
        ]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->deleteJson("/api/cart/items/{$item->cart_item_id}");

        $response->assertStatus(200)
            ->assertJson(['message' => '商品已從購物車移除']);

        $this->assertDatabaseMissing('cart_items', [
            'cart_item_id' => $item->cart_item_id,
        ]);
    }

    /**
     * 測試：清空購物車
     */
    public function test_can_clear_cart()
    {
        $cart = Cart::create(['member_id' => $this->member->member_id]);
        // 建立兩個項目
        CartItem::create(['cart_id' => $cart->cart_id, 'book_id' => $this->book->book_id, 'quantity' => 1, 'price' => 500]);
        // 假設有第二本書...這裡簡單重複用同一本模擬多筆資料，或再 create 一本
        CartItem::create(['cart_id' => $cart->cart_id, 'book_id' => $this->book->book_id, 'quantity' => 2, 'price' => 500]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->deleteJson("/api/cart/clear");

        $response->assertStatus(200)
            ->assertJson(['message' => '購物車已清空']);

        $this->assertEquals(0, CartItem::where('cart_id', $cart->cart_id)->count());
    }

    /**
     * 測試：非會員 (商家) 不能使用購物車
     */
    public function test_business_cannot_use_cart()
    {
        // 建立一個商家 User
        $businessUser = User::factory()->create(['role' => 'business']);
        $token = $businessUser->createToken('biz_token')->plainTextToken;

        // 嘗試加入購物車
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/cart/items', [
                'book_id' => $this->book->book_id,
                'quantity' => 1,
            ]);

        $response->assertStatus(403)
            ->assertJson(['message' => '非會員無法使用購物車']);
    }
}