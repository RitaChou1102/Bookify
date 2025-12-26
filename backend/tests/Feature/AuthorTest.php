<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Admin;
use App\Models\Author;
use App\Models\Book;
use App\Models\BookCategory;
use App\Models\Business;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthorTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $adminToken;
    protected $memberToken;
    protected $author;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. 建立管理員 (擁有 admin:all 權限)
        $this->adminUser = Admin::create([
            'name' => 'Super Admin',
            'login_id' => 'admin_author_' . rand(1000, 9999),
            'password' => Hash::make('password'),
        ]);
        $this->adminToken = $this->adminUser->createToken('admin_token', ['admin:all'])->plainTextToken;

        // 2. 建立一般會員 (擁有 role:member 權限)
        $member = User::factory()->create(['role' => 'member']);
        $this->memberToken = $member->createToken('member_token', ['role:member'])->plainTextToken;

        // 3. 建立一個測試用的作者
        $this->author = Author::create(['name' => 'J.K. Rowling']);
    }

    /**
     * 測試：任何人都能取得作者列表
     */
    public function test_public_can_view_author_list()
    {
        $response = $this->getJson('/api/authors');

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'J.K. Rowling']);
    }

    /**
     * 測試：任何人都能查看單一作者
     */
    public function test_public_can_view_single_author()
    {
        $response = $this->getJson("/api/authors/{$this->author->author_id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'author_id' => $this->author->author_id,
                     'name' => 'J.K. Rowling'
                 ]);
    }

    /**
     * 測試：管理員可以新增作者
     */
    public function test_admin_can_create_author()
    {
        $data = ['name' => 'New Author'];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->adminToken])
                         ->postJson('/api/admin/authors', $data);

        $response->assertStatus(201)
                 ->assertJson(['message' => '作者新增成功']);

        $this->assertDatabaseHas('authors', ['name' => 'New Author']);
    }

    /**
     * 測試：不能新增重複名稱的作者
     */
    public function test_cannot_create_duplicate_author_name()
    {
        // 嘗試新增一個已經存在的名字 ('J.K. Rowling' 在 setUp 已經建過了)
        $data = ['name' => 'J.K. Rowling'];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->adminToken])
                         ->postJson('/api/admin/authors', $data);

        $response->assertStatus(422) // Unprocessable Entity
                 ->assertJsonValidationErrors(['name']);
    }

    /**
     * 測試：管理員可以更新作者資訊
     */
    public function test_admin_can_update_author()
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->adminToken])
                         ->putJson("/api/admin/authors/{$this->author->author_id}", [
                             'name' => 'J.K. Rowling Updated'
                         ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => '作者資訊更新成功']);

        $this->assertDatabaseHas('authors', ['name' => 'J.K. Rowling Updated']);
    }

    /**
     * 測試：管理員可以刪除沒有書籍的作者
     */
    public function test_admin_can_delete_author_without_books()
    {
        // 建立一個沒有書的作者
        $emptyAuthor = Author::create(['name' => 'Empty Author']);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->adminToken])
                         ->deleteJson("/api/admin/authors/{$emptyAuthor->author_id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => '作者已刪除']);

        $this->assertDatabaseMissing('authors', ['author_id' => $emptyAuthor->author_id]);
    }

    /**
     * 測試：如果作者名下有書，不能刪除 (保護機制)
     */
    public function test_admin_cannot_delete_author_with_books()
    {
        // 1. 準備書籍需要的關聯資料
        $category = BookCategory::create(['name' => 'Fantasy']);
        $bizUser = User::factory()->create(['role' => 'business']);
        $business = Business::create([
            'user_id' => $bizUser->user_id,
            'store_name' => 'Test Store', 
            'bank_account' => '123'
        ]);

        // 2. 幫目前的測試作者建立一本書
        Book::create([
            'name' => 'Harry Potter',
            'author_id' => $this->author->author_id, // 綁定作者
            'business_id' => $business->business_id,
            'category_id' => $category->category_id,
            'price' => 500,
            'stock' => 10,
            'isbn' => '9789573317241',
            'publisher' => 'Crown',
            'publish_date' => '2000-01-01',
            'condition' => 'new',
            'listing' => true,
            'edition' => 1
        ]);

        // 3. 嘗試刪除該作者
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->adminToken])
                         ->deleteJson("/api/admin/authors/{$this->author->author_id}");

        // 4. 預期失敗 (400 Bad Request)
        $response->assertStatus(400)
                 ->assertJson(['message' => '該作者尚有書籍上架中，無法刪除']);

        // 5. 確認作者還在資料庫
        $this->assertDatabaseHas('authors', ['author_id' => $this->author->author_id]);
    }

    /**
     * 測試：一般會員不能操作作者管理功能
     */
    public function test_member_cannot_manage_authors()
    {
        // 嘗試新增
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->memberToken])
                         ->postJson('/api/admin/authors', ['name' => 'Hacker Author']);
        
        // 預期被拒絕 (403 Forbidden)
        $response->assertStatus(403);

        // 嘗試刪除
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->memberToken])
                         ->deleteJson("/api/admin/authors/{$this->author->author_id}");

        $response->assertStatus(403);
    }
}