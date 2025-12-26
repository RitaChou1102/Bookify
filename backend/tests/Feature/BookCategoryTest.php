<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Admin;
use App\Models\BookCategory;
use App\Models\Book;
use App\Models\Author;
use App\Models\Business;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BookCategoryTest extends TestCase
{
    use RefreshDatabase;

    protected $adminToken;
    protected $memberToken;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. 建立管理員 (擁有 admin:all 權限)
        $admin = Admin::create([
            'name' => 'Category Admin',
            'login_id' => 'admin_cat_' . rand(1000, 9999),
            'password' => Hash::make('password'),
        ]);
        $this->adminToken = $admin->createToken('admin_token', ['admin:all'])->plainTextToken;

        // 2. 建立一般會員 (擁有 role:member 權限)
        $member = User::factory()->create(['role' => 'member']);
        $this->memberToken = $member->createToken('member_token', ['role:member'])->plainTextToken;

        // 3. 建立一個基礎分類供測試
        $this->category = BookCategory::create(['name' => 'Literature']);
    }

    /**
     * 測試：任何人都能取得分類列表
     */
    public function test_public_can_view_category_list()
    {
        $response = $this->getJson('/api/categories');

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Literature']);
    }

    /**
     * 測試：任何人都能查看單一分類 (需確認是否包含書籍結構)
     */
    public function test_public_can_view_single_category()
    {
        $response = $this->getJson("/api/categories/{$this->category->category_id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'category_id' => $this->category->category_id,
                     'name' => 'Literature',
                 ])
                 ->assertJsonStructure(['books']); // 確認有回傳書籍欄位
    }

    /**
     * 測試：管理員可以新增分類
     */
    public function test_admin_can_create_category()
    {
        $data = ['name' => 'Science Fiction'];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->adminToken])
                         ->postJson('/api/admin/categories', $data);

        $response->assertStatus(201)
                 ->assertJson(['message' => '分類新增成功']);

        $this->assertDatabaseHas('book_categories', ['name' => 'Science Fiction']);
    }

    /**
     * 測試：不能新增重複名稱的分類
     */
    public function test_cannot_create_duplicate_category()
    {
        // 嘗試新增已存在的 'Literature'
        $data = ['name' => 'Literature'];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->adminToken])
                         ->postJson('/api/admin/categories', $data);

        $response->assertStatus(422) // Unprocessable Entity
                 ->assertJsonValidationErrors(['name']);
    }

    /**
     * 測試：管理員可以更新分類
     */
    public function test_admin_can_update_category()
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->adminToken])
                         ->putJson("/api/admin/categories/{$this->category->category_id}", [
                             'name' => 'Modern Literature'
                         ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => '分類更新成功']);

        $this->assertDatabaseHas('book_categories', ['name' => 'Modern Literature']);
    }

    /**
     * 測試：管理員可以刪除「空」的分類
     */
    public function test_admin_can_delete_empty_category()
    {
        // 建立一個新的空分類
        $emptyCategory = BookCategory::create(['name' => 'Empty Category']);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->adminToken])
                         ->deleteJson("/api/admin/categories/{$emptyCategory->category_id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => '分類已刪除']);

        $this->assertDatabaseMissing('book_categories', ['category_id' => $emptyCategory->category_id]);
    }

    /**
     * 測試：如果分類下有書籍，不能刪除 (保護機制)
     */
    public function test_admin_cannot_delete_category_with_books()
    {
        // 1. 準備書籍需要的關聯資料 (Author, Business)
        $author = Author::create(['name' => 'Test Author']);
        $bizUser = User::factory()->create(['role' => 'business']);
        $business = Business::create([
            'user_id' => $bizUser->user_id,
            'store_name' => 'Test Store',
            'bank_account' => '123'
        ]);

        // 2. 在目前的分類 (Literature) 下建立一本書
        Book::create([
            'name' => 'Test Book',
            'category_id' => $this->category->category_id, // 綁定分類
            'author_id' => $author->author_id,
            'business_id' => $business->business_id,
            'price' => 100,
            'stock' => 5,
            'isbn' => '9780000000000',
            'publisher' => 'Test Pub',
            'publish_date' => '2023-01-01',
            'condition' => 'new',
            'listing' => true,
            'edition' => 1
        ]);

        // 3. 嘗試刪除該分類
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->adminToken])
                         ->deleteJson("/api/admin/categories/{$this->category->category_id}");

        // 4. 預期失敗 (400 Bad Request)
        $response->assertStatus(400)
                 ->assertJson(['message' => '該分類下仍有書籍，無法刪除']);

        // 5. 確認分類還在
        $this->assertDatabaseHas('book_categories', ['category_id' => $this->category->category_id]);
    }

    /**
     * 測試：一般會員不能操作分類管理
     */
    public function test_member_cannot_manage_categories()
    {
        // 嘗試新增
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->memberToken])
                         ->postJson('/api/admin/categories', ['name' => 'Hacked Category']);
        
        $response->assertStatus(403);

        // 嘗試刪除
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->memberToken])
                         ->deleteJson("/api/admin/categories/{$this->category->category_id}");

        $response->assertStatus(403);
    }
}