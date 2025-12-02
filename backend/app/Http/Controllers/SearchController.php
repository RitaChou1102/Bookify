<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\SearchHistory;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    // 進階搜尋並記錄歷史
    public function search(Request $request)
    {
        $keyword = $request->input('keyword');
        $user = $request->user();

        // 記錄搜尋歷史 (如果是會員)
        if ($user && $user->member) {
            SearchHistory::create([
                'member_id' => $user->member->member_id,
                'keyword' => $keyword,
                'search_time' => now()
            ]);
        }

        // 執行搜尋
        $books = Book::where('name', 'like', "%{$keyword}%")
                     ->orWhere('description', 'like', "%{$keyword}%")
                     ->with('author')
                     ->get();

        return response()->json($books);
    }

    // 取得個人搜尋歷史
    public function history(Request $request)
    {
        return SearchHistory::where('member_id', $request->user()->member->member_id)
                            ->orderByDesc('search_time')
                            ->take(10)
                            ->get();
    }
}