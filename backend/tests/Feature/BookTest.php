<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Business;
use App\Models\Book;
use App\Models\Author;
use App\Models\BookCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Admin; // [新增]
use Illuminate\Support\Facades\Hash; // [新增]

class BookTest extends TestCase
{
    use RefreshDatabase;

    protected $businessUser;
    protected $businessToken;
    protected $business;
    protected $author;
    protected $category;
    protected $book;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. 準備基礎資料：作者與分類 (建立書籍必備)
        $this->author = Author::create(['name' => 'Test Author']);
        $this->category = BookCategory::create(['name' => 'Test Category']);

        // 2. 準備一個「廠商」使用者
        $this->businessUser = User::factory()->create(['role' => 'business']);
        $this->business = Business::create([
            'user_id' => $this->businessUser->user_id,
            'store_name' => 'My Bookstore',
            'bank_account' => '123-456-789'
        ]);
        $this->businessToken = $this->businessUser->createToken('biz_token')->plainTextToken;

        // 3. 幫這個廠商建立一本書
        $this->book = Book::create([
            'name' => 'Original Book',
            'business_id' => $this->business->business_id,
            'author_id' => $this->author->author_id,
            'category_id' => $this->category->category_id,
            'isbn' => '978-0000000001',
            'price' => 500,
            'stock' => 10,
            'listing' => true,
            'publisher' => 'Test Publisher',
            'condition' => 'new',
            'publish_date' => '2023-01-01',
            'edition' => 1,
        ]);
    }

    /**
     * 測試：任何人都能看到書籍列表
     */
    public function test_public_can_view_books()
    {
        $response = $this->getJson('/api/books');

        $response->assertStatus(200)
                 ->assertJsonStructure(['data']); // 因為用了 paginate，資料會在 data 裡
    }

    /**
     * 測試：任何人都能看到單一書籍詳情
     */
    public function test_public_can_view_single_book()
    {
        $response = $this->getJson("/api/books/{$this->book->book_id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'book_id' => $this->book->book_id,
                     'name' => 'Original Book'
                 ]);
    }

    /**
     * 測試：任何人都能搜尋書籍
     */
    public function test_public_can_search_books()
    {
        $response = $this->getJson("/api/guest/search?keyword=Original");
        
        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Original Book']);
    }

    /**
     * 測試：廠商可以上架書籍 (Create)
     */
    public function test_business_can_create_book()
    {
        $data = [
            'name' => 'New Awesome Book',
            'author_id' => $this->author->author_id,
            'category_id' => $this->category->category_id,
            'isbn' => '978-0000000002',
            'price' => 300,
            'stock' => 5,
            'description' => 'A great book',
            'publish_date' => '2023-05-01',
            'publisher' => 'New Publisher',
            'condition' => 'new',
            'listing' => true,
            'edition' => 1,
        ];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->businessToken])
                         ->postJson('/api/books', $data);

        $response->assertStatus(201)
                 ->assertJson(['message' => '書籍上架成功']);

        $this->assertDatabaseHas('books', ['name' => 'New Awesome Book']);
    }

    /**
     * 測試：一般會員 (Member) 不能上架書籍
     */
    public function test_member_cannot_create_book()
    {
        // 建立一個普通會員
        $memberUser = User::factory()->create(['role' => 'member']);
        $token = $memberUser->createToken('mem_token')->plainTextToken;

        $data = [
            'name' => 'Member Book',
            // ... 其他必填欄位略，因為在權限檢查就會被擋下
        ];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
                         ->postJson('/api/books', $data);

        // 預期被拒絕 (403 Forbidden)
        $response->assertStatus(403);
    }

    /**
     * 測試：廠商可以修改自己的書
     */
    public function test_business_can_update_own_book()
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->businessToken])
                         ->putJson("/api/books/{$this->book->book_id}", [
                             'name' => 'Updated Book Name',
                             'price' => 999
                         ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('books', [
            'book_id' => $this->book->book_id,
            'name' => 'Updated Book Name',
            'price' => 999
        ]);
    }

    /**
     * 測試：廠商不能修改別人的書
     */
    public function test_business_cannot_update_others_book()
    {
        // 1. 建立「另一個」廠商 (Other Business)
        $otherUser = User::factory()->create(['role' => 'business']);
        $otherBusiness = Business::create([
            'user_id' => $otherUser->user_id,
            'store_name' => 'Other Store',
            'bank_account' => '999'
        ]);
        
        // 2. 建立「另一個廠商的書」
        $otherBook = Book::create([
            'name' => 'Other Book',
            'business_id' => $otherBusiness->business_id,
            'author_id' => $this->author->author_id,
            'category_id' => $this->category->category_id,
            'isbn' => '978-9999999999',
            'price' => 100,
            'stock' => 1,
            'listing' => true,
            'publisher' => 'Other Publisher',
            'condition' => 'used',
            'publish_date' => '2020-01-01',
            'edition' => 1,
        ]);

        // 3. 嘗試用「原本的廠商 Token」去修改「別人的書」
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->businessToken])
                         ->putJson("/api/books/{$otherBook->book_id}", [
                             'name' => 'Hacked Name'
                         ]);

        // 4. 預期被拒絕 (403)
        $response->assertStatus(403);
    }

    /**
     * 測試：廠商可以刪除自己的書
     */
    public function test_business_can_delete_own_book()
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->businessToken])
                         ->deleteJson("/api/books/{$this->book->book_id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => '書籍已刪除']);

        $this->assertDatabaseMissing('books', ['book_id' => $this->book->book_id]);
    }

    /**
     * 測試：管理員可以刪除任何書籍
     */
    public function test_admin_can_delete_any_book()
    {
        // 1. 建立管理員
        $admin = Admin::create([
            'name' => 'Super Admin',
            'login_id' => 'admin_delete_' . rand(1000, 9999),
            'password' => Hash::make('password'),
        ]);
        
        // 2. 發給他 Token (權限: admin:all)
        $token = $admin->createToken('admin_token', ['admin:all'])->plainTextToken;

        // 3. 執行刪除 (刪除 $this->book, 這本書是 setup 裡建立的, 屬於 business)
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
                         ->deleteJson("/api/admin/books/{$this->book->book_id}");

        // 4. 驗證
        $response->assertStatus(200)
                 ->assertJson(['message' => '書籍已刪除']);

        $this->assertDatabaseMissing('books', ['book_id' => $this->book->book_id]);
    }
}