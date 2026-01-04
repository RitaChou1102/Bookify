<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Business;
use App\Models\Book;
use App\Models\Image;
use App\Models\Author;
use App\Models\BookCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Cloudinary\Cloudinary;
use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Api\ApiResponse;
use Tests\TestCase;
use Mockery;

class ImageTest extends TestCase
{
    use RefreshDatabase;

    protected $businessUser;
    protected $businessToken;
    protected $business;
    protected $book;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. 建立廠商資料
        $this->businessUser = User::factory()->create(['role' => 'business']);
        $this->business = Business::create([
            'user_id' => $this->businessUser->user_id,
            'store_name' => 'Image Store',
            'bank_account' => '123-456'
        ]);
        $this->businessToken = $this->businessUser->createToken('biz_token')->plainTextToken;

        // 2. 建立書籍
        $author = Author::create(['name' => 'Test Author']);
        $category = BookCategory::create(['name' => 'Test Cat']);

        $this->book = Book::create([
            'business_id' => $this->business->business_id,
            'author_id' => $author->author_id,
            'category_id' => $category->category_id,
            'name' => 'Visual Book',
            'price' => 500,
            'stock' => 10,
            'isbn' => '9789860000000',
            'listing' => true,
            'publish_date' => '2023-01-01',
            'edition' => 1,
            'publisher' => 'Test Publisher',
            'condition' => 'new'
        ]);
    }

    /**
     * 測試：廠商可以上傳圖片 (Mock Cloudinary)
     */
    public function test_business_can_upload_image()
    {
        /**
         * 1. Mock ApiResponse（符合 upload() 回傳型別）
         */
        $responseJson = [
            'secure_url' => 'https://example.com/fake.jpg',
        ];

        $headers = []; // 一定要是 array，不能省略

        $apiResponse = new ApiResponse($responseJson, $headers);
        /**
         * 2. Mock UploadApi
         */
        $mockUploadApi = Mockery::mock(UploadApi::class);
        $mockUploadApi->shouldReceive('upload')
            ->once()
            ->andReturn($apiResponse);

        /**
         * 3. Mock Cloudinary
         */
        $mockCloudinary = Mockery::mock(Cloudinary::class);
        $mockCloudinary->shouldReceive('uploadApi')
            ->once()
            ->andReturn($mockUploadApi);

        /**
         * 4. 注入 Container
         */
        $this->app->instance(Cloudinary::class, $mockCloudinary);
        // 3. 準備假圖片檔案
        $file = UploadedFile::fake()->create('cover.jpg', 100, 'image/jpeg');

        $this->withoutExceptionHandling();

        // 5. 發送請求
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->businessToken])
                         ->postJson("/api/books/{$this->book->book_id}/images", [
                             'image' => $file
                         ]);

        // 6. 驗證
        $response->assertStatus(201)
                 ->assertJson(['message' => '上傳成功']);

        $this->assertDatabaseHas('images', [
            'book_id' => $this->book->book_id,
            'image_url' => 'https://example.com/fake.jpg',
            'image_index' => 1 // 第一張圖 index 應為 1
        ]);
    }

    /**
     * 測試：刪除圖片後，後面的圖片 index 會自動補上 (重排)
     */
    public function test_delete_image_reorders_indices()
    {
        // 1. 建立 3 張圖片，index 分別為 1, 2, 3
        $img1 = Image::create(['book_id' => $this->book->book_id, 'image_index' => 1, 'image_url' => 'url1']);
        $img2 = Image::create(['book_id' => $this->book->book_id, 'image_index' => 2, 'image_url' => 'url2']);
        $img3 = Image::create(['book_id' => $this->book->book_id, 'image_index' => 3, 'image_url' => 'url3']);

        // 2. 刪除中間那張 (index 2)
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->businessToken])
                         ->deleteJson("/api/images/{$img2->image_id}");

        $response->assertStatus(200);

        // 3. 驗證
        // img1 不變
        $this->assertDatabaseHas('images', ['image_id' => $img1->image_id, 'image_index' => 1]);
        // img2 已刪除
        $this->assertDatabaseMissing('images', ['image_id' => $img2->image_id]);
        // img3 的 index 應該從 3 變成 2 (往前遞補)
        $this->assertDatabaseHas('images', ['image_id' => $img3->image_id, 'image_index' => 2]);
    }

    /**
     * 測試：手動排序圖片 (往前移)
     */
    public function test_reorder_images_forward()
    {
        // 準備：1, 2, 3, 4, 5
        for ($i = 1; $i <= 5; $i++) {
            Image::create(['book_id' => $this->book->book_id, 'image_index' => $i, 'image_url' => "url{$i}"]);
        }

        // 目標：把 index 4 移到 index 2
        // 預期結果：1, 4(變2), 2(變3), 3(變4), 5
        $targetImg = Image::where('book_id', $this->book->book_id)->where('image_index', 4)->first();

        $data = [
            'book_id' => $this->book->book_id,
            'image_id' => $targetImg->image_id,
            'new_index' => 2
        ];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->businessToken])
                         ->patchJson('/api/images/reorder', $data);
        
        $response->assertStatus(200);

        // 驗證
        $this->assertEquals(2, $targetImg->refresh()->image_index); // 自己變成 2
        // 原本的 2 變成 3
        $this->assertEquals(3, Image::where('image_url', 'url2')->first()->image_index);
        // 原本的 3 變成 4
        $this->assertEquals(4, Image::where('image_url', 'url3')->first()->image_index);
        // 原本的 5 維持 5
        $this->assertEquals(5, Image::where('image_url', 'url5')->first()->image_index);
    }

    /**
     * 測試：其他廠商不能刪除我的圖片
     */
    public function test_other_business_cannot_delete_my_image()
    {
        // 建立圖片
        $img = Image::create(['book_id' => $this->book->book_id, 'image_index' => 1, 'image_url' => 'url1']);

        // 建立另一個壞壞廠商
        $hackerUser = User::factory()->create(['role' => 'business']);
        Business::create(['user_id' => $hackerUser->user_id, 'store_name' => 'Hacker', 'bank_account' => '000']);
        $hackerToken = $hackerUser->createToken('hacker')->plainTextToken;

        // 嘗試刪除
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $hackerToken])
                         ->deleteJson("/api/images/{$img->image_id}");

        $response->assertStatus(403)
                 ->assertJson(['message' => '您無權刪除此圖片']);
    }
}