<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Member;
use App\Models\Book;
use App\Models\Author;
use App\Models\BookCategory;
use App\Models\Business;
use App\Models\SearchHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    protected $memberUser;
    protected $memberToken;
    protected $book1;
    protected $book2;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. 準備基礎資料 (廠商、作者、分類)
        $bizUser = User::factory()->create(['role' => 'business']);
        $business = Business::create([
            'user_id' => $bizUser->user_id,
            'store_name' => 'Book Store',
            'bank_account' => '123'
        ]);
        
        $authorRowling = Author::create(['name' => 'J.K. Rowling']);
        $authorTolkien = Author::create(['name' => 'J.R.R. Tolkien']);
        $category = BookCategory::create(['name' => 'Fantasy']);

        // 2. 建立測試書籍
        // 書籍 A: Harry Potter (已上架)
        $this->book1 = Book::create([
            'name' => 'Harry Potter',
            'business_id' => $business->business_id,
            'author_id' => $authorRowling->author_id,
            'category_id' => $category->category_id,
            'price' => 500,
            'stock' => 10,
            'isbn' => '9780000000001',
            'publisher' => 'Bloomsbury',
            'condition' => 'new',
            'listing' => true, // 上架中
            'edition' => 1,
            'publish_date' => '2000-01-01'
        ]);

        // 書籍 B: Lord of the Rings (已上架)
        $this->book2 = Book::create([
            'name' => 'Lord of the Rings',
            'business_id' => $business->business_id,
            'author_id' => $authorTolkien->author_id,
            'category_id' => $category->category_id,
            'price' => 600,
            'stock' => 5,
            'isbn' => '9780000000002',
            'publisher' => 'Allen & Unwin',
            'condition' => 'used',
            'listing' => true, // 上架中
            'edition' => 1,
            'publish_date' => '1954-01-01'
        ]);

        // 書籍 C: Hidden Book (未上架)
        Book::create([
            'name' => 'Hidden Secret',
            'business_id' => $business->business_id,
            'author_id' => $authorRowling->author_id,
            'category_id' => $category->category_id,
            'price' => 999,
            'stock' => 1,
            'isbn' => '9780000000003',
            'publisher' => 'Secret Pub',
            'condition' => 'new',
            'listing' => false, // 未上架
            'edition' => 1,
            'publish_date' => '2023-01-01'
        ]);

        // 3. 建立一般會員
        $this->memberUser = User::factory()->create(['role' => 'member']);
        Member::create(['user_id' => $this->memberUser->user_id]);
        $this->memberToken = $this->memberUser->createToken('member_token', ['role:member'])->plainTextToken;
    }

    /**
     * 測試：可以透過「書名」搜尋到書籍
     */
    public function test_can_search_books_by_name()
    {
        $response = $this->getJson('/api/guest/search?keyword=Harry');

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Harry Potter'])
                 ->assertJsonMissing(['name' => 'Lord of the Rings']);
    }

    /**
     * 測試：可以透過「作者名稱」搜尋到書籍
     */
    public function test_can_search_books_by_author()
    {
        // 搜尋 'Tolkien' 應該找到 'Lord of the Rings'
        $response = $this->getJson('/api/guest/search?keyword=Tolkien');

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Lord of the Rings'])
                 ->assertJsonMissing(['name' => 'Harry Potter']);
    }

    /**
     * 測試：搜尋結果不包含未上架 (listing=false) 的書籍
     */
    public function test_search_hides_unlisted_books()
    {
        // 搜尋 'Hidden'，雖然書名有，但因為 listing=false，應該找不到
        $response = $this->getJson('/api/guest/search?keyword=Hidden');

        $response->assertStatus(200)
                 ->assertJsonCount(0, 'data'); // data 陣列應為空
    }

    /**
     * 測試：會員搜尋時會記錄歷史
     */
    public function test_member_search_records_history()
    {
        $keyword = 'Magic';

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->memberToken])
                         ->getJson("/api/user/search?keyword={$keyword}");

        $response->assertStatus(200);

        $this->assertDatabaseHas('search_histories', [
            'member_id' => $this->memberUser->user_id,
            'keyword' => $keyword
        ]);
    }

    /**
     * 測試：訪客 (未登入) 搜尋不會記錄歷史
     */
    public function test_guest_search_does_not_record_history()
    {
        $keyword = 'Ghost';

        $response = $this->getJson("/api/guest/search?keyword={$keyword}");
        
        $response->assertStatus(200);

        $this->assertDatabaseMissing('search_histories', [
            'keyword' => $keyword
        ]);
    }

    /**
     * 測試：搜尋第 2 頁時，不會重複記錄歷史
     */
    public function test_pagination_does_not_record_duplicate_history()
    {
        $keyword = 'Potter';

        // 第一頁：應該記錄
        $this->withHeaders(['Authorization' => 'Bearer ' . $this->memberToken])
             ->getJson("/api/user/search?keyword={$keyword}&page=1");

        // 第二頁：不應該再新增一筆紀錄
        $this->withHeaders(['Authorization' => 'Bearer ' . $this->memberToken])
             ->getJson("/api/user/search?keyword={$keyword}&page=2");

        // 檢查資料庫該關鍵字只有 1 筆紀錄
        $count = SearchHistory::where('member_id', $this->memberUser->user_id)
                              ->where('keyword', $keyword)
                              ->count();
        
        $this->assertEquals(1, $count);
    }

    /**
     * 測試：會員可以查看自己的搜尋歷史
     */
    public function test_member_can_view_search_history()
    {
        // 先建立幾筆歷史紀錄
        SearchHistory::create([
            'member_id' => $this->memberUser->user_id,
            'keyword' => 'Apple',
            'search_time' => now()->subMinutes(10)
        ]);
        SearchHistory::create([
            'member_id' => $this->memberUser->user_id,
            'keyword' => 'Banana',
            'search_time' => now()
        ]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->memberToken])
                         ->getJson('/api/search/history');

        $response->assertStatus(200)
                 ->assertJsonFragment(['keyword' => 'Apple'])
                 ->assertJsonFragment(['keyword' => 'Banana']);
                 
        // 確認排序 (Banana 最新，應該在前面)
        $data = $response->json();
        $this->assertEquals('Banana', $data[0]['keyword']);
    }
}