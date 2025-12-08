<?php

namespace App\Http\Controllers;

use App\Models\BookCategory;
use Illuminate\Http\Request;

class BookCategoryController extends Controller
{
    // 取得所有分類
    public function index()
    {
        return BookCategory::all();
    }

    // 取得單一分類及其書籍
    public function show($id)
    {
        return BookCategory::with('books')->findOrFail($id);
    }

    // [新增] 建立新分類
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191|unique:book_categories,name',
        ]);

        $category = BookCategory::create($validated);

        return response()->json([
            'message' => '分類新增成功',
            'data' => $category
        ], 201);
    }

    // [新增] 更新分類
    public function update(Request $request, $id)
    {
        $category = BookCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:191|unique:book_categories,name,' . $id . ',category_id',
        ]);

        $category->update($validated);

        return response()->json([
            'message' => '分類更新成功',
            'data' => $category
        ]);
    }

    // [新增] 刪除分類
    public function destroy($id)
    {
        $category = BookCategory::findOrFail($id);

        if ($category->books()->exists()) {
            return response()->json(['message' => '該分類下仍有書籍，無法刪除'], 400);
        }

        $category->delete();

        return response()->json(['message' => '分類已刪除']);
    }
}