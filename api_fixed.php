<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::get('/books', function () {
    try {
        $books = DB::table('books')
            ->join('authors', 'books.author_id', '=', 'authors.author_id')
            ->leftJoin('images', 'books.book_id', '=', 'images.book_id')
            ->select(
                'books.book_id as id',
                'books.name',
                'books.price',
                'books.description',
                'authors.name as author',
                DB::raw('ANY_VALUE(images.image_url) as image')
            )
            ->groupBy('books.book_id')
            ->get();
        return response()->json($books);
    } catch (\Throwable $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

Route::get('/books/{id}', function ($id) {
    try {
        $book = DB::table('books')
            ->join('authors', 'books.author_id', '=', 'authors.author_id')
            ->join('businesses', 'books.business_id', '=', 'businesses.business_id')
            ->leftJoin('images', 'books.book_id', '=', 'images.book_id')
            ->where('books.book_id', $id)
            ->select(
                'books.book_id as id',
                'books.*',
                'authors.name as author',
                'businesses.store_name as business_name',
                'images.image_url as image'
            )
            ->first();
        return response()->json($book);
    } catch (\Throwable $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});