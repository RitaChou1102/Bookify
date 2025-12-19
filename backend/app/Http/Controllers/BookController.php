<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // [新增] 必須加入這行，下面的 Auth::guard 才能運作

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

    // 搜尋書籍
    public function search($keyword)
    {
        // 搜尋書名 或 作者名稱
        // 優化：添加分頁機制，避免載入過多資料
        $books = Book::where('listing', true)
            ->where(function($query) use ($keyword) {
                $query->where('name', 'like', "%{$keyword}%")
                      ->orWhereHas('author', function($q) use ($keyword) {
                          $q->where('name', 'like', "%{$keyword}%");
                      });
            })
            ->with(['author', 'coverImage'])
            ->paginate(20); // 每頁 20 筆

        return response()->json($books);
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
        $user = $request->user();
        $isAdmin = Auth::guard('admin')->check();
        $isOwner = $user && $user->business && ($user->business->business_id === $book->business_id);
        if ($isAdmin || $isOwner) {
            $book->delete();
            return response()->json(['message' => '書籍已刪除']);
        }
        return response()->json(['message' => '無權限刪除此書籍'], 403);
    }
}
