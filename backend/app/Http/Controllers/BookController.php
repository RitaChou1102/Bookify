<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    // 取得所有上架書籍
    public function index()
    {
        // 使用 with() 預先載入作者與封面圖，避免 N+1 查詢問題，提升效能
        $books = Book::with(['author', 'coverImage'])
                     ->where('listing', true) // 只撈上架的書
                     ->paginate(20); // 分頁，每頁 20 筆
        return response()->json($books);
    }

    // 取得單一書籍詳情
    public function show($id)
    {
        // 撈出書籍，並連同作者、類別、廠商、所有圖片一起撈出來
        $book = Book::with([
            'author',
            'category',
            'business',
            'images',
            'reviews',
            ])
            ->withCount('reviews')
            ->find($id);

        if (!$book) {
            return response()->json(['message' => '找不到該書籍'], 404);
        }

        return response()->json($book);
    }

    // 新增書籍 (需驗證權限)
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->business) {
            return response()->json(['message' => '只有廠商可以上架書籍'], 403);
        }

        $validated = $request->validate([
            'name' => 'required',
            'author_id' => 'required|exists:authors,author_id',
            'category_id' => 'required|exists:book_categories,category_id',
            'isbn' => 'required',
            'edition' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable',
            'publish_date' => 'required|date',
            'publisher' => 'required',
            'condition' => 'required|in:new,used',
            'listing' => 'boolean',
        ]);

        $book = $user->business->books()->create($validated);

        return response()->json(['message' => '書籍上架成功', 'book' => $book], 201);
    }

    // 修改書籍
    public function update(Request $request, $id)
    {
        $book = Book::findOrFail($id);
        
        // 檢查是否為該書籍的擁有者
        if ($request->user()->business->business_id !== $book->business_id) {
            return response()->json(['message' => '無權修改此書籍'], 403);
        }

        $book->update($request->all());
        return response()->json(['message' => '書籍更新成功', 'book' => $book]);
    }

    // 刪除書籍
    public function destroy(Request $request, $id)
    {
        $book = Book::findOrFail($id);
        $user = $request->user(); // 這裡取到的可能是 User 物件，也可能是 Admin 物件
        
        // [修正重點] 改用 tokenCan 來檢查是否有管理員權限
        // 注意：Auth::guard('admin')->check() 在純 Token API 模式下通常會失效
        $isAdmin = $user->tokenCan('admin:all');
        
        // 檢查是否為書籍擁有者
        // 這裡加個簡單判斷：如果 $user 沒有 business 屬性 (例如 Admin)，就視為 false
        $isOwner = $user->business && ($user->business->business_id === $book->business_id);
        
        if ($isAdmin || $isOwner) {
            $book->delete();
            return response()->json(['message' => '書籍已刪除']);
        }
        return response()->json(['message' => '無權限刪除此書籍'], 403);
    }
    // 新增 search 方法
    public function search(Request $request)
    {
        $keyword = $request->input('keyword');
        
        // 預設只搜尋已上架的書，並預載作者與封面圖
        $query = Book::where('listing', true)
                     ->with(['author', 'coverImage']);

        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                // 1. 搜尋書名
                $q->where('name', 'like', "%{$keyword}%")
                  // 2. 或搜尋描述
                  ->orWhere('description', 'like', "%{$keyword}%")
                  // 3. 或搜尋作者名字 (關聯查詢)
                  ->orWhereHas('author', function($subQ) use ($keyword) {
                      $subQ->where('name', 'like', "%{$keyword}%");
                  });
            });
        }

        // 分頁回傳，一頁 12 筆
        return response()->json($query->paginate(12));
    }
}
