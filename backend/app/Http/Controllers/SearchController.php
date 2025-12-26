<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\SearchHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    // 進階搜尋並記錄歷史
    public function search(Request $request)
    {
        $keyword = trim($request->query('keyword'));
        $user = $request->user();

        // 記錄搜尋歷史 (如果是會員)
        if ($user && $user->member && filled($keyword) && $request->query('page', 1) == 1) {
            SearchHistory::create([
                // [修正] member_id 外鍵是指向 users.user_id，不是 members.member_id
                'member_id' => $user->user_id,
                'keyword' => $keyword,
                'search_time' => now()
            ]);
        }

        // 執行搜尋
        $books = Book::where('listing', true)
            ->where(function($query) use ($keyword) {
                $query->where('name', 'like', "%{$keyword}%")
                      ->orWhereHas('author', function($q) use ($keyword) {
                          $q->where('name', 'like', "%{$keyword}%");
                      });
            })
            ->with(['author', 'coverImage'])
            ->paginate(20)
            ->withQueryString(); // 每頁 20 筆

        return response()->json($books);
    }

    // 取得個人搜尋歷史
    public function history(Request $request)
    {
        // [修正] 改用 $request->user()->user_id
        return SearchHistory::where('member_id', $request->user()->user_id)
                            ->select('keyword', DB::raw('MAX(search_time) as last_search_time'))
                            ->groupBy('keyword')
                            ->orderByDesc('last_search_time')
                            ->take(10)
                            ->get();
    }
}