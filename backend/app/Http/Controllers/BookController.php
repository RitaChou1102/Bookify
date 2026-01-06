<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Author;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    // 取得所有上架書籍
    public function index()
    {
        $books = Book::with(['author', 'coverImage'])
                     ->where('listing', true)
                     ->orderByDesc('book_id')
                     ->paginate(20);
        return response()->json($books);
    }

    // 取得單一書籍詳情
    public function show($id)
    {
        $book = Book::with([
            'author',
            'category',
            'business',
            'images',
            'reviews.user',
            ])
            ->withCount('reviews')
            ->find($id);

        if (!$book) {
            return response()->json(['message' => '找不到該書籍'], 404);
        }

        return response()->json($book);
    }

    // 上架書籍
    public function store(Request $request)
    {
        $user = $request->user();

        // 🔒 1. 權限檢查
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

        return DB::transaction(function () use ($validated, $user) {
            $author = \App\Models\Author::firstOrCreate(['name' => $validated['author']]);

            // 防呆機制：確保出版社有值
            $publisherName = $user->business->store_name;
            if (empty($publisherName)) {
                $publisherName = $user->name ?? '個人賣家';
            }

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
                'publisher' => $publisherName,
                'condition' => 'new',
                'business_id' => $user->business->business_id,
                
                // 🟢 [新增] 補上版次預設值，解決報錯
                'edition' => 1, 
            ]);

            // 4. 圖片處理
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
        
        // 檢查是否為該書籍的擁有者
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
            // 檢查訂單
            if ($book->orderDetails()->exists()) {
                $book->update(['listing' => false]);
                return response()->json([
                    'message' => '此書籍已有訂單紀錄，系統已自動將其「下架」以保留帳務資料。'
                ]);
            }

            // 安全刪除
            DB::transaction(function () use ($book) {
                $book->images()->delete();
                
                if (method_exists($book, 'cartItems')) {
                    $book->cartItems()->delete();
                }

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

    // 賣家的書籍列表
    public function myBooks(Request $request)
    {
        $user = $request->user();
        $books = $user->books()
                      ->with(['coverImage'])
                      ->orderByDesc('created_at')
                      ->get();

        return response()->json($books);
    }
}