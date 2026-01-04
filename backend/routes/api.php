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
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

/*
|--------------------------------------------------------------------------
| 1. 公開 API
|--------------------------------------------------------------------------
*/

// Ping 測試
Route::get('/ping', function() {
    return response()->json(['status' => 'ok', 'message' => 'Pong! Server is alive!']);
});

// 🔄 [本地開發專用] 模擬上傳模式 (避開 Docker SSL 崩潰問題)
Route::post('/upload-image', function (Request $request) {
    try {
        if (!$request->hasFile('image')) {
            return response()->json(['status' => 'error', 'msg' => '沒有收到檔案'], 400);
        }

        // 回傳隨機假圖，避開 Docker 崩潰問題
        $mockImages = [
            'https://images.unsplash.com/photo-1544947950-fa07a98d237f?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=400&q=80',
        ];
        
        return response()->json([
            'status' => 'success',
            'msg' => '上傳成功', 
            'url' => $mockImages[array_rand($mockImages)]
        ]);

    } catch (\Throwable $e) {
        return response()->json(['status' => 'fatal', 'msg' => '錯誤：' . $e->getMessage()], 500);
    }
});

// 其他原本的路由...
Route::get('/books/search', [\App\Http\Controllers\BookController::class, 'search']);
Route::get('/books', [BookController::class, 'index']);
Route::get('/books/{id}', [BookController::class, 'show']);
Route::get('/guest/search', [SearchController::class, 'search']);

Route::get('/authors', [AuthorController::class, 'index']);
Route::get('/authors/{id}', [AuthorController::class, 'show']);
Route::get('/categories', [BookCategoryController::class, 'index']);
Route::get('/categories/{id}', [BookCategoryController::class, 'show']);
Route::get('/books/{bookId}/reviews', [ReviewController::class, 'getBookReviews']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::prefix('admin')->group(function () {
    Route::post('/login', [AdminAuthController::class, 'login']);
});

// 2. 前台用戶受保護 API
Route::middleware(['auth:sanctum', 'check.blacklist'])->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/user/password', [\App\Http\Controllers\UserController::class, 'changePassword']);

    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/items', [CartController::class, 'addItem']);
    Route::put('/cart/items/{id}', [CartController::class, 'updateItem']);
    Route::delete('/cart/items/{id}', [CartController::class, 'removeItem']);
    Route::delete('/cart/clear', [CartController::class, 'clear']);

    Route::post('/cart/checkout', [OrderController::class, 'checkout']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);

    Route::get('/search/history', [SearchController::class, 'history']);
    Route::get('/user/search', [SearchController::class, 'search']);
    Route::post('/reviews', [ReviewController::class, 'store']);

    Route::post('/complains', [ComplainUserController::class, 'store']);
    Route::get('/complains', [ComplainUserController::class, 'index']);
    Route::get('/coupons/validate/{code}', [CouponController::class, 'validateCode']);
    Route::get('/coupons/business/{businessId}', [CouponController::class, 'getBusinessCoupons']);

    Route::post('/books', [BookController::class, 'store']);
    Route::put('/books/{id}', [BookController::class, 'update']);
    Route::delete('/books/{id}', [BookController::class, 'destroy']);
    Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);
    Route::post('/coupons', [CouponController::class, 'store']);
    Route::put('/coupons/{id}',[CouponController::class, 'update']);

    // 原本的上傳路由 (暫時移除，避免干擾)
    // Route::post('/upload-image', ...); 

    Route::post('/books/{book_id}/images', [ImageController::class, 'store']);
    Route::delete('/images/{image_id}', [ImageController::class, 'destroy']);
    Route::patch('/images/reorder', [ImageController::class, 'reorder']);
    Route::get('/user/profile', [UserController::class, 'show']);
    Route::put('/user/profile', [UserController::class, 'update']);

    Route::get('/my-books', [BookController::class, 'myBooks']);
    Route::put('/books/{id}', [BookController::class, 'update']);
    Route::get('/vendor/orders', [\App\Http\Controllers\OrderController::class, 'sellerSales']);
    Route::post('/vendor/register', [\App\Http\Controllers\AuthController::class, 'registerVendor']);
});

// 3. 後台管理員受保護 API
Route::prefix('admin')->middleware(['auth:sanctum', 'abilities:admin:all'])->group(function () {
    Route::post('/logout', [AdminAuthController::class, 'logout']);
    Route::get('/me', [AdminAuthController::class, 'me']); 
    Route::get('/blacklist', [BlackListController::class, 'index']);
    Route::post('/ban-user', [BlackListController::class, 'banUser']);
    Route::delete('/unban-user/{user_id}', [BlackListController::class, 'unbanUser']);
    Route::prefix('reports')->group(function () {
        Route::get('/', [ReportController::class, 'index']);
        Route::post('/', [ReportController::class, 'store']);
        Route::get('/{id}', [ReportController::class, 'show']);
        Route::delete('/{id}', [ReportController::class, 'destroy']);
    });
    Route::patch('/complains/{id}/in-progress', [ComplainAdminController::class, 'markAsInProgress']);
    Route::put('/complains/{id}/resolve', [ComplainAdminController::class, 'resolveComplain']); 
    Route::post('/authors', [AuthorController::class, 'store']);
    Route::put('/authors/{id}', [AuthorController::class, 'update']);
    Route::delete('/authors/{id}', [AuthorController::class, 'destroy']); 
    Route::post('/categories', [BookCategoryController::class, 'store']);
    Route::put('/categories/{id}', [BookCategoryController::class, 'update']);
    Route::delete('/categories/{id}', [BookCategoryController::class, 'destroy']);
    Route::delete('/books/{id}', [BookController::class, 'destroy']);
});