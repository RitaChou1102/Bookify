<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Member;
use App\Models\Business;
use App\Models\Book;
use App\Models\BookCategory;
use App\Models\Author;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\OrderDetail;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    protected $memberUser;
    protected $memberToken;
    protected $book;
    protected $order;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. 建立基礎資料 (廠商、作者、分類、書籍)
        $bizUser = User::factory()->create(['role' => 'business']);
        $business = Business::create([
            'user_id' => $bizUser->user_id,
            'store_name' => 'Book Store',
            'bank_account' => '123'
        ]);
        
        $author = Author::create(['name' => 'Test Author']);
        $category = BookCategory::create(['name' => 'Test Category']);

        $this->book = Book::create([
            'name' => 'Reviewable Book',
            'business_id' => $business->business_id,
            'author_id' => $author->author_id,
            'category_id' => $category->category_id,
            'price' => 500,
            'stock' => 10,
            'isbn' => '9780000000001',
            'publisher' => 'Test Pub',
            'condition' => 'new',
            'listing' => true,
            'edition' => 1,
            'publish_date' => '2023-01-01'
        ]);

        // 2. 建立會員
        $this->memberUser = User::factory()->create(['role' => 'member']);
        Member::create(['user_id' => $this->memberUser->user_id]);
        $this->memberToken = $this->memberUser->createToken('member_token', ['role:member'])->plainTextToken;

        // 3. 建立一張「已完成」的訂單 (代表會員買過這本書)
        // 注意：ReviewController 檢查的是 Order::STATUS_COMPLETED
        // 請確保這裡的狀態字串與 Order Model 中的常數一致 (通常是 'Completed' 或 'completed')
        $this->order = Order::create([
            'member_id' => $this->memberUser->member->member_id,
            'business_id' => $business->business_id,
            'total_amount' => 500,
            'shipping_fee' => 60,
            'payment_method' => 'Credit_card',
            'order_status' => 'Completed', // 假設這是 Order::STATUS_COMPLETED 的值
        ]);

        OrderDetail::create([
            'order_id' => $this->order->order_id,
            'book_id' => $this->book->book_id, // 綁定這本書
            'quantity' => 1,
            'price' => 500
        ]);
    }

    /**
     * 測試：任何人都能查看書籍評價
     */
    public function test_public_can_view_book_reviews()
    {
        // 先手動建立一筆評價
        Review::create([
            'book_id' => $this->book->book_id,
            'order_id' => $this->order->order_id,
            'rating' => 5,
            'comment' => 'Great book!',
            'review_time' => now()
        ]);

        $response = $this->getJson("/api/books/{$this->book->book_id}/reviews");

        $response->assertStatus(200)
                 ->assertJsonFragment(['comment' => 'Great book!'])
                 ->assertJsonFragment(['rating' => 5]);
    }

    /**
     * 測試：會員可以對「已購買且已完成」的訂單提交評價
     */
    public function test_member_can_submit_review_for_completed_order()
    {
        $data = [
            'book_id' => $this->book->book_id,
            'order_id' => $this->order->order_id,
            'rating' => 4,
            'comment' => 'Good read.',
        ];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->memberToken])
                         ->postJson('/api/reviews', $data);

        $response->assertStatus(200)
                 ->assertJson(['message' => '評價已送出']);

        $this->assertDatabaseHas('reviews', [
            'book_id' => $this->book->book_id,
            'comment' => 'Good read.',
            'rating' => 4
        ]);
    }

    /**
     * 測試：會員不能評價「未完成」的訂單
     */
    public function test_member_cannot_review_incomplete_order()
    {
        // 建立一張「運送中」的訂單
        $incompleteOrder = Order::create([
            'member_id' => $this->memberUser->member->member_id,
            'business_id' => $this->book->business_id,
            'total_amount' => 500,
            'shipping_fee' => 60,
            'payment_method' => 'Credit_card',
            'order_status' => 'Shipped',
        ]);

        $data = [
            'book_id' => $this->book->book_id,
            'order_id' => $incompleteOrder->order_id,
            'rating' => 5,
            'comment' => 'Too early to review',
        ];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->memberToken])
                         ->postJson('/api/reviews', $data);

        // 預期被拒絕 (403)
        $response->assertStatus(403);
    }

    /**
     * 測試：會員不能評價「不是自己的」訂單
     */
    public function test_member_cannot_review_others_order()
    {
        // 建立另一個會員的訂單
        $otherUser = User::factory()->create(['role' => 'member']);
        Member::create(['user_id' => $otherUser->user_id]);
        
        $othersOrder = Order::create([
            'member_id' => $otherUser->member->member_id, // 別人的 ID
            'business_id' => $this->book->business_id,
            'total_amount' => 500,
            'shipping_fee' => 60,
            'payment_method' => 'Credit_card',
            'order_status' => 'Completed',
        ]);

        $data = [
            'book_id' => $this->book->book_id,
            'order_id' => $othersOrder->order_id,
            'rating' => 5,
            'comment' => 'Hacking review',
        ];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->memberToken])
                         ->postJson('/api/reviews', $data);

        // 預期被拒絕 (403)
        $response->assertStatus(403);
    }

    /**
     * 測試：評分數值驗證 (1-5)
     */
    public function test_rating_validation()
    {
        $data = [
            'book_id' => $this->book->book_id,
            'order_id' => $this->order->order_id,
            'rating' => 6, // 超出範圍
            'comment' => 'Invalid rating',
        ];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->memberToken])
                         ->postJson('/api/reviews', $data);

        $response->assertStatus(422) // Unprocessable Entity
                 ->assertJsonValidationErrors(['rating']);
    }
}