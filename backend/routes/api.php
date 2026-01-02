<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// 引入所有控制器
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminAuthController; 
use App\Http\Controllers\AdminController;     
use App\Http\Controllers\BookController;
use App\Http\Controllers\BlackListController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\BookCategoryController;
use App\Http\Controllers\ComplainAdminController;
use App\Http\Controllers\ComplainUserController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ImageUploadController;
/*
|--------------------------------------------------------------------------
| 1. 公開 API (Public) - 任何人皆可訪問
|--------------------------------------------------------------------------
*/
// 書籍搜尋 API
Route::get('/books/search', [\App\Http\Controllers\BookController::class, 'search']);
// 書籍相關
Route::get('/books', [BookController::class, 'index']);           // 瀏覽書籍
Route::get('/books/{id}', [BookController::class, 'show']);       // 書籍詳情
Route::get('/guest/search', [SearchController::class, 'search']); // 搜尋

// 基礎資訊
Route::get('/authors', [AuthorController::class, 'index']);
Route::get('/authors/{id}', [AuthorController::class, 'show']);
Route::get('/categories', [BookCategoryController::class, 'index']);
Route::get('/categories/{id}', [BookCategoryController::class, 'show']);
Route::get('/books/{bookId}/reviews', [ReviewController::class, 'getBookReviews']);

// --- 前台用戶認證 (User/Business) ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// --- 後台管理員認證 (Admin) ---
// 這裡新增管理員專用的登入路徑，與前台分開
Route::prefix('admin')->group(function () {
    Route::post('/login', [AdminAuthController::class, 'login']);
});


/*
|--------------------------------------------------------------------------
| 2. 前台用戶受保護 API (User Protected)
|--------------------------------------------------------------------------
| 適用對象：一般會員 (Member) & 商家 (Business)
| 驗證方式：Header 需帶 Authorization: Bearer <UserToken>
*/
Route::middleware(['auth:sanctum', 'check.blacklist'])->group(function () {
    // 注意：暫時移除 'ability:user-access' 以確保基本認證正常工作
    // 如果需要能力檢查，可以稍後重新添加

    // --- 帳戶管理 ---
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/user/password', [\App\Http\Controllers\UserController::class, 'changePassword']);

    // --- 購物車 ---
    Route::get('/cart', [CartController::class, 'show']);
    Route::post('/cart/items', [CartController::class, 'addItem']);
    Route::put('/cart/items/{id}', [CartController::class, 'updateItem']);
    Route::delete('/cart/items/{id}', [CartController::class, 'removeItem']);
    Route::delete('/cart/clear', [CartController::class, 'clear']);

    // --- 訂單 ---
    Route::post('/cart/checkout', [OrderController::class, 'checkout']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);

    // --- 搜尋與評價 ---
    Route::get('/search/history', [SearchController::class, 'history']);
    Route::get('/user/search', [SearchController::class, 'search']);
    Route::post('/reviews', [ReviewController::class, 'store']);

    // --- 客訴與優惠券 ---
    // [修正] 改用 ComplainUserController
    Route::post('/complains', [ComplainUserController::class, 'store']);
    Route::get('/complains', [ComplainUserController::class, 'index']);
    Route::get('/coupons/validate/{code}', [CouponController::class, 'validateCode']);
    Route::get('/coupons/business/{businessId}', [CouponController::class, 'getBusinessCoupons']);

    // --- 廠商專區 (Business Only) ---
    // 注意：這裡還是 User 表，只是 role='business'，所以放在這裡沒問題
    // 建議在 Controller 內部或用 Middleware 檢查 role === 'business'
    // book
    Route::post('/books', [BookController::class, 'store']);
    Route::put('/books/{id}', [BookController::class, 'update']);
    Route::delete('/books/{id}', [BookController::class, 'destroy']);
    // order
    Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);
    // coupons
    Route::post('/coupons', [CouponController::class, 'store']);
    Route::put('/coupons/{id}',[CouponController::class, 'update']);
    // Image
    Route::post('/upload-image', [ImageUploadController::class, 'upload']);
    Route::post('/books/{book_id}/images', [ImageController::class, 'store']);// 批次上傳書籍圖片
    Route::delete('/images/{image_id}', [ImageController::class, 'destroy']);// 刪除單一圖片
    Route::patch('/images/reorder', [ImageController::class, 'reorder']);// 更新圖片排序
    // [新增] 個人資料 API
    Route::get('/user/profile', [UserController::class, 'show']);
    Route::put('/user/profile', [UserController::class, 'update']);
});


/*
|--------------------------------------------------------------------------
| 3. 後台管理員受保護 API (Admin Protected)
|--------------------------------------------------------------------------
| 適用對象：管理員 (Admin)
| 驗證方式：Header 需帶 Authorization: Bearer <AdminToken>
| 網址前綴：/api/admin/...
*/
Route::prefix('admin')->middleware(['auth:sanctum', 'abilities:admin:all'])->group(function () {
    
    // --- 管理員專屬功能 ---
    Route::post('/logout', [AdminAuthController::class, 'logout']);
    Route::get('/me', [AdminAuthController::class, 'me']); // 查看自己的管理員資料

    // --- 黑名單 ---
    Route::get('/blacklist', [BlackListController::class, 'index']);
    Route::post('/ban-user', [BlackListController::class, 'banUser']); // 封鎖
    Route::delete('/unban-user/{user_id}', [BlackListController::class, 'unbanUser']); // 解除封鎖

    // --- reports ---
    Route::prefix('reports')->group(function () {
        // 1. 取得報表列表 (支援分頁、篩選)
        Route::get('/', [ReportController::class, 'index']);

        // 2. 生成新報表 (執行統計邏輯並儲存)
        Route::post('/', [ReportController::class, 'store']);

        // 3. 取得單一報表詳細數據 (包含 stats_data)
        Route::get('/{id}', [ReportController::class, 'show']);

        // 4. 刪除歷史報表
        Route::delete('/{id}', [ReportController::class, 'destroy']);
    });

    // --- 業務管理(客訴) ---
    Route::patch('/complains/{id}/in-progress', [ComplainAdminController::class, 'markAsInProgress']); // 處理中
    Route::put('/complains/{id}/resolve', [ComplainAdminController::class, 'resolveComplain']); // 處理客訴

    // --- [新增] 作者管理 (Admin) ---
    Route::post('/authors', [AuthorController::class, 'store']);       // 新增作者
    Route::put('/authors/{id}', [AuthorController::class, 'update']);  // 修改作者
    Route::delete('/authors/{id}', [AuthorController::class, 'destroy']); // 刪除作者
    
    // --- [新增] 書籍分類管理 (Admin) ---
    Route::post('/categories', [BookCategoryController::class, 'store']);    // 新增分類
    Route::put('/categories/{id}', [BookCategoryController::class, 'update']); // 修改分類
    Route::delete('/categories/{id}', [BookCategoryController::class, 'destroy']); // 刪除分類

    // --- 刪除書籍 ---
    Route::delete('/books/{id}', [BookController::class, 'destroy']);

});