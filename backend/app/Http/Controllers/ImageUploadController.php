<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ImageUploadController extends Controller
{
    /**
     * 單純上傳圖片，回傳網址
     * 不需要 Book ID，因為書還沒建立
     */
    public function upload(Request $request)
    {
        // 1. 驗證是否有檔案
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // 限制 2MB
        ]);

        try {
            // 2. 上傳到 Cloudinary (暫存在 temp 資料夾，或者直接上傳到根目錄)
            // getRealPath() 是取得暫存檔案的路徑
            $uploadedFileUrl = Cloudinary::upload($request->file('image')->getRealPath())->getSecurePath();

            // 3. 回傳圖片網址給前端
            return response()->json([
                'url' => $uploadedFileUrl,
                'message' => '上傳成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => '上傳失敗',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}