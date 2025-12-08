<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    // 取得所有作者列表
    public function index()
    {
        return Author::all();
    }

    // 取得單一作者及其書籍
    public function show($id)
    {
        return Author::with('books')->findOrFail($id);
    }

    // [新增] 建立新作者
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:authors,name', // 確保名稱唯一
        ]);

        $author = Author::create($validated);

        return response()->json([
            'message' => '作者新增成功',
            'data' => $author
        ], 201);
    }

    // [新增] 更新作者資訊
    public function update(Request $request, $id)
    {
        $author = Author::findOrFail($id);

        $validated = $request->validate([
            // 更新時排除自己的 ID 檢查唯一性
            'name' => 'required|string|max:255|unique:authors,name,' . $id . ',author_id',
        ]);

        $author->update($validated);

        return response()->json([
            'message' => '作者資訊更新成功',
            'data' => $author
        ]);
    }

    // [新增] 刪除作者
    public function destroy($id)
    {
        $author = Author::findOrFail($id);

        // 檢查是否有關聯書籍，若有則阻止刪除 (保持資料完整性)
        if ($author->books()->exists()) {
            return response()->json(['message' => '該作者尚有書籍上架中，無法刪除'], 400);
        }

        $author->delete();

        return response()->json(['message' => '作者已刪除']);
    }
}