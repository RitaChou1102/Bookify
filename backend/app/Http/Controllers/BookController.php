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

    public function store(Request $request)
{
    $user = $request->user();

    // 🔒 1. 權限檢查：確認使用者是否有「廠商 (Business)」身分
    // 假設 User 模型有 business() 關聯
    if (!$user->business) {
        return response()->json(['message' => '您尚未註冊成為賣家，無法上架商品'], 403);
    }

    // 2. 驗證欄位
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'author' => 'required|string|max:255',
        'price' => 'required|integer|min:0',
        'stock' => 'required|integer|min:1',
        'description' => 'nullable|string',
        'image_url' => 'required|url',
    ]);

    return \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $user) {
        // ... (中間的 Author 處理保持不變) ...
        $author = \App\Models\Author::firstOrCreate(['name' => $validated['author']]);

        // 3. 建立書籍
        $book = $user->books()->create([
            'name' => $validated['name'],
            'author_id' => $author->author_id,
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'description' => $validated['description'],
            'listing' => true,
            'publish_date' => now(),
            'isbn' => 'N/A',
            'publisher' => $user->business->store_name, // 🟢 改用商店名稱
            'condition' => 'new', // 廠商賣的通常是新品? 或是讓前端傳
            'business_id' => $user->business->business_id, // 🟢 綁定廠商 ID
        ]);

        // 4. 圖片處理 (保持不變)
        \App\Models\Image::create([
            'book_id' => $book->book_id,
            'image_url' => $validated['image_url'],
            'is_cover' => true
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
        $user = $request->user();
        $book = Book::find($id);

        if (!$book) {
            return response()->json(['message' => '找不到該書籍'], 404);
        }

        // 權限檢查
        if ((int)$book->user_id !== (int)$user->user_id && !$user->tokenCan('admin:all')) {
            return response()->json(['message' => '無權限刪除此書籍'], 403);
        }

        try {
            // 1. [關鍵] 檢查這本書是否已經有訂單？
            // 必須先在 Book Model 裡加上 orderDetails() 關聯
            if ($book->orderDetails()->exists()) {
                // A計畫：有人買過 -> 不能刪，改為「下架」
                $book->update(['listing' => false]);
                return response()->json([
                    'message' => '此書籍已有訂單紀錄，系統已自動將其「下架」以保留帳務資料。'
                ]);
            }

            // B計畫：沒人買過 -> 安全刪除
            DB::transaction(function () use ($book) {
                // 先刪圖片
                $book->images()->delete();
                
                // 先刪購物車 (購物車不重要，可以刪)
                if (method_exists($book, 'cartItems')) {
                    $book->cartItems()->delete();
                }

                // 最後刪除本體
                $book->delete();
            });

            return response()->json(['message' => '書籍已完全刪除']);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => '操作失敗', 
                'error' => $e->getMessage()
            ], 500);
        }
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
    public function myBooks(Request $request)
    {
        // 1. 抓出目前登入的使用者
        $user = $request->user();

        // 2. 找出這個人所有的書，並依照時間排序
        $books = $user->books()
                      ->with(['coverImage']) // 記得要把圖片也抓出來
                      ->orderByDesc('created_at')
                      ->get();

        return response()->json($books);
    }
}