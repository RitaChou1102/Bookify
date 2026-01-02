<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Author;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // [新增] 引入 DB Facade 用於交易

class BookController extends Controller
{
    // 取得所有上架書籍
    public function index()
    {
        $books = Book::with(['author', 'coverImage'])
                     ->where('listing', true)
                     ->orderByDesc('created_at') // [優化] 讓新書排在前面
                     ->paginate(20);
        return response()->json($books);
    }

    // 取得單一書籍詳情
    public function show($id)
    {
        $book = Book::with([
            'author',
            'category', // 如果你的書有分類，確保 Model 有此關聯
            'business',
            'images',
            'reviews.user', // [優化] 順便載入評論的使用者資訊
            ])
            ->withCount('reviews')
            ->find($id);

        if (!$book) {
            return response()->json(['message' => '找不到該書籍'], 404);
        }

        return response()->json($book);
    }

    // [🔥重點修改] 賣家上架書籍 (C2C 版本)
    public function store(Request $request)
    {
        $user = $request->user();

        // 1. 驗證欄位
        // 我們移除了 author_id 的檢查，改為接收 author (名字字串)
        // 移除了 business 的檢查，因為現在是 C2C，人人都能賣
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'author' => 'required|string|max:255', // 接收作者名字
            'price' => 'required|integer|min:0',
            'stock' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'image_url' => 'required|url', // 接收 Cloudinary 圖片網址
            
            // 如果你的資料庫 category_id 是必填 (NOT NULL)，請解開下面這行，並確保前端有傳
            // 'category_id' => 'required|exists:book_categories,category_id',
        ]);

        return DB::transaction(function () use ($validated, $user) {
            // 2. 處理作者 (如果作者不存在就自動建立)
            // firstOrCreate 會用 name 去找，找不到就新增
            $author = Author::firstOrCreate(
                ['name' => $validated['author']]
            );

            // 3. 建立書籍
            // 注意：這裡假設 User 模型有 books() 關聯
            // 如果報錯，請檢查 User.php 是否有 public function books() { return $this->hasMany(Book::class, 'user_id'); }
            $book = $user->books()->create([
                'name' => $validated['name'],
                'author_id' => $author->author_id, // 關聯剛剛取得的作者 ID
                'price' => $validated['price'],
                'stock' => $validated['stock'],
                'description' => $validated['description'],
                'listing' => true, // 預設直接上架
                'publish_date' => now(), // 簡單起見，預設今天 (或是讓前端傳)
                'isbn' => 'N/A', // C2C 二手書不一定有 ISBN，給預設值
                'publisher' => '個人賣家', // 給預設值
                'condition' => 'used', // 預設二手
                'edition' => 1,
                // 如果有 category_id 記得加進來
                // 'category_id' => $validated['category_id'] ?? 1, // 給個預設分類 ID 1 以防報錯
            ]);

            // 4. 儲存圖片到 images 資料表
            Image::create([
                'book_id' => $book->book_id,
                'image_url' => $validated['image_url'],
                'is_cover' => true // 標記為封面
            ]);

            return response()->json(['message' => '書籍上架成功', 'book' => $book], 201);
        });
    }

    // 修改書籍
    public function update(Request $request, $id)
    {
        $book = Book::findOrFail($id);
        
        // [修正] 檢查是否為該書籍的擁有者 (比對 user_id)
        // 假設 books 表有 user_id 欄位
        if ($request->user()->user_id !== $book->user_id) {
            return response()->json(['message' => '無權修改此書籍'], 403);
        }

        $book->update($request->all());
        return response()->json(['message' => '書籍更新成功', 'book' => $book]);
    }

    // 刪除書籍
    public function destroy(Request $request, $id)
    {
        $book = Book::findOrFail($id);
        $user = $request->user();
        
        $isAdmin = $user->tokenCan('admin:all');
        
        // [修正] 檢查擁有權 (C2C 邏輯)
        $isOwner = ($user->user_id === $book->user_id);
        
        if ($isAdmin || $isOwner) {
            $book->delete();
            return response()->json(['message' => '書籍已刪除']);
        }
        return response()->json(['message' => '無權限刪除此書籍'], 403);
    }

    // 搜尋書籍
    public function search(Request $request)
    {
        $keyword = $request->input('keyword');
        
        $query = Book::where('listing', true)
                     ->with(['author', 'coverImage']);

        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%")
                  ->orWhereHas('author', function($subQ) use ($keyword) {
                      $subQ->where('name', 'like', "%{$keyword}%");
                  });
            });
        }

        return response()->json($query->paginate(12));
    }
}