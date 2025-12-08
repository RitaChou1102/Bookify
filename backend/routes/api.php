<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// 引入所有控制器
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\BookCategoryController;
use App\Http\Controllers\ComplainController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ============================================
// 1. 公開 API (Public) - 任何人皆可訪問
// ============================================

// 書籍相關
Route::get('/books', [BookController::class, 'index']);           // 瀏覽書籍列表 (分頁)
Route::get('/books/{id}', [BookController::class, 'show']);       // 書籍詳細資訊
Route::get('/books/search/{keyword}', [BookController::class, 'search']); // 書籍搜尋

// 基礎資訊 (作者與分類)
Route::get('/authors', [AuthorController::class, 'index']);       // 作者列表
Route::get('/authors/{id}', [AuthorController::class, 'show']);   // 作者詳情
Route::get('/categories', [BookCategoryController::class, 'index']); // 分類列表
Route::get('/categories/{id}', [BookCategoryController::class, 'show']); // 分類詳情

// 評價 (公開讀取)
Route::get('/books/{bookId}/reviews', [ReviewController::class, 'getBookReviews']); // 某本書的評價

// 註冊與登入
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


// ============================================
// 2. 受保護 API (Protected) - 需登入 (Bearer Token)
// ============================================
Route::middleware('auth:sanctum')->group(function () {

    // --- 使用者帳戶 ---
    Route::get('/profile', [AuthController::class, 'profile']);         // 取得個人資料
    Route::put('/profile', [AuthController::class, 'updateProfile']);   // 更新個人資料
    Route::post('/logout', [AuthController::class, 'logout']);          // 登出

    // --- 購物車 (Cart) ---
    Route::get('/cart', [CartController::class, 'show']);               // 查看購物車
    Route::post('/cart/items', [CartController::class, 'addItem']);     // 加入商品
    Route::put('/cart/items/{id}', [CartController::class, 'updateItem']); // 更新數量
    Route::delete('/cart/items/{id}', [CartController::class, 'removeItem']); // 移除商品
    Route::delete('/cart/clear', [CartController::class, 'clear']);     // 清空購物車

    // --- 訂單與結帳 (Order) ---
    Route::post('/cart/checkout', [OrderController::class, 'checkout']); // 結帳 (建立訂單)
    Route::get('/orders', [OrderController::class, 'index']);            // 查看我的訂單
    Route::get('/orders/{id}', [OrderController::class, 'show']);        // 訂單詳情

    // --- 搜尋歷史 (Search History) ---
    Route::get('/search/history', [SearchController::class, 'history']); // 我的搜尋紀錄
    // 若要使用進階搜尋並記錄：
    Route::post('/search', [SearchController::class, 'search']);

    // --- 評價與互動 (Review) ---
    Route::post('/reviews', [ReviewController::class, 'store']);         // 撰寫評價

    // --- 客訴 (Complaint) ---
    Route::post('/complains', [ComplainController::class, 'store']);     // 提交客訴
    Route::get('/complains', [ComplainController::class, 'index']);      // 查看我的客訴

    // --- 優惠券 (Coupon) ---
    Route::get('/coupons/validate/{code}', [CouponController::class, 'validateCode']); // 驗證優惠碼

    // ============================================
    // 3. 廠商專區 (Business Only) - 需檢查權限
    // ============================================
    // 這裡建議在 Controller 內做檢查，或另外寫 Middleware
    Route::post('/books', [BookController::class, 'store']);             // 上架新書
    Route::put('/books/{id}', [BookController::class, 'update']);        // 修改書籍
    Route::delete('/books/{id}', [BookController::class, 'destroy']);    // 下架書籍
    
    Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']); // 更新訂單狀態
    Route::get('/coupons/business/{businessId}', [CouponController::class, 'getBusinessCoupons']); // 廠商看自己的優惠券

    // ============================================
    // 4. 管理員專區 (Admin Only)
    // ============================================
    Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    
        // --- 原有的管理員路由 ---
        Route::post('/ban', [AdminController::class, 'banUser']);
        Route::put('/complains/{id}/resolve', [AdminController::class, 'resolveComplain']);
        Route::get('/reports', [AdminController::class, 'getReports']);

        // --- [新增] 作者管理 (Admin) ---
        Route::post('/authors', [AuthorController::class, 'store']);       // 新增作者
        Route::put('/authors/{id}', [AuthorController::class, 'update']);  // 修改作者
        Route::delete('/authors/{id}', [AuthorController::class, 'destroy']); // 刪除作者
        
        // --- [新增] 書籍分類管理 (Admin) ---
        Route::post('/categories', [BookCategoryController::class, 'store']);    // 新增分類
        Route::put('/categories/{id}', [BookCategoryController::class, 'update']); // 修改分類
        Route::delete('/categories/{id}', [BookCategoryController::class, 'destroy']); // 刪除分類
    });
});