<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class Controller
{
    /**
     * 統一的 JSON 響應方法，確保使用 UTF-8 編碼
     *
     * @param mixed $data
     * @param int $status
     * @param array $headers
     * @param int $options
     * @return JsonResponse
     */
    protected function jsonResponse($data = null, int $status = 200, array $headers = [], int $options = 0): JsonResponse
    {
        // 確保使用 UTF-8 編碼，並保持中文字符不被轉義
        $options = $options | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        
        return response()->json($data, $status, $headers, $options)
            ->header('Content-Type', 'application/json; charset=utf-8');
    }
}
