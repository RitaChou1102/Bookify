<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureJsonEncoding
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // 如果是 JSON 響應，確保使用 UTF-8 編碼
        if ($response instanceof \Illuminate\Http\JsonResponse) {
            // 設置 Content-Type header，確保包含 charset=utf-8
            $response->header('Content-Type', 'application/json; charset=utf-8');
            
            // 獲取當前的編碼選項，並添加 UTF-8 支持
            $currentOptions = $response->getEncodingOptions();
            $response->setEncodingOptions(
                $currentOptions | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
        }

        return $response;
    }
}

