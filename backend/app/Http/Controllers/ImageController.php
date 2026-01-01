<?php

namespace App\Http\Controllers;
use App\Models\Image;
use App\Models\Book;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ImageController extends Controller
{
    /**
     * Business 上傳書籍圖片
     */
    public function store(Request $request, $book_id)
    {
        $book = Book::findOrFail($book_id);
        $currentUser = $request->user();
        // 1. 檢查是否為該書籍的擁有者 (Business)
        // 這裡判斷登入者 ID 是否等於書籍的 business_id
        if (!$currentUser->business || $book->business_id !== $currentUser->business->business_id) {
            return response()->json(['message' => '您無權為此書籍上傳圖片'], 403);
        }
        $request->validate([
            'image' => 'required|image|max:5120',
        ]);
        $result = Cloudinary::upload($request->file('image')->getRealPath(), [
            'folder' => "books/business_{$book->business_id}/book_{$book_id}",
        ]);
        $image = Image::create([
            'book_id' => $book_id,
            'image_url' => $result->getSecurePath(),
            'image_index' => Image::where('book_id', $book_id)->max('image_index') + 1,
        ]);
        return response()->json(['message' => '上傳成功', 'data' => $image], 201);
    }
    public function destroy(Request $request, $image_id)
    {
        $image = Image::with('book')->findOrFail($image_id);
        $currentUser = $request->user();
        if (!$currentUser) {
            return response()->json(['message' => '未登入'], 401);
        }
        $isAdmin = $currentUser instanceof \App\Models\Admin;
        $isOwner = $currentUser->business && 
                   ($image->book->business_id === $currentUser->business->business_id);
        if (!$isAdmin && !$isOwner) {
            return response()->json(['message' => '您無權刪除此圖片'], 403);
        }
        DB::transaction(function () use ($image) {
            $bookId = $image->book_id;
            $currentIndex = $image->image_index;

            // 1. 執行刪除
            $image->delete();

            // 2. 將所有 index 大於被刪除圖片的紀錄，全部減 1
            // 例如：原本 4->3, 5->4, 6->5
            Image::where('book_id', $bookId)
                ->where('image_index', '>', $currentIndex)
                ->decrement('image_index');
        });
        return response()->json(['message' => '圖片已成功刪除']);
    }
    public function reorder(Request $request)
    {
        $request->validate([
            'book_id'   => 'required|exists:books,book_id',
            'image_id'  => 'required|exists:images,image_id',
            'new_index' => 'required|integer|min:1',
        ]);

        $bookId   = $request->book_id;
        $imageId  = $request->image_id;
        $newIndex = $request->new_index;

        $targetImage = Image::where('book_id', $bookId)->findOrFail($imageId);
        $oldIndex    = $targetImage->image_index;

        if ($oldIndex == $newIndex) {
            return response()->json(['message' => '索引未變動']);
        }

        DB::transaction(function () use ($bookId, $targetImage, $oldIndex, $newIndex) {
            // 1. 先把目標圖片移到 -1 (暫存區)
            $targetImage->update(['image_index' => -1]);

            // 2. 執行其他圖片的位移 (加入 orderBy 防止撞車)
            if ($newIndex < $oldIndex) {
                // 往前移 (例如 4 -> 2)：區間 [2, 3] 的圖片都要 +1 (變 [3, 4])
                // [修正] 必須「倒著改」(3->4, 再 2->3)，否則 2->3 會撞到原本的 3
                Image::where('book_id', $bookId)
                    ->whereBetween('image_index', [$newIndex, $oldIndex - 1])
                    ->orderBy('image_index', 'desc') // 關鍵：降冪更新
                    ->increment('image_index');
            } else {
                // 往後移 (例如 2 -> 4)：區間 [3, 4] 的圖片都要 -1 (變 [2, 3])
                // [修正] 必須「順著改」(3->2, 再 4->3)，否則 4->3 會撞到原本的 3
                Image::where('book_id', $bookId)
                    ->whereBetween('image_index', [$oldIndex + 1, $newIndex])
                    ->orderBy('image_index', 'asc')  // 關鍵：升冪更新
                    ->decrement('image_index');
            }

            // 3. 最後把目標圖片從 -1 移到正確的新位置
            $targetImage->update(['image_index' => $newIndex]);
        });

        return response()->json(['message' => '重新排序完成']);
    }
}