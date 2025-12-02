<?php

namespace App\Http\Controllers;

use App\Models\BookCategory;
use Illuminate\Http\Request;

class BookCategoryController extends Controller
{
    public function index()
    {
        return BookCategory::all();
    }

    public function show($id)
    {
        // 取得分類及其書籍
        return BookCategory::with('books')->findOrFail($id);
    }
}