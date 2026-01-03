<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ImageUploadController extends Controller
{
    public function upload(Request $request)
    {
        // 1. 驗證檔案
        $request->validate([
            'image' => 'required|image|max:2048', // 最大 2MB
        ]);

        try {
            // 2. 取得暫存路徑
            $realPath = $request->file('image')->getRealPath();

            // 3. 上傳到 Cloudinary (並略過 SSL 驗證，防止 Docker 報錯)
            $result = Cloudinary::upload($realPath, [
                'folder' => 'bookify_products',
                'verify' => false 
            ]);

            // 4. 回傳成功網址
            return response()->json([
                'url' => $result->getSecurePath(),
                'message' => '上傳成功'
            ]);

        } catch (\Throwable $e) {
            // 5. 如果失敗，回傳詳細原因
            return response()->json([
                'message' => '上傳失敗',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}