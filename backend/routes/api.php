<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ============================================
// 書籍相關 API
// ============================================

// 書籍列表和查詢
Route::get('/books', [App\Http\Controllers\BookController::class, 'index']);
Route::get('/books/{id}', [App\Http\Controllers\BookController::class, 'show']);
Route::get('/books/search/{keyword}', [App\Http\Controllers\BookController::class, 'search']);

// 書籍管理（需要認證）
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/books', [App\Http\Controllers\BookController::class, 'store']);
    Route::put('/books/{id}', [App\Http\Controllers\BookController::class, 'update']);
    Route::delete('/books/{id}', [App\Http\Controllers\BookController::class, 'destroy']);
});

// ============================================
// 作者相關 API
// ============================================

Route::get('/authors', [App\Http\Controllers\AuthorController::class, 'index']);
Route::get('/authors/{id}', [App\Http\Controllers\AuthorController::class, 'show']);

// ============================================
// 書籍類別相關 API
// ============================================

Route::get('/categories', [App\Http\Controllers\BookCategoryController::class, 'index']);
Route::get('/categories/{id}', [App\Http\Controllers\BookCategoryController::class, 'show']);

// ============================================
// 購物車相關 API（需要認證）
// ============================================

Route::middleware('auth:sanctum')->group(function () {
    // 購物車
    Route::get('/cart', [App\Http\Controllers\CartController::class, 'show']);
    Route::post('/cart/items', [App\Http\Controllers\CartController::class, 'addItem']);
    Route::put('/cart/items/{id}', [App\Http\Controllers\CartController::class, 'updateItem']);
    Route::delete('/cart/items/{id}', [App\Http\Controllers\CartController::class, 'removeItem']);
    Route::delete('/cart/clear', [App\Http\Controllers\CartController::class, 'clear']);
    
    // 結帳
    Route::post('/cart/checkout', [App\Http\Controllers\OrderController::class, 'checkout']);
});

// ============================================
// 訂單相關 API（需要認證）
// ============================================

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/orders', [App\Http\Controllers\OrderController::class, 'index']);
    Route::get('/orders/{id}', [App\Http\Controllers\OrderController::class, 'show']);
    Route::put('/orders/{id}/status', [App\Http\Controllers\OrderController::class, 'updateStatus']);
});

// ============================================
// 評價相關 API（需要認證）
// ============================================

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/reviews', [App\Http\Controllers\ReviewController::class, 'store']);
    Route::put('/reviews/{id}', [App\Http\Controllers\ReviewController::class, 'update']);
});

Route::get('/books/{bookId}/reviews', [App\Http\Controllers\ReviewController::class, 'getBookReviews']);

// ============================================
// 優惠券相關 API
// ============================================

Route::get('/coupons/validate/{code}', [App\Http\Controllers\CouponController::class, 'validate']);
Route::get('/coupons/business/{businessId}', [App\Http\Controllers\CouponController::class, 'getBusinessCoupons']);

// ============================================
// 使用者相關 API
// ============================================

// 註冊和登入（不需要認證）
Route::post('/register', [App\Http\Controllers\AuthController::class, 'register']);
Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);

// 需要認證的使用者功能
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [App\Http\Controllers\AuthController::class, 'profile']);
    Route::put('/profile', [App\Http\Controllers\AuthController::class, 'updateProfile']);
    Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout']);
});

// ============================================
// 搜尋相關 API（需要認證）
// ============================================

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/search/history', [App\Http\Controllers\SearchController::class, 'history']);
    Route::post('/search', [App\Http\Controllers\SearchController::class, 'search']);
});

